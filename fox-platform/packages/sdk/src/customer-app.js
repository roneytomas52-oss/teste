import {
  getCustomerOrderDetail,
  getCustomerOrders,
  getCustomerProfile,
  getSession,
  login,
  logout,
  registerCustomer,
  requireSession,
  setSession,
  updateCustomerProfile
} from "./fox-platform-sdk.js";

function setText(selector, value) {
  const target = document.querySelector(selector);
  if (target) {
    target.textContent = value ?? "";
  }
}

function setInputValue(selector, value) {
  const target = document.querySelector(selector);
  if (target) {
    target.value = value ?? "";
  }
}

function showFeedback(selector, message, tone = "success", useHtml = false) {
  const target = document.querySelector(selector);
  if (!target) return;

  target.hidden = false;
  target.dataset.tone = tone;

  if (useHtml) {
    target.innerHTML = message;
    return;
  }

  target.textContent = message;
}

function hideFeedback(selector) {
  const target = document.querySelector(selector);
  if (!target) return;

  target.hidden = true;
  target.textContent = "";
  delete target.dataset.tone;
}

function formatDateTime(value) {
  if (!value || value === "-") return "-";

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) {
    return value;
  }

  return parsed.toLocaleString("pt-BR");
}

function getCustomerSession() {
  const session = getSession();
  if (!session) return null;

  const guard = session.guard || session.role;
  return guard === "customer" ? session : null;
}

function syncCustomerSession(profile) {
  const session = getCustomerSession();
  if (!session || !profile) return;

  setSession({
    ...session,
    name: profile.full_name,
    email: profile.email,
    accountLabel: profile.full_name
  });
}

function renderCustomerActions() {
  const target = document.querySelector("#fx-public-auth-actions");
  if (!target) return;

  const session = getCustomerSession();
  if (session) {
    target.innerHTML = `
      <a class="fx-button-ghost" href="./my-orders.html">Meus pedidos</a>
      <a class="fx-button-secondary" href="./account.html">Minha conta</a>
      <button class="fx-button" type="button" id="fx-customer-logout">Sair</button>
    `;

    document.querySelector("#fx-customer-logout")?.addEventListener("click", async () => {
      await logout();
      window.location.href = "./index.html";
    });
    return;
  }

  target.innerHTML = `
    <a class="fx-button-ghost" href="./customer-login.html">Entrar</a>
    <a class="fx-button-secondary" href="./customer-register.html">Criar conta</a>
    <a class="fx-button" href="./stores.html">Pedir agora</a>
  `;
}

async function handleLoginScreen() {
  if (getCustomerSession()) {
    window.location.href = "./account.html";
    return;
  }

  const form = document.querySelector("#fx-customer-login-form");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    hideFeedback("#fx-customer-login-feedback");

    const email = document.querySelector("#fx-customer-login-email")?.value?.trim() || "";
    const password = document.querySelector("#fx-customer-login-password")?.value || "";

    try {
      await login("customer", email, password);
      window.location.href = "./account.html";
    } catch (error) {
      showFeedback(
        "#fx-customer-login-feedback",
        error?.message || "Nao foi possivel entrar com a conta do cliente.",
        "danger"
      );
    }
  });
}

async function handleRegisterScreen() {
  if (getCustomerSession()) {
    window.location.href = "./account.html";
    return;
  }

  const form = document.querySelector("#fx-customer-register-form");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    hideFeedback("#fx-customer-register-feedback");

    const payload = {
      full_name: document.querySelector("#fx-customer-register-full-name")?.value?.trim() || "",
      email: document.querySelector("#fx-customer-register-email")?.value?.trim() || "",
      phone: document.querySelector("#fx-customer-register-phone")?.value?.trim() || "",
      password: document.querySelector("#fx-customer-register-password")?.value || "",
      city: document.querySelector("#fx-customer-register-city")?.value?.trim() || "",
      state: document.querySelector("#fx-customer-register-state")?.value?.trim() || "",
      marketing_opt_in: Boolean(document.querySelector("#fx-customer-register-marketing")?.checked)
    };

    try {
      await registerCustomer(payload);
      await login("customer", payload.email, payload.password);
      window.location.href = "./account.html";
    } catch (error) {
      showFeedback(
        "#fx-customer-register-feedback",
        error?.message || "Nao foi possivel criar a conta do cliente.",
        "danger"
      );
    }
  });
}

function renderCustomerOrderCards(payload) {
  const summary = document.querySelector("#fx-customer-orders-summary");
  const list = document.querySelector("#fx-customer-orders-list");
  const items = payload?.items || [];

  if (summary) {
    summary.innerHTML = (payload?.summary || [])
      .map(
        (item) => `
          <article class="fx-compact-metric">
            <strong>${item.value}</strong>
            <span>${item.label}</span>
          </article>
        `
      )
      .join("");
  }

  if (!list) return;

  if (!items.length) {
    list.innerHTML = `<article class="fx-card"><p class="fx-copy">Nenhum pedido associado a esta conta ainda.</p></article>`;
    return;
  }

  list.innerHTML = items
    .map(
      (item) => `
        <article class="fx-card fx-order-history-card">
          <div class="fx-card-header">
            <div>
              <h3 class="fx-title-sm">${item.order_number}</h3>
              <p class="fx-copy-sm">${item.store_name}</p>
            </div>
            <span class="fx-pill">${item.status}</span>
          </div>
          <div class="fx-store-meta">
            <span class="fx-tag">${item.payment_method}</span>
            <span class="fx-tag">${item.payment_status}</span>
            <span class="fx-tag">${item.total}</span>
          </div>
          <p class="fx-copy-sm">Criado em ${formatDateTime(item.placed_at)}</p>
          <div class="fx-hero-actions">
            <a class="fx-button-secondary" href="./customer-order.html?order=${item.order_id}">Ver detalhes</a>
            <a class="fx-button-ghost" href="./track.html?order=${encodeURIComponent(item.order_number)}">Rastrear</a>
          </div>
        </article>
      `
    )
    .join("");
}

function renderCustomerAccount(profile, ordersPayload) {
  setText("#fx-customer-account-name", profile.full_name || "-");
  setText("#fx-customer-account-email", profile.email || "-");
  setText("#fx-customer-account-phone", profile.phone || "-");
  setText("#fx-customer-account-location", [profile.city, profile.state].filter(Boolean).join(" - ") || "-");

  setInputValue("#fx-customer-account-full-name", profile.full_name);
  setInputValue("#fx-customer-account-email", profile.email);
  setInputValue("#fx-customer-account-phone", profile.phone);
  setInputValue("#fx-customer-account-city", profile.city);
  setInputValue("#fx-customer-account-state", profile.state);

  const marketing = document.querySelector("#fx-customer-account-marketing");
  if (marketing) {
    marketing.checked = Boolean(profile.marketing_opt_in);
  }

  const recent = document.querySelector("#fx-customer-account-recent-orders");
  const recentItems = (ordersPayload?.items || []).slice(0, 3);
  if (recent) {
    recent.innerHTML = recentItems.length
      ? recentItems
          .map(
            (item) => `
              <div class="fx-info-row">
                <strong>${item.order_number}</strong>
                <span>${item.status} · ${item.total}</span>
              </div>
            `
          )
          .join("")
      : `<div class="fx-note">Nenhum pedido recente nesta conta.</div>`;
  }

  const summary = document.querySelector("#fx-customer-account-summary");
  if (summary) {
    summary.innerHTML = (ordersPayload?.summary || [])
      .map(
        (item) => `
          <article class="fx-compact-metric">
            <strong>${item.value}</strong>
            <span>${item.label}</span>
          </article>
        `
      )
      .join("");
  }
}

async function handleAccountScreen() {
  const session = requireSession("customer", "customer");
  if (!session) return;

  const [profile, ordersPayload] = await Promise.all([
    getCustomerProfile(),
    getCustomerOrders()
  ]);
  syncCustomerSession(profile);
  renderCustomerAccount(profile, ordersPayload);

  const form = document.querySelector("#fx-customer-account-form");
  if (!form || form.dataset.bound === "true") return;
  form.dataset.bound = "true";

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    hideFeedback("#fx-customer-account-feedback");

    try {
      const updatedProfile = await updateCustomerProfile({
        full_name: document.querySelector("#fx-customer-account-full-name")?.value?.trim() || "",
        email: document.querySelector("#fx-customer-account-email")?.value?.trim() || "",
        phone: document.querySelector("#fx-customer-account-phone")?.value?.trim() || "",
        city: document.querySelector("#fx-customer-account-city")?.value?.trim() || "",
        state: document.querySelector("#fx-customer-account-state")?.value?.trim() || "",
        marketing_opt_in: Boolean(document.querySelector("#fx-customer-account-marketing")?.checked)
      });

      syncCustomerSession(updatedProfile);
      renderCustomerAccount(updatedProfile, await getCustomerOrders());
      showFeedback("#fx-customer-account-feedback", "Conta atualizada com sucesso.");
    } catch (error) {
      showFeedback(
        "#fx-customer-account-feedback",
        error?.message || "Nao foi possivel atualizar a conta do cliente.",
        "danger"
      );
    }
  });
}

async function handleOrdersScreen() {
  const session = requireSession("customer", "customer");
  if (!session) return;

  const payload = await getCustomerOrders();
  renderCustomerOrderCards(payload);
}

function renderCustomerOrderDetail(payload) {
  const order = payload?.order || null;
  const items = payload?.items || [];
  const timeline = payload?.timeline || [];

  if (!order) {
    showFeedback("#fx-customer-order-feedback", "Pedido nao encontrado para esta conta.", "danger");
    return;
  }

  setText("#fx-customer-order-number", order.order_number);
  setText("#fx-customer-order-store", order.store_name);
  setText("#fx-customer-order-region", order.store_region);
  setText("#fx-customer-order-status", order.status);
  setText("#fx-customer-order-customer", order.customer_name);
  setText("#fx-customer-order-phone", order.customer_phone);
  setText("#fx-customer-order-email", order.customer_email);
  setText("#fx-customer-order-address", order.customer_address);
  setText("#fx-customer-order-payment-method", order.payment_method);
  setText("#fx-customer-order-payment-status", order.payment_status);
  setText("#fx-customer-order-subtotal", order.subtotal);
  setText("#fx-customer-order-delivery-fee", order.delivery_fee);
  setText("#fx-customer-order-total", order.total);
  setText("#fx-customer-order-placed-at", formatDateTime(order.placed_at));
  setText("#fx-customer-order-progress", order.progress_label);

  const statusBadge = document.querySelector("#fx-customer-order-status");
  if (statusBadge) {
    statusBadge.className = "fx-pill";
  }

  const itemsContainer = document.querySelector("#fx-customer-order-items");
  if (itemsContainer) {
    itemsContainer.innerHTML = items.length
      ? items
          .map(
            (item) => `
              <article class="fx-card fx-track-item">
                <div class="fx-card-header">
                  <h3 class="fx-title-sm">${item.name}</h3>
                  <span class="fx-pill">${item.quantity} un.</span>
                </div>
                <div class="fx-store-meta">
                  <span class="fx-tag">${item.unit_price}</span>
                  <span class="fx-tag">${item.total_price}</span>
                </div>
                <p class="fx-copy-sm">Observacoes: ${item.notes}</p>
              </article>
            `
          )
          .join("")
      : `<article class="fx-card"><p class="fx-copy">Nenhum item registrado neste pedido.</p></article>`;
  }

  const timelineContainer = document.querySelector("#fx-customer-order-timeline");
  if (timelineContainer) {
    timelineContainer.innerHTML = timeline.length
      ? timeline
          .map(
            (item) => `
              <article class="fx-track-timeline-item">
                <div class="fx-track-timeline-head">
                  <strong>${item.title}</strong>
                  <span>${formatDateTime(item.created_at)}</span>
                </div>
                <p class="fx-copy-sm">${item.description}</p>
              </article>
            `
          )
          .join("")
      : `<article class="fx-card"><p class="fx-copy">Ainda nao ha eventos registrados para este pedido.</p></article>`;
  }

  const trackingLink = document.querySelector("#fx-customer-order-track-link");
  if (trackingLink) {
    trackingLink.href = `./track.html?order=${encodeURIComponent(order.order_number)}`;
  }
}

async function handleOrderDetailScreen() {
  const session = requireSession("customer", "customer");
  if (!session) return;

  const params = new URLSearchParams(window.location.search);
  const orderId = params.get("order") || "";
  if (!orderId) {
    showFeedback("#fx-customer-order-feedback", "Pedido nao informado.", "danger");
    return;
  }

  try {
    renderCustomerOrderDetail(await getCustomerOrderDetail(orderId));
    hideFeedback("#fx-customer-order-feedback");
  } catch (error) {
    showFeedback(
      "#fx-customer-order-feedback",
      error?.message || "Nao foi possivel carregar o pedido do cliente.",
      "danger"
    );
  }
}

async function boot() {
  renderCustomerActions();

  const screen = document.body.dataset.fxScreen;
  if (screen === "customer-login") {
    await handleLoginScreen();
    return;
  }

  if (screen === "customer-register") {
    await handleRegisterScreen();
    return;
  }

  if (screen === "customer-account") {
    await handleAccountScreen();
    return;
  }

  if (screen === "customer-orders") {
    await handleOrdersScreen();
    return;
  }

  if (screen === "customer-order-detail") {
    await handleOrderDetailScreen();
  }
}

boot();

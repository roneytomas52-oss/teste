import {
  createCustomerOrder,
  createPublicDriverLead,
  createPublicOrder,
  createPublicPartnerLead,
  getCustomerProfile,
  getPublicCategories,
  getPublicOrderTracking,
  getPublicPlatformMetrics,
  getPublicStoreDetail,
  getPublicStores,
  getSession,
  logout
} from "./fox-platform-sdk.js";

function currency(value) {
  return Number(value || 0).toLocaleString("pt-BR", {
    style: "currency",
    currency: "BRL"
  });
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

function getCustomerSession() {
  const session = getSession();
  if (!session) return null;
  const guard = session.guard || session.role;
  return guard === "customer" ? session : null;
}

function renderPublicAuthActions() {
  const target = document.querySelector("#fx-public-auth-actions");
  if (!target) return;

  const session = getCustomerSession();
  if (session) {
    target.innerHTML = `
      <a class="fx-button-ghost" href="./my-orders.html">Meus pedidos</a>
      <a class="fx-button-secondary" href="./account.html">Minha conta</a>
      <button class="fx-button" type="button" id="fx-customer-logout">Sair</button>
    `;

    const logoutButton = document.querySelector("#fx-customer-logout");
    logoutButton?.addEventListener("click", async () => {
      await logout();
      window.location.href = "./index.html";
    });
    return;
  }

  target.innerHTML = `
    <a class="fx-button-ghost" href="./customer-login.html">Entrar</a>
    <a class="fx-button-secondary" href="./customer-register.html">Criar conta</a>
    <a class="fx-button" href="../../admin/src/login.html">Entrar no painel</a>
  `;
}

function renderCategories(payload) {
  const container = document.querySelector("#fx-landing-categories");
  if (!container) return;

  const items = payload?.items || [];
  if (!items.length) {
    container.innerHTML = `
      <div class="fx-card">
        <p class="fx-copy">Nenhuma categoria pública disponível no momento.</p>
      </div>
    `;
    return;
  }

  container.innerHTML = items
    .map(
      (item) => `
        <article class="fx-card">
          <div class="fx-card-header">
            <h3 class="fx-title-sm">${item.name}</h3>
            <span class="fx-pill">${item.product_count} itens</span>
          </div>
          <p class="fx-copy-sm">${item.description}</p>
          <div class="fx-hero-actions">
            <a class="fx-button-ghost" href="./stores.html?category=${item.slug}">${item.cta}</a>
          </div>
        </article>
      `
    )
    .join("");
}

function renderMetrics(payload) {
  const container = document.querySelector("#fx-landing-proof");
  if (!container) return;

  container.innerHTML = (payload?.items || [])
    .map(
      (item) => `
        <article class="fx-stat">
          <div class="fx-stat-value">${item.value}</div>
          <div class="fx-stat-label">${item.label}</div>
        </article>
      `
    )
    .join("");
}

function renderPublicStores(payload) {
  const container = document.querySelector("#fx-public-stores");
  if (!container) return;

  const items = payload?.items || [];
  if (!items.length) {
    container.innerHTML = `
      <article class="fx-card fx-store-empty">
        <p class="fx-copy">Nenhuma loja encontrada para este filtro.</p>
      </article>
    `;
    return;
  }

  container.innerHTML = items
    .map(
      (item) => `
        <article class="fx-card">
          <div class="fx-card-header">
            <div>
              <h3 class="fx-title-sm">${item.trade_name}</h3>
              <p class="fx-copy-sm">${item.lead}</p>
            </div>
            <span class="fx-pill">${item.primary_category}</span>
          </div>
          <div class="fx-store-meta">
            <span class="fx-tag">${item.city} - ${item.state}</span>
            <span class="fx-tag">${item.product_count} itens</span>
            <span class="fx-tag">${item.completed_orders} concluídos</span>
          </div>
          <div class="fx-hero-actions">
            <a class="fx-button" href="./store.html?store=${item.id}">Abrir loja</a>
          </div>
        </article>
      `
    )
    .join("");
}

function renderPublicStoreDetail(payload) {
  const store = payload?.store || null;
  const products = payload?.products || [];
  if (!store) return;

  const title = document.querySelector("#fx-public-store-name");
  const lead = document.querySelector("#fx-public-store-lead");
  if (title) title.textContent = store.trade_name;
  if (lead) {
    lead.textContent = `${store.lead} ${store.product_count} itens ativos e ${store.completed_orders} pedidos concluídos.`;
  }

  const container = document.querySelector("#fx-public-store-products");
  if (!container) return;

  if (!products.length) {
    container.innerHTML = `
      <article class="fx-card">
        <p class="fx-copy">Esta loja ainda não possui itens ativos no catálogo público.</p>
      </article>
    `;
    return;
  }

  container.innerHTML = products
    .map(
      (product) => `
        <article class="fx-product-card" data-product-id="${product.id}" data-product-price="${product.price_value}">
          <div class="fx-product-grid">
            <div>
              <div class="fx-card-header">
                <div>
                  <h3 class="fx-title-sm">${product.name}</h3>
                  <p class="fx-copy-sm">${product.description}</p>
                </div>
                <span class="fx-pill">${product.category_name}</span>
              </div>
              <div class="fx-store-meta">
                <span class="fx-tag">${product.price}</span>
                <span class="fx-tag">Estoque ${product.stock_quantity}</span>
              </div>
            </div>
            <div class="fx-field">
              <label for="qty-${product.id}">Qtd.</label>
              <input class="fx-qty-input js-public-order-qty" id="qty-${product.id}" type="number" min="0" max="${product.stock_quantity}" value="0" />
            </div>
          </div>
        </article>
      `
    )
    .join("");
}

function renderPublicOrderTracking(payload) {
  const order = payload?.order || null;
  const items = payload?.items || [];
  const timeline = payload?.timeline || [];

  const emptyState = document.querySelector("#fx-public-track-empty");
  const content = document.querySelector("#fx-public-track-content");
  if (!order || !content) {
    if (emptyState) emptyState.hidden = false;
    if (content) content.hidden = true;
    return;
  }

  if (emptyState) emptyState.hidden = true;
  content.hidden = false;

  const summary = document.querySelector("#fx-public-track-summary");
  if (summary) {
    summary.innerHTML = `
      <div class="fx-card-header">
        <div>
          <h2 class="fx-title-sm">Pedido ${order.order_number}</h2>
          <p class="fx-copy-sm">${order.store_name} · ${order.store_region}</p>
        </div>
        <span class="fx-pill">${order.status}</span>
      </div>
      <div class="fx-track-grid">
        <div class="fx-track-stat">
          <span class="fx-tag">Cliente</span>
          <strong>${order.customer_name}</strong>
          <small>${order.customer_phone}</small>
        </div>
        <div class="fx-track-stat">
          <span class="fx-tag">Pagamento</span>
          <strong>${order.payment_method}</strong>
          <small>${order.payment_status}</small>
        </div>
        <div class="fx-track-stat">
          <span class="fx-tag">Total</span>
          <strong>${order.total}</strong>
          <small>Entrega ${order.delivery_fee}</small>
        </div>
      </div>
      <p class="fx-copy">${order.progress_label}</p>
      <p class="fx-copy-sm">Endereço: ${order.customer_address}</p>
    `;
  }

  const itemsContainer = document.querySelector("#fx-public-track-items");
  if (itemsContainer) {
    itemsContainer.innerHTML = items
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
            <p class="fx-copy-sm">Observações: ${item.notes}</p>
          </article>
        `
      )
      .join("");
  }

  const timelineContainer = document.querySelector("#fx-public-track-timeline");
  if (timelineContainer) {
    timelineContainer.innerHTML = timeline.length
      ? timeline
          .map(
            (item) => `
              <article class="fx-track-timeline-item">
                <div class="fx-track-timeline-head">
                  <strong>${item.title}</strong>
                  <span>${item.created_at}</span>
                </div>
                <p class="fx-copy-sm">${item.description}</p>
              </article>
            `
          )
          .join("")
      : `<article class="fx-card"><p class="fx-copy">Ainda não há atualizações registradas para este pedido.</p></article>`;
  }
}

function bindPartnerLeadForm() {
  const form = document.querySelector("#fx-partner-lead-form");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const payload = {
      company_name: document.querySelector("#fx-partner-company-name")?.value ?? "",
      contact_name: document.querySelector("#fx-partner-contact-name")?.value ?? "",
      email: document.querySelector("#fx-partner-email")?.value ?? "",
      phone: document.querySelector("#fx-partner-phone")?.value ?? "",
      city: document.querySelector("#fx-partner-city")?.value ?? "",
      business_type: document.querySelector("#fx-partner-business-type")?.value ?? "restaurant"
    };

    try {
      const response = await createPublicPartnerLead(payload);
      form.reset();
      showFeedback(
        "#fx-partner-lead-feedback",
        `Solicitação recebida. Protocolo ${response.protocol}. ${response.next_step}`
      );
    } catch (error) {
      showFeedback(
        "#fx-partner-lead-feedback",
        error?.message || "Não foi possível registrar o interesse do parceiro.",
        "danger"
      );
    }
  });
}

function bindDriverLeadForm() {
  const form = document.querySelector("#fx-driver-lead-form");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const payload = {
      full_name: document.querySelector("#fx-driver-full-name")?.value ?? "",
      email: document.querySelector("#fx-driver-email")?.value ?? "",
      phone: document.querySelector("#fx-driver-phone")?.value ?? "",
      city: document.querySelector("#fx-driver-city")?.value ?? "",
      modal: document.querySelector("#fx-driver-modal")?.value ?? "motorcycle"
    };

    try {
      const response = await createPublicDriverLead(payload);
      form.reset();
      showFeedback(
        "#fx-driver-lead-feedback",
        `Solicitação recebida. Protocolo ${response.protocol}. ${response.next_step}`
      );
    } catch (error) {
      showFeedback(
        "#fx-driver-lead-feedback",
        error?.message || "Não foi possível registrar o interesse do entregador.",
        "danger"
      );
    }
  });
}

async function bootStoresScreen() {
  const params = new URLSearchParams(window.location.search);
  const filters = {
    search: params.get("search") || "",
    city: params.get("city") || "",
    category: params.get("category") || ""
  };

  const searchInput = document.querySelector("#fx-public-store-search");
  const cityInput = document.querySelector("#fx-public-store-city");
  const categoryInput = document.querySelector("#fx-public-store-category");
  if (searchInput) searchInput.value = filters.search;
  if (cityInput) cityInput.value = filters.city;
  if (categoryInput) categoryInput.value = filters.category;

  renderPublicStores(await getPublicStores(filters));

  const form = document.querySelector("#fx-public-store-filters");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const nextFilters = {
      search: searchInput?.value?.trim() || "",
      city: cityInput?.value?.trim() || "",
      category: categoryInput?.value || ""
    };

    const query = new URLSearchParams();
    Object.entries(nextFilters).forEach(([key, value]) => {
      if (value) query.set(key, value);
    });

    window.history.replaceState({}, "", `./stores.html${query.toString() ? `?${query.toString()}` : ""}`);
    renderPublicStores(await getPublicStores(nextFilters));
  });
}

function selectedOrderItems() {
  return Array.from(document.querySelectorAll(".js-public-order-qty"))
    .map((input) => {
      const quantity = Number(input.value || 0);
      const card = input.closest("[data-product-id]");

      return {
        product_id: card?.dataset.productId || "",
        quantity,
        notes: ""
      };
    })
    .filter((item) => item.product_id && item.quantity > 0);
}

function refreshOrderSummary() {
  const summary = document.querySelector("#fx-public-order-summary");
  if (!summary) return;

  const items = selectedOrderItems();
  if (!items.length) {
    summary.textContent = "Nenhum item selecionado.";
    return;
  }

  const total = items.reduce((carry, item) => {
    const card = document.querySelector(`[data-product-id="${item.product_id}"]`);
    return carry + Number(card?.dataset.productPrice || 0) * item.quantity;
  }, 0);

  summary.textContent = `${items.length} item(ns) selecionado(s) · Subtotal ${currency(total)}`;
}

async function bootStoreDetailScreen() {
  const params = new URLSearchParams(window.location.search);
  const storeId = params.get("store") || "";

  if (!storeId) {
    const container = document.querySelector("#fx-public-store-products");
    if (container) {
      container.innerHTML = `
        <article class="fx-card">
          <p class="fx-copy">Loja não informada.</p>
        </article>
      `;
    }
    return;
  }

  const detail = await getPublicStoreDetail(storeId);
  renderPublicStoreDetail(detail);
  refreshOrderSummary();

  const accountState = document.querySelector("#fx-public-order-account-state");
  const customerSession = getCustomerSession();
  if (customerSession) {
    try {
      const profile = await getCustomerProfile();
      const nameInput = document.querySelector("#fx-public-order-customer-name");
      const phoneInput = document.querySelector("#fx-public-order-customer-phone");

      if (nameInput) {
        nameInput.value = profile.full_name || "";
        nameInput.disabled = true;
      }

      if (phoneInput) {
        phoneInput.value = profile.phone || "";
        phoneInput.disabled = true;
      }

      if (accountState) {
        accountState.hidden = false;
        accountState.dataset.tone = "success";
        accountState.innerHTML = `Pedido vinculado a <strong>${profile.full_name}</strong>. O historico ficara disponivel na sua conta.`;
      }
    } catch (error) {
      if (accountState) {
        accountState.hidden = false;
        accountState.dataset.tone = "danger";
        accountState.textContent = error?.message || "Nao foi possivel carregar a conta do cliente.";
      }
    }
  } else if (accountState) {
    accountState.hidden = false;
    accountState.dataset.tone = "warning";
    accountState.innerHTML = `Voce pode pedir como visitante ou <a href="./customer-login.html">entrar na sua conta</a> para guardar historico e dados.`;
  }

  document.querySelectorAll(".js-public-order-qty").forEach((input) => {
    input.addEventListener("input", refreshOrderSummary);
  });

  const form = document.querySelector("#fx-public-order-form");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const items = selectedOrderItems();
    if (!items.length) {
      showFeedback("#fx-public-order-feedback", "Selecione ao menos um item para criar o pedido.", "danger");
      return;
    }

    try {
      const payload = {
        store_id: storeId,
        customer_address: document.querySelector("#fx-public-order-customer-address")?.value ?? "",
        payment_method: document.querySelector("#fx-public-order-payment-method")?.value ?? "online_card",
        items
      };
      const order = customerSession
        ? await createCustomerOrder(payload)
        : await createPublicOrder({
            ...payload,
            customer_name: document.querySelector("#fx-public-order-customer-name")?.value ?? "",
            customer_phone: document.querySelector("#fx-public-order-customer-phone")?.value ?? ""
          });

      showFeedback(
        "#fx-public-order-feedback",
        customerSession
          ? `Pedido <strong>${order.order_number}</strong> criado com sucesso. Total ${order.total}. ${order.next_step} <a href="./customer-order.html?order=${order.order_id}">Ver pedido</a>.`
          : `Pedido <strong>${order.order_number}</strong> criado com sucesso. Total ${order.total}. ${order.next_step} <a href="./track.html?order=${order.order_number}">Acompanhar pedido</a>.`,
        "success",
        true
      );
      form.reset();
      document.querySelectorAll(".js-public-order-qty").forEach((input) => {
        input.value = "0";
      });
      refreshOrderSummary();
    } catch (error) {
      showFeedback(
        "#fx-public-order-feedback",
        error?.message || "Não foi possível criar o pedido.",
        "danger"
      );
    }
  });
}

async function loadTracking(orderNumber) {
  const payload = await getPublicOrderTracking(orderNumber);
  renderPublicOrderTracking(payload);
}

async function bootTrackScreen() {
  const params = new URLSearchParams(window.location.search);
  const orderInput = document.querySelector("#fx-public-track-order");
  const form = document.querySelector("#fx-public-track-form");
  const initialOrderNumber = params.get("order") || "";

  if (orderInput) {
    orderInput.value = initialOrderNumber;
  }

  if (initialOrderNumber) {
    try {
      await loadTracking(initialOrderNumber);
    } catch (error) {
      showFeedback(
        "#fx-public-track-feedback",
        error?.message || "Não foi possível localizar o pedido informado.",
        "danger"
      );
    }
  }

  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const orderNumber = orderInput?.value?.trim() || "";
    if (!orderNumber) {
      showFeedback("#fx-public-track-feedback", "Informe o número do pedido.", "danger");
      return;
    }

    try {
      window.history.replaceState({}, "", `./track.html?order=${encodeURIComponent(orderNumber)}`);
      showFeedback("#fx-public-track-feedback", "Consulta realizada com sucesso.");
      await loadTracking(orderNumber);
    } catch (error) {
      showFeedback(
        "#fx-public-track-feedback",
        error?.message || "Não foi possível localizar o pedido informado.",
        "danger"
      );
      renderPublicOrderTracking(null);
    }
  });
}

async function boot() {
  const screen = document.body.dataset.fxScreen || document.querySelector("main")?.dataset.fxScreen;
  renderPublicAuthActions();

  if (screen === "landing-home") {
    renderCategories(await getPublicCategories());
    renderMetrics(await getPublicPlatformMetrics());
    bindPartnerLeadForm();
    bindDriverLeadForm();
    return;
  }

  if (screen === "landing-stores") {
    await bootStoresScreen();
    return;
  }

  if (screen === "landing-store") {
    await bootStoreDetailScreen();
    return;
  }

  if (screen === "landing-track") {
    await bootTrackScreen();
  }
}

boot();

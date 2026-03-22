import {
  approveAdminDriver,
  approveAdminPartner,
  bindLogout,
  getAdminDashboard,
  getAdminOrderDetail,
  getAdminData,
  getAdminFinance,
  getAdminDriverApprovals,
  getAdminOrders,
  getAdminPartnerApprovals,
  getAdminSettings,
  getAdminSupport,
  login,
  rejectAdminDriver,
  rejectAdminPartner,
  requireSession,
  updateAdminSettings
} from "./fox-platform-sdk.js";

function setText(selector, value) {
  const target = document.querySelector(selector);
  if (target && value !== undefined) {
    target.textContent = value;
  }
}

function renderSummary(items) {
  document.querySelectorAll(".fx-hero-panel .fx-mini-kpi").forEach((element, index) => {
    const current = items[index];
    if (!current) return;
    const strong = element.querySelector("strong");
    const span = element.querySelector("span");
    if (strong) strong.textContent = current.value;
    if (span) span.textContent = current.label;
  });
}

function renderMetrics(items) {
  document.querySelectorAll(".fx-compact-metric").forEach((element, index) => {
    const current = items[index];
    if (!current) return;
    const strong = element.querySelector("strong");
    const span = element.querySelector("span");
    if (strong) strong.textContent = current.value;
    if (span) span.textContent = current.label;
  });
}

function renderFinance(finance) {
  setText(".fx-balance-value", finance.balance);
  setText(".fx-balance-card .fx-copy", finance.balanceNote);
  document.querySelectorAll(".fx-stat").forEach((element, index) => {
    const current = finance.stats[index];
    if (!current) return;
    const value = element.querySelector(".fx-stat-value");
    const label = element.querySelector(".fx-stat-label");
    if (value) value.textContent = current.value;
    if (label) label.textContent = current.label;
  });

  const highlights = document.querySelector("#fx-admin-finance-highlights");
  if (highlights) {
    highlights.innerHTML = (finance.highlights || [])
      .map(
        (item) => `
          <article class="fx-finance-card">
            <h3>${item.title}</h3>
            <p class="fx-copy-sm">${item.text}</p>
            <div class="fx-finance-meta">
              ${(item.meta || []).map((meta) => `<span class="fx-tag">${meta}</span>`).join("")}
            </div>
            <div class="fx-inline-actions">
              <a class="${item.action_tone === "secondary" ? "fx-button-secondary" : "fx-button"}" href="#">${item.action_label}</a>
            </div>
          </article>
        `
      )
      .join("");
  }

  const payouts = document.querySelector("#fx-admin-finance-payouts");
  if (payouts) {
    payouts.innerHTML = (finance.payouts || [])
      .map(
        (item) => `
          <tr>
            <td>${item.partner}</td>
            <td>${item.period}</td>
            <td><span class="fx-status ${item.status_type || "warning"}">${item.status}</span></td>
            <td>${item.net_amount}</td>
            <td>${item.note}</td>
          </tr>
        `
      )
      .join("");
  }
}

function renderAnalytics(analytics) {
  document.querySelectorAll(".fx-analytics-card").forEach((element, index) => {
    const current = analytics.cards[index];
    if (!current) return;
    const value = element.querySelector("strong");
    const label = element.querySelector(".fx-copy-sm");
    if (value) value.textContent = current.value;
    if (label) label.textContent = current.label;
  });
}

function renderSupport(data) {
  const count = document.querySelector("#fx-admin-support-count");
  const stream = document.querySelector("#fx-admin-support-stream");
  const distribution = document.querySelector("#fx-admin-support-distribution");
  const sla = document.querySelector("#fx-admin-support-sla");

  if (count) {
    count.textContent = `${(data.priorityQueue || []).length} protocolos`;
  }

  if (stream) {
    const queue = data.priorityQueue || [];
    stream.innerHTML = queue.length
      ? queue
    .map(
      (item) => `
        <div class="fx-activity-row">
          <div class="fx-activity-dot"></div>
          <div>
            <strong>${item.title}</strong>
            <p class="fx-copy-sm">${item.summary}</p>
          </div>
          <span class="fx-status ${item.statusType}">${item.status}</span>
        </div>
      `
    )
    .join("")
      : `<div class="fx-note">Nenhum protocolo prioritario aberto no momento.</div>`;
  }

  if (distribution) {
    distribution.innerHTML = (data.distribution || [])
      .map(
        (item) => `
          <div class="fx-info-row">
            <strong>${item.label}</strong>
            <span>${item.value}</span>
          </div>
        `
      )
      .join("");
  }

  if (sla) {
    sla.innerHTML = (data.sla || []).map((item) => `<li>${item}</li>`).join("");
  }
}

function renderSettings(settings) {
  setText("#fx-settings-platform-name", settings.branding?.platform_name || "Fox Delivery");
  setText("#fx-settings-support-email", settings.branding?.support_email || "-");
  setText("#fx-settings-partner-login-url", settings.branding?.partner_login_url || "-");

  const fields = [
    ["#fx-settings-platform-name-input", settings.branding?.platform_name],
    ["#fx-settings-support-email-input", settings.branding?.support_email],
    ["#fx-settings-partner-login-url-input", settings.branding?.partner_login_url],
    ["#fx-settings-default-order-sla-input", settings.operations?.default_order_sla_minutes],
    ["#fx-settings-partner-review-window-input", settings.operations?.partner_review_window_hours],
    ["#fx-settings-driver-review-window-input", settings.operations?.driver_review_window_hours],
    ["#fx-settings-partner-polling-input", settings.notifications?.partner_polling_seconds],
    ["#fx-settings-driver-polling-input", settings.notifications?.driver_polling_seconds],
    ["#fx-settings-access-token-ttl-input", settings.security?.access_token_ttl_minutes],
    ["#fx-settings-refresh-token-ttl-input", settings.security?.refresh_token_ttl_days],
    ["#fx-settings-reset-token-ttl-input", settings.security?.password_reset_token_ttl_minutes]
  ];

  fields.forEach(([selector, value]) => {
    const field = document.querySelector(selector);
    if (field) {
      field.value = value ?? "";
    }
  });

  const digestToggle = document.querySelector("#fx-settings-admin-digest-input");
  if (digestToggle) {
    digestToggle.checked = Boolean(settings.notifications?.admin_digest_enabled);
  }
}

async function handleSettingsScreen() {
  const form = document.querySelector("#fx-admin-settings-form");
  if (!form) return;

  let settings = await getAdminSettings();
  renderSettings(settings);

  if (form.dataset.bound === "true") return;
  form.dataset.bound = "true";

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      settings = await updateAdminSettings({
        branding: {
          platform_name: document.querySelector("#fx-settings-platform-name-input")?.value?.trim() || "",
          support_email: document.querySelector("#fx-settings-support-email-input")?.value?.trim() || "",
          partner_login_url: document.querySelector("#fx-settings-partner-login-url-input")?.value?.trim() || ""
        },
        operations: {
          default_order_sla_minutes: Number(document.querySelector("#fx-settings-default-order-sla-input")?.value || 0),
          partner_review_window_hours: Number(document.querySelector("#fx-settings-partner-review-window-input")?.value || 0),
          driver_review_window_hours: Number(document.querySelector("#fx-settings-driver-review-window-input")?.value || 0)
        },
        notifications: {
          partner_polling_seconds: Number(document.querySelector("#fx-settings-partner-polling-input")?.value || 0),
          driver_polling_seconds: Number(document.querySelector("#fx-settings-driver-polling-input")?.value || 0),
          admin_digest_enabled: Boolean(document.querySelector("#fx-settings-admin-digest-input")?.checked)
        },
        security: {
          access_token_ttl_minutes: Number(document.querySelector("#fx-settings-access-token-ttl-input")?.value || 0),
          refresh_token_ttl_days: Number(document.querySelector("#fx-settings-refresh-token-ttl-input")?.value || 0),
          password_reset_token_ttl_minutes: Number(document.querySelector("#fx-settings-reset-token-ttl-input")?.value || 0)
        }
      });

      renderSettings(settings);
      const feedback = document.querySelector("#fx-admin-settings-feedback");
      if (feedback) {
        feedback.hidden = false;
        feedback.dataset.tone = "success";
        feedback.textContent = "Configuracoes atualizadas com sucesso.";
      }
    } catch (error) {
      const feedback = document.querySelector("#fx-admin-settings-feedback");
      if (feedback) {
        feedback.hidden = false;
        feedback.dataset.tone = "danger";
        feedback.textContent = error?.message || "Nao foi possivel salvar as configuracoes da plataforma.";
      }
    }
  });
}

function renderAudit(data) {
  const stream = document.querySelector(".fx-stream");
  if (!stream) return;
  stream.innerHTML = data.events
    .map(
      (item) => `
        <div class="fx-activity-row">
          <div class="fx-activity-dot"></div>
          <div>
            <strong>${item.title}</strong>
            <p class="fx-copy-sm">${item.summary}</p>
          </div>
          <span class="fx-copy-sm">${item.time}</span>
        </div>
      `
    )
    .join("");
}

function renderAdminDashboard(data) {
  setText(".fx-hero-content .fx-title-lg", data.heroTitle);
  setText(".fx-hero-content .fx-lead", data.heroLead);
  renderSummary(data.summary || []);
  renderMetrics(data.metrics || []);

  const approvalsTable = document.querySelector("#fx-admin-dashboard-approvals");
  if (approvalsTable) {
    approvalsTable.innerHTML = (data.approvals || [])
      .map(
        (item) => `
          <tr>
            <td>${item.name}</td>
            <td>Parceiro</td>
            <td><span class="fx-status ${item.statusType}">${item.status}</span></td>
            <td>${item.meta?.join(" · ") || "-"}</td>
          </tr>
        `
      )
      .join("");
  }

  const alertsList = document.querySelector("#fx-admin-dashboard-alerts");
  if (alertsList) {
    alertsList.innerHTML = (data.alerts || [])
      .map((item) => `<li>${item}</li>`)
      .join("");
  }
}

function renderAdminOrders(data, query = "", filter = "all") {
  const tbody = document.querySelector("#fx-admin-orders-table-body");
  const summary = document.querySelector("#fx-admin-orders-summary");
  if (!tbody) return;

  const normalizedQuery = query.trim().toLowerCase();
  const items = (data.items || []).filter((item) => {
    const matchesQuery =
      normalizedQuery === "" ||
      item.id.toLowerCase().includes(normalizedQuery) ||
      item.store_name.toLowerCase().includes(normalizedQuery) ||
      item.customer.toLowerCase().includes(normalizedQuery);

    if (!matchesQuery) return false;
    if (filter === "all") return true;
    return item.status_key === filter;
  });

  if (summary) {
    summary.textContent = `${data.totals?.total || items.length} pedidos visiveis, ${data.totals?.critical || 0} em prioridade critica.`;
  }

  if (!items.length) {
    tbody.innerHTML = `<tr><td colspan="7"><div class="fx-note">Nenhum pedido encontrado para este filtro.</div></td></tr>`;
    return;
  }

  tbody.innerHTML = items
    .map(
      (item) => `
        <tr>
          <td>${item.id}</td>
          <td>${item.store_name}</td>
          <td><span class="fx-status ${item.statusType}">${item.status}</span></td>
          <td>${item.sla}</td>
          <td>${item.driver_name}</td>
          <td>${item.value}</td>
          <td><a class="fx-button-secondary" href="./order-detail.html?order=${item.order_id || item.id}">Detalhar</a></td>
        </tr>
      `
    )
    .join("");
}

function renderAdminOrderDetail(data) {
  const order = data.order || {};

  setText("#fx-admin-order-detail-id", order.id || "-");
  setText("#fx-admin-order-detail-status", order.status || "-");
  setText("#fx-admin-order-detail-store", order.store_name || "-");
  setText("#fx-admin-order-detail-customer", order.customer || "-");
  setText("#fx-admin-order-detail-customer-phone", order.customer_phone || "-");
  setText("#fx-admin-order-detail-address", order.customer_address || "-");
  setText("#fx-admin-order-detail-driver", order.driver_name || "-");
  setText("#fx-admin-order-detail-payment-method", order.payment_method || "-");
  setText("#fx-admin-order-detail-payment-status", order.payment_status || "-");
  setText("#fx-admin-order-detail-subtotal", order.subtotal || "-");
  setText("#fx-admin-order-detail-delivery-fee", order.delivery_fee || "-");
  setText("#fx-admin-order-detail-total", order.total || "-");
  setText("#fx-admin-order-detail-placed-at", order.placed_at || "-");
  setText("#fx-admin-order-detail-accepted-at", order.accepted_at || "-");
  setText("#fx-admin-order-detail-completed-at", order.completed_at || "-");
  setText("#fx-admin-order-detail-cancelled-at", order.cancelled_at || "-");
  setText("#fx-admin-order-detail-sla", order.sla || "-");

  const status = document.querySelector("#fx-admin-order-detail-status");
  if (status) {
    status.className = `fx-status ${order.status_type || "warning"}`;
  }

  const items = document.querySelector("#fx-admin-order-detail-items");
  if (items) {
    items.innerHTML = (data.items || []).length
      ? (data.items || []).map((item) => `
          <div class="fx-order-line">
            <div>
              <strong>${item.name}</strong>
              <p class="fx-copy-sm">${item.quantity} unidade(s) · ${item.unit_price}</p>
            </div>
            <div>
              <strong>${item.total_price}</strong>
              <p class="fx-copy-sm">${item.notes}</p>
            </div>
          </div>
        `).join("")
      : `<div class="fx-note">Nenhum item registrado neste pedido.</div>`;
  }

  const timeline = document.querySelector("#fx-admin-order-detail-timeline");
  if (timeline) {
    timeline.innerHTML = (data.timeline || []).length
      ? (data.timeline || []).map((entry) => `
          <div class="fx-order-line">
            <div>
              <strong>${entry.title}</strong>
              <p class="fx-copy-sm">${entry.description}</p>
            </div>
            <div>
              <strong>${entry.actor}</strong>
              <p class="fx-copy-sm">${entry.created_at}</p>
            </div>
          </div>
        `).join("")
      : `<div class="fx-note">Ainda nao existem eventos na linha do tempo deste pedido.</div>`;
  }
}

function getOrderIdFromLocation() {
  const params = new URLSearchParams(window.location.search);
  return params.get("order") || "";
}

function renderApprovalCards(selector, items, scope) {
  const container = document.querySelector(selector);
  if (!container) return;

  if (!items?.length) {
    container.innerHTML = `<div class="fx-note">Nenhum cadastro nesta fila.</div>`;
    return;
  }

  container.innerHTML = items
    .map(
      (item) => `
        <article class="fx-approval-card">
          <h3>${item.name}</h3>
          <p class="fx-copy-sm">${item.summary}</p>
          <div class="fx-approval-meta">
            ${(item.meta || []).map((meta) => `<span class="fx-tag">${meta}</span>`).join("")}
          </div>
          <div class="fx-inline-actions">
            <span class="fx-status ${item.statusType}">${item.status}</span>
            <button class="fx-button-secondary js-approval-action" type="button" data-scope="${scope}" data-decision="reject" data-approval-id="${item.id}">Rejeitar</button>
            <button class="fx-button js-approval-action" type="button" data-scope="${scope}" data-decision="approve" data-approval-id="${item.id}">Aprovar</button>
          </div>
        </article>
      `
    )
    .join("");
}

async function handleApprovalsScreen(scope) {
  const config = scope === "partner"
    ? {
        selector: "#fx-admin-partners-approvals",
        loader: getAdminPartnerApprovals,
        approve: approveAdminPartner,
        reject: rejectAdminPartner
      }
    : {
        selector: "#fx-admin-drivers-approvals",
        loader: getAdminDriverApprovals,
        approve: approveAdminDriver,
        reject: rejectAdminDriver
      };

  let payload = await config.loader();
  renderApprovalCards(config.selector, payload.items || [], scope);

  const container = document.querySelector(config.selector);
  if (!container) return;

  container.addEventListener("click", async (event) => {
    const button = event.target.closest(".js-approval-action");
    if (!button) return;

    button.disabled = true;
    const approvalId = button.dataset.approvalId;
    const decision = button.dataset.decision;

    try {
      payload = decision === "approve"
        ? await config.approve(approvalId)
        : await config.reject(approvalId);

      renderApprovalCards(config.selector, payload.items || [], scope);
    } catch (_error) {
      button.disabled = false;
    }
  });
}

async function handleLogin() {
  const form = document.querySelector("#fx-admin-login-form");
  const error = document.querySelector("#fx-login-error");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    error.hidden = true;
    const email = document.querySelector("#admin-email")?.value ?? "";
    const password = document.querySelector("#admin-password")?.value ?? "";
    try {
      await login("admin", email, password);
      window.location.href = "./index.html";
    } catch (err) {
      error.hidden = false;
      error.textContent = err.message;
    }
  });
}

async function handleOrdersScreen() {
  const search = document.querySelector("#fx-admin-orders-search");
  const chips = document.querySelectorAll(".fx-filter-chip");
  let activeFilter = "all";
  const data = await getAdminOrders();

  const rerender = () => {
    renderAdminOrders(data, search?.value ?? "", activeFilter);
  };

  rerender();
  search?.addEventListener("input", rerender);
  chips.forEach((chip) => {
    chip.addEventListener("click", () => {
      chips.forEach((item) => item.classList.remove("is-active"));
      chip.classList.add("is-active");
      activeFilter = chip.dataset.filter || "all";
      rerender();
    });
  });
}

async function handleOrderDetailScreen() {
  const orderId = getOrderIdFromLocation();
  const feedback = document.querySelector("#fx-admin-order-detail-feedback");

  if (!orderId) {
    if (feedback) {
      feedback.hidden = false;
      feedback.dataset.tone = "danger";
      feedback.textContent = "Pedido nao informado para consulta.";
    }
    return;
  }

  try {
    renderAdminOrderDetail(await getAdminOrderDetail(orderId));
  } catch (error) {
    if (feedback) {
      feedback.hidden = false;
      feedback.dataset.tone = "danger";
      feedback.textContent = error?.message || "Nao foi possivel carregar o pedido.";
    }
  }
}

async function boot() {
  const screen = document.body.dataset.fxScreen;
  if (screen === "login") {
    await handleLogin();
    return;
  }

  const session = requireSession("admin", "admin");
  if (!session) return;
  bindLogout("admin");

  if (screen === "dashboard") {
    renderAdminDashboard(await getAdminDashboard());
    return;
  }

  if (screen === "orders") {
    await handleOrdersScreen();
    return;
  }

  if (screen === "order-detail") {
    await handleOrderDetailScreen();
    return;
  }

  if (screen === "partners-approvals") {
    await handleApprovalsScreen("partner");
    return;
  }

  if (screen === "drivers-approvals") {
    await handleApprovalsScreen("driver");
    return;
  }

  if (screen === "finance") {
    renderFinance(await getAdminFinance());
    return;
  }

  if (screen === "support") {
    renderSupport(await getAdminSupport());
    return;
  }

  if (screen === "settings") {
    await handleSettingsScreen();
    return;
  }

  const data = await getAdminData();

  if (screen === "analytics") {
    renderAnalytics(data.analytics);
  }

  if (screen === "audit") {
    renderAudit(data.audit);
  }
}

boot();

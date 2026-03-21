import {
  bindLogout,
  getAdminDashboard,
  getAdminData,
  getAdminDriverApprovals,
  getAdminOrders,
  getAdminPartnerApprovals,
  login,
  requireSession
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
  const stream = document.querySelector(".fx-stream");
  if (!stream) return;
  stream.innerHTML = data.priorityQueue
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
    .join("");
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
    tbody.innerHTML = `<tr><td colspan="6"><div class="fx-note">Nenhum pedido encontrado para este filtro.</div></td></tr>`;
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
        </tr>
      `
    )
    .join("");
}

function renderApprovalCards(selector, items) {
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
            <a class="fx-button" href="#">${item.action}</a>
          </div>
        </article>
      `
    )
    .join("");
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

  if (screen === "partners-approvals") {
    renderApprovalCards("#fx-admin-partners-approvals", (await getAdminPartnerApprovals()).items || []);
    return;
  }

  if (screen === "drivers-approvals") {
    renderApprovalCards("#fx-admin-drivers-approvals", (await getAdminDriverApprovals()).items || []);
    return;
  }

  const data = await getAdminData();

  if (screen === "finance") {
    renderFinance(data.finance);
  }

  if (screen === "analytics") {
    renderAnalytics(data.analytics);
  }

  if (screen === "support") {
    renderSupport(data.support);
  }

  if (screen === "audit") {
    renderAudit(data.audit);
  }
}

boot();

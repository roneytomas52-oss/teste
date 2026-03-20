import {
  bindLogout,
  getPartnerData,
  injectSessionLabel,
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

function renderOrders(rows) {
  const tbody = document.querySelector(".fx-table-card tbody");
  if (!tbody) return;
  tbody.innerHTML = rows
    .map(
      (row) => `
        <tr>
          <td>${row.id}</td>
          <td>${row.customer}</td>
          <td><span class="fx-status ${row.statusType}">${row.status}</span></td>
          <td>${row.sla}</td>
          <td>${row.value}</td>
          <td><a class="fx-button-ghost" href="#">${row.action}</a></td>
        </tr>
      `
    )
    .join("");
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

function renderReports(reports) {
  document.querySelectorAll(".fx-report-card").forEach((element, index) => {
    const current = reports.cards[index];
    if (!current) return;
    const value = element.querySelector("strong");
    const label = element.querySelector(".fx-copy-sm");
    if (value) value.textContent = current.value;
    if (label) label.textContent = current.label;
  });
}

function renderMessages(messages) {
  const list = document.querySelector(".fx-thread-list");
  if (!list) return;
  list.innerHTML = messages.threads
    .map(
      (thread, index) => `
        <article class="fx-thread-item${index === 0 ? " is-active" : ""}">
          <div class="fx-thread-head">
            <strong>${thread.title}</strong>
            <span class="fx-status ${thread.statusType}">${thread.status}</span>
          </div>
          <p class="fx-copy-sm">${thread.summary}</p>
          <span class="fx-copy-sm">${thread.time}</span>
        </article>
      `
    )
    .join("");
}

function renderSupport(support) {
  const list = document.querySelector(".fx-ticket-list");
  if (!list) return;
  list.innerHTML = support.tickets
    .map(
      (ticket) => `
        <article class="fx-ticket-card">
          <div class="fx-ticket-head">
            <strong>${ticket.id} - ${ticket.channel}</strong>
            <span class="fx-status ${ticket.statusType}">${ticket.status}</span>
          </div>
          <p class="fx-copy-sm">${ticket.summary}</p>
          <div class="fx-inline-actions">
            ${ticket.meta.map((item) => `<span class="fx-tag">${item}</span>`).join("")}
          </div>
        </article>
      `
    )
    .join("");
}

function renderHelp(help) {
  const grid = document.querySelector(".fx-article-grid");
  if (!grid) return;
  grid.innerHTML = help.articles
    .map(
      (article) => `
        <article class="fx-article-card">
          <strong>${article.title}</strong>
          <p class="fx-copy-sm">${article.text}</p>
        </article>
      `
    )
    .join("");
}

async function handleLogin() {
  const form = document.querySelector("#fx-partner-login-form");
  const error = document.querySelector("#fx-login-error");
  if (!form) return;
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    error.hidden = true;
    const email = document.querySelector("#partner-email")?.value ?? "";
    const password = document.querySelector("#partner-password")?.value ?? "";
    try {
      await login("partner", email, password);
      window.location.href = "./index.html";
    } catch (err) {
      error.hidden = false;
      error.textContent = err.message;
    }
  });
}

async function boot() {
  const screen = document.body.dataset.fxScreen;
  if (screen === "login") {
    await handleLogin();
    return;
  }

  const session = requireSession("partner", "partner");
  if (!session) return;
  bindLogout("partner");
  injectSessionLabel(".fx-brand-chip strong", { accountLabel: "Fox Partner" });
  const data = await getPartnerData();

  if (screen === "dashboard") {
    setText(".fx-hero-content .fx-title-lg", data.dashboard.heroTitle);
    setText(".fx-hero-content .fx-lead", data.dashboard.heroLead);
    renderSummary(data.dashboard.summary);
    renderMetrics(data.dashboard.metrics);
  }

  if (screen === "orders") {
    renderOrders(data.orders);
  }

  if (screen === "finance") {
    renderFinance(data.finance);
  }

  if (screen === "reports") {
    renderReports(data.reports);
  }

  if (screen === "messages") {
    renderMessages(data.messages);
  }

  if (screen === "support") {
    renderSupport(data.support);
  }

  if (screen === "help") {
    renderHelp(data.help);
  }
}

boot();

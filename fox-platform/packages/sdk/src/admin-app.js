import { bindLogout, getAdminData, login, requireSession } from "./fox-platform-sdk.js";

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

async function boot() {
  const screen = document.body.dataset.fxScreen;
  if (screen === "login") {
    await handleLogin();
    return;
  }

  const session = requireSession("admin", "admin");
  if (!session) return;
  bindLogout("admin");
  const data = await getAdminData();

  if (screen === "dashboard") {
    setText(".fx-hero-content .fx-title-lg", data.dashboard.heroTitle);
    setText(".fx-hero-content .fx-lead", data.dashboard.heroLead);
    renderSummary(data.dashboard.summary);
    renderMetrics(data.dashboard.metrics);
  }

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

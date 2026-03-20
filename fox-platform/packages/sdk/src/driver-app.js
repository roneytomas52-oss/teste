import { bindLogout, getDriverData, login, requireSession } from "./fox-platform-sdk.js";

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

function renderEarnings(earnings) {
  setText(".fx-balance-value", earnings.balance);
  setText(".fx-balance-card .fx-copy", earnings.balanceNote);
  const list = document.querySelector(".fx-check-card .fx-list");
  if (!list) return;
  list.innerHTML = earnings.stats.map((item) => `<li>${item.value} de ${item.label}.</li>`).join("");
}

function renderSupport(support) {
  const list = document.querySelector(".fx-ticket-stream");
  if (!list) return;
  list.innerHTML = support.tickets
    .map(
      (ticket) => `
        <div class="fx-timeline-item">
          <div>
            <strong>${ticket.id}</strong>
            <p class="fx-copy-sm">${ticket.summary}</p>
          </div>
          <span class="fx-status ${ticket.statusType}">${ticket.status}</span>
        </div>
      `
    )
    .join("");
}

function renderProfile(profile) {
  const fields = document.querySelectorAll(".fx-field input");
  if (fields.length < 6) return;
  fields[0].value = profile.name;
  fields[1].value = profile.email;
  fields[2].value = profile.phone;
  fields[3].value = profile.mode;
  fields[4].value = profile.city;
  fields[5].value = profile.bankAccount;
}

async function handleLogin() {
  const form = document.querySelector("#fx-driver-login-form");
  const error = document.querySelector("#fx-login-error");
  if (!form) return;
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    error.hidden = true;
    const email = document.querySelector("#driver-email")?.value ?? "";
    const password = document.querySelector("#driver-password")?.value ?? "";
    try {
      await login("driver", email, password);
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

  const session = requireSession("driver", "driver");
  if (!session) return;
  bindLogout("driver");
  const data = await getDriverData();

  if (screen === "dashboard") {
    setText(".fx-hero-content .fx-title-lg", data.dashboard.heroTitle);
    setText(".fx-hero-content .fx-lead", data.dashboard.heroLead);
    renderSummary(data.dashboard.summary);
    renderMetrics(data.dashboard.metrics);
  }

  if (screen === "earnings") {
    renderEarnings(data.earnings);
  }

  if (screen === "support") {
    renderSupport(data.support);
  }

  if (screen === "profile") {
    renderProfile(data.profile);
  }
}

boot();

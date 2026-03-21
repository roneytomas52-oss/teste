import {
  bindLogout,
  getDriverAvailability,
  getDriverDashboard,
  getDriverDocuments,
  getDriverEarnings,
  getDriverNotifications,
  getDriverProfile,
  getDriverSupport,
  login,
  markDriverNotificationRead,
  requireSession,
  createDriverSupportTicket,
  updateDriverProfile
} from "./fox-platform-sdk.js";

function setText(selector, value) {
  const target = document.querySelector(selector);
  if (target && value !== undefined) {
    target.textContent = value;
  }
}

function setInputValue(selector, value) {
  const target = document.querySelector(selector);
  if (target) {
    target.value = value ?? "";
  }
}

function showFeedback(selector, message, tone = "success") {
  const target = document.querySelector(selector);
  if (!target) return;
  target.hidden = false;
  target.dataset.tone = tone;
  target.textContent = message;
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

function renderDashboard(dashboard) {
  setText(".fx-hero-content .fx-title-lg", dashboard.heroTitle);
  setText(".fx-hero-content .fx-lead", dashboard.heroLead);
  renderSummary(dashboard.summary || []);
  renderMetrics(dashboard.metrics || []);

  const runs = document.querySelector("#fx-driver-dashboard-runs");
  if (runs) {
    runs.innerHTML = (dashboard.recent_runs || [])
      .map(
        (row) => `
          <tr>
            <td>${row.id}</td>
            <td><span class="fx-status ${row.status_type}">${row.status}</span></td>
            <td>${row.value}</td>
            <td>${row.time}</td>
          </tr>
        `
      )
      .join("");
  }

  const checklist = document.querySelector("#fx-driver-checklist");
  if (checklist) {
    checklist.innerHTML = (dashboard.checklist || []).map((item) => `<li>${item}</li>`).join("");
  }
}

function renderEarnings(earnings) {
  setText(".fx-balance-value", earnings.balance);
  setText(".fx-balance-card .fx-copy", earnings.balanceNote);

  const stats = document.querySelector("#fx-driver-earnings-stats");
  if (stats) {
    stats.innerHTML = (earnings.stats || []).map((item) => `<li>${item.value} de ${item.label}.</li>`).join("");
  }

  const tbody = document.querySelector("#fx-driver-earnings-transactions");
  if (tbody) {
    tbody.innerHTML = (earnings.transactions || [])
      .map(
        (transaction) => `
          <tr>
            <td>${transaction.date}</td>
            <td>${transaction.run}</td>
            <td><span class="fx-status ${transaction.status_type}">${transaction.status}</span></td>
            <td>${transaction.value}</td>
            <td>${transaction.note}</td>
          </tr>
        `
      )
      .join("");
  }
}

function renderAvailability(availability) {
  renderMetrics(availability.metrics || []);

  const grid = document.querySelector("#fx-driver-availability-slots");
  if (!grid) return;

  grid.innerHTML = (availability.slots || [])
    .map(
      (slot) => `
        <article class="fx-slot-card ${slot.status_key === "open" ? "is-open" : "is-closed"}">
          <div class="fx-card-header">
            <h3 class="fx-title-sm">${slot.title}</h3>
            <span class="fx-status ${slot.status_type}">${slot.status}</span>
          </div>
          <p class="fx-copy-sm">${slot.description}</p>
        </article>
      `
    )
    .join("");
}

function renderDocuments(documents) {
  const summary = document.querySelector("#fx-driver-documents-summary");
  if (summary) {
    summary.innerHTML = (documents.summary || [])
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

  const cards = document.querySelector("#fx-driver-documents-cards");
  if (cards) {
    cards.innerHTML = (documents.documents || [])
      .map(
        (document) => `
          <article class="fx-doc-card">
            <div class="fx-card-header">
              <h3 class="fx-title-sm">${document.title}</h3>
              <span class="fx-status ${document.status_type}">${document.status}</span>
            </div>
            <p class="fx-copy-sm">${document.description}</p>
            <div class="fx-doc-meta">
              <span>Vigencia: ${document.expires_at}</span>
              <span>Revisao: ${document.reviewed_at}</span>
            </div>
          </article>
        `
      )
      .join("");
  }

  const checklist = document.querySelector("#fx-driver-documents-checklist");
  if (checklist) {
    checklist.innerHTML = (documents.checklist || []).map((item) => `<li>${item}</li>`).join("");
  }

  const actions = document.querySelector("#fx-driver-documents-actions");
  if (actions) {
    const rows = documents.pending_actions || [];
    actions.innerHTML = rows.length
      ? rows
          .map(
            (item) => `
              <div class="fx-doc-item">
                <strong>${item.title}</strong>
                <p class="fx-copy-sm">${item.text}</p>
              </div>
            `
          )
          .join("")
      : `<div class="fx-doc-item"><strong>Nenhuma pendencia</strong><p class="fx-copy-sm">Os documentos principais estao aptos para a operacao atual.</p></div>`;
  }
}

function renderSupport(support) {
  const list = document.querySelector("#fx-driver-support-tickets");
  if (!list) return;

  const tickets = support?.tickets || [];
  if (!tickets.length) {
    list.innerHTML = `<div class="fx-note">Nenhum chamado recente registrado.</div>`;
    return;
  }

  list.innerHTML = tickets
    .map(
      (ticket) => `
        <div class="fx-timeline-item">
          <div>
            <strong>${ticket.id}</strong>
            <p class="fx-copy-sm">${ticket.summary}</p>
            <div class="fx-inline-actions">
              ${(ticket.meta || []).map((item) => `<span class="fx-tag">${item}</span>`).join("")}
            </div>
          </div>
          <span class="fx-status ${ticket.statusType}">${ticket.status}</span>
        </div>
      `
    )
    .join("");
}

function renderNotifications(payload) {
  const summary = document.querySelector("#fx-driver-notifications-summary");
  const list = document.querySelector("#fx-driver-notifications-list");

  if (summary) {
    summary.innerHTML = (payload.summary || [])
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

  const items = payload.items || [];
  if (!items.length) {
    list.innerHTML = `<div class="fx-note">Nenhuma notificacao operacional disponivel.</div>`;
    return;
  }

  list.innerHTML = items
    .map(
      (item) => `
        <article class="fx-notification-card ${item.is_read ? "is-read" : "is-unread"}">
          <div class="fx-card-header">
            <div>
              <h3 class="fx-title-sm">${item.title}</h3>
              <p class="fx-copy-sm">${item.context}</p>
            </div>
            <span class="fx-status ${item.level_type}">${item.level}</span>
          </div>
          <p class="fx-copy-sm">${item.body}</p>
          <div class="fx-inline-actions">
            <span class="fx-tag">${item.created_at}</span>
            ${item.action_url ? `<a class="fx-button-ghost" href="${item.action_url}">${item.action_label || "Abrir"}</a>` : ""}
            ${!item.is_read ? `<button class="fx-button-ghost js-driver-notification-read" type="button" data-notification-id="${item.id}">Marcar como lida</button>` : ""}
          </div>
        </article>
      `
    )
    .join("");
}

async function handleSupportForm() {
  const form = document.querySelector("#fx-driver-support-form");
  if (!form || form.dataset.bound === "true") return;
  form.dataset.bound = "true";

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const payload = {
      channel: document.querySelector("#fx-driver-support-channel")?.value ?? "operations",
      priority: document.querySelector("#fx-driver-support-priority")?.value ?? "normal",
      subject: document.querySelector("#fx-driver-support-subject")?.value ?? "",
      description: document.querySelector("#fx-driver-support-description")?.value ?? ""
    };

    try {
      const support = await createDriverSupportTicket(payload);
      renderSupport(support);
      form.reset();
      showFeedback("#fx-driver-support-feedback", "Chamado enviado para a central da Fox Delivery.");
    } catch (error) {
      showFeedback(
        "#fx-driver-support-feedback",
        error?.message || "Nao foi possivel abrir o chamado do entregador.",
        "danger"
      );
    }
  });
}

async function handleNotificationsScreen() {
  const list = document.querySelector("#fx-driver-notifications-list");
  let payload = await getDriverNotifications();
  renderNotifications(payload);

  list?.addEventListener("click", async (event) => {
    const trigger = event.target.closest(".js-driver-notification-read");
    if (!trigger) return;

    try {
      payload = await markDriverNotificationRead(trigger.dataset.notificationId);
      renderNotifications(payload);
      showFeedback("#fx-driver-notifications-feedback", "Notificacao marcada como lida.");
    } catch (error) {
      showFeedback(
        "#fx-driver-notifications-feedback",
        error?.message || "Nao foi possivel atualizar a notificacao.",
        "danger"
      );
    }
  });
}

function renderProfile(profile) {
  setInputValue("#fx-driver-full-name", profile.full_name);
  setInputValue("#fx-driver-email", profile.email);
  setInputValue("#fx-driver-phone", profile.phone);
  setInputValue("#fx-driver-modal", profile.modal);
  setInputValue("#fx-driver-city", profile.city);
  setInputValue("#fx-driver-bank-name", profile.bank_name);
  setInputValue("#fx-driver-bank-branch-number", profile.bank_branch_number);
  setInputValue("#fx-driver-bank-account-number", profile.bank_account_number);

  setText("#fx-driver-account-status", profile.status);
  setText("#fx-driver-last-login", profile.last_login_at);
  setText("#fx-driver-documents-status", profile.documents_status);
  setText("#fx-driver-main-modal", profile.modal);

  const status = document.querySelector("#fx-driver-account-status");
  if (status) {
    status.className = `fx-status ${profile.status_type || "success"}`;
  }

  const docsStatus = document.querySelector("#fx-driver-documents-status");
  if (docsStatus) {
    docsStatus.className = `fx-status ${profile.documents_status_type || "success"}`;
  }
}

async function handleProfileForm() {
  const form = document.querySelector("#fx-driver-profile-form");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const payload = {
      full_name: document.querySelector("#fx-driver-full-name")?.value ?? "",
      email: document.querySelector("#fx-driver-email")?.value ?? "",
      phone: document.querySelector("#fx-driver-phone")?.value ?? "",
      modal: document.querySelector("#fx-driver-modal")?.value ?? "",
      city: document.querySelector("#fx-driver-city")?.value ?? "",
      bank_name: document.querySelector("#fx-driver-bank-name")?.value ?? "",
      bank_branch_number: document.querySelector("#fx-driver-bank-branch-number")?.value ?? "",
      bank_account_number: document.querySelector("#fx-driver-bank-account-number")?.value ?? ""
    };

    try {
      const profile = await updateDriverProfile(payload);
      renderProfile(profile);
      showFeedback("#fx-driver-profile-feedback", "Perfil do entregador atualizado com sucesso.");
    } catch (error) {
      showFeedback(
        "#fx-driver-profile-feedback",
        error?.message || "Nao foi possivel atualizar o perfil do entregador.",
        "danger"
      );
    }
  });
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

  if (screen === "dashboard") {
    renderDashboard(await getDriverDashboard());
    return;
  }

  if (screen === "earnings") {
    renderEarnings(await getDriverEarnings());
    return;
  }

  if (screen === "availability") {
    renderAvailability(await getDriverAvailability());
    return;
  }

  if (screen === "documents") {
    renderDocuments(await getDriverDocuments());
    return;
  }

  if (screen === "profile") {
    renderProfile(await getDriverProfile());
    await handleProfileForm();
    return;
  }

  if (screen === "support") {
    renderSupport(await getDriverSupport());
    await handleSupportForm();
    return;
  }

  if (screen === "notifications") {
    await handleNotificationsScreen();
    return;
  }
}

boot();

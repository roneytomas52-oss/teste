import {
  addPartnerStoreDocument,
  bindLogout,
  getPartnerData,
  getPartnerProfile,
  getPartnerStore,
  injectSessionLabel,
  login,
  requireSession,
  updatePartnerProfile,
  updatePartnerStore,
  updatePartnerStoreHours
} from "./fox-platform-sdk.js";

const CACHE_KEYS = {
  profile: "fox-partner-profile-cache",
  store: "fox-partner-store-cache"
};

const WEEKDAYS = [
  "Domingo",
  "Segunda-feira",
  "Terça-feira",
  "Quarta-feira",
  "Quinta-feira",
  "Sexta-feira",
  "Sábado"
];

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

function readCache(key) {
  try {
    const raw = window.localStorage.getItem(key);
    return raw ? JSON.parse(raw) : null;
  } catch (_error) {
    return null;
  }
}

function writeCache(key, value) {
  window.localStorage.setItem(key, JSON.stringify(value));
}

function showFeedback(selector, message, tone = "success") {
  const target = document.querySelector(selector);
  if (!target) return;
  target.hidden = false;
  target.dataset.tone = tone;
  target.textContent = message;
}

function renderStatusChip(target, status) {
  if (!target) return;

  const toneMap = {
    active: "success",
    approved: "success",
    pending: "warning",
    rejected: "danger",
    suspended: "warning",
    blocked: "danger"
  };

  const labelMap = {
    active: "Ativa",
    approved: "Aprovado",
    pending: "Pendente",
    rejected: "Rejeitado",
    suspended: "Suspensa",
    blocked: "Bloqueada"
  };

  target.className = `fx-status ${toneMap[status] || "warning"}`;
  target.textContent = labelMap[status] || status || "-";
}

function formatDateTime(value) {
  if (!value) return "-";

  try {
    return new Intl.DateTimeFormat("pt-BR", {
      dateStyle: "short",
      timeStyle: "short"
    }).format(new Date(value));
  } catch (_error) {
    return value;
  }
}

function getDefaultHours() {
  return WEEKDAYS.map((_, weekday) => ({
    weekday,
    opens_at: "09:00",
    closes_at: "18:00",
    is_active: weekday !== 0
  }));
}

async function loadProfileState(session) {
  if (session?.source === "api") {
    const profile = await getPartnerProfile();
    writeCache(CACHE_KEYS.profile, profile);
    return profile;
  }

  const cached = readCache(CACHE_KEYS.profile);
  if (cached) return cached;

  const profile = await getPartnerProfile();
  writeCache(CACHE_KEYS.profile, profile);
  return profile;
}

async function loadStoreState(session) {
  if (session?.source === "api") {
    const store = await getPartnerStore();
    writeCache(CACHE_KEYS.store, store);
    return store;
  }

  const cached = readCache(CACHE_KEYS.store);
  if (cached) return cached;

  const store = await getPartnerStore();
  writeCache(CACHE_KEYS.store, store);
  return store;
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

function renderProfile(profile) {
  setInputValue("#profile-full-name", profile.full_name);
  setInputValue("#profile-email", profile.email);
  setInputValue("#profile-phone", profile.phone);
  setInputValue("#profile-locale", profile.locale);
  setInputValue("#profile-legal-name", profile.legal_name);
  setInputValue("#profile-document-number", profile.document_number);

  renderStatusChip(document.querySelector("#profile-status"), profile.status);
  setText("#profile-last-login", formatDateTime(profile.last_login_at));
  setText("#profile-role", profile.roles?.[0]?.name || "-");
  setText("#profile-scope", profile.roles?.[0]?.scope || "-");
}

function renderDocuments(documents) {
  const container = document.querySelector("#fx-store-document-list");
  if (!container) return;

  if (!documents?.length) {
    container.innerHTML = `<div class="fx-note">Nenhum documento registrado até o momento.</div>`;
    return;
  }

  container.innerHTML = documents
    .map(
      (document) => `
        <article class="fx-document-item">
          <div class="fx-document-head">
            <div>
              <strong>${document.label}</strong>
              <div class="fx-copy-sm">${document.file_name}</div>
            </div>
            <span class="fx-status ${document.status === "approved" ? "success" : document.status === "rejected" ? "danger" : "warning"}">
              ${document.status === "approved" ? "Aprovado" : document.status === "rejected" ? "Rejeitado" : "Pendente"}
            </span>
          </div>
          <div class="fx-document-meta">
            <span class="fx-tag">${document.document_type}</span>
            <span class="fx-copy-sm">${formatDateTime(document.created_at)}</span>
          </div>
          <p class="fx-copy-sm">${document.storage_path}</p>
        </article>
      `
    )
    .join("");
}

function renderStore(storeState) {
  const store = storeState.store || {};
  setInputValue("#store-trade-name", store.trade_name);
  setInputValue("#store-legal-name", store.legal_name);
  setInputValue("#store-email", store.email);
  setInputValue("#store-phone", store.phone);
  setInputValue("#store-document-number", store.document_number);
  setInputValue("#store-city", store.city);
  setInputValue("#store-state", store.state);
  setInputValue("#store-country", store.country);
  setInputValue("#store-description", store.description);

  renderStatusChip(document.querySelector("#store-status"), store.status);
  setText("#store-contact", store.phone || store.email || "-");
  setText("#store-region", [store.city, store.state].filter(Boolean).join(" / ") || "-");
  setText("#store-documents-count", String(storeState.documents?.length || 0));

  renderDocuments(storeState.documents || []);
}

function renderSchedules(hours) {
  const list = document.querySelector("#fx-schedule-list");
  if (!list) return;

  const normalized = getDefaultHours().map((baseHour) => {
    const current = hours.find((item) => Number(item.weekday) === baseHour.weekday);
    return current ? { ...baseHour, ...current } : baseHour;
  });

  list.innerHTML = normalized
    .map(
      (hour) => `
        <article class="fx-schedule-card" data-weekday="${hour.weekday}">
          <div class="fx-schedule-head">
            <strong>${WEEKDAYS[hour.weekday]}</strong>
            <label class="fx-switch-line">
              <input class="js-hour-active" type="checkbox" ${hour.is_active ? "checked" : ""} />
              <span>Dia ativo</span>
            </label>
          </div>
          <div class="fx-form-grid">
            <div class="fx-field">
              <label>Abertura</label>
              <input class="js-hour-open" type="time" value="${hour.opens_at}" />
            </div>
            <div class="fx-field">
              <label>Fechamento</label>
              <input class="js-hour-close" type="time" value="${hour.closes_at}" />
            </div>
          </div>
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

function hydrateBrand(storeState, session) {
  injectSessionLabel(".fx-brand-chip strong", {
    accountLabel: storeState?.store?.trade_name || session?.accountLabel || "Fox Partner"
  });
}

async function handleProfileScreen(session) {
  const form = document.querySelector("#fx-partner-profile-form");
  if (!form) return;

  const profile = await loadProfileState(session);
  renderProfile(profile);

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const payload = {
        full_name: document.querySelector("#profile-full-name")?.value ?? "",
        email: document.querySelector("#profile-email")?.value ?? "",
        phone: document.querySelector("#profile-phone")?.value ?? ""
      };

      const updated = await updatePartnerProfile(payload);
      writeCache(CACHE_KEYS.profile, updated);
      renderProfile(updated);
      showFeedback("#fx-profile-feedback", "Perfil atualizado com sucesso.");
    } catch (error) {
      showFeedback("#fx-profile-feedback", error.message, "error");
    }
  });
}

async function handleStoreScreen(session) {
  const storeForm = document.querySelector("#fx-partner-store-form");
  const documentForm = document.querySelector("#fx-partner-document-form");
  if (!storeForm) return;

  let storeState = await loadStoreState(session);
  renderStore(storeState);
  hydrateBrand(storeState, session);

  storeForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const payload = {
        trade_name: document.querySelector("#store-trade-name")?.value ?? "",
        legal_name: document.querySelector("#store-legal-name")?.value ?? "",
        email: document.querySelector("#store-email")?.value ?? "",
        phone: document.querySelector("#store-phone")?.value ?? "",
        document_number: document.querySelector("#store-document-number")?.value ?? "",
        city: document.querySelector("#store-city")?.value ?? "",
        state: document.querySelector("#store-state")?.value ?? "",
        country: document.querySelector("#store-country")?.value ?? "",
        description: document.querySelector("#store-description")?.value ?? ""
      };

      storeState = await updatePartnerStore(payload);
      writeCache(CACHE_KEYS.store, storeState);
      renderStore(storeState);
      hydrateBrand(storeState, session);
      showFeedback("#fx-store-feedback", "Dados da loja atualizados com sucesso.");
    } catch (error) {
      showFeedback("#fx-store-feedback", error.message, "error");
    }
  });

  if (!documentForm) return;

  documentForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const payload = {
        document_type: document.querySelector("#document-type")?.value ?? "",
        label: document.querySelector("#document-label")?.value ?? "",
        file_name: document.querySelector("#document-file-name")?.value ?? "",
        storage_path: document.querySelector("#document-storage-path")?.value ?? "",
        status: "pending",
        metadata: {
          source: "partner-portal"
        }
      };

      const response = await addPartnerStoreDocument(payload);
      storeState = {
        ...storeState,
        documents: response.documents || storeState.documents || []
      };
      writeCache(CACHE_KEYS.store, storeState);
      renderStore(storeState);
      documentForm.reset();
      showFeedback("#fx-store-feedback", "Documento adicionado à fila de análise.");
    } catch (error) {
      showFeedback("#fx-store-feedback", error.message, "error");
    }
  });
}

async function handleSchedulesScreen(session) {
  const form = document.querySelector("#fx-partner-hours-form");
  if (!form) return;

  let storeState = await loadStoreState(session);
  renderSchedules(storeState.hours || []);
  hydrateBrand(storeState, session);

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const hours = Array.from(document.querySelectorAll(".fx-schedule-card")).map((card) => ({
      weekday: Number(card.dataset.weekday),
      opens_at: card.querySelector(".js-hour-open")?.value ?? "09:00",
      closes_at: card.querySelector(".js-hour-close")?.value ?? "18:00",
      is_active: card.querySelector(".js-hour-active")?.checked ?? false
    }));

    try {
      const response = await updatePartnerStoreHours(hours);
      storeState = {
        ...storeState,
        hours: response.hours || hours
      };
      writeCache(CACHE_KEYS.store, storeState);
      renderSchedules(storeState.hours || []);
      showFeedback("#fx-hours-feedback", "Horários atualizados com sucesso.");
    } catch (error) {
      showFeedback("#fx-hours-feedback", error.message, "error");
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
  injectSessionLabel(".fx-brand-chip strong", session);

  if (screen === "profile") {
    await handleProfileScreen(session);
    return;
  }

  if (screen === "store") {
    await handleStoreScreen(session);
    return;
  }

  if (screen === "schedules") {
    await handleSchedulesScreen(session);
    return;
  }

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

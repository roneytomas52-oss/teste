import {
  addPartnerStoreDocument,
  bindLogout,
  createPartnerProduct,
  createPartnerTeamMember,
  createPartnerSupportTicket,
  getPartnerCatalog,
  getPartnerDashboard,
  getPartnerFinance,
  getPartnerData,
  getPartnerNotifications,
  getPartnerOrders,
  getPartnerProfile,
  getPartnerStore,
  getPartnerSupport,
  getPartnerSupportThread,
  getPartnerTeam,
  injectSessionLabel,
  login,
  markPartnerNotificationRead,
  replyPartnerSupportThread,
  requireSession,
  updatePartnerOrderStatus,
  updatePartnerProduct,
  updatePartnerProfile,
  updatePartnerProductInventory,
  updatePartnerTeamMember,
  updatePartnerTeamMemberStatus,
  updatePartnerStore,
  updatePartnerStoreHours
} from "./fox-platform-sdk.js";

const CACHE_KEYS = {
  profile: "fox-partner-profile-cache",
  store: "fox-partner-store-cache",
  catalog: "fox-partner-catalog-cache"
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

function buildCacheKey(type, session) {
  return `${CACHE_KEYS[type]}:${session?.id || "anonymous"}`;
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
  const cacheKey = buildCacheKey("profile", session);

  if (session?.source === "api") {
    const profile = await getPartnerProfile();
    writeCache(cacheKey, profile);
    return profile;
  }

  const cached = readCache(cacheKey);
  if (cached) return cached;

  const profile = await getPartnerProfile();
  writeCache(cacheKey, profile);
  return profile;
}

async function loadStoreState(session) {
  const cacheKey = buildCacheKey("store", session);

  if (session?.source === "api") {
    const store = await getPartnerStore();
    writeCache(cacheKey, store);
    return store;
  }

  const cached = readCache(cacheKey);
  if (cached) return cached;

  const store = await getPartnerStore();
  writeCache(cacheKey, store);
  return store;
}

async function loadCatalogState(session) {
  const cacheKey = buildCacheKey("catalog", session);

  if (session?.source === "api") {
    const catalog = await getPartnerCatalog();
    writeCache(cacheKey, catalog);
    return catalog;
  }

  const cached = readCache(cacheKey);
  if (cached) return cached;

  const catalog = await getPartnerCatalog();
  writeCache(cacheKey, catalog);
  return catalog;
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

function renderDashboardOrders(rows) {
  const container = document.querySelector("#fx-dashboard-orders");
  if (!container) return;

  if (!rows?.length) {
    container.innerHTML = `<div class="fx-note">Nenhum pedido em destaque no momento.</div>`;
    return;
  }

  container.innerHTML = rows
    .map(
      (row) => `
        <div class="fx-order-row">
          <div>
            <strong>${row.id}</strong>
            <p class="fx-copy-sm">${row.customer} - ${row.driver_name || "sem atribuicao"}.</p>
          </div>
          <span class="fx-status ${row.statusType}">${row.status}</span>
        </div>
      `
    )
    .join("");
}

function renderTopProducts(rows) {
  const tbody = document.querySelector("#fx-dashboard-top-products");
  if (!tbody) return;

  tbody.innerHTML = (rows || [])
    .map(
      (row) => `
        <tr>
          <td>${row.name}</td>
          <td>${row.category}</td>
          <td>${row.sold_count}</td>
          <td><span class="fx-status ${row.status_type || "success"}">${row.status}</span></td>
        </tr>
      `
    )
    .join("");
}

function renderHealth(rows) {
  const container = document.querySelector("#fx-dashboard-health");
  if (!container) return;

  container.innerHTML = (rows || [])
    .map(
      (row) => `
        <div class="fx-check-item">
          <strong>${row.title}</strong>
          <p class="fx-copy-sm">${row.text}</p>
        </div>
      `
    )
    .join("");
}

function getNextPartnerOrderAction(statusKey) {
  const transitions = {
    pending_acceptance: { next: "accepted", label: "Aceitar" },
    accepted: { next: "preparing", label: "Iniciar preparo" },
    preparing: { next: "ready_for_pickup", label: "Marcar como pronto" },
    ready_for_pickup: { next: "on_route", label: "Sinalizar coleta" },
    on_route: { next: "completed", label: "Concluir pedido" }
  };

  return transitions[statusKey] || null;
}

function filterOrders(items, query, filter) {
  const normalizedQuery = query.trim().toLowerCase();

  return (items || []).filter((item) => {
    const matchesQuery =
      normalizedQuery === "" ||
      item.id.toLowerCase().includes(normalizedQuery) ||
      item.customer.toLowerCase().includes(normalizedQuery) ||
      (item.driver_name || "").toLowerCase().includes(normalizedQuery);

    if (!matchesQuery) {
      return false;
    }

    if (filter === "all") {
      return true;
    }

    return item.status_key === filter;
  });
}

function renderOrdersTable(payload, query = "", filter = "all") {
  const tbody = document.querySelector("#fx-orders-table-body");
  const summary = document.querySelector("#fx-orders-summary");
  if (!tbody) return;

  const rows = filterOrders(payload.orders || [], query, filter);

  if (summary) {
    summary.textContent = `${payload.totals?.total || rows.length} pedidos mapeados, ${payload.totals?.pending || 0} aguardando aceite e ${payload.totals?.critical || 0} criticos.`;
  }

  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="fx-note">Nenhum pedido encontrado para este filtro.</div></td></tr>`;
    return;
  }

  tbody.innerHTML = rows
    .map((row) => {
      const nextAction = getNextPartnerOrderAction(row.status_key);

      return `
        <tr>
          <td>${row.id}</td>
          <td>${row.customer}</td>
          <td><span class="fx-status ${row.statusType}">${row.status}</span></td>
          <td>${row.sla}</td>
          <td>${row.value}</td>
          <td>
            ${
              nextAction
                ? `<button class="fx-button-ghost js-order-status" type="button" data-order-id="${row.order_id || row.id}" data-next-status="${nextAction.next}">${nextAction.label}</button>`
                : `<span class="fx-copy-sm">${row.action || "-"}</span>`
            }
          </td>
        </tr>
      `;
    })
    .join("");
}

function renderDashboard(data) {
  setText(".fx-hero-content .fx-title-lg", data.heroTitle);
  setText(".fx-hero-content .fx-lead", data.heroLead);
  renderSummary(data.summary || []);
  renderMetrics(data.metrics || []);
  renderDashboardOrders(data.orders || []);
  renderTopProducts(data.top_products || []);
  renderHealth(data.health || []);
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

  const payouts = document.querySelector("#fx-partner-finance-payouts");
  if (payouts) {
    payouts.innerHTML = (finance.payouts || [])
      .map(
        (item) => `
          <div class="fx-payout-item">
            <strong>${item.date}</strong>
            <p class="fx-copy-sm">${item.title}</p>
            <p class="fx-copy-sm">${item.text}</p>
            <span class="fx-status ${item.status_type || "warning"}">${item.amount} - ${item.status}</span>
          </div>
        `
      )
      .join("");
  }

  const bankAccount = document.querySelector("#fx-partner-bank-account");
  if (bankAccount) {
    bankAccount.innerHTML = `
      <div class="fx-info-row">
        <strong>Banco</strong>
        <span>${finance.bank_account?.bank_name || "-"}</span>
      </div>
      <div class="fx-info-row">
        <strong>Agencia</strong>
        <span>${finance.bank_account?.branch_number || "-"}</span>
      </div>
      <div class="fx-info-row">
        <strong>Conta</strong>
        <span>${finance.bank_account?.account_number || "-"}</span>
      </div>
      <div class="fx-info-row">
        <strong>Status</strong>
        <span class="fx-status ${finance.bank_account?.status_type || "warning"}">${finance.bank_account?.status || "em analise"}</span>
      </div>
    `;
  }

  const transactions = document.querySelector("#fx-partner-finance-transactions");
  if (transactions) {
    transactions.innerHTML = (finance.transactions || [])
      .map(
        (item) => `
          <tr>
            <td>${item.date}</td>
            <td>${item.description}</td>
            <td>${item.type}</td>
            <td><span class="fx-status ${item.status_type || "warning"}">${item.status}</span></td>
            <td>${item.value}</td>
          </tr>
        `
      )
      .join("");
  }
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

function renderMessageThreadList(tickets, activeTicketId) {
  const list = document.querySelector("#fx-partner-message-threads");
  if (!list) return;

  if (!tickets?.length) {
    list.innerHTML = `<div class="fx-note">Nenhum protocolo com historico disponivel.</div>`;
    return;
  }

  list.innerHTML = tickets
    .map(
      (ticket) => `
        <button
          class="fx-thread-item ${ticket.ticket_id === activeTicketId ? "is-active" : ""}"
          type="button"
          data-ticket-id="${ticket.ticket_id}"
        >
          <div class="fx-thread-head">
            <strong>${ticket.channel}</strong>
            <span class="fx-status ${ticket.statusType}">${ticket.status}</span>
          </div>
          <p class="fx-copy-sm">${ticket.summary}</p>
          <span class="fx-copy-sm">${(ticket.meta || []).join(" · ")}</span>
        </button>
      `
    )
    .join("");
}

function renderMessageThreadView(thread) {
  const title = document.querySelector("#fx-partner-thread-title");
  const pill = document.querySelector("#fx-partner-thread-pill");
  const meta = document.querySelector("#fx-partner-thread-meta");
  const stream = document.querySelector("#fx-partner-message-stream");

  if (!stream) return;

  if (!thread?.ticket) {
    if (title) title.textContent = "Nenhum protocolo selecionado";
    if (pill) pill.textContent = "Aguardando selecao";
    if (meta) meta.textContent = "Escolha um protocolo para visualizar o historico completo.";
    stream.innerHTML = `<div class="fx-note">Selecione um protocolo da lista para abrir a conversa.</div>`;
    return;
  }

  if (title) title.textContent = `Conversa com ${thread.ticket.channel}`;
  if (pill) pill.textContent = thread.ticket.id;
  if (meta) {
    meta.textContent = `Prioridade ${thread.ticket.priority} · Atualizado em ${thread.ticket.last_message_at ? formatDateTime(thread.ticket.last_message_at) : "-"}`;
  }

  stream.innerHTML = (thread.messages || [])
    .map(
      (message) => `
        <div class="fx-message-bubble ${message.direction === "outgoing" ? "is-outgoing" : "is-incoming"}">
          <strong>${message.author}</strong>
          <span>${message.body}</span>
          <small class="fx-copy-sm">${message.time}</small>
        </div>
      `
    )
    .join("");
}

function renderSupport(support) {
  const list = document.querySelector("#fx-partner-support-tickets");
  if (!list) return;

  const tickets = support?.tickets || [];
  if (!tickets.length) {
    list.innerHTML = `<div class="fx-note">Nenhum chamado aberto no momento.</div>`;
    return;
  }

  list.innerHTML = tickets
    .map(
      (ticket) => `
        <article class="fx-ticket-card">
          <div class="fx-ticket-head">
            <strong>${ticket.id} - ${ticket.channel}</strong>
            <span class="fx-status ${ticket.statusType}">${ticket.status}</span>
          </div>
          <p class="fx-copy-sm">${ticket.summary}</p>
          <div class="fx-inline-actions">
            ${(ticket.meta || []).map((item) => `<span class="fx-tag">${item}</span>`).join("")}
          </div>
          <div class="fx-inline-actions">
            <a class="fx-button-ghost" href="./messages.html?ticket=${ticket.ticket_id || ticket.id}">Abrir conversa</a>
          </div>
        </article>
      `
    )
    .join("");
}

async function handleSupportForm() {
  const form = document.querySelector("#fx-partner-support-form");
  if (!form || form.dataset.bound === "true") return;
  form.dataset.bound = "true";

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const payload = {
      channel: document.querySelector("#fx-partner-support-channel")?.value ?? "operations",
      priority: document.querySelector("#fx-partner-support-priority")?.value ?? "normal",
      subject: document.querySelector("#fx-partner-support-subject")?.value ?? "",
      description: document.querySelector("#fx-partner-support-description")?.value ?? ""
    };

    try {
      const support = await createPartnerSupportTicket(payload);
      renderSupport(support);
      form.reset();
      showFeedback("#fx-partner-support-feedback", "Chamado enviado para a fila operacional da Fox Delivery.");
    } catch (error) {
      showFeedback(
        "#fx-partner-support-feedback",
        error?.message || "Nao foi possivel abrir o chamado da loja.",
        "danger"
      );
    }
  });
}

async function handleMessagesScreen() {
  const list = document.querySelector("#fx-partner-message-threads");
  const form = document.querySelector("#fx-partner-message-form");
  const bodyField = document.querySelector("#fx-partner-message-body");
  const feedbackSelector = "#fx-partner-message-feedback";
  const support = await getPartnerSupport();
  const tickets = support?.tickets || [];
  const params = new URLSearchParams(window.location.search);
  let activeTicketId = params.get("ticket") || tickets[0]?.ticket_id || tickets[0]?.id || "";

  const loadThread = async (ticketId) => {
    activeTicketId = ticketId;
    renderMessageThreadList(tickets, activeTicketId);

    if (!activeTicketId) {
      renderMessageThreadView(null);
      return;
    }

    const thread = await getPartnerSupportThread(activeTicketId);
    renderMessageThreadView(thread);
    const nextUrl = new URL(window.location.href);
    nextUrl.searchParams.set("ticket", activeTicketId);
    window.history.replaceState({}, "", nextUrl);
  };

  await loadThread(activeTicketId);

  list?.addEventListener("click", async (event) => {
    const trigger = event.target.closest("[data-ticket-id]");
    if (!trigger) return;
    await loadThread(trigger.dataset.ticketId);
  });

  if (!form || form.dataset.bound === "true") return;
  form.dataset.bound = "true";

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    if (!activeTicketId) {
      showFeedback(feedbackSelector, "Selecione um protocolo antes de responder.", "danger");
      return;
    }

    const body = bodyField?.value?.trim() || "";
    if (!body) {
      showFeedback(feedbackSelector, "Digite uma mensagem para enviar ao suporte.", "danger");
      return;
    }

    try {
      const thread = await replyPartnerSupportThread(activeTicketId, body);
      renderMessageThreadView(thread);
      if (bodyField) bodyField.value = "";
      showFeedback(feedbackSelector, "Mensagem enviada com sucesso.");
    } catch (error) {
      showFeedback(feedbackSelector, error.message || "Nao foi possivel responder o protocolo.", "danger");
    }
  });
}

async function handleTeamScreen() {
  const form = document.querySelector("#fx-team-form");
  const list = document.querySelector("#fx-team-list");
  const feedbackSelector = "#fx-team-feedback";
  let payload = await getPartnerTeam();
  renderTeam(payload);

  const resetForm = () => {
    setInputValue("#fx-team-member-id", "");
    setInputValue("#fx-team-full-name", "");
    setInputValue("#fx-team-email", "");
    setInputValue("#fx-team-phone", "");
    setInputValue("#fx-team-role", "manager");
    document.querySelectorAll(".js-team-permission").forEach((input) => {
      input.checked = false;
    });
    const title = document.querySelector("#fx-team-form-title");
    const submit = document.querySelector("#fx-team-submit");
    if (title) title.textContent = "Convidar novo membro";
    if (submit) submit.textContent = "Enviar convite";
  };

  const fillForm = (member) => {
    setInputValue("#fx-team-member-id", member.id);
    setInputValue("#fx-team-full-name", member.full_name);
    setInputValue("#fx-team-email", member.email);
    setInputValue("#fx-team-phone", member.phone);
    setInputValue("#fx-team-role", member.role_slug);
    const permissions = new Set(member.permissions || []);
    document.querySelectorAll(".js-team-permission").forEach((input) => {
      input.checked = permissions.has(input.value);
    });
    const title = document.querySelector("#fx-team-form-title");
    const submit = document.querySelector("#fx-team-submit");
    if (title) title.textContent = `Editar acesso de ${member.full_name}`;
    if (submit) submit.textContent = "Salvar alteracoes";
  };

  resetForm();

  list?.addEventListener("click", async (event) => {
    const edit = event.target.closest(".js-team-edit");
    if (edit) {
      const member = (payload.members || []).find((item) => item.id === edit.dataset.memberId);
      if (member) fillForm(member);
      return;
    }

    const status = event.target.closest(".js-team-status");
    if (!status) return;

    try {
      payload = await updatePartnerTeamMemberStatus(status.dataset.memberId, status.dataset.status);
      renderTeam(payload);
      showFeedback(feedbackSelector, "Status do membro atualizado com sucesso.");
    } catch (error) {
      showFeedback(feedbackSelector, error.message || "Nao foi possivel atualizar o status do membro.", "danger");
    }
  });

  const resetButton = document.querySelector("#fx-team-reset");
  resetButton?.addEventListener("click", resetForm);

  if (!form || form.dataset.bound === "true") return;
  form.dataset.bound = "true";

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const memberId = document.querySelector("#fx-team-member-id")?.value?.trim() || "";
    const permissions = Array.from(document.querySelectorAll(".js-team-permission:checked")).map((input) => input.value);
    const body = {
      full_name: document.querySelector("#fx-team-full-name")?.value?.trim() || "",
      email: document.querySelector("#fx-team-email")?.value?.trim() || "",
      phone: document.querySelector("#fx-team-phone")?.value?.trim() || "",
      role_slug: document.querySelector("#fx-team-role")?.value || "manager",
      permissions
    };

    try {
      payload = memberId
        ? await updatePartnerTeamMember(memberId, body)
        : await createPartnerTeamMember(body);
      renderTeam(payload);
      resetForm();
      showFeedback(feedbackSelector, memberId ? "Membro atualizado com sucesso." : "Convite enviado com sucesso.");
    } catch (error) {
      showFeedback(feedbackSelector, error.message || "Nao foi possivel salvar o membro da equipe.", "danger");
    }
  });
}

async function handleNotificationsScreen() {
  const list = document.querySelector("#fx-notifications-list");
  let payload = await getPartnerNotifications();
  renderNotifications(payload);

  list?.addEventListener("click", async (event) => {
    const trigger = event.target.closest(".js-notification-read");
    if (!trigger) return;

    try {
      payload = await markPartnerNotificationRead(trigger.dataset.notificationId);
      renderNotifications(payload);
      showFeedback("#fx-notifications-feedback", "Notificacao marcada como lida.");
    } catch (error) {
      showFeedback("#fx-notifications-feedback", error.message || "Nao foi possivel atualizar a notificacao.", "danger");
    }
  });
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

function renderTeam(team) {
  const summary = document.querySelector("#fx-team-summary");
  const list = document.querySelector("#fx-team-list");

  if (summary) {
    summary.innerHTML = (team.summary || [])
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

  if (!(team.members || []).length) {
    list.innerHTML = `<div class="fx-note">Nenhum membro adicional cadastrado para esta loja.</div>`;
    return;
  }

  list.innerHTML = (team.members || [])
    .map(
      (member) => `
        <article class="fx-team-member-card" data-member-id="${member.id}">
          <div class="fx-card-header">
            <div>
              <h3 class="fx-title-sm">${member.full_name}</h3>
              <p class="fx-copy-sm">${member.role_label}</p>
            </div>
            <span class="fx-status ${member.status_type}">${member.status}</span>
          </div>
          <div class="fx-team-member-meta">
            <span>${member.email}</span>
            <span>${member.phone}</span>
            <span>Ultimo acesso: ${member.last_login_at}</span>
          </div>
          <div class="fx-inline-actions">
            ${(member.permissions || []).map((permission) => `<span class="fx-tag">${permission}</span>`).join("")}
          </div>
          <div class="fx-inline-actions">
            <button class="fx-button-ghost js-team-edit" type="button" data-member-id="${member.id}">Editar</button>
            <button class="fx-button-ghost js-team-status" type="button" data-member-id="${member.id}" data-status="${member.status_key === "active" ? "suspended" : "active"}">
              ${member.status_key === "active" ? "Suspender" : "Ativar"}
            </button>
          </div>
        </article>
      `
    )
    .join("");
}

function renderNotifications(payload) {
  const summary = document.querySelector("#fx-notifications-summary");
  const list = document.querySelector("#fx-notifications-list");

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
    list.innerHTML = `<div class="fx-note">Nenhuma notificacao operacional registrada.</div>`;
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
            ${!item.is_read ? `<button class="fx-button-ghost js-notification-read" type="button" data-notification-id="${item.id}">Marcar como lida</button>` : ""}
          </div>
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

function getInventoryTone(product) {
  if (product.status === "paused") return "danger";
  if (product.inventory_state === "out" || product.inventory_state === "low") return "warning";
  return "success";
}

function getInventoryLabel(product) {
  if (product.status === "paused") return "pausado";
  if (product.inventory_state === "out") return "esgotado";
  if (product.inventory_state === "low") return "estoque baixo";
  return "ativo";
}

function filterCatalogProducts(products, query, filter) {
  const normalizedQuery = query.trim().toLowerCase();

  return products.filter((product) => {
    const matchesQuery =
      normalizedQuery === "" ||
      product.name.toLowerCase().includes(normalizedQuery) ||
      product.category.toLowerCase().includes(normalizedQuery) ||
      product.sku.toLowerCase().includes(normalizedQuery);

    if (!matchesQuery) {
      return false;
    }

    if (filter === "top") return product.sold_count >= 60;
    if (filter === "paused") return product.status === "paused";
    if (filter === "attention") return product.inventory_state === "low" || product.inventory_state === "out";

    return true;
  });
}

function normalizeCatalogState(catalog) {
  return {
    categories: catalog?.categories || [],
    products: catalog?.products || [],
    inventory: catalog?.inventory || {}
  };
}

function renderCatalogSummary(catalog) {
  const target = document.querySelector("#fx-catalog-summary");
  if (!target) return;

  const productCount = catalog.products?.length || 0;
  const activeCount = (catalog.products || []).filter((product) => product.status === "active").length;
  const pausedCount = catalog.inventory?.paused_count || 0;

  target.textContent = `${productCount} itens cadastrados, ${activeCount} ativos e ${pausedCount} pausados no catalogo da loja.`;
}

function renderCategoryOptions(categories) {
  const select = document.querySelector("#fx-product-category");
  if (!select) return;

  select.innerHTML = categories
    .map((category) => `<option value="${category.id}">${category.name}</option>`)
    .join("");
}

function renderCatalog(products, selectedProductId = "") {
  const container = document.querySelector("#fx-catalog-list");
  if (!container) return;

  if (!products.length) {
    container.innerHTML = `<div class="fx-note">Nenhum produto encontrado para este filtro.</div>`;
    return;
  }

  container.innerHTML = products
    .map(
      (product) => `
        <article class="fx-product-card${selectedProductId === product.id ? " is-selected" : ""}">
          <div class="fx-product-thumb"></div>
          <div class="fx-card-header">
            <h3 class="fx-title-sm">${product.name}</h3>
            <span class="fx-status ${getInventoryTone(product)}">${getInventoryLabel(product)}</span>
          </div>
          <p class="fx-copy-sm">${product.description}</p>
          <div class="fx-product-meta">
            <span class="fx-tag">${product.category}</span>
            <span class="fx-tag">${product.price}</span>
            <span class="fx-tag">${product.sold_count} vendas</span>
          </div>
          <div class="fx-inline-actions">
            <button class="fx-button-secondary js-product-edit" type="button" data-product-id="${product.id}">Editar</button>
            <a class="fx-button" href="./inventory.html">Ajustar estoque</a>
            <button class="fx-button-ghost js-catalog-pause" type="button" data-product-id="${product.id}">
              ${product.status === "paused" ? "Reativar" : "Pausar"}
            </button>
          </div>
        </article>
      `
    )
    .join("");
}

function populateProductForm(product, categories) {
  const title = document.querySelector("#fx-product-form-title");
  const resetButton = document.querySelector("#fx-product-reset");

  if (!product) {
    setInputValue("#fx-product-id", "");
    setInputValue("#fx-product-name", "");
    setInputValue("#fx-product-sku", "");
    setInputValue("#fx-product-price", "");
    setInputValue("#fx-product-stock", "0");
    setInputValue("#fx-product-min-stock", "0");
    setInputValue("#fx-product-image", "");
    setInputValue("#fx-product-description", "");
    setInputValue("#fx-product-status", "active");
    setInputValue("#fx-product-category", categories[0]?.id || "");
    if (title) title.textContent = "Cadastrar novo produto";
    if (resetButton) resetButton.textContent = "Limpar formulario";
    return;
  }

  setInputValue("#fx-product-id", product.id);
  setInputValue("#fx-product-name", product.name);
  setInputValue("#fx-product-sku", product.sku);
  setInputValue("#fx-product-price", String(product.base_price ?? ""));
  setInputValue("#fx-product-stock", String(product.stock_quantity ?? 0));
  setInputValue("#fx-product-min-stock", String(product.min_stock_quantity ?? 0));
  setInputValue("#fx-product-image", product.image_path || "");
  setInputValue("#fx-product-description", product.description || "");
  setInputValue("#fx-product-status", product.status || "active");

  const matchingCategory = categories.find(
    (category) => category.id === product.category_id || category.slug === product.category_slug || category.name === product.category
  );
  setInputValue("#fx-product-category", matchingCategory?.id || categories[0]?.id || "");

  if (title) title.textContent = `Editar produto: ${product.name}`;
  if (resetButton) resetButton.textContent = "Cancelar edicao";
}

function buildProductPayload() {
  return {
    category_id: document.querySelector("#fx-product-category")?.value ?? "",
    name: document.querySelector("#fx-product-name")?.value?.trim() ?? "",
    description: document.querySelector("#fx-product-description")?.value?.trim() ?? "",
    sku: document.querySelector("#fx-product-sku")?.value?.trim().toUpperCase() ?? "",
    base_price: Number(document.querySelector("#fx-product-price")?.value ?? 0),
    currency: "BRL",
    status: document.querySelector("#fx-product-status")?.value ?? "active",
    stock_quantity: Number(document.querySelector("#fx-product-stock")?.value ?? 0),
    min_stock_quantity: Number(document.querySelector("#fx-product-min-stock")?.value ?? 0),
    image_path: document.querySelector("#fx-product-image")?.value?.trim() ?? ""
  };
}

function renderInventorySummary(metrics) {
  const values = [
    { value: String(metrics.low_stock_count || 0), label: "itens abaixo do mínimo" },
    { value: String(metrics.paused_count || 0), label: "itens pausados" },
    { value: String(metrics.normal_count || 0), label: "itens com disponibilidade normal" },
    { value: metrics.review_sla || "15 min", label: "tempo médio para revisão de ruptura" }
  ];

  document.querySelectorAll(".fx-compact-metric").forEach((element, index) => {
    const current = values[index];
    if (!current) return;
    const strong = element.querySelector("strong");
    const span = element.querySelector("span");
    if (strong) strong.textContent = current.value;
    if (span) span.textContent = current.label;
  });
}

function renderInventoryTable(products) {
  const tbody = document.querySelector("#fx-inventory-table-body");
  if (!tbody) return;

  tbody.innerHTML = products
    .map(
      (product) => `
        <tr>
          <td>${product.name}</td>
          <td>${product.category}</td>
          <td>${product.stock_quantity}</td>
          <td>${product.min_stock_quantity}</td>
          <td><span class="fx-status ${getInventoryTone(product)}">${getInventoryLabel(product)}</span></td>
          <td>
            <button
              class="fx-button-ghost js-inventory-update"
              type="button"
              data-product-id="${product.id}"
              data-stock="${product.stock_quantity}"
              data-min-stock="${product.min_stock_quantity}"
              data-status="${product.status}"
              data-name="${product.name}"
            >
              Ajustar
            </button>
          </td>
        </tr>
      `
    )
    .join("");
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

async function handleDashboardScreen() {
  const dashboard = await getPartnerDashboard();
  renderDashboard(dashboard);
}

async function handleOrdersScreen() {
  const search = document.querySelector("#fx-orders-search");
  const chips = document.querySelectorAll(".fx-filter-chip");
  let activeFilter = "all";
  let payload = await getPartnerOrders();

  const rerender = () => {
    renderOrdersTable(payload, search?.value ?? "", activeFilter);
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

  document.querySelector("#fx-orders-table-body")?.addEventListener("click", async (event) => {
    const trigger = event.target.closest(".js-order-status");
    if (!trigger) return;

    try {
      payload = await updatePartnerOrderStatus(trigger.dataset.orderId, {
        status: trigger.dataset.nextStatus,
        note: "Atualizacao manual via portal do parceiro"
      });
      rerender();
      showFeedback("#fx-orders-feedback", "Status do pedido atualizado com sucesso.");
    } catch (error) {
      showFeedback("#fx-orders-feedback", error.message, "error");
    }
  });
}

async function handleCatalogScreen(session) {
  const search = document.querySelector("#fx-catalog-search");
  const chips = document.querySelectorAll(".fx-filter-chip");
  const form = document.querySelector("#fx-product-form");
  const reset = document.querySelector("#fx-product-reset");
  const newButton = document.querySelector("#fx-catalog-new");
  let activeFilter = "all";
  let selectedProductId = "";
  let catalog = normalizeCatalogState(await loadCatalogState(session));

  const rerender = () => {
    const query = search?.value ?? "";
    renderCatalogSummary(catalog);
    renderCategoryOptions(catalog.categories);
    renderCatalog(filterCatalogProducts(catalog.products || [], query, activeFilter), selectedProductId);
  };

  populateProductForm(null, catalog.categories);
  rerender();

  search?.addEventListener("input", rerender);
  newButton?.addEventListener("click", () => {
    selectedProductId = "";
    populateProductForm(null, catalog.categories);
    rerender();
  });
  reset?.addEventListener("click", () => {
    selectedProductId = "";
    populateProductForm(null, catalog.categories);
    rerender();
  });
  chips.forEach((chip) => {
    chip.addEventListener("click", () => {
      chips.forEach((item) => item.classList.remove("is-active"));
      chip.classList.add("is-active");
      activeFilter = chip.dataset.filter || "all";
      rerender();
    });
  });

  form?.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const productId = document.querySelector("#fx-product-id")?.value ?? "";
      const payload = buildProductPayload();
      const response = productId
        ? await updatePartnerProduct(productId, payload)
        : await createPartnerProduct(payload);

      catalog = normalizeCatalogState({
        categories: response.categories || catalog.categories || [],
        products: response.products || catalog.products || [],
        inventory: response.inventory || catalog.inventory || {}
      });
      writeCache(buildCacheKey("catalog", session), catalog);
      selectedProductId = response.product?.id || productId || "";
      populateProductForm(
        (catalog.products || []).find((product) => product.id === selectedProductId) || null,
        catalog.categories
      );
      rerender();
      showFeedback("#fx-catalog-feedback", productId ? "Produto atualizado com sucesso." : "Produto cadastrado com sucesso.");
    } catch (error) {
      showFeedback("#fx-catalog-feedback", error.message, "error");
    }
  });

  document.querySelector("#fx-catalog-list")?.addEventListener("click", async (event) => {
    const editTrigger = event.target.closest(".js-product-edit");
    if (editTrigger) {
      selectedProductId = editTrigger.dataset.productId || "";
      const currentProduct = (catalog.products || []).find((product) => product.id === selectedProductId) || null;
      populateProductForm(currentProduct, catalog.categories);
      rerender();
      return;
    }

    const trigger = event.target.closest(".js-catalog-pause");
    if (!trigger || document.body.dataset.fxScreen !== "catalog") return;

    const productId = trigger.dataset.productId;
    const current = (catalog.products || []).find((product) => product.id === productId);
    if (!current) return;

    try {
      const response = await updatePartnerProductInventory(productId, {
        stock_quantity: current.stock_quantity,
        min_stock_quantity: current.min_stock_quantity,
        status: current.status === "paused" ? "active" : "paused",
        note: "Atualização rápida via catálogo"
      });

      catalog = {
        categories: response.categories || catalog.categories || [],
        products: response.products || catalog.products || [],
        inventory: response.inventory || catalog.inventory || {}
      };
      writeCache(buildCacheKey("catalog", session), catalog);
      if (selectedProductId === productId) {
        const refreshed = (catalog.products || []).find((product) => product.id === productId) || null;
        populateProductForm(refreshed, catalog.categories);
      }
      rerender();
      showFeedback("#fx-catalog-feedback", "Status do produto atualizado com sucesso.");
    } catch (error) {
      showFeedback("#fx-catalog-feedback", error.message, "error");
    }
  });
}

async function handleInventoryScreen(session) {
  let catalog = await loadCatalogState(session);
  renderInventorySummary(catalog.inventory || {});
  renderInventoryTable(catalog.products || []);

  document.querySelector("#fx-inventory-table-body")?.addEventListener("click", async (event) => {
    const trigger = event.target.closest(".js-inventory-update");
    if (!trigger) return;

    const productId = trigger.dataset.productId;
    const current = (catalog.products || []).find((product) => product.id === productId);
    if (!current) return;

    const nextStock = window.prompt(`Novo estoque para ${current.name}:`, String(current.stock_quantity));
    if (nextStock === null) return;

    const nextMinStock = window.prompt(`Novo estoque mínimo para ${current.name}:`, String(current.min_stock_quantity));
    if (nextMinStock === null) return;

    const parsedStock = Number(nextStock);
    const parsedMinStock = Number(nextMinStock);
    if (Number.isNaN(parsedStock) || Number.isNaN(parsedMinStock) || parsedStock < 0 || parsedMinStock < 0) {
      showFeedback("#fx-inventory-feedback", "Informe valores numéricos válidos para o estoque.", "error");
      return;
    }

    try {
      const response = await updatePartnerProductInventory(productId, {
        stock_quantity: parsedStock,
        min_stock_quantity: parsedMinStock,
        status: current.status,
        note: "Ajuste manual via tela de estoque"
      });

      catalog = {
        products: response.products || catalog.products || [],
        inventory: response.inventory || catalog.inventory || {}
      };
      writeCache(buildCacheKey("catalog", session), catalog);
      renderInventorySummary(catalog.inventory || {});
      renderInventoryTable(catalog.products || []);
      showFeedback("#fx-inventory-feedback", "Estoque atualizado com sucesso.");
    } catch (error) {
      showFeedback("#fx-inventory-feedback", error.message, "error");
    }
  });
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
      writeCache(buildCacheKey("profile", session), updated);
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
      writeCache(buildCacheKey("store", session), storeState);
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
      writeCache(buildCacheKey("store", session), storeState);
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
      writeCache(buildCacheKey("store", session), storeState);
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
  try {
    const sharedStoreState = await loadStoreState(session);
    hydrateBrand(sharedStoreState, session);
  } catch (_error) {
    injectSessionLabel(".fx-brand-chip strong", session);
  }

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

  if (screen === "catalog") {
    await handleCatalogScreen(session);
    return;
  }

  if (screen === "inventory") {
    await handleInventoryScreen(session);
    return;
  }

  if (screen === "dashboard") {
    await handleDashboardScreen();
    return;
  }

  if (screen === "orders") {
    await handleOrdersScreen();
    return;
  }

  if (screen === "messages") {
    await handleMessagesScreen();
    return;
  }

  if (screen === "team") {
    await handleTeamScreen();
    return;
  }

  if (screen === "notifications") {
    await handleNotificationsScreen();
    return;
  }

  if (screen === "finance") {
    renderFinance(await getPartnerFinance());
    return;
  }

  if (screen === "support") {
    renderSupport(await getPartnerSupport());
    await handleSupportForm();
    return;
  }

  const data = await getPartnerData();

  if (screen === "reports") {
    renderReports(data.reports);
  }

  if (screen === "help") {
    renderHelp(data.help);
  }
}

boot();

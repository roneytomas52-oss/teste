import {
  addAdminOrderNote,
  approveAdminDriver,
  approveAdminPartner,
  bindLogout,
  createAdminAccessMember,
  getAdminAnalytics,
  getAdminDashboard,
  getAdminAccess,
  getAdminOrderDetail,
  getAdminData,
  getAdminFinance,
  getAdminDriverApprovals,
  getAdminDriverApprovalDetail,
  getAdminNotifications,
  getAdminOrders,
  getAdminPartnerApprovals,
  getAdminPartnerApprovalDetail,
  getAdminReports,
  getAdminSettings,
  getAdminSupport,
  getAdminSupportThread,
  login,
  markAdminNotificationRead,
  reviewAdminDriverApproval,
  reviewAdminPartnerApproval,
  replyAdminSupportThread,
  rejectAdminDriver,
  rejectAdminPartner,
  requireSession,
  updateAdminAccessMember,
  updateAdminAccessMemberStatus,
  updateAdminOrderStatus,
  updateAdminSupportTicketStatus,
  updateAdminSettings
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

function hideFeedback(selector) {
  const target = document.querySelector(selector);
  if (!target) return;

  target.hidden = true;
  target.textContent = "";
  delete target.dataset.tone;
}

function formatDateTime(value) {
  if (!value || value === "-") return "-";
  if (value === "agora") return "agora";

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) {
    return value;
  }

  return parsed.toLocaleString("pt-BR");
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
    const current = (analytics.cards || [])[index];
    if (!current) return;
    const value = element.querySelector("strong");
    const label = element.querySelector(".fx-copy-sm");
    if (value) value.textContent = current.value;
    if (label) label.textContent = current.label;
  });

  const statusList = document.querySelector("#fx-admin-analytics-status");
  if (statusList) {
    statusList.innerHTML = (analytics.status_distribution || [])
      .map(
        (item) => `
          <div class="fx-bar-row">
            <div class="fx-bar-head"><span>${item.label}</span><strong>${String(item.share).replace(".", ",")}%</strong></div>
            <div class="fx-bar-track"><div class="fx-bar-fill" style="width: ${item.share}%;"></div></div>
          </div>
        `
      )
      .join("");
  }

  const cityList = document.querySelector("#fx-admin-analytics-cities");
  if (cityList) {
    cityList.innerHTML = (analytics.city_distribution || [])
      .map(
        (item) => `
          <div class="fx-bar-row">
            <div class="fx-bar-head"><span>${item.label}</span><strong>${String(item.share).replace(".", ",")}%</strong></div>
            <div class="fx-bar-track"><div class="fx-bar-fill" style="width: ${item.share}%;"></div></div>
          </div>
        `
      )
      .join("");
  }

  const highlights = document.querySelector("#fx-admin-analytics-highlights");
  if (highlights) {
    highlights.innerHTML = (analytics.highlights || []).map((item) => `<li>${item}</li>`).join("");
  }
}

function renderReports(data) {
  const summary = document.querySelector("#fx-admin-reports-summary");
  if (summary) {
    summary.innerHTML = (data.summary || [])
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

  const partnerStatus = document.querySelector("#fx-admin-reports-partner-status");
  if (partnerStatus) {
    partnerStatus.innerHTML = (data.partner_status || [])
      .map((item) => `<div class="fx-info-row"><strong>${item.label}</strong><span>${item.value}</span></div>`)
      .join("");
  }

  const driverStatus = document.querySelector("#fx-admin-reports-driver-status");
  if (driverStatus) {
    driverStatus.innerHTML = (data.driver_status || [])
      .map((item) => `<div class="fx-info-row"><strong>${item.label}</strong><span>${item.value}</span></div>`)
      .join("");
  }

  const supportTeams = document.querySelector("#fx-admin-reports-support-teams");
  if (supportTeams) {
    supportTeams.innerHTML = (data.support_teams || [])
      .map((item) => `<div class="fx-info-row"><strong>${item.label}</strong><span>${item.value}</span></div>`)
      .join("");
  }

  const topStores = document.querySelector("#fx-admin-reports-top-stores");
  if (topStores) {
    topStores.innerHTML = (data.top_stores || [])
      .map(
        (item) => `
          <tr>
            <td>${item.label}</td>
            <td>${item.orders}</td>
            <td>${item.gross}</td>
          </tr>
        `
      )
      .join("");
  }
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
            <div class="fx-inline-actions">
              <a class="fx-button-secondary" href="./support-detail.html?ticket=${item.ticket_id || item.id}">Abrir ticket</a>
            </div>
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

function renderAdminSupportThread(thread) {
  const ticket = thread?.ticket || null;
  const stream = document.querySelector("#fx-admin-support-thread-stream");
  const statusSelect = document.querySelector("#fx-admin-support-status-input");

  if (!ticket) {
    setText("#fx-admin-support-detail-id", "-");
    setText("#fx-admin-support-detail-subject", "Ticket nao encontrado");
    setText("#fx-admin-support-detail-meta", "Nao foi possivel carregar o protocolo selecionado.");
    setText("#fx-admin-support-detail-scope", "-");
    setText("#fx-admin-support-detail-counterpart", "-");
    setText("#fx-admin-support-detail-channel", "-");
    setText("#fx-admin-support-detail-priority", "-");
    setText("#fx-admin-support-detail-team", "-");
    setText("#fx-admin-support-detail-created-at", "-");
    setText("#fx-admin-support-detail-last-message-at", "-");
    if (stream) {
      stream.innerHTML = `<div class="fx-note">Ticket indisponivel para atendimento.</div>`;
    }
    return;
  }

  setText("#fx-admin-support-detail-id", ticket.id || "-");
  setText("#fx-admin-support-detail-subject", ticket.subject || "-");
  setText(
    "#fx-admin-support-detail-meta",
    `${ticket.scope || "Atendimento"} - ${ticket.counterpart || "sem referencia"}`
  );
  setText("#fx-admin-support-detail-scope", ticket.scope || "-");
  setText("#fx-admin-support-detail-counterpart", ticket.counterpart || "-");
  setText("#fx-admin-support-detail-channel", ticket.channel || "-");
  setText("#fx-admin-support-detail-priority", ticket.priority || "-");
  setText("#fx-admin-support-detail-team", ticket.assigned_team || "-");
  setText("#fx-admin-support-detail-created-at", formatDateTime(ticket.created_at));
  setText("#fx-admin-support-detail-last-message-at", formatDateTime(ticket.last_message_at));
  setText("#fx-admin-support-detail-status-label", ticket.status || "-");

  const badge = document.querySelector("#fx-admin-support-detail-status-label");
  if (badge) {
    badge.className = `fx-status ${ticket.status_type || "warning"}`;
  }

  if (statusSelect) {
    const normalizedStatus = {
      aberto: "open",
      "em andamento": "in_progress",
      respondido: "answered",
      concluido: "resolved"
    }[String(ticket.status || "").toLowerCase()] || ticket.status_key || "open";
    statusSelect.value = normalizedStatus;
  }

  if (stream) {
    const messages = thread.messages || [];
    stream.innerHTML = messages.length
      ? messages
          .map(
            (message) => `
              <article class="fx-message-bubble ${message.direction === "outgoing" ? "is-outgoing" : "is-incoming"}">
                <strong>${message.author}</strong>
                <p>${message.body}</p>
                <small class="fx-copy-sm">${message.time}</small>
              </article>
            `
          )
          .join("")
      : `<div class="fx-note">Nenhuma mensagem registrada neste ticket.</div>`;
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

function renderAdminAccess(payload) {
  const summary = document.querySelector("#fx-admin-access-summary");
  const roles = document.querySelector("#fx-admin-access-roles");
  const members = document.querySelector("#fx-admin-access-members");

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

  if (roles) {
    roles.innerHTML = (payload.roles || [])
      .map(
        (role) => `
          <article class="fx-access-role-card">
            <div class="fx-card-header">
              <div>
                <h3 class="fx-title-sm">${role.name}</h3>
                <p class="fx-copy-sm">${role.description || "Perfil administrativo da plataforma."}</p>
              </div>
              <span class="fx-pill">${role.slug}</span>
            </div>
            <div class="fx-inline-actions">
              ${(role.permissions || []).map((permission) => `<span class="fx-tag">${permission}</span>`).join("")}
            </div>
          </article>
        `
      )
      .join("");
  }

  if (!members) return;

  if (!(payload.members || []).length) {
    members.innerHTML = `<div class="fx-note">Nenhum membro administrativo cadastrado.</div>`;
    return;
  }

  members.innerHTML = (payload.members || [])
    .map(
      (member) => `
        <article class="fx-access-member-card" data-member-id="${member.id}">
          <div class="fx-card-header">
            <div>
              <h3 class="fx-title-sm">${member.full_name}</h3>
              <p class="fx-copy-sm">${member.role_label} · ${member.department}</p>
            </div>
            <span class="fx-status ${member.status_type}">${member.status}</span>
          </div>
          <div class="fx-access-member-meta">
            <span>${member.email}</span>
            <span>${member.phone}</span>
            <span>Ultimo acesso: ${member.last_login_at}</span>
          </div>
          <div class="fx-inline-actions">
            ${(member.permissions || []).map((permission) => `<span class="fx-tag">${permission}</span>`).join("")}
          </div>
          <div class="fx-inline-actions">
            <button class="fx-button-ghost js-admin-access-edit" type="button" data-member-id="${member.id}">Editar</button>
            <button class="fx-button-ghost js-admin-access-status" type="button" data-member-id="${member.id}" data-status="${member.status_key === "active" ? "suspended" : "active"}">
              ${member.status_key === "active" ? "Suspender" : "Ativar"}
            </button>
            <button class="fx-button-ghost js-admin-access-status" type="button" data-member-id="${member.id}" data-status="${member.status_key === "blocked" ? "active" : "blocked"}">
              ${member.status_key === "blocked" ? "Reativar" : "Bloquear"}
            </button>
          </div>
        </article>
      `
    )
    .join("");
}

function resetAdminAccessForm(payload) {
  setInputValue("#fx-admin-access-member-id", "");
  setInputValue("#fx-admin-access-full-name", "");
  setInputValue("#fx-admin-access-email", "");
  setInputValue("#fx-admin-access-phone", "");
  setInputValue("#fx-admin-access-department", "Operacao");
  setInputValue("#fx-admin-access-role", payload.allowed_roles?.[0]?.slug || "admin_operacional");
  setInputValue("#fx-admin-access-status", "active");
  setText("#fx-admin-access-form-title", "Novo membro administrativo");
  const submit = document.querySelector("#fx-admin-access-submit");
  if (submit) submit.textContent = "Salvar acesso";
}

function fillAdminAccessForm(payload, memberId) {
  const member = (payload.members || []).find((item) => item.id === memberId);
  if (!member) return;

  setInputValue("#fx-admin-access-member-id", member.id);
  setInputValue("#fx-admin-access-full-name", member.full_name);
  setInputValue("#fx-admin-access-email", member.email);
  setInputValue("#fx-admin-access-phone", member.phone === "-" ? "" : member.phone);
  setInputValue("#fx-admin-access-department", member.department);
  setInputValue("#fx-admin-access-role", member.role_slug);
  setInputValue("#fx-admin-access-status", member.status_key);
  setText("#fx-admin-access-form-title", `Editar ${member.full_name}`);
  const submit = document.querySelector("#fx-admin-access-submit");
  if (submit) submit.textContent = "Atualizar acesso";
}

async function handlePermissionsScreen() {
  const form = document.querySelector("#fx-admin-access-form");
  const members = document.querySelector("#fx-admin-access-members");
  const feedbackSelector = "#fx-admin-access-feedback";

  if (!form) return;

  let payload = await getAdminAccess();
  renderAdminAccess(payload);
  resetAdminAccessForm(payload);
  hideFeedback(feedbackSelector);

  if (members && members.dataset.bound !== "true") {
    members.dataset.bound = "true";
    members.addEventListener("click", async (event) => {
      const edit = event.target.closest(".js-admin-access-edit");
      if (edit) {
        fillAdminAccessForm(payload, edit.dataset.memberId);
        hideFeedback(feedbackSelector);
        return;
      }

      const statusButton = event.target.closest(".js-admin-access-status");
      if (!statusButton) return;

      try {
        payload = await updateAdminAccessMemberStatus(
          statusButton.dataset.memberId,
          statusButton.dataset.status
        );
        renderAdminAccess(payload);
        showFeedback(feedbackSelector, "Status do acesso atualizado com sucesso.");
      } catch (error) {
        showFeedback(
          feedbackSelector,
          error?.message || "Nao foi possivel atualizar o status do membro.",
          "danger"
        );
      }
    });
  }

  const resetButton = document.querySelector("#fx-admin-access-reset");
  if (resetButton && resetButton.dataset.bound !== "true") {
    resetButton.dataset.bound = "true";
    resetButton.addEventListener("click", () => {
      resetAdminAccessForm(payload);
      hideFeedback(feedbackSelector);
    });
  }

  if (form.dataset.bound === "true") return;
  form.dataset.bound = "true";

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const memberId = document.querySelector("#fx-admin-access-member-id")?.value?.trim() || "";
    const body = {
      full_name: document.querySelector("#fx-admin-access-full-name")?.value?.trim() || "",
      email: document.querySelector("#fx-admin-access-email")?.value?.trim() || "",
      phone: document.querySelector("#fx-admin-access-phone")?.value?.trim() || "",
      department: document.querySelector("#fx-admin-access-department")?.value?.trim() || "",
      role_slug: document.querySelector("#fx-admin-access-role")?.value || "admin_operacional",
      status: document.querySelector("#fx-admin-access-status")?.value || "active"
    };

    try {
      payload = memberId
        ? await updateAdminAccessMember(memberId, body)
        : await createAdminAccessMember(body);
      renderAdminAccess(payload);
      resetAdminAccessForm(payload);
      showFeedback(feedbackSelector, memberId ? "Acesso atualizado com sucesso." : "Acesso criado com sucesso.");
    } catch (error) {
      showFeedback(
        feedbackSelector,
        error?.message || "Nao foi possivel salvar o acesso administrativo.",
        "danger"
      );
    }
  });
}

function renderAdminNotifications(payload) {
  const summary = document.querySelector("#fx-admin-notifications-summary");
  const list = document.querySelector("#fx-admin-notifications-list");

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

  if (!(payload.items || []).length) {
    list.innerHTML = `<div class="fx-note">Nenhuma notificacao administrativa registrada.</div>`;
    return;
  }

  list.innerHTML = (payload.items || [])
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
            ${!item.is_read ? `<button class="fx-button-ghost js-admin-notification-read" type="button" data-notification-id="${item.id}">Marcar como lida</button>` : ""}
          </div>
        </article>
      `
    )
    .join("");
}

async function handleNotificationsScreen() {
  const list = document.querySelector("#fx-admin-notifications-list");
  let payload = await getAdminNotifications();
  renderAdminNotifications(payload);
  hideFeedback("#fx-admin-notifications-feedback");

  let pollingHandle = window.setInterval(async () => {
    try {
      payload = await getAdminNotifications();
      renderAdminNotifications(payload);
    } catch (_error) {
      // Mantem a ultima renderizacao.
    }
  }, 30000);

  window.addEventListener(
    "beforeunload",
    () => {
      if (pollingHandle) {
        window.clearInterval(pollingHandle);
        pollingHandle = null;
      }
    },
    { once: true }
  );

  list?.addEventListener("click", async (event) => {
    const trigger = event.target.closest(".js-admin-notification-read");
    if (!trigger) return;

    try {
      payload = await markAdminNotificationRead(trigger.dataset.notificationId);
      renderAdminNotifications(payload);
      showFeedback("#fx-admin-notifications-feedback", "Notificacao marcada como lida.");
    } catch (error) {
      showFeedback(
        "#fx-admin-notifications-feedback",
        error?.message || "Nao foi possivel atualizar a notificacao.",
        "danger"
      );
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

  const statusInput = document.querySelector("#fx-admin-order-status-input");
  if (statusInput) {
    statusInput.value = order.status_key || "pending_acceptance";
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
            <a class="fx-button-secondary" href="./${scope === "partner" ? "partner" : "driver"}-approval-detail.html?${scope}=${item.id}">Analisar</a>
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

function renderApprovalDetail(type, data) {
  const approval = data?.approval || {};
  const documents = data?.documents || [];
  const history = data?.review_history || [];

  const prefix = type === "partner" ? "partner" : "driver";

  setText(`#fx-admin-${prefix}-approval-name`, approval.name || "-");
  setText(`#fx-admin-${prefix}-approval-subtitle`, type === "partner"
    ? `${approval.city || "-"} - ${approval.state || "-"}`
    : `${approval.modal || "-"} - ${approval.city || "-"} - ${approval.state || "-"}`);
  setText(`#fx-admin-${prefix}-approval-status`, approval.status || "-");

  const statusBadge = document.querySelector(`#fx-admin-${prefix}-approval-status`);
  if (statusBadge) {
    statusBadge.className = `fx-status ${approval.status_type || "warning"}`;
  }

  const fieldMap = type === "partner"
    ? {
        "#fx-admin-partner-approval-legal-name": approval.legal_name,
        "#fx-admin-partner-approval-document": approval.document_number,
        "#fx-admin-partner-approval-store-email": approval.store_email,
        "#fx-admin-partner-approval-store-phone": approval.store_phone,
        "#fx-admin-partner-approval-owner-name": approval.owner_name,
        "#fx-admin-partner-approval-owner-email": approval.owner_email,
        "#fx-admin-partner-approval-owner-phone": approval.owner_phone,
        "#fx-admin-partner-approval-account-status": approval.account_status
      }
    : {
        "#fx-admin-driver-approval-email": approval.email,
        "#fx-admin-driver-approval-phone": approval.phone,
        "#fx-admin-driver-approval-modal": approval.modal,
        "#fx-admin-driver-approval-bank": approval.bank_account,
        "#fx-admin-driver-approval-rating": approval.rating,
        "#fx-admin-driver-approval-last-active": approval.last_active_at
      };

  Object.entries(fieldMap).forEach(([selector, value]) => setText(selector, value || "-"));

  const docsContainer = document.querySelector(`#fx-admin-${prefix}-approval-documents`);
  if (docsContainer) {
    docsContainer.innerHTML = documents.length
      ? documents
          .map(
            (document) => `
              <div class="fx-order-line">
                <div>
                  <strong>${document.label}</strong>
                  <p class="fx-copy-sm">${document.type} · ${document.file_name}</p>
                </div>
                <div>
                  <span class="fx-status ${document.status_type || "warning"}">${document.status}</span>
                  <p class="fx-copy-sm">${document.meta || "-"} · ${document.updated_at || "-"}</p>
                </div>
              </div>
            `
          )
          .join("")
      : `<div class="fx-note">Nenhum documento registrado para esta analise.</div>`;
  }

  const historyContainer = document.querySelector(`#fx-admin-${prefix}-approval-history`);
  if (historyContainer) {
    historyContainer.innerHTML = history.length
      ? history
          .map(
            (entry) => `
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
            `
          )
          .join("")
      : `<div class="fx-note">Nenhuma decisao registrada nesta analise.</div>`;
  }
}

function getApprovalIdFromLocation(type) {
  const params = new URLSearchParams(window.location.search);
  return params.get(type) || "";
}

async function handleApprovalDetailScreen(type) {
  const approvalId = getApprovalIdFromLocation(type);
  const feedbackSelector = `#fx-admin-${type}-approval-feedback`;
  const decisionForm = document.querySelector(`#fx-admin-${type}-approval-decision-form`);
  const decisionInput = document.querySelector(`#fx-admin-${type}-approval-decision-input`);
  const noteInput = document.querySelector(`#fx-admin-${type}-approval-note-input`);

  if (!approvalId) {
    showFeedback(feedbackSelector, "Cadastro nao informado para analise.", "danger");
    return;
  }

  const loader = type === "partner" ? getAdminPartnerApprovalDetail : getAdminDriverApprovalDetail;
  const resolver = type === "partner" ? reviewAdminPartnerApproval : reviewAdminDriverApproval;

  let payload = await loader(approvalId);
  renderApprovalDetail(type, payload);
  hideFeedback(feedbackSelector);

  if (decisionForm && decisionForm.dataset.bound !== "true") {
    decisionForm.dataset.bound = "true";
    decisionForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      try {
        payload = await resolver(approvalId, {
          decision: decisionInput?.value || "reject",
          note: noteInput?.value?.trim() || ""
        });
        renderApprovalDetail(type, payload);
        if (noteInput) noteInput.value = "";
        showFeedback(feedbackSelector, "Analise registrada com sucesso.");
      } catch (error) {
        showFeedback(
          feedbackSelector,
          error?.message || "Nao foi possivel registrar a analise.",
          "danger"
        );
      }
    });
  }
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
  const statusForm = document.querySelector("#fx-admin-order-status-form");
  const noteForm = document.querySelector("#fx-admin-order-note-form");
  const statusInput = document.querySelector("#fx-admin-order-status-input");
  const statusNote = document.querySelector("#fx-admin-order-status-note");
  const noteInput = document.querySelector("#fx-admin-order-note-input");

  if (!orderId) {
    if (feedback) {
      feedback.hidden = false;
      feedback.dataset.tone = "danger";
      feedback.textContent = "Pedido nao informado para consulta.";
    }
    return;
  }

  try {
    let detail = await getAdminOrderDetail(orderId);
    renderAdminOrderDetail(detail);
    hideFeedback("#fx-admin-order-detail-feedback");

    if (statusForm && statusForm.dataset.bound !== "true") {
      statusForm.dataset.bound = "true";
      statusForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        try {
          detail = await updateAdminOrderStatus(orderId, {
            status: statusInput?.value || "pending_acceptance",
            note: statusNote?.value?.trim() || ""
          });
          renderAdminOrderDetail(detail);
          if (statusNote) statusNote.value = "";
          showFeedback("#fx-admin-order-detail-feedback", "Status do pedido atualizado com sucesso.");
        } catch (error) {
          showFeedback(
            "#fx-admin-order-detail-feedback",
            error?.message || "Nao foi possivel atualizar o status do pedido.",
            "danger"
          );
        }
      });
    }

    if (noteForm && noteForm.dataset.bound !== "true") {
      noteForm.dataset.bound = "true";
      noteForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const note = noteInput?.value?.trim() || "";
        if (!note) {
          showFeedback("#fx-admin-order-detail-feedback", "Informe a observacao antes de salvar.", "danger");
          return;
        }

        try {
          detail = await addAdminOrderNote(orderId, { note });
          renderAdminOrderDetail(detail);
          if (noteInput) noteInput.value = "";
          showFeedback("#fx-admin-order-detail-feedback", "Observacao registrada com sucesso.");
        } catch (error) {
          showFeedback(
            "#fx-admin-order-detail-feedback",
            error?.message || "Nao foi possivel registrar a observacao do pedido.",
            "danger"
          );
        }
      });
    }
  } catch (error) {
    if (feedback) {
      feedback.hidden = false;
      feedback.dataset.tone = "danger";
      feedback.textContent = error?.message || "Nao foi possivel carregar o pedido.";
    }
  }
}

function getSupportTicketIdFromLocation() {
  const params = new URLSearchParams(window.location.search);
  return params.get("ticket") || "";
}

async function handleSupportDetailScreen() {
  const ticketId = getSupportTicketIdFromLocation();
  const replyForm = document.querySelector("#fx-admin-support-reply-form");
  const statusForm = document.querySelector("#fx-admin-support-status-form");
  const replyBody = document.querySelector("#fx-admin-support-reply-body");
  const statusInput = document.querySelector("#fx-admin-support-status-input");
  const statusNote = document.querySelector("#fx-admin-support-status-note");
  const feedbackSelector = "#fx-admin-support-feedback";

  if (!ticketId) {
    showFeedback(feedbackSelector, "Ticket nao informado para atendimento.", "danger");
    renderAdminSupportThread(null);
    return;
  }

  let thread = await getAdminSupportThread(ticketId);
  renderAdminSupportThread(thread);
  hideFeedback(feedbackSelector);

  if (replyForm && replyForm.dataset.bound !== "true") {
    replyForm.dataset.bound = "true";
    replyForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      const body = replyBody?.value?.trim() || "";
      if (!body) {
        showFeedback(feedbackSelector, "Informe a resposta antes de enviar.", "danger");
        return;
      }

      try {
        thread = await replyAdminSupportThread(ticketId, body);
        renderAdminSupportThread(thread);
        if (replyBody) replyBody.value = "";
        showFeedback(feedbackSelector, "Resposta registrada com sucesso.");
      } catch (error) {
        showFeedback(
          feedbackSelector,
          error?.message || "Nao foi possivel responder o ticket.",
          "danger"
        );
      }
    });
  }

  if (statusForm && statusForm.dataset.bound !== "true") {
    statusForm.dataset.bound = "true";
    statusForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      try {
        thread = await updateAdminSupportTicketStatus(ticketId, {
          status: statusInput?.value || "open",
          note: statusNote?.value?.trim() || ""
        });
        renderAdminSupportThread(thread);
        if (statusNote) statusNote.value = "";
        showFeedback(feedbackSelector, "Status do ticket atualizado com sucesso.");
      } catch (error) {
        showFeedback(
          feedbackSelector,
          error?.message || "Nao foi possivel atualizar o ticket.",
          "danger"
        );
      }
    });
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

  if (screen === "partner-approval-detail") {
    await handleApprovalDetailScreen("partner");
    return;
  }

  if (screen === "driver-approval-detail") {
    await handleApprovalDetailScreen("driver");
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

  if (screen === "support-detail") {
    await handleSupportDetailScreen();
    return;
  }

  if (screen === "permissions") {
    await handlePermissionsScreen();
    return;
  }

  if (screen === "notifications") {
    await handleNotificationsScreen();
    return;
  }

  if (screen === "settings") {
    await handleSettingsScreen();
    return;
  }

  if (screen === "analytics") {
    renderAnalytics(await getAdminAnalytics());
    return;
  }

  if (screen === "reports") {
    renderReports(await getAdminReports());
    return;
  }

  const data = await getAdminData();

  if (screen === "audit") {
    renderAudit(data.audit);
  }
}

boot();

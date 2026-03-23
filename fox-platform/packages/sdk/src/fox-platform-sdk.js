const MOCK_BASE = new URL("../../../apps/api/mock/v1/", import.meta.url);
const SESSION_KEY = "fox-platform-session";

const LOGIN_ROUTES = {
  partner: "../../../apps/partner-portal/src/login.html",
  admin: "../../../apps/admin/src/login.html",
  driver: "../../../apps/driver-portal/src/login.html"
};

function normalizeBase(base) {
  return base.endsWith("/") ? base : `${base}/`;
}

function resolveApiBase() {
  const explicit =
    document.documentElement.dataset.fxApiBase ||
    window.__FOX_API_BASE__ ||
    window.localStorage.getItem("fox-platform-api-base");

  if (explicit) {
    return normalizeBase(explicit);
  }

  const hostname = window.location.hostname;
  const isLocalHost =
    hostname === "127.0.0.1" ||
    hostname === "localhost" ||
    hostname === "";

  if (
    window.location.protocol === "file:" ||
    (isLocalHost && window.location.port !== "8099")
  ) {
    return "http://127.0.0.1:8099/";
  }

  if (window.location.protocol === "http:" || window.location.protocol === "https:") {
    return normalizeBase(`${window.location.origin}/`);
  }

  return normalizeBase(new URL("../../../apps/api/public/", import.meta.url).href);
}

const API_BASE = resolveApiBase();

function buildLoginUrl(app) {
  return new URL(LOGIN_ROUTES[app], import.meta.url).href;
}

function buildMockUrl(resource) {
  return new URL(resource, MOCK_BASE);
}

function buildApiUrl(path) {
  const sanitizedPath = path.replace(/^\/+/, "");
  if (API_BASE.endsWith("/api/") && sanitizedPath.startsWith("api/")) {
    return new URL(sanitizedPath.slice(4), API_BASE);
  }
  return new URL(sanitizedPath, API_BASE);
}

async function readJson(response) {
  const raw = await response.text();
  if (!raw) {
    return {};
  }

  try {
    return JSON.parse(raw);
  } catch (_error) {
    return {};
  }
}

async function loadJson(resource) {
  const response = await fetch(buildMockUrl(resource));
  if (!response.ok) {
    throw new Error(`Nao foi possivel carregar ${resource}`);
  }
  return response.json();
}

async function requestApi(path, { method = "GET", body, auth = true, allowFallback = false, headers = {} } = {}) {
  const session = auth ? getSession() : null;
  const requestHeaders = {
    Accept: "application/json",
    "X-Device-Name": "web",
    ...headers
  };

  if (body !== undefined) {
    requestHeaders["Content-Type"] = "application/json";
  }

  if (auth && session?.accessToken) {
    requestHeaders.Authorization = `Bearer ${session.accessToken}`;
  }

  try {
    const response = await fetch(buildApiUrl(path), {
      method,
      headers: requestHeaders,
      body: body !== undefined ? JSON.stringify(body) : undefined
    });
    const payload = await readJson(response);

    if (response.ok) {
      return payload;
    }

    const isFoxPlatformError = Boolean(payload?.error?.code);
    const isGenericGatewayStyleError =
      typeof payload?.detail === "string" &&
      payload.detail.trim().toLowerCase() === "bad request";

    if (
      allowFallback &&
      (!isFoxPlatformError || isGenericGatewayStyleError || [400, 404, 405, 500, 502, 503].includes(response.status))
    ) {
      return null;
    }

    const error = new Error(
      payload?.error?.message ||
        (isGenericGatewayStyleError ? "A requisicao nao foi atendida pela API da Fox Platform." : null) ||
        payload?.detail ||
        payload?.message ||
        `Nao foi possivel concluir a requisicao (${response.status}).`
    );
    error.status = response.status;
    error.payload = payload;
    throw error;
  } catch (error) {
    if (allowFallback && (error instanceof TypeError || error?.status === undefined)) {
      return null;
    }

    throw error;
  }
}

async function mockLogin(role, email, password) {
  const data = await loadJson("auth-users.json");
  const user = data.users.find(
    (item) =>
      item.role === role &&
      item.email.toLowerCase() === email.trim().toLowerCase() &&
      item.password === password
  );

  if (!user) {
    throw new Error("Credenciais invalidas para este portal.");
  }

  const session = {
    id: user.id,
    role: user.role,
    guard: user.role,
    name: user.name,
    accountLabel: user.accountLabel,
    email: user.email,
    permissions: user.permissions || [],
    partnerAccess: user.partnerAccess || null,
    loggedAt: new Date().toISOString(),
    source: "mock"
  };

  setSession(session);
  return session;
}

function unwrapPayload(payload) {
  if (payload && typeof payload === "object" && "data" in payload) {
    return payload.data;
  }

  return payload;
}

export function getSession() {
  try {
    const raw = window.localStorage.getItem(SESSION_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch (_error) {
    return null;
  }
}

export function setSession(payload) {
  window.localStorage.setItem(SESSION_KEY, JSON.stringify(payload));
}

export function clearSession() {
  window.localStorage.removeItem(SESSION_KEY);
}

export async function login(role, email, password) {
  const payload = await requestApi("api/v1/auth/login", {
    method: "POST",
    body: {
      email: email.trim(),
      password,
      guard: role
    },
    auth: false,
    allowFallback: true
  });

  if (!payload) {
    return mockLogin(role, email, password);
  }

  const data = unwrapPayload(payload);
  const session = {
    id: data.user.id,
    role,
    guard: role,
    name: data.user.name,
    accountLabel:
      data.user.partner_access?.trade_name ||
      data.user.partner_access?.store_name ||
      data.user.name,
    email: data.user.email,
    accessToken: data.access_token,
    refreshToken: data.refresh_token,
    expiresIn: data.expires_in,
    loggedAt: new Date().toISOString(),
    source: "api",
    roles: data.user.roles ?? [],
    permissions: data.user.permissions ?? [],
    partnerAccess: data.user.partner_access ?? null
  };

  setSession(session);
  return session;
}

export function requireSession(app, role) {
  const session = getSession();
  const activeRole = session?.guard || session?.role;
  if (!session || activeRole !== role) {
    window.location.href = buildLoginUrl(app);
    return null;
  }
  return session;
}

export async function logout() {
  const session = getSession();
  if (session?.refreshToken) {
    try {
      await requestApi("api/v1/auth/logout", {
        method: "POST",
        body: { refresh_token: session.refreshToken },
        allowFallback: true
      });
    } catch (_error) {
      // Logout local permanece mesmo com falha da API.
    }
  }

  clearSession();
}

export function bindLogout(app, selector = ".js-fx-logout, a[href$=\"login.html\"]") {
  document.querySelectorAll(selector).forEach((element) => {
    element.addEventListener("click", async (event) => {
      event.preventDefault();
      await logout();
      window.location.href = buildLoginUrl(app);
    });
  });
}

export function injectSessionLabel(selector, session) {
  const target = document.querySelector(selector);
  if (target && session?.accountLabel) {
    target.textContent = session.accountLabel;
  }
}

export async function getAuthenticatedUser() {
  const payload = await requestApi("api/v1/auth/me", {
    allowFallback: true
  });

  if (!payload) {
    const session = getSession();
    if (!session) {
      return null;
    }

    return {
      id: session.id,
      name: session.name,
      email: session.email,
      guard: session.guard || session.role,
      roles: (session.roles || []).map((slug) => ({ slug })),
      permissions: session.permissions || [],
      partner_access: session.partnerAccess || null
    };
  }

  return unwrapPayload(payload);
}

export async function getPartnerProfile() {
  const payload = await requestApi("api/v1/partner/profile", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  return data.profile;
}

export async function updatePartnerProfile(body) {
  const payload = await requestApi("api/v1/partner/profile", {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return {
    ...(await getPartnerProfile()),
    ...body
  };
}

export async function getPartnerStore() {
  const payload = await requestApi("api/v1/partner/store", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  return data.store;
}

export async function updatePartnerStore(body) {
  const payload = await requestApi("api/v1/partner/store", {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerStore();

  return {
    ...current,
    store: {
      ...current.store,
      ...body
    }
  };
}

export async function updatePartnerStoreHours(hours) {
  const payload = await requestApi("api/v1/partner/store/hours", {
    method: "PUT",
    body: { hours },
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return {
    hours
  };
}

export async function addPartnerStoreDocument(document) {
  const payload = await requestApi("api/v1/partner/store/documents", {
    method: "POST",
    body: document,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerStore();
  const nextDocument = {
    id: `mock-${Date.now()}`,
    created_at: new Date().toISOString(),
    ...document
  };

  return {
    document: nextDocument,
    documents: [nextDocument, ...(current.documents || [])]
  };
}

export async function getPartnerTeam() {
  const payload = await requestApi("api/v1/partner/team", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  return data.team || { summary: [], members: [] };
}

export async function createPartnerTeamMember(body) {
  const payload = await requestApi("api/v1/partner/team", {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerTeam();
  const nextMember = {
    id: `team-${Date.now()}`,
    full_name: body.full_name,
    email: body.email,
    phone: body.phone,
    role_slug: body.role_slug,
    role_label: body.role_slug,
    status: "convite pendente",
    status_key: "invited",
    status_type: "warning",
    permissions: body.permissions || [],
    last_login_at: "-",
    created_at: new Date().toLocaleString("pt-BR")
  };

  return {
    summary: current.summary || [],
    members: [nextMember, ...(current.members || [])]
  };
}

export async function updatePartnerTeamMember(memberId, body) {
  const payload = await requestApi(`api/v1/partner/team/${memberId}`, {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerTeam();
  return {
    ...current,
    members: (current.members || []).map((member) =>
      member.id === memberId
        ? {
            ...member,
            ...body,
            role_label: body.role_slug || member.role_label
          }
        : member
    )
  };
}

export async function updatePartnerTeamMemberStatus(memberId, status) {
  const payload = await requestApi(`api/v1/partner/team/${memberId}/status`, {
    method: "PUT",
    body: { status },
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerTeam();
  const labelMap = {
    active: ["ativo", "success"],
    suspended: ["suspenso", "danger"],
    invited: ["convite pendente", "warning"]
  };

  return {
    ...current,
    members: (current.members || []).map((member) =>
      member.id === memberId
        ? {
            ...member,
            status_key: status,
            status: labelMap[status]?.[0] || status,
            status_type: labelMap[status]?.[1] || "warning"
          }
        : member
    )
  };
}

export async function getPartnerCatalog() {
  const payload = await requestApi("api/v1/partner/catalog/products", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  return data.catalog;
}

export async function getPartnerDashboard() {
  const payload = await requestApi("api/v1/partner/dashboard", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  return {
    ...data.dashboard,
    orders: (data.orders || []).slice(0, 3),
    top_products: (data.catalog?.products || []).slice(0, 3).map((product) => ({
      name: product.name,
      category: product.category,
      sold_count: product.sold_count,
      status:
        product.inventory_state === "out"
          ? "esgotado"
          : product.inventory_state === "low"
            ? "estoque baixo"
            : product.status === "paused"
              ? "pausado"
              : "ativo",
      status_type:
        product.inventory_state === "out" || product.inventory_state === "low"
          ? "warning"
          : product.status === "paused"
            ? "danger"
            : "success"
    })),
    health: [
      {
        title: "Catalogo",
        text: `${data.catalog?.inventory?.normal_count || 0} itens com disponibilidade normal e ${data.catalog?.inventory?.low_stock_count || 0} em atencao.`
      },
      {
        title: "Horarios",
        text: "Turnos configurados para operar durante a semana e fim de semana."
      },
      {
        title: "Pedidos",
        text: `${(data.orders || []).length} pedidos visiveis no painel com acompanhamento por status.`
      },
      {
        title: "Financeiro",
        text: data.finance?.balanceNote || "Resumo financeiro da operacao disponivel no portal."
      }
    ]
  };
}

export async function getPartnerFinance() {
  const payload = await requestApi("api/v1/partner/finance/summary", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  const finance = data.finance || {};

  return {
    balance: finance.balance || "R$ 0,00",
    balanceNote: finance.balanceNote || "Resumo financeiro indisponivel no momento.",
    stats: finance.stats || [],
    payouts:
      finance.payouts || [
        {
          date: "24/03/2026",
          title: "Repasse referente ao periodo 17/03 a 23/03.",
          text: "Repasse semanal consolidado a partir dos pedidos concluidos no ciclo.",
          status: "previsto",
          status_type: "warning",
          amount: "R$ 3.182,40"
        },
        {
          date: "31/03/2026",
          title: "Repasse referente ao periodo 24/03 a 30/03.",
          text: "Repasse em conferencia bancaria apos fechamento da operacao.",
          status: "em processamento",
          status_type: "warning",
          amount: "R$ 2.940,18"
        }
      ],
    bank_account:
      finance.bank_account || {
        bank_name: "Banco Fox",
        branch_number: "1524",
        account_number: "45897-2",
        status: "validada",
        status_type: "success"
      },
    transactions:
      finance.transactions || [
        {
          date: "20/03/2026",
          description: "Pedidos concluidos no turno do almoco",
          type: "credito operacional",
          status: "processado",
          status_type: "success",
          value: "+ R$ 1.284,70"
        },
        {
          date: "20/03/2026",
          description: "Taxas da plataforma e pagamentos online",
          type: "taxa da plataforma",
          status: "processado",
          status_type: "success",
          value: "- R$ 318,42"
        },
        {
          date: "18/03/2026",
          description: "Ajuste de cancelamento reembolsado",
          type: "ajuste",
          status: "revisar",
          status_type: "danger",
          value: "- R$ 42,10"
        }
      ]
  };
}

export async function getPartnerOrders() {
  const payload = await requestApi("api/v1/partner/orders", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  return {
    totals: {
      total: (data.orders || []).length,
      pending: (data.orders || []).filter((item) => item.status === "aguardando aceite").length,
      critical: (data.orders || []).filter((item) => item.status === "aguardando aceite" || item.status === "cancelado").length
    },
    orders: data.orders || []
  };
}

export async function getPartnerOrderDetail(orderId) {
  const payload = await requestApi(`api/v1/partner/orders/${orderId}`, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const orders = await getPartnerOrders();
  const item = (orders.orders || []).find((entry) => (entry.order_id || entry.id) === orderId);

  return {
    order: {
      id: item?.id || orderId,
      order_id: item?.order_id || orderId,
      store_name: orders.store?.trade_name || "Fox Delivery",
      customer: item?.customer || "Cliente Fox",
      customer_phone: "-",
      customer_address: "-",
      driver_name: item?.driver_name || "sem atribuicao",
      status: item?.status || "em analise",
      status_key: item?.status_key || "pending_acceptance",
      status_type: item?.statusType || "warning",
      payment_method: "Cartao online",
      payment_status: "Pago",
      subtotal: item?.value || "R$ 0,00",
      delivery_fee: "R$ 0,00",
      total: item?.value || "R$ 0,00",
      placed_at: item?.placed_at || "-",
      accepted_at: "-",
      completed_at: "-",
      cancelled_at: "-",
      sla: item?.sla || "-"
    },
    items: [],
    timeline: []
  };
}

export async function updatePartnerOrderStatus(orderId, body) {
  const payload = await requestApi(`api/v1/partner/orders/${orderId}/status`, {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  const orders = (data.orders || []).map((item) => {
    if (item.order_id !== orderId && item.id !== orderId && item.id !== `#${orderId}`) {
      return item;
    }

    const labelMap = {
      pending_acceptance: ["aguardando aceite", "warning", "Aceitar"],
      accepted: ["aceito", "success", "Iniciar preparo"],
      preparing: ["em preparo", "success", "Atualizar"],
      ready_for_pickup: ["pronto para retirada", "warning", "Sinalizar coleta"],
      on_route: ["em rota", "success", "Acompanhar entrega"],
      completed: ["concluido", "success", "Ver detalhes"],
      cancelled: ["cancelado", "danger", "Registrar motivo"]
    };

    const [status, statusType, action] = labelMap[body.status] || [body.status, "warning", "Atualizar"];

    return {
      ...item,
      order_id: item.order_id || orderId,
      status,
      status_key: body.status,
      statusType,
      action
    };
  });

  return {
    totals: {
      total: orders.length,
      pending: orders.filter((item) => item.status_key === "pending_acceptance" || item.status === "aguardando aceite").length,
      critical: orders.filter((item) => item.status_key === "pending_acceptance" || item.status === "aguardando aceite" || item.status_key === "cancelled" || item.status === "cancelado").length
    },
    orders
  };
}

export async function createPartnerProduct(body) {
  const payload = await requestApi("api/v1/partner/catalog/products", {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerCatalog();
  const category = (current.categories || []).find((item) => item.id === body.category_id);
  const nextProduct = {
    id: `mock-${Date.now()}`,
    name: body.name,
    description: body.description,
    sku: body.sku,
    price: new Intl.NumberFormat("pt-BR", {
      style: "currency",
      currency: body.currency || "BRL"
    }).format(Number(body.base_price || 0)),
    base_price: Number(body.base_price || 0),
    currency: body.currency || "BRL",
    status: body.status,
    category: category?.name || "Sem categoria",
    category_slug: category?.slug || "sem-categoria",
    stock_quantity: Number(body.stock_quantity || 0),
    min_stock_quantity: Number(body.min_stock_quantity || 0),
    sold_count: 0,
    inventory_state:
      Number(body.stock_quantity || 0) <= 0
        ? "out"
        : Number(body.stock_quantity || 0) <= Number(body.min_stock_quantity || 0)
          ? "low"
          : "normal",
    image_path: body.image_path || ""
  };

  const products = [nextProduct, ...(current.products || [])];

  return {
    ...current,
    product: nextProduct,
    products,
    inventory: {
      low_stock_count: products.filter((item) => item.inventory_state === "low" || item.inventory_state === "out").length,
      paused_count: products.filter((item) => item.status === "paused").length,
      normal_count: products.filter((item) => item.inventory_state === "normal").length,
      review_sla: current.inventory?.review_sla || "15 min"
    }
  };
}

export async function updatePartnerProduct(productId, body) {
  const payload = await requestApi(`api/v1/partner/catalog/products/${productId}`, {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerCatalog();
  const category = (current.categories || []).find((item) => item.id === body.category_id);
  const products = (current.products || []).map((product) => {
    if (product.id !== productId) {
      return product;
    }

    const stockQuantity = Number(body.stock_quantity || 0);
    const minStockQuantity = Number(body.min_stock_quantity || 0);

    return {
      ...product,
      name: body.name,
      description: body.description,
      sku: body.sku,
      price: new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: body.currency || "BRL"
      }).format(Number(body.base_price || 0)),
      base_price: Number(body.base_price || 0),
      currency: body.currency || "BRL",
      status: body.status,
      category: category?.name || product.category,
      category_slug: category?.slug || product.category_slug,
      stock_quantity: stockQuantity,
      min_stock_quantity: minStockQuantity,
      inventory_state:
        stockQuantity <= 0 ? "out" : stockQuantity <= minStockQuantity ? "low" : "normal",
      image_path: body.image_path || product.image_path
    };
  });

  return {
    ...current,
    product: products.find((item) => item.id === productId) || null,
    products,
    inventory: {
      low_stock_count: products.filter((item) => item.inventory_state === "low" || item.inventory_state === "out").length,
      paused_count: products.filter((item) => item.status === "paused").length,
      normal_count: products.filter((item) => item.inventory_state === "normal").length,
      review_sla: current.inventory?.review_sla || "15 min"
    }
  };
}

export async function updatePartnerProductInventory(productId, body) {
  const payload = await requestApi(`api/v1/partner/catalog/products/${productId}/inventory`, {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerCatalog();
  const products = (current.products || []).map((product) => {
    if (product.id !== productId) return product;

    const nextStock = Number(body.stock_quantity ?? product.stock_quantity ?? 0);
    const nextMin = Number(body.min_stock_quantity ?? product.min_stock_quantity ?? 0);
    const inventoryState = nextStock <= 0 ? "out" : (nextStock <= nextMin ? "low" : "normal");

    return {
      ...product,
      stock_quantity: nextStock,
      min_stock_quantity: nextMin,
      status: body.status ?? product.status,
      inventory_state: inventoryState
    };
  });

  const inventory = {
    low_stock_count: products.filter((product) => product.inventory_state === "low" || product.inventory_state === "out").length,
    paused_count: products.filter((product) => product.status === "paused").length,
    normal_count: products.filter((product) => product.inventory_state === "normal").length,
    review_sla: current.inventory?.review_sla || "15 min"
  };

  const updatedProduct = products.find((product) => product.id === productId) || null;

  return {
    product: updatedProduct,
    products,
    inventory
  };
}

export async function getPartnerSupport() {
  const payload = await requestApi("api/v1/partner/support", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  return data.support || { tickets: [] };
}

export async function createPartnerSupportTicket(body) {
  const payload = await requestApi("api/v1/partner/support/tickets", {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerSupport();
  const generatedId = `ticket-${Date.now()}`;
  const next = {
    id: `#SUP-${String(Date.now()).slice(-6)}`,
    ticket_id: generatedId,
    channel: body.channel,
    priority: body.priority,
    last_message_at: new Date().toISOString(),
    status: "aberto",
    statusType: body.priority === "critical" ? "danger" : body.priority === "high" ? "warning" : "success",
    summary: body.subject,
    meta: [`Prioridade ${body.priority}`, "Atualizado agora"]
  };

  return {
    tickets: [next, ...(current.tickets || [])]
  };
}

export async function getPartnerSupportThread(ticketId) {
  const payload = await requestApi(`api/v1/partner/support/${ticketId}`, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  const tickets = data.support?.tickets || [];
  const ticket = tickets.find((item) => item.ticket_id === ticketId) || tickets[0];

  return {
    ticket,
    messages: (data.messages?.[ticket?.ticket_id] || []).map((message) => ({
      ...message,
      time: message.time || "Agora"
    }))
  };
}

export async function replyPartnerSupportThread(ticketId, body) {
  const payload = await requestApi(`api/v1/partner/support/${ticketId}/messages`, {
    method: "POST",
    body: { body },
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerSupportThread(ticketId);
  return {
    ...current,
    messages: [
      ...(current.messages || []),
      {
        id: `msg-${Date.now()}`,
        direction: "outgoing",
        author: "Loja parceira",
        body,
        time: new Date().toLocaleString("pt-BR"),
        role: "partner_owner"
      }
    ]
  };
}

export async function getPartnerNotifications() {
  const payload = await requestApi("api/v1/partner/notifications", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("partner-portal.json");
  return data.notifications || { summary: [], items: [] };
}

export async function markPartnerNotificationRead(notificationId) {
  const payload = await requestApi(`api/v1/partner/notifications/${notificationId}/read`, {
    method: "POST",
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getPartnerNotifications();
  return {
    ...current,
    items: (current.items || []).map((item) =>
      item.id === notificationId ? { ...item, is_read: true } : item
    )
  };
}

export async function getPartnerData() {
  return loadJson("partner-portal.json");
}

export async function getAdminData() {
  return loadJson("admin.json");
}

export async function getAdminDashboard() {
  const payload = await requestApi("api/v1/admin/dashboard", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return {
    ...data.dashboard,
    approvals: data.partnerApprovals || [],
    alerts: (data.support?.priorityQueue || []).map((item) => item.summary)
  };
}

export async function getAdminAnalytics() {
  const payload = await requestApi("api/v1/admin/analytics", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return data.analytics || {
    cards: [],
    status_distribution: [],
    city_distribution: [],
    highlights: []
  };
}

export async function getAdminFinance() {
  const payload = await requestApi("api/v1/admin/finance/overview", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  const finance = data.finance || {};

  return {
    balance: finance.balance || "R$ 0,00",
    balanceNote: finance.balanceNote || "Resumo financeiro indisponivel no momento.",
    stats: finance.stats || [],
    highlights:
      finance.highlights || [
        {
          title: "Repasses em processamento",
          text: "Lotes financeiros com conferencia concluida ou em etapa final antes do envio bancario.",
          meta: ["12 parceiros", "R$ 94.500"],
          action_label: "Abrir lote",
          action_tone: "primary"
        },
        {
          title: "Ajustes e estornos",
          text: "Eventos que precisam de revisao do financeiro antes do fechamento do ciclo da plataforma.",
          meta: ["5 ajustes", "2 estornos"],
          action_label: "Revisar fila",
          action_tone: "secondary"
        }
      ],
    payouts:
      finance.payouts || [
        {
          partner: "Fox Burgers Centro",
          period: "17/03/2026 a 23/03/2026",
          status: "aprovado",
          status_type: "success",
          net_amount: "R$ 8.460,32",
          note: "conta validada"
        },
        {
          partner: "Mercado Nova Rota",
          period: "17/03/2026 a 23/03/2026",
          status: "em conferencia",
          status_type: "warning",
          net_amount: "R$ 6.218,40",
          note: "revisar divergencia de estoque"
        }
      ]
  };
}

export async function getAdminOrders() {
  const payload = await requestApi("api/v1/admin/orders", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return {
    totals: {
      total: (data.orders || []).length,
      critical: (data.orders || []).filter((item) => item.status_key === "pending_acceptance" || item.status_key === "cancelled").length
    },
    items: data.orders || []
  };
}

export async function getAdminOrderDetail(orderId) {
  const payload = await requestApi(`api/v1/admin/orders/${orderId}`, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const orders = await getAdminOrders();
  const item = (orders.items || []).find((entry) => (entry.order_id || entry.id) === orderId);

  return {
    order: {
      id: item?.id || orderId,
      order_id: item?.order_id || orderId,
      store_name: item?.store_name || "Fox Delivery",
      customer: item?.customer || "Cliente Fox",
      customer_phone: "-",
      customer_address: "-",
      driver_name: item?.driver_name || "sem atribuicao",
      status: item?.status || "em analise",
      status_key: item?.status_key || "pending_acceptance",
      status_type: item?.statusType || "warning",
      payment_method: "Cartao online",
      payment_status: "Pago",
      subtotal: item?.value || "R$ 0,00",
      delivery_fee: "R$ 0,00",
      total: item?.value || "R$ 0,00",
      placed_at: "-",
      accepted_at: "-",
      completed_at: "-",
      cancelled_at: "-",
      sla: item?.sla || "-"
    },
    items: [],
    timeline: []
  };
}

function buildFallbackAdminOrderStatusMeta(status) {
  const mapping = {
    pending_acceptance: { label: "aguardando aceite", tone: "warning" },
    accepted: { label: "aceito", tone: "success" },
    preparing: { label: "em preparo", tone: "success" },
    ready_for_pickup: { label: "pronto para retirada", tone: "warning" },
    on_route: { label: "em rota", tone: "success" },
    completed: { label: "concluido", tone: "success" },
    cancelled: { label: "cancelado", tone: "danger" }
  };

  return mapping[status] || { label: status, tone: "warning" };
}

export async function updateAdminOrderStatus(orderId, body) {
  const payload = await requestApi(`api/v1/admin/orders/${orderId}/status`, {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getAdminOrderDetail(orderId);
  const meta = buildFallbackAdminOrderStatusMeta(body.status);
  const nextTimeline = [
    ...(current.timeline || []),
    {
      title: `Status atualizado para ${meta.label}`,
      description: body.note || "Atualizacao manual via Admin Fox Platform.",
      actor: "Fox Platform",
      created_at: "agora"
    }
  ];

  return {
    ...current,
    order: {
      ...current.order,
      status: meta.label,
      status_key: body.status,
      status_type: meta.tone,
      accepted_at:
        body.status === "accepted" && (!current.order?.accepted_at || current.order.accepted_at === "-")
          ? "agora"
          : current.order?.accepted_at || "-",
      completed_at: body.status === "completed" ? "agora" : current.order?.completed_at || "-",
      cancelled_at: body.status === "cancelled" ? "agora" : body.status !== "cancelled" ? "-" : current.order?.cancelled_at || "-"
    },
    timeline: nextTimeline
  };
}

export async function addAdminOrderNote(orderId, body) {
  const payload = await requestApi(`api/v1/admin/orders/${orderId}/note`, {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getAdminOrderDetail(orderId);

  return {
    ...current,
    timeline: [
      ...(current.timeline || []),
      {
        title: "Observacao interna registrada",
        description: body.note,
        actor: "Fox Platform",
        created_at: "agora"
      }
    ]
  };
}

export async function getAdminReports() {
  const payload = await requestApi("api/v1/admin/reports", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return data.reports || {
    summary: [],
    partner_status: [],
    driver_status: [],
    support_teams: [],
    top_stores: []
  };
}

export async function getAdminPartnerApprovals() {
  const payload = await requestApi("api/v1/admin/approvals/partners", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return {
    items: data.partnerApprovals || []
  };
}

export async function getAdminPartnerApprovalDetail(partnerId) {
  const payload = await requestApi(`api/v1/admin/approvals/partners/${partnerId}`, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const list = await getAdminPartnerApprovals();
  const item = (list.items || []).find((entry) => entry.id === partnerId) || list.items?.[0];

  return {
    approval: {
      id: item?.id || partnerId,
      name: item?.name || "Parceiro Fox Delivery",
      legal_name: item?.name || "Empresa parceira",
      document_number: "00.000.000/0001-00",
      status: item?.status || "documentacao pendente",
      status_type: item?.statusType || "warning",
      city: item?.meta?.[0] || "-",
      state: item?.meta?.[1] || "-",
      store_email: "contato@parceirofox.com.br",
      store_phone: "+55 11 90000-0000",
      owner_name: "Responsavel da operacao",
      owner_email: "responsavel@parceirofox.com.br",
      owner_phone: "+55 11 90000-0001",
      account_status: "pendente"
    },
    documents: [
      { label: "Comprovante de CNPJ", type: "cnpj", file_name: "cnpj.pdf", status: "aprovado", status_type: "success", meta: "issuer: Receita Federal", updated_at: "agora" },
      { label: "Alvara de funcionamento", type: "alvara", file_name: "alvara.pdf", status: "pendente", status_type: "warning", meta: "notes: aguardando vigencia", updated_at: "agora" }
    ],
    review_history: [
      { title: "Observacao administrativa", description: "Fila criada para revisao documental.", actor: "Fox Platform", created_at: "agora" }
    ]
  };
}

export async function reviewAdminPartnerApproval(partnerId, body) {
  const payload = await requestApi(`api/v1/admin/approvals/partners/${partnerId}/decision`, {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const detail = await getAdminPartnerApprovalDetail(partnerId);
  const approved = body.decision === "approve";

  return {
    ...detail,
    approval: {
      ...detail.approval,
      status: approved ? "ativo" : "revisao manual",
      status_type: approved ? "success" : "danger",
      account_status: approved ? "ativa" : "revisao manual"
    },
    review_history: [
      {
        title: approved ? "Cadastro aprovado" : "Cadastro movido para revisao",
        description: body.note || (approved ? "Cadastro aprovado pela operacao administrativa." : "Cadastro movido para revisao manual."),
        actor: "Fox Platform",
        created_at: "agora"
      },
      ...(detail.review_history || [])
    ]
  };
}

export async function getAdminDriverApprovals() {
  const payload = await requestApi("api/v1/admin/approvals/drivers", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return {
    items: data.driverApprovals || []
  };
}

export async function getAdminDriverApprovalDetail(driverId) {
  const payload = await requestApi(`api/v1/admin/approvals/drivers/${driverId}`, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const list = await getAdminDriverApprovals();
  const item = (list.items || []).find((entry) => entry.id === driverId) || list.items?.[0];

  return {
    approval: {
      id: item?.id || driverId,
      name: item?.name || "Entregador Fox Delivery",
      email: "entregador@foxdelivery.com.br",
      phone: "+55 11 90000-0003",
      modal: item?.meta?.[0] || "Moto",
      status: item?.status || "documentacao pendente",
      status_type: item?.statusType || "warning",
      city: item?.meta?.[1] || "-",
      state: item?.meta?.[2] || "-",
      bank_account: "Banco Fox 1524 - 99341-0",
      rating: "4,90",
      last_active_at: "agora"
    },
    documents: [
      { label: "Documento de identidade", type: "identidade", file_name: "identidade.pdf", status: "aprovado", status_type: "success", meta: "-", updated_at: "agora" },
      { label: "Comprovante complementar", type: "cadastro", file_name: "cadastro.pdf", status: "pendente", status_type: "warning", meta: "-", updated_at: "agora" }
    ],
    review_history: [
      { title: "Observacao administrativa", description: "Cadastro aguardando validacao complementar.", actor: "Fox Platform", created_at: "agora" }
    ]
  };
}

export async function reviewAdminDriverApproval(driverId, body) {
  const payload = await requestApi(`api/v1/admin/approvals/drivers/${driverId}/decision`, {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const detail = await getAdminDriverApprovalDetail(driverId);
  const approved = body.decision === "approve";

  return {
    ...detail,
    approval: {
      ...detail.approval,
      status: approved ? "ativo" : "revisao manual",
      status_type: approved ? "success" : "danger"
    },
    review_history: [
      {
        title: approved ? "Cadastro aprovado" : "Cadastro movido para revisao",
        description: body.note || (approved ? "Cadastro aprovado pela operacao administrativa." : "Cadastro movido para revisao manual."),
        actor: "Fox Platform",
        created_at: "agora"
      },
      ...(detail.review_history || [])
    ]
  };
}

async function filterFallbackApprovalQueue(fileName, key, itemId) {
  const data = await loadJson(fileName);
  return {
    items: (data[key] || []).filter((item) => item.id !== itemId)
  };
}

export async function approveAdminPartner(partnerId) {
  const payload = await requestApi(`api/v1/admin/approvals/partners/${partnerId}/approve`, {
    method: "POST",
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return filterFallbackApprovalQueue("admin.json", "partnerApprovals", partnerId);
}

export async function rejectAdminPartner(partnerId) {
  const payload = await requestApi(`api/v1/admin/approvals/partners/${partnerId}/reject`, {
    method: "POST",
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return filterFallbackApprovalQueue("admin.json", "partnerApprovals", partnerId);
}

export async function approveAdminDriver(driverId) {
  const payload = await requestApi(`api/v1/admin/approvals/drivers/${driverId}/approve`, {
    method: "POST",
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return filterFallbackApprovalQueue("admin.json", "driverApprovals", driverId);
}

export async function rejectAdminDriver(driverId) {
  const payload = await requestApi(`api/v1/admin/approvals/drivers/${driverId}/reject`, {
    method: "POST",
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return filterFallbackApprovalQueue("admin.json", "driverApprovals", driverId);
}

export async function getAdminSupport() {
  const payload = await requestApi("api/v1/admin/support/queue", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return {
    priorityQueue: data.support?.priorityQueue || [],
    distribution: [
      { label: "Operacao", value: "7 tickets" },
      { label: "Financeiro", value: "5 tickets" },
      { label: "Catalogo", value: "4 tickets" },
      { label: "Comercial", value: "2 tickets" }
    ],
    sla: [
      "Tempo medio de primeira resposta: 19 min.",
      "85% da fila dentro do SLA acordado.",
      "2 chamados escalados para revisao manual."
    ]
  };
}

function buildFallbackAdminSupportTickets(data) {
  return (data.support?.priorityQueue || []).map((item, index) => ({
    id: item.ticket_id || item.id || `mock-admin-ticket-${index + 1}`,
    title: item.title || `#SUP-MOCK-${index + 1}`,
    summary: item.summary || "Chamado sem resumo disponivel no fallback.",
    status: item.status || "em analise",
    statusType: item.statusType || "warning",
    channel: item.channel || "Operacao",
    counterpart: item.counterpart || "Parceiro/Entregador",
    priority: item.priority || (item.statusType === "danger" ? "critico" : "normal"),
    priority_type: item.priority_type || item.statusType || "warning",
    assigned_team: item.assigned_team || "Operacao",
    created_at: item.created_at || "agora",
    last_message_at: item.last_message_at || "agora",
    scope: item.scope || "Parceiro",
    subject: item.subject || item.title || "Chamado operacional",
    description: item.description || item.summary || "Sem descricao adicional."
  }));
}

export async function getAdminSupportThread(ticketId) {
  const payload = await requestApi(`api/v1/admin/support/${ticketId}`, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  const tickets = buildFallbackAdminSupportTickets(data);
  const ticket = tickets.find((item) => item.id === ticketId) || tickets[0] || null;

  if (!ticket) {
    throw new Error("Chamado nao encontrado para atendimento.");
  }

  return {
    ticket,
    messages: [
      {
        id: `${ticket.id}-incoming`,
        direction: "incoming",
        author: ticket.counterpart,
        body: ticket.summary,
        time: ticket.last_message_at,
        role: ticket.scope?.toLowerCase() === "entregador" ? "driver" : "partner"
      },
      {
        id: `${ticket.id}-outgoing`,
        direction: "outgoing",
        author: "Fox Platform",
        body: "Time interno ciente do protocolo. Seguimos acompanhando a tratativa.",
        time: ticket.last_message_at,
        role: "admin"
      }
    ]
  };
}

export async function replyAdminSupportThread(ticketId, body) {
  const payload = await requestApi(`api/v1/admin/support/${ticketId}/messages`, {
    method: "POST",
    body: { body },
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const thread = await getAdminSupportThread(ticketId);
  return {
    ...thread,
    messages: [
      ...(thread.messages || []),
      {
        id: `${ticketId}-reply-${Date.now()}`,
        direction: "outgoing",
        author: "Fox Platform",
        body,
        time: "agora",
        role: "admin"
      }
    ]
  };
}

export async function updateAdminSupportTicketStatus(ticketId, update) {
  const payload = await requestApi(`api/v1/admin/support/${ticketId}/status`, {
    method: "PUT",
    body: update,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const thread = await getAdminSupportThread(ticketId);
  const nextStatus = update?.status || thread.ticket.status;
  const label = {
    open: "aberto",
    in_progress: "em andamento",
    answered: "respondido",
    resolved: "concluido"
  }[nextStatus] || nextStatus;
  const tone = {
    open: "warning",
    in_progress: "warning",
    answered: "success",
    resolved: "success"
  }[nextStatus] || "warning";

  return {
    ...thread,
    ticket: {
      ...thread.ticket,
      status: label,
      status_type: tone,
      last_message_at: "agora"
    },
    messages: update?.note
      ? [
          ...(thread.messages || []),
          {
            id: `${ticketId}-status-${Date.now()}`,
            direction: "outgoing",
            author: "Fox Platform",
            body: update.note,
            time: "agora",
            role: "admin"
          }
        ]
      : thread.messages || []
  };
}

export async function getAdminSettings() {
  const payload = await requestApi("api/v1/admin/settings", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return data.settings || {
    branding: {},
    operations: {},
    notifications: {},
    security: {}
  };
}

export async function updateAdminSettings(body) {
  const payload = await requestApi("api/v1/admin/settings", {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getAdminSettings();
  return {
    branding: {
      ...current.branding,
      ...(body.branding || {})
    },
    operations: {
      ...current.operations,
      ...(body.operations || {})
    },
    notifications: {
      ...current.notifications,
      ...(body.notifications || {})
    },
    security: {
      ...current.security,
      ...(body.security || {})
    }
  };
}

export async function getAdminAccess() {
  const payload = await requestApi("api/v1/admin/access", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return data.access || {
    summary: [],
    roles: [],
    members: [],
    allowed_roles: []
  };
}

export async function createAdminAccessMember(body) {
  const payload = await requestApi("api/v1/admin/access/members", {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getAdminAccess();
  const role = (current.roles || []).find((item) => item.slug === body.role_slug) || {
    slug: body.role_slug,
    name: body.role_slug,
    permissions: []
  };
  const memberId = `admin-member-${Date.now()}`;

  return {
    ...current,
    summary: [
      { label: "usuarios internos", value: String((current.members || []).length + 1) },
      ...(current.summary || []).slice(1)
    ],
    members: [
      {
        id: memberId,
        user_id: memberId,
        full_name: body.full_name,
        email: body.email,
        phone: body.phone || "-",
        department: body.department || "Operacao",
        role_slug: body.role_slug,
        role_label: role.name || body.role_slug,
        permissions: role.permissions || [],
        status: body.status === "active" ? "ativo" : body.status === "suspended" ? "suspenso" : body.status === "blocked" ? "bloqueado" : "pendente",
        status_key: body.status,
        status_type: body.status === "active" ? "success" : body.status === "blocked" ? "danger" : "warning",
        is_super: body.role_slug === "super_admin",
        last_login_at: "-",
        created_at: "agora"
      },
      ...(current.members || [])
    ]
  };
}

export async function updateAdminAccessMember(memberId, body) {
  const payload = await requestApi(`api/v1/admin/access/members/${memberId}`, {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getAdminAccess();
  const role = (current.roles || []).find((item) => item.slug === body.role_slug) || {
    slug: body.role_slug,
    name: body.role_slug,
    permissions: []
  };

  return {
    ...current,
    members: (current.members || []).map((member) =>
      member.id === memberId
        ? {
            ...member,
            full_name: body.full_name,
            email: body.email,
            phone: body.phone || "-",
            department: body.department || "Operacao",
            role_slug: body.role_slug,
            role_label: role.name || body.role_slug,
            permissions: role.permissions || [],
            status: body.status === "active" ? "ativo" : body.status === "suspended" ? "suspenso" : body.status === "blocked" ? "bloqueado" : "pendente",
            status_key: body.status,
            status_type: body.status === "active" ? "success" : body.status === "blocked" ? "danger" : "warning",
            is_super: body.role_slug === "super_admin"
          }
        : member
    )
  };
}

export async function updateAdminAccessMemberStatus(memberId, status) {
  const payload = await requestApi(`api/v1/admin/access/members/${memberId}/status`, {
    method: "PUT",
    body: { status },
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getAdminAccess();
  return {
    ...current,
    members: (current.members || []).map((member) =>
      member.id === memberId
        ? {
            ...member,
            status: status === "active" ? "ativo" : status === "suspended" ? "suspenso" : status === "blocked" ? "bloqueado" : "pendente",
            status_key: status,
            status_type: status === "active" ? "success" : status === "blocked" ? "danger" : "warning"
          }
        : member
    )
  };
}

export async function getAdminNotifications() {
  const payload = await requestApi("api/v1/admin/notifications", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("admin.json");
  return data.notifications || { summary: [], items: [] };
}

export async function markAdminNotificationRead(notificationId) {
  const payload = await requestApi(`api/v1/admin/notifications/${notificationId}/read`, {
    method: "POST",
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getAdminNotifications();
  return {
    ...current,
    items: (current.items || []).map((item) =>
      item.id === notificationId ? { ...item, is_read: true } : item
    )
  };
}

export async function getDriverDashboard() {
  const payload = await requestApi("api/v1/driver/dashboard", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("driver-portal.json");
  const dashboard = data.dashboard || {};

  return {
    heroTitle: dashboard.heroTitle || "Ganhos, documentos e disponibilidade organizados em um unico ambiente.",
    heroLead: dashboard.heroLead || "Acompanhe o que precisa da sua operacao em um painel proprio da Fox Delivery.",
    summary:
      dashboard.summary || [
        { label: "ganhos liquidos acumulados", value: "R$ 1.286,40" },
        { label: "entregas concluidas", value: "62" },
        { label: "presenca nas janelas abertas", value: "98%" }
      ],
    metrics:
      dashboard.metrics || [
        { label: "modalidade ativa na operacao", value: "Moto" },
        { label: "tempo medio por corrida", value: "24 min" },
        { label: "ganho medio por entrega", value: "R$ 8,72" },
        { label: "avaliacao media recente", value: "4,9" }
      ],
    recent_runs: [
      { id: "#RUN-8741", status: "concluida", status_type: "success", value: "R$ 9,40", time: "12:08" },
      { id: "#RUN-8739", status: "concluida", status_type: "success", value: "R$ 7,90", time: "11:34" },
      { id: "#RUN-8734", status: "em analise", status_type: "warning", value: "R$ 12,10", time: "10:42" }
    ],
    checklist: [
      "2 documentos aprovados na conta operacional.",
      "Conta bancaria configurada para repasses.",
      "Ultima atividade registrada hoje as 13:18."
    ]
  };
}

export async function getDriverProfile() {
  const payload = await requestApi("api/v1/driver/profile", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("driver-portal.json");
  const profile = data.profile || {};

  return {
    full_name: profile.name || "Lucas Ferreira",
    email: profile.email || "entregador@foxdelivery.com.br",
    phone: profile.phone || "(11) 98888-3344",
    modal: profile.mode || "Moto",
    city: profile.city || "Sao Paulo",
    bank_name: "Banco Fox",
    bank_branch_number: "341",
    bank_account_number: "45897-2",
    bank_account: profile.bankAccount || "Banco Fox 341 - 45897-2",
    status: "ativa",
    status_type: "success",
    last_login_at: "20/03/2026 13:18",
    documents_status: "validados",
    documents_status_type: "success"
  };
}

export async function updateDriverProfile(body) {
  const payload = await requestApi("api/v1/driver/profile", {
    method: "PUT",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return {
    ...(await getDriverProfile()),
    ...body,
    bank_account: `${body.bank_name} ${body.bank_branch_number} - ${body.bank_account_number}`
  };
}

export async function getDriverEarnings() {
  const payload = await requestApi("api/v1/driver/earnings", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("driver-portal.json");
  const earnings = data.earnings || {};

  return {
    balance: earnings.balance || "R$ 1.286,40",
    balanceNote:
      earnings.balanceNote ||
      "Valor acumulado no periodo atual, ja considerando ganhos por corrida e ajustes da operacao.",
    stats:
      earnings.stats || [
        { label: "ganho medio por entrega", value: "R$ 8,72" },
        { label: "corridas concluidas na semana", value: "62" },
        { label: "repasse previsto", value: "24/03/2026" }
      ],
    transactions: [
      { date: "20/03/2026", run: "#RUN-8741", status: "concluida", status_type: "success", value: "R$ 9,40", note: "credito operacional" },
      { date: "20/03/2026", run: "#RUN-8739", status: "concluida", status_type: "success", value: "R$ 7,90", note: "credito operacional" },
      { date: "19/03/2026", run: "#RUN-8727", status: "ajuste em analise", status_type: "warning", value: "R$ 12,10", note: "conferencia de distancia" },
      { date: "18/03/2026", run: "#RUN-8718", status: "concluida", status_type: "success", value: "R$ 10,20", note: "farmacia" }
    ]
  };
}

export async function getDriverAvailability() {
  const payload = await requestApi("api/v1/driver/availability", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return {
    metrics: [
      { label: "presenca nas janelas abertas", value: "98%" },
      { label: "janelas ativas nesta semana", value: "6" },
      { label: "area principal da operacao", value: "Zona Sul" },
      { label: "maior volume de corrida", value: "12h-14h" }
    ],
    slots: [
      { title: "Segunda-feira", status: "aberta", status_type: "success", status_key: "open", description: "11:00 as 14:00 e 18:00 as 22:00" },
      { title: "Terca-feira", status: "aberta", status_type: "success", status_key: "open", description: "11:00 as 14:00 e 18:00 as 22:00" },
      { title: "Quarta-feira", status: "parcial", status_type: "warning", status_key: "partial", description: "18:00 as 22:00" },
      { title: "Quinta-feira", status: "aberta", status_type: "success", status_key: "open", description: "11:00 as 14:00 e 18:00 as 22:00" },
      { title: "Sexta-feira", status: "aberta", status_type: "success", status_key: "open", description: "11:00 as 14:00 e 18:00 as 23:00" },
      { title: "Sabado", status: "fechada", status_type: "danger", status_key: "closed", description: "sem janelas abertas no momento" }
    ]
  };
}

export async function getDriverDocuments() {
  const payload = await requestApi("api/v1/driver/documents", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return {
    summary: [
      { label: "documentos aprovados", value: "2" },
      { label: "documentos pendentes", value: "1" },
      { label: "modalidade ativa", value: "Moto" }
    ],
    documents: [
      { title: "Documento de identidade", status: "validado", status_type: "success", description: "Documento principal conferido e aprovado pela equipe.", expires_at: "-", reviewed_at: "20/03/2026 11:10" },
      { title: "CNH", status: "validado", status_type: "success", description: "Habilitacao com categoria compativel e vigencia ate 2027.", expires_at: "30/11/2027", reviewed_at: "20/03/2026 11:15" },
      { title: "Documento do veiculo", status: "pendente", status_type: "warning", description: "Arquivo aceito, mas programado para reconferencia no proximo ciclo.", expires_at: "-", reviewed_at: "-" }
    ],
    checklist: [
      "Documento de identidade aprovado.",
      "CNH validada para a modalidade ativa.",
      "Conta de recebimento registrada para repasses."
    ],
    pending_actions: [
      { title: "Revisar arquivo do veiculo", text: "Atualize a documentacao do veiculo para concluir a analise operacional." }
    ]
  };
}

export async function getDriverSupport() {
  const payload = await requestApi("api/v1/driver/support", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("driver-portal.json");
  return data.support || { tickets: [] };
}

export async function createDriverSupportTicket(body) {
  const payload = await requestApi("api/v1/driver/support/tickets", {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getDriverSupport();
  const generatedId = `driver-ticket-${Date.now()}`;
  const next = {
    id: `#DRV-${String(Date.now()).slice(-4)}`,
    ticket_id: generatedId,
    channel: body.channel,
    priority: body.priority,
    last_message_at: new Date().toISOString(),
    summary: body.subject,
    status: "aberto",
    statusType: body.priority === "critical" ? "danger" : body.priority === "high" ? "warning" : "success",
    meta: [body.channel, `Prioridade ${body.priority}`, "Atualizado agora"]
  };

  return {
    tickets: [next, ...(current.tickets || [])]
  };
}

export async function getDriverSupportThread(ticketId) {
  const payload = await requestApi(`api/v1/driver/support/${ticketId}`, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("driver-portal.json");
  const tickets = data.support?.tickets || [];
  const ticket = tickets.find((item) => item.ticket_id === ticketId) || tickets[0];

  return {
    ticket,
    messages: (data.messages?.[ticket?.ticket_id] || []).map((message) => ({
      ...message,
      time: message.time || "Agora"
    }))
  };
}

export async function replyDriverSupportThread(ticketId, body) {
  const payload = await requestApi(`api/v1/driver/support/${ticketId}/messages`, {
    method: "POST",
    body: { body },
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getDriverSupportThread(ticketId);
  return {
    ...current,
    messages: [
      ...(current.messages || []),
      {
        id: `drv-msg-${Date.now()}`,
        direction: "outgoing",
        author: "Entregador",
        body,
        time: new Date().toLocaleString("pt-BR"),
        role: "driver"
      }
    ]
  };
}

export async function getDriverNotifications() {
  const payload = await requestApi("api/v1/driver/notifications", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("driver-portal.json");
  return data.notifications || { summary: [], items: [] };
}

export async function markDriverNotificationRead(notificationId) {
  const payload = await requestApi(`api/v1/driver/notifications/${notificationId}/read`, {
    method: "POST",
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const current = await getDriverNotifications();
  return {
    ...current,
    items: (current.items || []).map((item) =>
      item.id === notificationId ? { ...item, is_read: true } : item
    )
  };
}

export async function getPublicCategories() {
  const payload = await requestApi("api/v1/public/categories", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("landing.json");
  return {
    items: data.categories || []
  };
}

export async function getPublicPlatformMetrics() {
  const payload = await requestApi("api/v1/public/platform-metrics", {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("landing.json");
  return {
    items: data.metrics || []
  };
}

export async function getPublicStores(filters = {}) {
  const query = new URLSearchParams();
  if (filters.city) query.set("city", filters.city);
  if (filters.category) query.set("category", filters.category);
  if (filters.search) query.set("search", filters.search);

  const path = `api/v1/public/stores${query.toString() ? `?${query.toString()}` : ""}`;
  const payload = await requestApi(path, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("landing.json");
  const items = data.stores || [];
  const normalized = items.filter((item) => {
    const cityMatch = !filters.city || String(item.city || "").toLowerCase() === String(filters.city).toLowerCase();
    const categoryMatch = !filters.category || String(item.primary_category_slug || "").toLowerCase() === String(filters.category).toLowerCase();
    const searchMatch = !filters.search || `${item.trade_name} ${item.city}`.toLowerCase().includes(String(filters.search).toLowerCase());
    return cityMatch && categoryMatch && searchMatch;
  });

  return {
    filters,
    items: normalized
  };
}

export async function getPublicStoreDetail(storeId) {
  const payload = await requestApi(`api/v1/public/stores/${storeId}`, {
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  const data = await loadJson("landing.json");
  const store = (data.stores || []).find((item) => item.id === storeId) || null;
  const detail = data.store_details?.[storeId] || null;

  if (!store || !detail) {
    throw new Error("Loja nao encontrada.");
  }

  return detail;
}

export async function createPublicOrder(body) {
  const payload = await requestApi("api/v1/public/orders", {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return {
    order_id: `mock-${Date.now()}`,
    order_number: `FD-${String(Date.now()).slice(-8)}`,
    store_name: "Fox Delivery",
    status: "recebido",
    status_key: "pending_acceptance",
    total: "R$ 0,00",
    next_step: "A loja recebeu o pedido e vai iniciar a analise para aceite e preparo."
  };
}

export async function createPublicPartnerLead(body) {
  const payload = await requestApi("api/v1/public/partner-leads", {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return {
    protocol: `PAR-${String(Date.now()).slice(-8)}`,
    status: "recebido",
    next_step: "Nossa equipe comercial vai retornar com os proximos passos do cadastro."
  };
}

export async function createPublicDriverLead(body) {
  const payload = await requestApi("api/v1/public/driver-leads", {
    method: "POST",
    body,
    allowFallback: true
  });

  if (payload) {
    return unwrapPayload(payload);
  }

  return {
    protocol: `DRV-${String(Date.now()).slice(-8)}`,
    status: "recebido",
    next_step: "O time operacional vai revisar seus dados e orientar a proxima etapa."
  };
}

export async function getDriverData() {
  return loadJson("driver-portal.json");
}

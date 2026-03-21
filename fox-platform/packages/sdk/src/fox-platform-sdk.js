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

    if (allowFallback && [404, 405, 500, 502, 503].includes(response.status)) {
      return null;
    }

    const error = new Error(
      payload?.error?.message ||
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
    accountLabel: data.user.name,
    email: data.user.email,
    accessToken: data.access_token,
    refreshToken: data.refresh_token,
    expiresIn: data.expires_in,
    loggedAt: new Date().toISOString(),
    source: "api",
    roles: data.user.roles ?? []
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
      roles: (session.roles || []).map((slug) => ({ slug }))
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

export async function getDriverData() {
  return loadJson("driver-portal.json");
}

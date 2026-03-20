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
    return normalizeBase(`${window.location.origin}/api/`);
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
  return new URL(path.replace(/^\/+/, ""), API_BASE);
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

export async function getPartnerData() {
  return loadJson("partner-portal.json");
}

export async function getAdminData() {
  return loadJson("admin.json");
}

export async function getDriverData() {
  return loadJson("driver-portal.json");
}

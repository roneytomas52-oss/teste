const API_BASE = new URL("../../../apps/api/mock/v1/", import.meta.url);
const SESSION_KEY = "fox-platform-session";

const LOGIN_ROUTES = {
  partner: "../../../apps/partner-portal/src/login.html",
  admin: "../../../apps/admin/src/login.html",
  driver: "../../../apps/driver-portal/src/login.html"
};

function buildLoginUrl(app) {
  return new URL(LOGIN_ROUTES[app], import.meta.url).href;
}

async function loadJson(resource) {
  const response = await fetch(new URL(resource, API_BASE));
  if (!response.ok) {
    throw new Error(`Nao foi possivel carregar ${resource}`);
  }
  return response.json();
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
    name: user.name,
    accountLabel: user.accountLabel,
    email: user.email,
    loggedAt: new Date().toISOString()
  };

  setSession(session);
  return session;
}

export function requireSession(app, role) {
  const session = getSession();
  if (!session || session.role !== role) {
    window.location.href = buildLoginUrl(app);
    return null;
  }
  return session;
}

export function bindLogout(app, selector = ".js-fx-logout, a[href$=\"login.html\"]") {
  document.querySelectorAll(selector).forEach((element) => {
    element.addEventListener("click", (event) => {
      event.preventDefault();
      clearSession();
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

export async function getPartnerData() {
  return loadJson("partner-portal.json");
}

export async function getAdminData() {
  return loadJson("admin.json");
}

export async function getDriverData() {
  return loadJson("driver-portal.json");
}

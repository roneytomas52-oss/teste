import {
  createPublicDriverLead,
  createPublicPartnerLead,
  getPublicCategories,
  getPublicPlatformMetrics
} from "./fox-platform-sdk.js";

function renderCategories(payload) {
  const container = document.querySelector("#fx-landing-categories");
  if (!container) return;

  const items = payload?.items || [];
  if (!items.length) {
    container.innerHTML = `<div class="fx-card"><p class="fx-copy">Nenhuma categoria publica disponivel no momento.</p></div>`;
    return;
  }

  container.innerHTML = items
    .map(
      (item) => `
        <article class="fx-card">
          <div class="fx-card-header">
            <h3 class="fx-title-sm">${item.name}</h3>
            <span class="fx-pill">${item.product_count} itens</span>
          </div>
          <p class="fx-copy-sm">${item.description}</p>
          <div class="fx-hero-actions">
            <a class="fx-button-ghost" href="#fx-partner-lead-form">${item.cta}</a>
          </div>
        </article>
      `
    )
    .join("");
}

function renderMetrics(payload) {
  const container = document.querySelector("#fx-landing-proof");
  if (!container) return;

  container.innerHTML = (payload?.items || [])
    .map(
      (item) => `
        <article class="fx-stat">
          <div class="fx-stat-value">${item.value}</div>
          <div class="fx-stat-label">${item.label}</div>
        </article>
      `
    )
    .join("");
}

function showFeedback(selector, message, tone = "success") {
  const target = document.querySelector(selector);
  if (!target) return;
  target.hidden = false;
  target.dataset.tone = tone;
  target.textContent = message;
}

function bindPartnerLeadForm() {
  const form = document.querySelector("#fx-partner-lead-form");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const payload = {
      company_name: document.querySelector("#fx-partner-company-name")?.value ?? "",
      contact_name: document.querySelector("#fx-partner-contact-name")?.value ?? "",
      email: document.querySelector("#fx-partner-email")?.value ?? "",
      phone: document.querySelector("#fx-partner-phone")?.value ?? "",
      city: document.querySelector("#fx-partner-city")?.value ?? "",
      business_type: document.querySelector("#fx-partner-business-type")?.value ?? "restaurant"
    };

    try {
      const response = await createPublicPartnerLead(payload);
      form.reset();
      showFeedback(
        "#fx-partner-lead-feedback",
        `Solicitacao recebida. Protocolo ${response.protocol}. ${response.next_step}`
      );
    } catch (error) {
      showFeedback(
        "#fx-partner-lead-feedback",
        error?.message || "Nao foi possivel registrar o interesse do parceiro.",
        "danger"
      );
    }
  });
}

function bindDriverLeadForm() {
  const form = document.querySelector("#fx-driver-lead-form");
  if (!form) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const payload = {
      full_name: document.querySelector("#fx-driver-full-name")?.value ?? "",
      email: document.querySelector("#fx-driver-email")?.value ?? "",
      phone: document.querySelector("#fx-driver-phone")?.value ?? "",
      city: document.querySelector("#fx-driver-city")?.value ?? "",
      modal: document.querySelector("#fx-driver-modal")?.value ?? "motorcycle"
    };

    try {
      const response = await createPublicDriverLead(payload);
      form.reset();
      showFeedback(
        "#fx-driver-lead-feedback",
        `Solicitacao recebida. Protocolo ${response.protocol}. ${response.next_step}`
      );
    } catch (error) {
      showFeedback(
        "#fx-driver-lead-feedback",
        error?.message || "Nao foi possivel registrar o interesse do entregador.",
        "danger"
      );
    }
  });
}

async function boot() {
  renderCategories(await getPublicCategories());
  renderMetrics(await getPublicPlatformMetrics());
  bindPartnerLeadForm();
  bindDriverLeadForm();
}

boot();

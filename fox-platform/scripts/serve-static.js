const http = require("http");
const fs = require("fs");
const path = require("path");

const root = path.resolve(process.argv[2] || process.cwd());
const port = Number(process.argv[3] || 3000);
const label = process.argv[4] || "fox-platform";

const mimeTypes = {
  ".html": "text/html; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".js": "application/javascript; charset=utf-8",
  ".mjs": "application/javascript; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".svg": "image/svg+xml",
  ".png": "image/png",
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".webp": "image/webp",
  ".ico": "image/x-icon",
  ".txt": "text/plain; charset=utf-8",
};

function respond(res, status, body, headers = {}) {
  res.writeHead(status, headers);
  res.end(body);
}

function renderIndex() {
  return `<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Fox Platform Local</title>
    <style>
      body{font-family:Segoe UI,Arial,sans-serif;background:#0f172a;color:#e5e7eb;margin:0;padding:40px}
      .wrap{max-width:860px;margin:0 auto}
      h1{margin:0 0 12px;font-size:40px}
      p{color:#cbd5e1;line-height:1.6}
      .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:28px}
      a.card{display:block;padding:20px 22px;border-radius:18px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.08);box-shadow:0 18px 38px rgba(15,23,42,.24)}
      a.card strong{display:block;font-size:18px;margin-bottom:8px}
      a.card span{display:block;color:#cbd5e1;font-size:14px}
      .meta{margin-top:24px;padding:16px 18px;border-radius:16px;background:#111827;border:1px solid rgba(255,255,255,.08)}
      code{color:#fdba74}
    </style>
  </head>
  <body>
    <div class="wrap">
      <h1>Fox Platform</h1>
      <p>Ambiente local dos portais e da landing. Abra um dos apps abaixo e mantenha a API rodando em <code>http://127.0.0.1:8099</code>.</p>
      <div class="grid">
        <a class="card" href="/apps/landing/src/index.html"><strong>Landing</strong><span>Home pública da Fox Delivery.</span></a>
        <a class="card" href="/apps/admin/src/login.html"><strong>Admin</strong><span>Painel administrativo da operação.</span></a>
        <a class="card" href="/apps/partner-portal/src/login.html"><strong>Partner Portal</strong><span>Portal da loja parceira.</span></a>
        <a class="card" href="/apps/driver-portal/src/login.html"><strong>Driver Portal</strong><span>Portal do entregador.</span></a>
      </div>
      <div class="meta">Root estático: <code>${root}</code></div>
    </div>
  </body>
</html>`;
}

const server = http.createServer((req, res) => {
  const url = new URL(req.url || "/", `http://${req.headers.host || "127.0.0.1"}`);
  let pathname = decodeURIComponent(url.pathname);

  if (pathname === "/") {
    return respond(res, 200, renderIndex(), { "Content-Type": "text/html; charset=utf-8" });
  }

  const safePath = path.normalize(path.join(root, pathname));
  if (!safePath.startsWith(root)) {
    return respond(res, 403, "Forbidden");
  }

  let target = safePath;
  try {
    const stats = fs.existsSync(target) ? fs.statSync(target) : null;
    if (stats?.isDirectory()) {
      const indexPath = path.join(target, "index.html");
      if (fs.existsSync(indexPath)) {
        target = indexPath;
      }
    }

    if (!fs.existsSync(target) || !fs.statSync(target).isFile()) {
      return respond(res, 404, "Not Found");
    }

    const ext = path.extname(target).toLowerCase();
    const contentType = mimeTypes[ext] || "application/octet-stream";
    res.writeHead(200, { "Content-Type": contentType, "Cache-Control": "no-store" });
    fs.createReadStream(target).pipe(res);
  } catch (error) {
    respond(res, 500, error.message || "Internal Server Error");
  }
});

server.listen(port, "127.0.0.1", () => {
  console.log(`[static] ${label} em http://127.0.0.1:${port}`);
});

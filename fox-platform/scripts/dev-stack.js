const { spawn } = require("child_process");
const path = require("path");

const root = path.resolve(__dirname, "..");
const apiRoot = path.join(root, "apps", "api");
const php = process.env.PHP_BIN || "php";
const node = process.execPath;
const children = [];

function start(name, command, args, options = {}) {
  const child = spawn(command, args, {
    cwd: options.cwd || root,
    stdio: "inherit",
    shell: false,
    env: process.env,
  });

  child.on("exit", (code, signal) => {
    const reason = signal ? `signal ${signal}` : `code ${code}`;
    console.log(`[${name}] finalizado (${reason})`);
  });

  children.push(child);
  return child;
}

function shutdown() {
  for (const child of children) {
    if (!child.killed) {
      child.kill("SIGINT");
    }
  }
}

process.on("SIGINT", () => {
  shutdown();
  process.exit(0);
});

process.on("SIGTERM", () => {
  shutdown();
  process.exit(0);
});

console.log("[fox-platform] Subindo API e servidor estatico local...");
console.log("[fox-platform] Landing:         http://127.0.0.1:3000/apps/landing/src/index.html");
console.log("[fox-platform] Admin:           http://127.0.0.1:3000/apps/admin/src/login.html");
console.log("[fox-platform] Partner Portal:  http://127.0.0.1:3000/apps/partner-portal/src/login.html");
console.log("[fox-platform] Driver Portal:   http://127.0.0.1:3000/apps/driver-portal/src/login.html");
console.log("[fox-platform] API:             http://127.0.0.1:8099/health");

start("api", php, ["-S", "127.0.0.1:8099", "-t", "public", "public/router.php"], { cwd: apiRoot });
start("static", node, [path.join(root, "scripts", "serve-static.js"), root, "3000", "fox-platform"]);

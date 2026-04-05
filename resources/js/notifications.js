(function () {
  const LS_KEY = "deposito_notifications_v1";
  const host = document.getElementById("deposito-toast-host");
  const badge = document.getElementById("notif-badge");
  const bell = document.getElementById("notif-bell");
  const panel = document.getElementById("notif-panel");
  const list = document.getElementById("notif-list");

  function loadStore() {
    try {
      return JSON.parse(localStorage.getItem(LS_KEY) || "[]");
    } catch {
      return [];
    }
  }

  function saveStore(items) {
    localStorage.setItem(LS_KEY, JSON.stringify(items.slice(0, 50)));
  }

  function bumpBadge(n) {
    if (!badge) return;
    const c = Math.min(99, n);
    if (c > 0) {
      badge.textContent = String(c);
      badge.classList.remove("hidden");
    } else {
      badge.classList.add("hidden");
    }
  }

  function renderPanel() {
    if (!list) return;
    const items = loadStore();
    list.innerHTML = "";
    items.forEach((it) => {
      const li = document.createElement("li");
      li.className =
        "rounded border border-dep-border/60 bg-dep-bg/50 p-2 text-gray-300";
      li.textContent = it.title + " — " + it.message;
      list.appendChild(li);
    });
    bumpBadge(items.filter((x) => !x.read).length);
  }

  function pushNotif(payload) {
    const items = loadStore();
    const row = {
      id: Date.now(),
      read: false,
      title: payload.title || "Aviso",
      message: payload.message || "",
      at: new Date().toISOString(),
    };
    items.unshift(row);
    saveStore(items);
    renderPanel();
  }

  window.DepositoToast = function (opts) {
    if (!host) return;
    const el = document.createElement("div");
    el.className = "deposito-toast";
    const icon =
      opts.type === "danger"
        ? "⛔"
        : opts.type === "success"
          ? "✓"
          : "ℹ";
    el.innerHTML =
      '<div class="flex gap-2">' +
      '<span class="text-lg">' +
      icon +
      "</span>" +
      '<div class="flex-1">' +
      '<div class="text-sm font-semibold text-white">' +
      (opts.title || "") +
      "</div>" +
      '<div class="mt-0.5 text-xs text-gray-400">' +
      (opts.message || "") +
      "</div>" +
      '<div class="deposito-toast-progress"></div>' +
      "</div>" +
      '<button type="button" class="text-gray-500 hover:text-white" aria-label="Cerrar">✕</button>' +
      "</div>";
    const close = () => {
      el.classList.add("deposito-toast-out");
      setTimeout(() => el.remove(), 280);
    };
    el.querySelector("button").addEventListener("click", close);
    host.appendChild(el);
    setTimeout(close, 5000);
  };

  if (bell && panel) {
    bell.addEventListener("click", () => {
      panel.classList.toggle("hidden");
      const items = loadStore().map((x) => ({ ...x, read: true }));
      saveStore(items);
      renderPanel();
    });
  }

  document.addEventListener("click", (e) => {
    if (!panel || !bell) return;
    if (!panel.contains(e.target) && !bell.contains(e.target)) {
      panel.classList.add("hidden");
    }
  });

  const cfg = window.__DEPOSITO__ || {};
  if (
    cfg.reverbKey &&
    typeof window.Pusher !== "undefined" &&
    typeof window.Echo !== "undefined"
  ) {
    const EchoClass = window.Echo;
    const echo = new EchoClass({
      broadcaster: "reverb",
      key: cfg.reverbKey,
      wsHost: cfg.wsHost,
      wsPort: cfg.wsPort,
      wssPort: cfg.wsPort,
      forceTLS: !!cfg.wss,
      enabledTransports: ["ws", "wss"],
    });
    echo.channel("deposito-notifications")
      .listen(".low-stock", (e) => {
        pushNotif(e);
        window.DepositoToast({
          type: "danger",
          title: e.title || "Alerta de stock",
          message: e.message || "",
        });
      })
      .listen(".product-movement", (e) => {
        pushNotif(e);
        window.DepositoToast({
          type: "success",
          title: e.title || "Movimiento",
          message: e.message || "",
        });
      });
  }

  renderPanel();
})();

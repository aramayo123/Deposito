(function () {
  const form = document.getElementById("global-search-form");
  const out = document.getElementById("search-results");
  if (!form || !out) return;

  function esc(s) {
    if (s == null || s === "") return "";
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function fmtDate(iso) {
    if (!iso) return "—";
    const d = String(iso).slice(0, 10);
    if (!/^\d{4}-\d{2}-\d{2}$/.test(d)) return esc(String(iso));
    const [y, m, day] = d.split("-");
    return day + "/" + m + "/" + y;
  }

  function fmtTime(t) {
    if (t == null || t === "") return "";
    const s = String(t);
    const m = s.match(/^(\d{1,2}):(\d{2})/);
    return m ? m[1].padStart(2, "0") + ":" + m[2] : "";
  }

  function truncate(str, max) {
    const t = String(str || "").trim();
    if (!t) return "";
    if (t.length <= max) return t;
    return t.slice(0, max - 1) + "…";
  }

  function sec(title, html) {
    const s = document.createElement("section");
    s.className = "deposito-card mb-4 rounded-xl p-4";
    s.innerHTML =
      "<h3 class='mb-3 text-sm font-semibold text-blue-300'>" +
      esc(title) +
      "</h3>" +
      html;
    out.appendChild(s);
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const q = new URLSearchParams(fd);
    const r = await fetch("/api/reports/search?" + q.toString(), {
      headers: { Accept: "application/json" },
    });
    const data = await r.json();
    out.innerHTML = "";

    if (data.deposits?.length) {
      const rows = data.deposits
        .map(function (d) {
          const created = d.created_at ? fmtDate(d.created_at) : "";
          return (
            "<li class='rounded-lg border border-dep-border/40 px-3 py-2'>" +
            "<div class='flex flex-wrap items-baseline gap-x-2 gap-y-0'>" +
            "<a class='font-medium text-blue-400 hover:underline' href='/deposits/" +
            esc(d.id) +
            "'>" +
            esc(d.name) +
            "</a>" +
            (created ? "<span class='text-gray-500'>" + esc(created) + "</span>" : "") +
            "</div></li>"
          );
        })
        .join("");
      sec("Depósitos", "<ul class='space-y-2 text-sm'>" + rows + "</ul>");
    }

    if (data.products?.length) {
      const rows = data.products
        .map(function (p) {
          const sub =
            "<span class='text-gray-500'>Disponible:</span> " +
            esc(p.available_quantity) +
            " · <span class='text-gray-500'>Dañados:</span> " +
            esc(p.damaged_quantity) +
            " · <span class='text-gray-500'>Stock mín.:</span> " +
            esc(p.minimum_stock);
          return (
            "<li class='rounded-lg border border-dep-border/40 px-3 py-2'>" +
            "<div><a class='font-medium text-blue-400 hover:underline' href='/products/" +
            esc(p.id) +
            "'>" +
            esc(p.product_code) +
            "</a><span class='text-gray-300'> — " +
            esc(p.name || "—") +
            "</span></div>" +
            "<div class='mt-1 text-xs text-gray-400'>" +
            sub +
            "</div></li>"
          );
        })
        .join("");
      sec("Productos", "<ul class='space-y-2 text-sm'>" + rows + "</ul>");
    }

    if (data.entries?.length) {
      const rows = data.entries
        .map(function (x) {
          const when =
            fmtDate(x.entry_date) +
            (fmtTime(x.entry_time) ? " · " + esc(fmtTime(x.entry_time)) : "");
          const note = truncate(x.notes, 100);
          const noteHtml = note
            ? "<div class='mt-1 text-xs text-gray-500'>" + esc(note) + "</div>"
            : "";
          return (
            "<li class='rounded-lg border border-dep-border/40 px-3 py-2'>" +
            "<div class='flex flex-wrap items-baseline gap-x-2 gap-y-0'>" +
            "<a class='font-medium text-blue-400 hover:underline' href='/entries/" +
            esc(x.id) +
            "'>" +
            esc(x.entry_code) +
            "</a>" +
            "<span class='text-gray-500'>" +
            esc(when) +
            "</span>" +
            "<span class='text-gray-500'>·</span>" +
            "<span class='text-gray-400'>" +
            esc(x.items_count) +
            " ítem(s)</span></div>" +
            noteHtml +
            "</li>"
          );
        })
        .join("");
      sec("Entradas", "<ul class='space-y-2 text-sm'>" + rows + "</ul>");
    }

    if (data.exits?.length) {
      const rows = data.exits
        .map(function (x) {
          const when =
            fmtDate(x.exit_date) +
            (fmtTime(x.exit_time) ? " · " + esc(fmtTime(x.exit_time)) : "");
          const workshop =
            x.is_for_workshop === true || x.is_for_workshop === 1
              ? "<span class='ml-1 rounded bg-amber-500/20 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-300'>Taller</span>"
              : "";
          const tech =
            x.is_for_workshop === true || x.is_for_workshop === 1
              ? "<span class='text-gray-500'>Destino:</span> <span class='text-amber-300/90'>Taller</span>"
              : (x.technician_name
                ? "<span class='text-gray-500'>Técnico:</span> " + esc(x.technician_name)
                : "");
          const depositName = x.deposit ? x.deposit.name : (x.deposit_name || "");
          const depositLabel = depositName
            ? "<span class='ml-2 text-emerald-300'>" + esc(depositName) + "</span>"
            : "";
          const note = truncate(x.notes, 100);
          const noteHtml = note
            ? "<div class='mt-1 text-xs text-gray-500'>" + esc(note) + "</div>"
            : "";
          return (
            "<li class='rounded-lg border border-dep-border/40 px-3 py-2'>" +
            "<div class='flex flex-wrap items-baseline gap-x-2 gap-y-0'>" +
            "<a class='font-medium text-blue-400 hover:underline' href='/exits/" +
            esc(x.id) +
            "'>" +
            esc(x.exit_code) +
            "</a>" +
            workshop +
            "<span class='text-gray-500'>" +
            esc(when) +
            "</span>" +
            "<span class='text-gray-500'>·</span>" +
            "<span class='text-gray-400'>" +
            esc(x.items_count) +
            " ítem(s)</span></div>" +
            "<div class='mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-300'>" +
            (tech ? "<span>" + tech + "</span>" : "") +
            (depositLabel ? "<span>Depósito: " + depositLabel + "</span>" : "") +
            "</div>" +
            noteHtml +
            "</li>"
          );
        })
        .join("");
      sec("Salidas", "<ul class='space-y-2 text-sm'>" + rows + "</ul>");
    }

    if (data.history?.length) {
      const rows = data.history
        .map(function (h) {
          const prod = h.product;
          const code = prod && prod.product_code ? prod.product_code : null;
          const pname = prod && prod.name ? prod.name : null;
          const pid = h.product_id;
          const prodLine =
            code && pid
              ? "<a class='text-blue-400 hover:underline' href='/products/" +
                esc(pid) +
                "'>" +
                esc(code) +
                "</a>" +
                (pname ? "<span class='text-gray-400'> — " + esc(pname) + "</span>" : "")
              : code
                ? esc(code) + (pname ? "<span class='text-gray-400'> — " + esc(pname) + "</span>" : "")
                : "<span class='text-gray-500'>—</span>";
          const depositName = h.deposit_name || (h.deposit ? h.deposit.name : "");
          const tech =
            h.technician_name || depositName
              ? "<div class='mt-1 flex flex-wrap gap-x-3 gap-y-0 text-xs text-gray-400'>" +
                (h.technician_name
                  ? "<span><span class='text-gray-500'>Técnico:</span> " +
                    esc(h.technician_name) +
                    "</span>"
                  : "") +
                (depositName
                  ? "<span><span class='text-gray-500'>Depósito:</span> <span class='text-emerald-300'>" +
                    esc(depositName) +
                    "</span></span>"
                  : "") +
                "</div>"
              : "";
          const qty =
            h.quantity_change != null
              ? "<span class='text-gray-500'>Cant.:</span> " +
                (h.quantity_change > 0 ? "+" : "") +
                esc(h.quantity_change) +
                (h.quantity_after != null
                  ? " → <span class='text-gray-400'>" + esc(h.quantity_after) + "</span>"
                  : "")
              : "";
          const when = h.created_at ? fmtDate(h.created_at) : "";
          const meta =
            "<div class='mt-1 flex flex-wrap gap-x-2 gap-y-0 text-[11px] text-gray-500'>" +
            (when ? "<span>" + esc(when) + "</span>" : "") +
            (qty ? "<span>" + qty + "</span>" : "") +
            "</div>";
          const desc = h.description ? esc(String(h.description)) : "";
          return (
            "<li class='rounded-lg border border-dep-border/40 border-l-2 border-l-blue-500/40 px-3 py-2'>" +
            "<div class='text-sm'><span class='font-medium text-gray-200'>" +
            esc(h.action_display || h.action_type || "") +
            "</span>" +
            "<span class='text-gray-500'> · </span>" +
            prodLine +
            "</div>" +
            tech +
            (desc ? "<div class='mt-1 text-xs text-gray-400'>" + desc + "</div>" : "") +
            meta +
            "</li>"
          );
        })
        .join("");
      sec("Historial", "<ul class='space-y-2 text-sm'>" + rows + "</ul>");
    }

    if (!out.children.length) out.textContent = "Sin resultados.";
  });
})();

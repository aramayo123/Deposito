(function () {
  const tbody = document.getElementById("items-body");
  const addBtn = document.getElementById("add-item");
  const tpl = document.getElementById("item-row-tpl");
  const workshop = document.getElementById("is_for_workshop");
  const techFields = document.getElementById("tech-fields");
  const preset = window.__EXIT_PRESET__;

  function toggleTech() {
    if (!workshop || !techFields) return;
    const on = workshop.checked;
    techFields.classList.toggle("hidden", on);
    techFields.classList.toggle("opacity-50", on);
  }
  workshop?.addEventListener("change", toggleTech);
  toggleTech();

  if (!tbody || !tpl) return;

  let idx = 0;

  function bindRow(tr) {
    tr.querySelector(".btn-remove")?.addEventListener("click", () => tr.remove());
    const search = tr.querySelector(".product-search");
    const hid = tr.querySelector(".product-id");
    const box = tr.querySelector(".autocomplete");
    const qty = tr.querySelector(".qty-out");
    let timer;
    search?.addEventListener("input", function () {
      clearTimeout(timer);
      const q = search.value.trim();
      if (q.length < 1) {
        box.classList.add("hidden");
        return;
      }
      timer = setTimeout(async () => {
        const r = await fetch("/api/products?q=" + encodeURIComponent(q) + "&per_page=15", {
          headers: { Accept: "application/json" },
        });
        const j = await r.json();
        const data = j.data || j;
        box.innerHTML = "";
        (Array.isArray(data) ? data : []).forEach((p) => {
          const a = document.createElement("button");
          a.type = "button";
          a.className = "block w-full px-2 py-1 text-left text-xs hover:bg-white/10";
          a.textContent = p.product_code + " — " + (p.name || "—");
          a.addEventListener("click", () => {
            hid.value = p.id;
            search.value = p.product_code;
            box.classList.add("hidden");
            tr.dataset.max = String(p.available_quantity);
            tr.querySelector(".stock-hint").textContent =
              "Stock disp.: " + p.available_quantity;
          });
          box.appendChild(a);
        });
        box.classList.remove("hidden");
      }, 300);
    });
    qty?.addEventListener("input", () => {
      const max = parseFloat(tr.dataset.max || "0");
      const v = parseFloat(qty.value) || 0;
      if (max && v > max) {
        qty.setCustomValidity("Supera stock disponible");
      } else {
        qty.setCustomValidity("");
      }
    });
  }

  function addRow() {
    const html = tpl.innerHTML.replace(/__IDX__/g, String(idx++));
    const w = document.createElement("tbody");
    w.innerHTML = html.trim();
    const tr = w.firstElementChild;
    tbody.appendChild(tr);
    bindRow(tr);
    return tr;
  }

  addBtn?.addEventListener("click", () => addRow());
  const first = addRow();
  if (preset) {
    first.querySelector(".product-id").value = preset;
    fetch("/api/products?per_page=100")
      .then((r) => r.json())
      .then((j) => {
        const data = j.data || j;
        const p = (Array.isArray(data) ? data : []).find((x) => String(x.id) === String(preset));
        if (p) {
          first.querySelector(".product-search").value = p.product_code;
          first.dataset.max = String(p.available_quantity);
          first.querySelector(".stock-hint").textContent = "Stock disp.: " + p.available_quantity;
        }
      });
  }
})();

(function () {
  const input = document.getElementById("product_code");
  const hint = document.getElementById("code-hint");
  const drop = document.getElementById("drop");
  const file = document.getElementById("photo");
  const preview = document.getElementById("preview");
  if (!input) return;

  let t;
  input.addEventListener("input", function () {
    clearTimeout(t);
    const code = input.value.trim();
    if (code.length < 2) {
      hint.textContent = "";
      return;
    }
    t = setTimeout(async () => {
      try {
        const ex = input.dataset.excludeId
          ? "?exclude_id=" + encodeURIComponent(input.dataset.excludeId)
          : "";
        const r = await fetch(
          "/api/products/" + encodeURIComponent(code) + "/check-code" + ex,
          { headers: { Accept: "application/json" } }
        );
        const j = await r.json();
        hint.textContent = j.available ? "Código disponible" : "Código ya existe";
        hint.className = j.available ? "mt-1 text-xs text-emerald-400" : "mt-1 text-xs text-red-400";
      } catch {
        hint.textContent = "";
      }
    }, 400);
  });

  if (drop && file) {
    drop.addEventListener("click", () => file.click());
    drop.addEventListener("dragover", (e) => {
      e.preventDefault();
      drop.classList.add("border-blue-500");
    });
    drop.addEventListener("dragleave", () => drop.classList.remove("border-blue-500"));
    drop.addEventListener("drop", (e) => {
      e.preventDefault();
      drop.classList.remove("border-blue-500");
      if (e.dataTransfer.files[0]) {
        file.files = e.dataTransfer.files;
        showPreview(e.dataTransfer.files[0]);
      }
    });
    file.addEventListener("change", () => {
      if (file.files[0]) showPreview(file.files[0]);
    });
  }

  function showPreview(f) {
    if (!preview) return;
    preview.src = URL.createObjectURL(f);
    preview.classList.remove("hidden");
  }
})();

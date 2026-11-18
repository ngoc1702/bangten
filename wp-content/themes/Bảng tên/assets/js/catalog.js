document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('catalog-filter-form');
  if (!form) return;
  const category = form.querySelector('[data-catalog-category]');
  const ordering = document.getElementById('catalog-orderby');
  const panel = form.closest('details');
  const desktop = window.matchMedia('(min-width: 901px)');
  const panelKey = 'caia-catalog-panel:' + window.location.pathname;
  function rememberPanel() {
    if (desktop.matches) return;
    try { window.sessionStorage.setItem(panelKey, panel.open ? 'open' : 'closed'); } catch (_) {}
  }
  let pendingSubmit;
  form.addEventListener('submit', function () {
    window.clearTimeout(pendingSubmit);
    rememberPanel();
  });
  form.addEventListener('change', function (event) {
    window.clearTimeout(pendingSubmit);
    if (event.target === category) {
      const option = category.selectedOptions[0];
      const url = new URL(option.dataset.url, window.location.href);
      new FormData(form).forEach(function (value, name) {
        if (name !== 'catalog_category') url.searchParams.append(name, value);
      });
      window.location.assign(url.href);
    } else if (event.target.matches('input[type="checkbox"]')) {
      // Submit the latest combined selection once when several boxes change quickly.
      pendingSubmit = window.setTimeout(function () { form.requestSubmit(); }, 250);
    }
  });
  ordering.addEventListener('change', function () { form.requestSubmit(); });
  function updatePanel() {
    let open = false;
    try { open = window.sessionStorage.getItem(panelKey) === 'open'; } catch (_) {}
    panel.open = desktop.matches || open;
  }
  updatePanel();
  panel.addEventListener('toggle', rememberPanel);
  desktop.addEventListener('change', updatePanel);
});

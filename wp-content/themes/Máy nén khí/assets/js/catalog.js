document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('catalog-filter-form');
  if (!form) return;
  const ordering = document.getElementById('catalog-orderby');
  const panel = form.closest('details');
  const desktop = window.matchMedia('(min-width: 901px)');
  const panelKey = 'caia-catalog-panel:' + window.location.pathname;

  function rememberPanel() {
    if (desktop.matches) return;
    try { window.sessionStorage.setItem(panelKey, panel.open ? 'open' : 'closed'); } catch (_) {}
  }

  // Same routing as caia_catalog_view_url(): one category opens its archive, none or several use the shop.
  function navigate() {
    rememberPanel();
    const checked = Array.from(form.querySelectorAll('[data-catalog-category]:checked'));
    const url = new URL(checked.length === 1 ? checked[0].dataset.url : form.dataset.shopUrl, window.location.href);
    new FormData(form).forEach(function (value, name) {
      if (name === 'categories[]' && checked.length < 2) return;
      url.searchParams.append(name, value);
    });
    if (ordering) url.searchParams.set('orderby', ordering.value);
    window.location.assign(url.href);
  }

  let pending;
  function scheduleNavigate(event) {
    const label = event.target.closest('.catalog-filter-option');
    if (label) label.classList.toggle('is-checked', event.target.checked);
    window.clearTimeout(pending);
    // Apply the combined selection once when several boxes change quickly.
    pending = window.setTimeout(navigate, 350);
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    window.clearTimeout(pending);
    navigate();
  });
  form.addEventListener('change', function (event) {
    if (event.target.matches('input[type="checkbox"]')) scheduleNavigate(event);
  });
  if (ordering) ordering.addEventListener('change', navigate);

  function updatePanel() {
    let open = false;
    try { open = window.sessionStorage.getItem(panelKey) === 'open'; } catch (_) {}
    panel.open = desktop.matches || open;
  }
  updatePanel();
  panel.addEventListener('toggle', rememberPanel);
  desktop.addEventListener('change', updatePanel);
});

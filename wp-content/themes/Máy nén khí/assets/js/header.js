(() => {
  const init = () => {
    const header = document.querySelector('.site-header');
    const topbar = document.querySelector('.header-menusub-widget-area');
    if (!header) return;

    const syncHeight = () => {
      const height = Math.ceil(header.getBoundingClientRect().height + (topbar ? topbar.getBoundingClientRect().height : 0));
      document.documentElement.style.setProperty('--caia-header-height', `${height}px`);
      document.documentElement.style.setProperty('--caia-topbar-height', `${topbar ? topbar.offsetHeight : 0}px`);
    };
    syncHeight();
    if ('ResizeObserver' in window) {
      const observer = new ResizeObserver(syncHeight);
      observer.observe(header);
      if (topbar) observer.observe(topbar);
    }
    window.addEventListener('resize', syncHeight);

    // Keep the widget's search form, including its existing action and query name.
    header.querySelectorAll('input[type="search"]').forEach(input => {
      input.placeholder = 'Nhập sản phẩm tìm kiếm';
      input.setAttribute('aria-label', 'Tìm kiếm sản phẩm');
    });

  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();

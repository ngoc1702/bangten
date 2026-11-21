(function () {
  'use strict';

  if (!window.lottie) return;

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  document.querySelectorAll('.home-reviews__animation[data-src]').forEach(function (container) {
    var frame = container.closest('.home-reviews__image-frame');
    var animation;
    var visible = false;
    var ready = false;

    function updatePlayback() {
      if (!ready) return;
      if (visible && !document.hidden && !reducedMotion.matches) {
        animation.play();
      } else {
        animation.pause();
      }
    }

    function loadAnimation() {
      if (animation || reducedMotion.matches) return;
      animation = window.lottie.loadAnimation({
        container: container,
        renderer: 'svg',
        loop: true,
        autoplay: false,
        path: container.dataset.src,
        rendererSettings: { preserveAspectRatio: 'xMidYMid meet' }
      });
      animation.addEventListener('DOMLoaded', function () {
        ready = true;
        frame.classList.add('is-ready');
        updatePlayback();
      });
      animation.addEventListener('data_failed', function () {
        frame.classList.remove('is-ready');
      });
    }

    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        visible = entries[0].isIntersecting;
        if (visible) loadAnimation();
        updatePlayback();
      });
      observer.observe(frame);
    } else {
      visible = true;
      loadAnimation();
    }

    document.addEventListener('visibilitychange', updatePlayback);
    reducedMotion.addEventListener('change', function () {
      if (visible) loadAnimation();
      updatePlayback();
    });
  });
})();

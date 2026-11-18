(function () {
  "use strict";

  var hasStarted = false;
  var isInitialized = false;
  var observer = null;

  function addAutomaticRevealItems() {
    var candidates = document.querySelectorAll([
      "section",
      "main",
      ".site-inner .content > article",
      ".site-inner .content > .woocommerce"
    ].join(","));

    Array.prototype.forEach.call(candidates, function (item) {
      var isExcluded = item.id === "stop" || item.closest([
        ".site-header",
        ".site-footer",
        ".before_footer",
        ".product-contact-area",
        "#responsive-menu",
        "#mobile-menu-overlay",
        ".popup",
        ".modal"
      ].join(","));

      if (
        isExcluded ||
        item.hasAttribute("data-reveal") ||
        item.querySelector("[data-reveal]") ||
        item.querySelector("section")
      ) {
        return;
      }

      item.setAttribute("data-reveal", "fade-up");
    });
  }

  addAutomaticRevealItems();

  var revealItems = Array.prototype.slice.call(document.querySelectorAll("[data-reveal]"));

  function reveal(item) {
    item.classList.remove("is-reveal-pending");
    item.classList.add("is-revealed");
  }

  function isInInitialViewport(item) {
    var section = item.closest ? item.closest("section") : null;
    var rect = (section || item).getBoundingClientRect();

    return rect.bottom > 0 && rect.top < window.innerHeight;
  }

  function observePendingItems() {
    if (!("IntersectionObserver" in window)) {
      revealItems.forEach(reveal);
      return;
    }

    if (observer) {
      return;
    }

    observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) {
          return;
        }

        reveal(entry.target);
        observer.unobserve(entry.target);
      });
    }, {
      root: null,
      rootMargin: "0px 0px -12% 0px",
      threshold: 0.12
    });

    revealItems.forEach(function (item) {
      if (item.classList.contains("is-reveal-pending")) {
        observer.observe(item);
      }
    });
  }

  function refreshInitialViewport() {
    if (hasStarted) {
      return;
    }

    revealItems.forEach(function (item) {
      if (isInInitialViewport(item)) {
        reveal(item);
      }
    });
  }

  function initializeReveal() {
    if (isInitialized) {
      return;
    }

    isInitialized = true;

    revealItems.forEach(function (item) {
      var delay = parseInt(item.getAttribute("data-reveal-delay"), 10);

      if (!Number.isNaN(delay) && delay > 0) {
        item.style.setProperty("--reveal-delay", delay + "ms");
      }

      if (isInInitialViewport(item)) {
        reveal(item);
      } else {
        item.classList.add("is-reveal-pending");
      }
    });

    if (hasStarted) {
      observePendingItems();
    }
  }

  function scheduleInitialize() {
    window.requestAnimationFrame(function () {
      window.requestAnimationFrame(initializeReveal);
    });
  }

  function startReveal() {
    if (hasStarted) {
      return;
    }

    hasStarted = true;

    if (isInitialized) {
      observePendingItems();
    }
  }

  if (!revealItems.length) {
    return;
  }

  if ("loading" === document.readyState) {
    document.addEventListener("DOMContentLoaded", scheduleInitialize, { once: true });
  } else {
    scheduleInitialize();
  }

  window.addEventListener("load", refreshInitialViewport, { once: true });

  window.addEventListener("wheel", startReveal, { once: true, passive: true });
  window.addEventListener("touchmove", startReveal, { once: true, passive: true });
  window.addEventListener("scroll", startReveal, { once: true, passive: true });
  window.addEventListener("keydown", function (event) {
    if (["ArrowDown", "PageDown", "End", " "].indexOf(event.key) !== -1) {
      startReveal();
    }
  }, { once: true });
}());

jQuery(function ($) {
  var header = document.querySelector(".site-header");
  var banner = document.querySelector(".home-banner");
  function updateBannerSpace() {
    if (!header || !banner) return;
    var adminBar = document.getElementById("wpadminbar");
    var adminHeight = adminBar ? adminBar.getBoundingClientRect().height : 0;
    var headerTop = parseFloat(window.getComputedStyle(header).top) || 0;
    var height = Math.max(0, headerTop + header.getBoundingClientRect().height - adminHeight);
    banner.style.setProperty("--banner-header-height", height + "px");
    banner.style.setProperty("--banner-admin-height", adminHeight + "px");
  }
  updateBannerSpace();
  window.addEventListener("resize", updateBannerSpace);
  window.addEventListener("load", updateBannerSpace);
  if (header && typeof ResizeObserver !== "undefined") {
    new ResizeObserver(updateBannerSpace).observe(header);
  }

  if (typeof $.fn.slick !== "function") {
    return;
  }

  $(".home-banner__slider").each(function () {
    var $slider = $(this);

    if ($slider.hasClass("slick-initialized") || $slider.hasClass("home-banner__slider--single")) {
      return;
    }

    $slider.slick({
      arrows: true,
      prevArrow: '<button type="button" class="home-banner__arrow home-banner__arrow--prev" aria-label="Slide trước"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m15 5-7 7 7 7"/></svg></button>',
      nextArrow: '<button type="button" class="home-banner__arrow home-banner__arrow--next" aria-label="Slide tiếp theo"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m9 5 7 7-7 7"/></svg></button>',
      dots: false,
      infinite: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 4000,
      speed: 700,
      pauseOnHover: false,
      adaptiveHeight: false
    });
  });
});

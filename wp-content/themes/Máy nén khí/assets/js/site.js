/**
 * Site-wide behaviour: product gallery, homepage tabs and sliders,
 * news category strip, sticky-header state, anchor scrolling and comment form checks.
 */
jQuery(function ($) {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isPhone = function () { return window.matchMedia('(max-width: 767px)').matches; };

  /* Product gallery: keep the active thumbnail in view and centre the arrows on the main image. */
  document.querySelectorAll('.woocommerce-product-gallery').forEach(function (gallery) {
    function keepActiveThumbnailVisible() {
      var strip = gallery.querySelector('.flex-control-thumbs');
      var active = strip && strip.querySelector('img.flex-active');
      if (!active) return;
      var item = active.closest('li');
      var left = item.getBoundingClientRect().left - strip.getBoundingClientRect().left + strip.scrollLeft;
      var right = left + item.getBoundingClientRect().width;
      if (left < strip.scrollLeft - 1) {
        strip.scrollTo({ left: left, behavior: 'smooth' });
      } else if (right > strip.scrollLeft + strip.clientWidth + 1) {
        strip.scrollTo({ left: right - strip.clientWidth, behavior: 'smooth' });
      }
    }

    function alignGalleryArrows() {
      var viewport = gallery.querySelector('.flex-viewport');
      if (viewport) gallery.style.setProperty('--product-gallery-height', viewport.getBoundingClientRect().height + 'px');
      keepActiveThumbnailVisible();
    }

    if ('ResizeObserver' in window) new ResizeObserver(alignGalleryArrows).observe(gallery);
    new MutationObserver(keepActiveThumbnailVisible).observe(gallery, { subtree: true, childList: true, attributes: true, attributeFilter: ['class'] });
    window.addEventListener('load', alignGalleryArrows);
    window.addEventListener('resize', alignGalleryArrows);
    alignGalleryArrows();
  });

  /* Homepage product sections: accessible category tabs. */
  document.querySelectorAll('.home-products').forEach(function (section) {
    var tabs = Array.from(section.querySelectorAll('.home-products__tab'));
    var panels = section.querySelectorAll('.home-products__panel');
    var allProducts = section.querySelector('.home-products__button');
    if (!tabs.length) return;

    function activate(tab) {
      tabs.forEach(function (button) {
        var active = button === tab;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-selected', active ? 'true' : 'false');
        button.tabIndex = active ? 0 : -1;
      });
      panels.forEach(function (panel) {
        panel.hidden = panel.id !== tab.getAttribute('aria-controls');
      });
      if (allProducts) {
        allProducts.href = tab.dataset.categoryUrl;
        allProducts.setAttribute('aria-label', 'Xem tất cả sản phẩm trong ' + tab.dataset.categoryName);
      }
      if (isPhone()) {
        var strip = tab.parentElement;
        var bounds = strip.getBoundingClientRect();
        var itemBounds = tab.getBoundingClientRect();
        var offset = itemBounds.left < bounds.left ? itemBounds.left - bounds.left : Math.max(0, itemBounds.right - bounds.right);
        if (offset) strip.scrollBy({ left: offset, behavior: reduceMotion ? 'auto' : 'smooth' });
      }
    }

    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () { activate(tab); });
      tab.addEventListener('keydown', function (event) {
        var keys = { ArrowRight: (index + 1) % tabs.length, ArrowLeft: (index - 1 + tabs.length) % tabs.length, Home: 0, End: tabs.length - 1 };
        if (!(event.key in keys)) return;
        event.preventDefault();
        activate(tabs[keys[event.key]]);
        tabs[keys[event.key]].focus();
      });
    });
  });

  /* News page: keep the selected category visible in the scrollable strip on phones. */
  var newsCategories = document.querySelector('.news-page__categories');
  if (newsCategories) {
    var keepNewsCategoryVisible = function () {
      if (!isPhone()) return;
      var selected = newsCategories.querySelector('[aria-current="page"]');
      if (!selected) return;
      newsCategories.scrollLeft += Math.max(0, selected.getBoundingClientRect().right - newsCategories.getBoundingClientRect().right);
    };
    keepNewsCategoryVisible();
    window.addEventListener('resize', keepNewsCategoryVisible);
  }

  /* Header: compact state once the page scrolls. */
  var $body = $('body');
  var updateHeaderState = function () {
    $body.toggleClass('header-sub-hidden', $(window).scrollTop() > 100);
  };
  updateHeaderState();
  $(window).on('scroll', updateHeaderState);

  /* Smooth scrolling for same-page anchors. */
  $(document).on('click', 'a[href*="#"]:not([href="#"])', function (event) {
    if (location.pathname !== this.pathname || location.hostname !== this.hostname) return;
    var $target = $(this.hash);
    if (!$target.length) $target = $('[name="' + this.hash.slice(1) + '"]');
    if (!$target.length) return;
    event.preventDefault();
    $('html, body').animate({ scrollTop: $target.offset().top - 50 }, reduceMotion ? 0 : 500);
  });

  /* Comment form: ask for contact details in a popup before submitting. */
  var $commentForm = $('.content .comment-form');
  if ($commentForm.length) {
    var $popup = $commentForm.find('.popup-comment');
    $commentForm.find('#submit').on('click', function () {
      if (!$commentForm.find('#comment').val()) {
        alert('Bạn chưa nhập nội dung bình luận!');
      } else {
        $popup.fadeIn();
      }
      return false;
    });
    $popup.find('.close-popup-comment').on('click', function () { $popup.fadeOut(); });
    $('main.content #respond input#submit-commnent').on('click', function () {
      if (!$commentForm.find('#author').val()) {
        alert('Bạn chưa nhập họ và tên!');
        return false;
      }
      if (!$commentForm.find('#email').val()) {
        alert('Bạn chưa nhập email!');
        return false;
      }
      if (!$commentForm.find('.comment-form-phone #author').val()) {
        alert('Bạn chưa nhập số điện thoại!');
        return false;
      }
      $('.content #commentform').submit();
      $popup.fadeOut();
    });
  }

  /* Homepage sliders (slick is only loaded on the homepage). */
  if (!$.fn.slick) return;

  $('.home-hero__slider').not('.slick-initialized').each(function () {
    var multiple = $(this).children('.home-hero__slide').length > 1;
    $(this).slick({
      rows: 0,
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: multiple,
      prevArrow: '<button type="button" class="home-hero__arrow home-hero__arrow--prev" aria-label="Banner trước"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>',
      nextArrow: '<button type="button" class="home-hero__arrow home-hero__arrow--next" aria-label="Banner tiếp theo"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>',
      dots: multiple,
      infinite: multiple,
      autoplay: multiple,
      autoplaySpeed: 5000,
      speed: 550,
      pauseOnHover: true,
      pauseOnFocus: true,
      adaptiveHeight: true
    });
  });

  $('.home-news__slider').not('.slick-initialized').each(function () {
    var count = $(this).children('.home-news__card').length;
    $(this).slick({
      slidesToShow: 4,
      slidesToScroll: 4,
      arrows: false,
      dots: count > 4,
      infinite: true,
      autoplay: true,
      autoplaySpeed: 5000,
      speed: 500,
      pauseOnHover: true,
      pauseOnFocus: true,
      responsive: [
        { breakpoint: 1024, settings: { slidesToShow: 2, slidesToScroll: 2, dots: count > 2 } },
        { breakpoint: 600, settings: { slidesToShow: 1, slidesToScroll: 1, dots: count > 1 } }
      ]
    });
  });

  $('.home-reviews__slider').not('.slick-initialized').each(function () {
    $(this).slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: $(this).children('.home-reviews__slide').length > 1,
      cssEase: 'ease-in-out',
      autoplay: true,
      autoplaySpeed: 5000,
      speed: 550,
      pauseOnHover: true,
      pauseOnFocus: true,
      adaptiveHeight: true
    });
  });
});

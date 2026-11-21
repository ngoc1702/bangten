document.addEventListener('DOMContentLoaded', function () {
  var faqItems = document.querySelectorAll('.home-faq__item');

  if (!faqItems.length) {
    return;
  }

  faqItems.forEach(function (item) {
    item.open = false;

    item.addEventListener('toggle', function () {
      if (!item.open) {
        return;
      }

      faqItems.forEach(function (otherItem) {
        if (otherItem !== item) {
          otherItem.open = false;
        }
      });
    });
  });
});

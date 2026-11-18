(function ($) {
  'use strict';

  var checkboxFieldKey = 'field_caia_home_partner_categories';
  var orderedFieldKey = 'field_caia_home_partner_categories_ordered';

  function orderedField() {
    return $('.acf-field[data-key="' + orderedFieldKey + '"]');
  }

  function rows() {
    return orderedField().find('tr.acf-row').not('.acf-clone');
  }

  function selectedValues() {
    return rows().map(function () {
      return $(this).find('select').first().val();
    }).get().filter(Boolean);
  }

  function emptySelect() {
    return rows().find('select').filter(function () {
      return !$(this).val();
    }).first();
  }

  function addPartner(value) {
    if (!value || selectedValues().indexOf(value) !== -1) {
      return;
    }

    var $select = emptySelect();

    if (!$select.length) {
      orderedField().find('.acf-actions a[data-event="add-row"]').first().trigger('click');
      $select = emptySelect();
    }

    $select.val(value).trigger('change');
  }

  function removePartner(value) {
    rows().filter(function () {
      return $(this).find('select').first().val() === value;
    }).find('a[data-event="remove-row"]').first().trigger('click');
  }

  function populateExistingSelections() {
    if (selectedValues().length) {
      return;
    }

    $('.acf-field[data-key="' + checkboxFieldKey + '"] input[type="checkbox"]:checked').each(function () {
      addPartner($(this).val());
    });
  }

  acf.addAction('ready', function () {
    populateExistingSelections();
  });

  $(document).on('change', '.acf-field[data-key="' + checkboxFieldKey + '"] input[type="checkbox"]', function () {
    if (this.checked) {
      addPartner(this.value);
      return;
    }

    removePartner(this.value);
  });
})(jQuery);

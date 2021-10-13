(function ($) {
  Drupal.behaviors.portal_sub_menu = {
    attach: function (context, settings) {
      $('#ocha-menu').find('.sub-menu').hide();
      $('#ocha-menu li', context).click(function () {
        $(this).find('.sub-menu').toggle();
      });
    }
  };
}(jQuery));
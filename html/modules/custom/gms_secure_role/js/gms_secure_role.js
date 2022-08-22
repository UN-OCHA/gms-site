(function ($) {
    'use strict';
    Drupal.behaviors.gms_secure_role = {
        attach: function (context, settings) {
            jQuery('#show_popup').trigger('click');
        }
    };
}(jQuery));

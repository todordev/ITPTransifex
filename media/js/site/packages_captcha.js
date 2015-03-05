jQuery(document).ready(function() {
    "use strict";

    var $modalPackage     = jQuery('#js-modal-package');

    jQuery(".js-pkg-btn-download").bind("click", function(event) {
        event.preventDefault();

        jQuery('#js-form-package-id').val(jQuery(this).data("package-id"));
        $modalPackage.modal('show');
    });

    jQuery("#js-modal-btn-close").on("click", function(event) {
        event.preventDefault();
        $modalPackage.modal('hide');
    });

    jQuery("#js-modal-btn-download").on("click", function(event) {
        event.preventDefault();

        $modalPackage.modal('hide');
        jQuery("#js-form-download-project").submit();
    });

    $modalPackage.on('show.bs.modal', function () {
        grecaptcha.reset(transifexCaptcha);
    });

});

jQuery(document).ready(function() {
    "use strict";

    var $modalProject    = jQuery('#js-modal-project');

    jQuery(".js-prj-btn-download").bind("click", function(event) {
		event.preventDefault();

        jQuery('#js-form-language').val(jQuery(this).data("language"));
        $modalProject.modal('show');
	});

    jQuery("#js-modal-btn-close").on("click", function(event) {
        event.preventDefault();
        $modalProject.modal('hide');
    });

    jQuery("#js-modal-btn-download").on("click", function(event) {
        event.preventDefault();

        $modalProject.modal('hide');
        jQuery("#js-form-download-project").submit();
    });

    $modalProject.on('show.bs.modal', function () {
        grecaptcha.reset(transifexCaptcha);
    });
});

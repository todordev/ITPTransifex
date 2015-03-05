jQuery(document).ready(function() {
    "use strict";

    jQuery(".js-pkg-btn-download").bind("click", function(event) {
		event.preventDefault();

        jQuery("#js-form-package-id").val(jQuery(this).data("package-id"));

        jQuery("#js-form-download-package").submit();
	});

});

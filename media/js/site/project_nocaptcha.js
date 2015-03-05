jQuery(document).ready(function() {
    "use strict";

    jQuery(".js-prj-btn-download").bind("click", function(event) {
		event.preventDefault();

        jQuery("#js-form-language").val(jQuery(this).data("language"));

        jQuery("#js-form-download-project").submit();
	});

});

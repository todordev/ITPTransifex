jQuery(document).ready(function() {
	
	// Preview resources
    jQuery("#packagesList").on("click", ".js-btn-resources-list", function(event){
		
		event.preventDefault();
		
		var url = jQuery(this).attr("href");

		jQuery.ajax({
			type: "GET",
			url: url,
			dataType: "text html"
		}).done(function(response){
			
			jQuery("#js-resources-list-body").html(response);
			jQuery('#js-resources-list-modal').modal('show');
			
		});
		
	});
	
	jQuery("#js-resources-list-close-btn").on("click", function(event){
		jQuery('#js-resources-list-modal').modal('hide');
	});

    jQuery("#js-itptfx-btn-batch").on("click", function(event){
        event.preventDefault();

        var ids = [];

        // Get selected items.
        var checkBoxes  = jQuery("#packagesList").find("input:checkbox");
        jQuery.each(checkBoxes, function( index, value ) {

            if(jQuery(value).is(":checked")) {
                ids.push(parseInt(jQuery(value).val()));
            }

        });

        if (ids.length == 0) {
            jQuery('#collapseModal').modal('hide');
            ITPrismUIHelper.displayMessageFailure(Joomla.JText._('COM_ITPTRANSIFEX_PACKAGES_NOT_SELECTED'));
            return;
        }

        // Submit the form.
        var $batchForm = jQuery("#js-itptfx-batch-form");

        var url        = $batchForm.attr("action");
        var formData   = $batchForm.serializeArray();

        formData.push({name: 'ids', value: ids});

        jQuery.ajax({
            type: "POST",
            url: url,
            data: formData,
            dataType: "text json",
            beforeSend: function() {
                jQuery("#js-batch-ajaxloader").show();
            }
        }).done(function(response){

            /*jQuery("#js-batch-ajaxloader").hide();
            jQuery('#collapseModal').modal('hide');*/

            if(!response.success) {
                ITPrismUIHelper.displayMessageFailure(response.title, response.text);
            } else {
                ITPrismUIHelper.displayMessageSuccess(response.title, response.text);

                // Reload the page.
                setTimeout(function(){
                    window.location.replace("index.php?option=com_itptransifex&view=packages");
                }, 2000);

            }

        });

    });

    // Clear task after a time when download a package.
    var downloadButton = jQuery("#toolbar-download").find("button");
    downloadButton.on("click", function(){
        window.setTimeout(function(){
            jQuery("#js-form-task").val("");
        }, 3000)
    });

    // Check the radio button for new version on focus.
    jQuery("#new_version").on('focus', function(){
        jQuery("#new_version_radio").prop("checked", true);
    });

    // Check the radio button for new version on focus.
    jQuery("#search_string, #replace_string").on('focus', function(){
        jQuery("#replace_text_radio").prop("checked", true);
    });
});
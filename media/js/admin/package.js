jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'package.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    };

    jQuery("#resources-list").on("click", ".itptfx-btn-remove", function(event) {

        event.preventDefault();

        if (confirm(Joomla.JText._('COM_ITPTRANSIFEX_DELETE_ITEM_QUESTION'))) {

            var url = jQuery(this).attr("href");
            var resourceId = jQuery(this).data("rid");
            var packageId = jQuery(this).data("pid");

            var formData = {
                pid: packageId,
                rid: resourceId
            };

            // Load data for this package
            jQuery.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "text json",
                beforeSend: function() {
                    jQuery("#js-ajaxloader").show();
                },
                success: function(response) {

                    jQuery("#js-ajaxloader").hide();

                    if(!response.success) {

                        PrismUIHelper.displayMessageFailure(response.title, response.text);

                    } else {

                        jQuery("#resource-id"+resourceId).remove();
                        PrismUIHelper.displayMessageSuccess(response.title, response.text);

                    }

                }

            });
        }
    });

    // Display a form for adding resource.
    jQuery("#itptfx-btn-add").on("click", function(event) {
        event.preventDefault();

        jQuery("#itptfx-add-resource").show();
    });


    // Load resources from the server
    jQuery('#itptfx-resource-input').typeahead({

        ajax : {
            url: "index.php?option=com_itptransifex&format=raw&task=package.loadResources",
            method: "get",
            triggerLength: 3,
            preProcess: function (response) {

                if (response.success === false) {
                    return false;
                }

                return response.data;
            }
        },
        onSelect: function(item) {

            if (item.value) {

                var formData = {
                    pid: jQuery("#jform_id").val(),
                    rid: item.value
                };

                // Load data for this package
                var jqXHR = jQuery.ajax({
                    url: "index.php?option=com_itptransifex&task=package.addResource&format=raw",
                    type: "POST",
                    data: formData,
                    dataType: "html",
                    beforeSend: function() {
                        jQuery("#js-ajaxloader").show();
                    }
                });

                jqXHR.done(function(response) {

                    jQuery("#js-ajaxloader").hide();

                    if(!response) {
                        PrismUIHelper.displayMessageFailure(Joomla.JText._('COM_ITPTRANSIFEX_FAIL'), Joomla.JText._('COM_ITPTRANSIFEX_ERROR_CANNOT_ADD_RESOURCE'));
                    } else {
                        jQuery("#itptfx-resource-wrapper").append(response);
                    }

                });
            }
        }

    });
});
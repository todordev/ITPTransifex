jQuery(document).ready(function() {

    // Initialize button "Update".
    Joomla.submitbutton = function(task){
    	
        if (task == 'projects.update') {

    		if(!hasSelectedItems()) {
    			alert(Joomla.JText._("COM_ITPTRANSIFEX_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST"));
    		} else {
    			Joomla.submitform(task, document.getElementById('adminForm'));
    		}

        // Display modal window when user wants to export project data.
        } else if (task == 'package.downloadProject') {

            if(!hasSelectedItems()) {
                alert(Joomla.JText._("COM_ITPTRANSIFEX_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST"));
            } else {
                jQuery('#js-cfp-modal').modal('show');
            }

        } else {
        	Joomla.submitform(task, document.getElementById('adminForm'));
        }
    	
    };

    // Event for button Submit
    jQuery("#js-btn-cfp-submit").on("click", function(event){

        event.preventDefault();

        // Get resource IDs
        var projectId   = 0;
        var checkBoxes  = jQuery("#projectsList").find("input:checkbox");

        jQuery.each(checkBoxes, function( index, value ) {

            if(jQuery(value).is(":checked")) {
                projectId = parseInt(jQuery(value).val());
            }

        });

        jQuery('#js-cfp-modal').modal('hide');

        window.location = jQuery("#languageForm").attr("action")+"&id="+projectId+"&language="+jQuery("#js-cfp-language").val();

    });

    // Event for button Cancel
    jQuery("#js-btn-cfp-cancel").on("click", function(event){
        event.preventDefault();

        jQuery('#js-cfp-modal').modal('hide');
    });

    /**
     * Check for selected items.
     */
    function hasSelectedItems() {

        var hasSelectedItems = false;

        // Look for selected projects
        var checkBoxes  = jQuery("#projectsList").find("input:checkbox");
        jQuery.each(checkBoxes, function( index, value ) {

            if(jQuery(value).is(":checked")) {
                hasSelectedItems = true;
            }

        });

        return hasSelectedItems;

    }
});
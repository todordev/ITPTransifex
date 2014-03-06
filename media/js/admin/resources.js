jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
    	
    	// Update resources.
    	if (task == 'resources.update') {

    		if(!hasSelectedItems()) {
    			alert(Joomla.JText._("COM_ITPTRANSIFEX_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST"));
    		} else {
    			Joomla.submitform(task, document.getElementById('adminForm'));
    		}
    		
		// Display modal window for creating project
    	} else if (task == 'package.create') { 
    		
    		if(!hasSelectedItems()) {
    			alert(Joomla.JText._("COM_ITPTRANSIFEX_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST"));
    		} else {
    			jQuery('#js-cp-modal').modal('show');
    		}
        	
    	// Submit form
        } else {
        	Joomla.submitform(task, document.getElementById('adminForm'));
        }
    	
    };
    
    // Event for button Submit
    jQuery("#js-btn-sp").on("click", function(event){
		
		event.preventDefault();
		
		// Get form data
		var formData = jQuery("#packageForm").serializeArray();
		
		// Get resource IDs
		var resourceIDs = new Array();
		var checkBoxes  = jQuery("#resourcesList input:checkbox");
		jQuery.each(checkBoxes, function( index, value ) {
			
			if(jQuery(value).is(":checked")) {
				var resourceId = parseInt(jQuery(value).val());
				if(resourceId) {
					resourceIDs.push(resourceId);
				}
			}
			
		});
		
		jQuery.each(resourceIDs, function( index, value ) {
			var resource = {
				"name" : "resource[]",
				"value" : value
			};
			formData.push(resource);
		});
		
		var url = jQuery("#packageForm").attr("action");
		
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
				jQuery('#js-cp-modal').modal('hide');
				
				if(!response.success) {
					
					ITPrismUIHelper.displayMessageFailure(response.title, response.text);
				
				} else {
					
					// Reset form data
					resetProjectForm();
					
					ITPrismUIHelper.displayMessageSuccess(response.title, response.text);
					
				}
				
			}
				
		});
		
	});
    
    // Event for button Cancel
	jQuery("#js-btn-cp-cancel").on("click", function(event){
		event.preventDefault();
		
		// Reset form data
		resetProjectForm();
		
		jQuery('#js-cp-modal').modal('hide');
	});
	
	// Reset form data
    jQuery('#js-cp-modal').on('hide', function () {
    	resetProjectForm();
    });
        
    /**
     * Reset some form fields.
     */
    function resetProjectForm() {
    	
    	jQuery("#jform_name").val("");
		jQuery("#jform_filename").val("");
		jQuery("#jform_version").val("");
		jQuery("#jform_description").val("");
		
    }
    
    /**
     * Check for selected items.
     */
    function hasSelectedItems() {
    	
    	var hasSelectedItems = false;
    	
    	// Look for selected resources.
    	var checkBoxes  = jQuery("#adminForm input:checkbox");
		jQuery.each(checkBoxes, function( index, value ) {
			
			if(jQuery(value).is(":checked")) {
				hasSelectedItems = true;
			}
			
		});
		
		return hasSelectedItems;
		
    }
	
});
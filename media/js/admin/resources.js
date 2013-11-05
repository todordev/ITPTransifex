jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
    	
    	jQuery.itptransifex = {
			task: task
    	}
    	
        if (jQuery.itptransifex.task == 'package.create') {

        	jQuery('#js-cp-modal').modal('show');
        	
        } else {
        	
        	jQuery("#js-langcode-target").val("");
        	jQuery("#js-name-target").val("");
        	jQuery("#js-filename-target").val("");
    		jQuery("#js-version-target").val("");
    		jQuery("#js-desc-target").val("");
    		
        	Joomla.submitform(jQuery.itptransifex.task, document.getElementById('adminForm'));
        	
        }
    	
    };
    
    jQuery("#js-btn-loaddata").on("click", function(event){
    	event.preventDefault();
    	
    	var projectId = jQuery("#js-project-id").val(); 
    	var langCode  = ""; 
    	
    	// Get lanugage codes
		var radioButtons = jQuery(".js-languages");
		jQuery.each(radioButtons, function( index, value ) {
			
			if(jQuery(value).is(":checked")) {
				langCode = jQuery(value).val();
				return false;
			}
			
		});
		
		// Get resource IDs
		var resourceIDs = new Array();
		var checkBoxes  = jQuery("#resourcesList input:checkbox");
		jQuery.each(checkBoxes, function( index, value ) {
			
			if(jQuery(value).is(":checked")) {
				resourceIDs.push(jQuery(value).val());
			}
			
		});
		
		var data = {
			lang_code: langCode,
			project_id: projectId,
			cid: resourceIDs,
			format: "raw"
		} 
			
		// Load data for this package
		jQuery.ajax({
			url: "index.php?option=com_itptransifex&task=package.loadPackageData",
			type: "POST",
			data: data,
			dataType: "text json",
			beforeSend: function() {
				jQuery("#js-ajaxloader-load-data").show();
			},
			success: function(response) {
				
				jQuery("#js-ajaxloader-load-data").hide();
				
				if(!response.success) {
					ITPrismUIHelper.displayMessageFailure(response.title, response.text);
				} else {
					
					jQuery("#js-name-source").val(response.data.name);
					jQuery("#js-filename-source").val(response.data.filename);
					jQuery("#js-version-source").val(response.data.version);
					jQuery("#js-desc-source").val(response.data.description);
					
					ITPrismUIHelper.displayMessageSuccess(response.title, response.text);
					
				}
				
			}
				
		});

		
    });
    
    // Event for button Submit
    jQuery("#js-btn-sp").on("click", function(event){
		
		event.preventDefault();
		
		// Set language code
		var radioButtons = jQuery(".js-languages");
		jQuery.each(radioButtons, function( index, value ) {
			
			if(jQuery(value).is(":checked")) {
				var langCode = jQuery(value).val();
				jQuery("#js-langcode-target").val(langCode);
				return false;
			}
			
		});
		
		var name    	= jQuery("#js-name-source").val();
		var filename    = jQuery("#js-filename-source").val();
		var version 	= jQuery("#js-version-source").val();
		var desc    	= jQuery("#js-desc-source").val();
		var storeData  	= jQuery("#js-store-data-source").val();
		
		jQuery("#js-name-target").val(name);
		jQuery("#js-filename-target").val(filename);
		jQuery("#js-version-target").val(version);
		jQuery("#js-desc-target").val(desc);
		jQuery("#js-store-data-target").val(storeData);
		
		jQuery("#js-ajaxloader").show();
		Joomla.submitform(jQuery.itptransifex.task, document.getElementById('adminForm'));
	});
    
    // Event for button Cancel
	jQuery("#js-btn-cp-cancel").on("click", function(event){
		event.preventDefault();
		jQuery('#js-cp-modal').modal('hide');
	});
	
	// Clear the data
    jQuery('#js-cp-modal').on('hide', function () {
    	jQuery("#js-langcode-target").val("");
    	jQuery("#js-name-target").val("");
    	jQuery("#js-filename-target").val("");
		jQuery("#js-version-target").val("");
		jQuery("#js-desc-target").val("");
		
		jQuery("#js-name-source").val("");
		jQuery("#js-filename-source").val("");
		jQuery("#js-version-source").val("");
		jQuery("#js-desc-source").val("");
		jQuery("#js-store-data-source").val("");
    });
        
});
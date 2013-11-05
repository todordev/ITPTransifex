jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
    	
        if (task == 'projects.update') {

        	jQuery.itptransifex = {
        		selectedProjects: false	
        	};
        	
        	// Look for selected projects
        	var checkBoxes  = jQuery("#projectsList input:checkbox");
    		jQuery.each(checkBoxes, function( index, value ) {
    			
    			if(jQuery(value).is(":checked")) {
    				jQuery.itptransifex.selectedProjects = true;
    			}
    			
    		});
    		
    		if(!jQuery.itptransifex.selectedProjects) {
    			alert(Joomla.JText._("COM_ITPTRANSIFEX_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST"));
    		} else {
    			Joomla.submitform(task, document.getElementById('adminForm'));
    		}
        	
        } else {
        	Joomla.submitform(task, document.getElementById('adminForm'));
        }
    	
    };
    
});
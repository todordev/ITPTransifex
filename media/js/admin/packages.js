jQuery(document).ready(function() {
	
	// Preview resources
    jQuery("#packagesList").on("click", ".js-btn-resources-list", function(event){
		
		event.preventDefault();
		
		var url = jQuery(this).attr("href");
		console.log(url);
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
	
});
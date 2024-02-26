jQuery(document).ready(function () {
	jQuery('#toggleAdvancedSearch').click(function () {
		jQuery('#advancedSearchFields').slideToggle();
		var applyAdvance = jQuery('#apply_advance').val();
		if (applyAdvance == '0') {
			jQuery('#apply_advance').val('1');
		} else {
			jQuery('#apply_advance').val('0');
		}
	});
	jQuery('#age').on('input', function () {
		jQuery('#ageValue').text(jQuery(this).val());
	});
	jQuery('.star').on('mouseenter', function () {
		var value = jQuery(this).data('value');
		jQuery('.star').removeClass('selected');
		jQuery('.star').each(function () {
			if (jQuery(this).data('value') <= value) {
				jQuery(this).addClass('selected');
			}
		});
	});
	jQuery('.star').on('mouseleave', function () {
		var value = jQuery('#selectedRating').val();
		jQuery('.star').removeClass('selected');
		jQuery('.star').each(function () {
			if (jQuery(this).data('value') <= value) {
				jQuery(this).addClass('selected');
			}
		});
	});
	jQuery('.star').on('click touchstart', function () {
		var value = jQuery(this).data('value');
		jQuery('#selectedRating').val(value);
	});
	// Initialize Select2
	jQuery('.select2').select2();
	// Initialize DataTables
	var dataTable = jQuery('#profile-listings').DataTable({
		"processing": true,
		"serverSide": true,
		"lengthMenu": [[5], [5]], // Set default entries to 5 and hide entries selection
		"lengthChange": false, // Disable entries selection
		"bFilter": false, // Disable search filter
		"ajax": {
			"url": profile_listing_data.rest_endpoint_url, // custom profiles listing API endpoint URL
			"type": "GET", // Using GET method to retrieve data,
			"data": function (d) {
				// Including form fields' values in the AJAX call
				d.keyword = jQuery('#keyword').val();
				d.skills = jQuery('#skills').val();
				d.education = jQuery('#education').val();
				d.age = jQuery('#age').val();
				d.rating = jQuery('#selectedRating').val();
				d.jobs_completed = jQuery('#jobs_completed').val();
				d.years_experience = jQuery('#years_experience').val();
				d.apply_advance = jQuery('#apply_advance').val();

				// Adding nonce key to validate request
				d.nonce = profile_listing_data.nonce;
			},
			"dataSrc": "data" // data key contains the array of data
		},
		"columns": [
			{"data": "no"},
			{"data": "profile_name"},
			{"data": "age"},
			{"data": "years_of_experience"},
			{"data": "jobs_completed"},
			{"data": "ratings"}
		]
	});
	jQuery("#profile-listing-search-form").on('submit', function (event) {
		jQuery('#skills').val();
		event.preventDefault(); // Prevent the default form submission
		dataTable.ajax.reload(); // Reload DataTables with new data
	});
});

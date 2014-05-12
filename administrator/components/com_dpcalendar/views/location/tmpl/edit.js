dpjQuery(document).ready(function(){
	dpjQuery('#jform_street, #jform_number, #jform_zip, #jform_city, #jform_country, #jform_province').bind('change', function(e) {
		updateLocationFrame(true);
	});
	dpjQuery('#jform_latitude, #jform_longitude').bind('change', function(e) {
		var location = dpjQuery('#jform_latitude').val()+','+dpjQuery('#jform_longitude').val();
		dpjQuery('#event-location-frame').attr('src', 'http://maps.google.com/?q='+encodeURIComponent(location)+'&output=embed&iwloc=near');
	});
	
	if (dpjQuery('#jform_latitude').val() != null && dpjQuery('#jform_longitude').val() != null) {
		var location = dpjQuery('#jform_latitude').val()+','+dpjQuery('#jform_longitude').val();
		dpjQuery('#event-location-frame').attr('src', 'http://maps.google.com/?q='+encodeURIComponent(location)+'&output=embed&iwloc=near');
	} else {		
		updateLocationFrame(false);
	}
});

function updateLocationFrame(fetch){
	var location = '';
	location += dpjQuery('#jform_street').val()+',';
	location += dpjQuery('#jform_number').val()+',';
	location += dpjQuery('#jform_zip').val()+',';
	location += dpjQuery('#jform_city').val()+',';
	location += dpjQuery('#jform_country').val();
	dpjQuery('#event-location-frame').attr('src', 'http://maps.google.com/?q='+encodeURIComponent(location)+'&output=embed&iwloc=near');

	if(fetch){
		dpjQuery.ajax({
			type: 'POST',
			url: 'index.php?option=com_dpcalendar&task=location.loc&loc='+encodeURIComponent(location),
			success: function (data) {
				var json = dpjQuery.parseJSON(data);
				dpjQuery('#jform_latitude').val(json.latitude);
				dpjQuery('#jform_longitude').val(json.longitude);
			}
		});
	}
}
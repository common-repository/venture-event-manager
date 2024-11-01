(function(ventureEventListings, $, undefined) {

	function createFieldManager(dataInput, fields, pageType, fieldType) {

		if (dataInput.size() == 0) return;
		var classPrefix = pageType+'-'+fieldType+'-fields';

		var fieldStart = dataInput.val().split(',');
		newContent = '<div class="vem-fields-sorter">'+
			'<div class="vem-fields-sorter-one-column"><div class="vem-title">Include These</div><ul id="'+classPrefix+'-on" class="event-settings-sorter '+classPrefix+'-connected">';
		tmp = new Array();
		$.each(fields,function() {
			loc = $.inArray(this.key,fieldStart);
			if (loc > -1) {
				tmp[loc] = '<li class="ui-state-default" field="'+this.key+'"><i class="fas fa-grip-vertical grip"></i>'+this.value+'</li>';
			}
		});
		newContent += tmp.join('');
		newContent += '</ul></div>'+
		'<div class="vem-fields-sorter-one-column"><div class="vem-title">Exclude These</div><ul id="'+classPrefix+'-off" class="event-settings-sorter '+classPrefix+'-connected">';
		$.each(fields,function() {
			loc = $.inArray(this.key,fieldStart);
			if (loc == -1) {
				newContent += '<li class="ui-state-default" field="'+this.key+'"><i class="fas fa-grip-vertical grip"></i>'+this.value+'</li>';
			}
		});
		newContent += '</ul></div></div>';
		dataInput.hide().after(newContent);
	
		$( "#"+classPrefix+"-on, #"+classPrefix+"-off" ).sortable({
			connectWith: "."+classPrefix+"-connected",
			update: function(event, ui) {
				newVals = new Array();
				$('#'+classPrefix+'-on li').each(function(index,element) {
					newVals.push($(this).attr('field'));			
				});
				dataInput.val(newVals.join(','));
			}
		}).disableSelection();
	}
	
	$(document).ready(function() {
		createFieldManager($('#venture-event-listing_listing-event-fields'), vemEventSettingsFields.indexPageFields, 'listing', 'page');
		createFieldManager($('#venture-event-listing_listing-occurrence-fields'), vemEventSettingsFields.indexOccurrenceFields, 'listing', 'occurrence');
		$(".event-listing-id").text($("#post_ID").val()).parent('div').unwrap('td').unwrap('tr').unwrap('tbody').unwrap('table').show();
		$(".shortcode-wrapper").each(function(index) { $(this).text($(this).text()) });
	});

} (window.ventureEventListings = window.ventureEventListings || {}, jQuery));
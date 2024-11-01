(function(ventureOptions, $, undefined) {

	function updatePreview() {
		$.post(
			ventureAdminOptions.ajaxurl,
			{ 
				action: 'getRunDatesPreview',
				data: {
					showYears: $("select[name=venture-event-system_run-dates-display-years] option:selected").val(),
					withYearsFormat: $("#venture-event-system_date-format-with-years").val(),
					withoutYearsFormat: $("#venture-event-system_date-format-without-years").val(),
					textFormatDifferent: $("#venture-event-system_full-display-multiple-dates").val(),
					textFormatSame: $("#venture-event-system_full-display-single-date").val()
				}
			},
			function(data) {
				results = JSON.parse(data);
				$('#run-dates-preview-single').html(results.single);
				$('#run-dates-preview-multiple-same-year').html(results.multiple.sameYear);
				$('#run-dates-preview-multiple-years').html(results.multiple.differentYear);
				$('#run-dates-preview').show();
			}
		);
	}

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
		if ($("#run-dates-preview").size() > 0) {
			updatePreview();
			$(".form-table input, .form-table select").on("change", function() { updatePreview(); })
		}

		createFieldManager($('#venture-event-system_index-page-fields'), vemEventSettingsFields.indexPageFields, 'index', 'page');
		createFieldManager($('#venture-event-system_index-page-occurrence-fields'), vemEventSettingsFields.indexOccurrenceFields, 'index', 'occurrence');
		createFieldManager($('#venture-event-system_single-page-fields'), vemEventSettingsFields.singlePageFields, 'single', 'page');
		createFieldManager($('#venture-event-system_single-page-occurrence-fields'), vemEventSettingsFields.singleOccurrenceFields, 'single', 'occurrence');
		createFieldManager($('#venture-event-system_event-archive-index-fields'), vemEventSettingsFields.archivePageFields, 'archive', 'page');
		createFieldManager($('#venture-event-system_event-archive-index-occurrence-fields'), vemEventSettingsFields.archiveOccurrenceFields, 'archive', 'occurrence');
		createFieldManager($('#venture-event-system_event-archive-fields'), vemEventSettingsFields.archivesPageFields, 'archives', 'page');
		createFieldManager($('#venture-event-system_event-archive-occurrence-fields'), vemEventSettingsFields.archivesOccurrenceFields, 'archives', 'occurrence');
	});

} (window.ventureOptions = window.ventureOptions || {}, jQuery));
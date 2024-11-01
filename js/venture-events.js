(function(ventureEvents, $, undefined) {

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

	function createField(label, key, value, i) {
		var html = '<div class="one-field">';
		html += '<div class="move-icon"><i class="fas fa-grip-vertical"></i></div>';
		html += '<div class="field-input field-key"><input name="vem_sets['+label+']['+i+'][key]" value="'+key+'">'+'</div>';
		html += '<div class="field-input field-value"><input name="vem_sets['+label+']['+i+'][value]" value="'+value.replace(/"/g,"&quot;")+'">'+'</div>';
		html += '<div class="deleter-icon remove-field"><i class="fas fa-trash"></i></div>';
		html += '</div>';

		return html;
	}

	function updateSetUi() {
		$('.set-ui').each(function(index) {
			var label = $('.set-label', this).val();
			var data = JSON.parse($('.set-value-raw', this).val()) || {};
			var i = 0;
			var html = '';
			for (var field in data) {
				html += createField(label, data[field].key, data[field].value, i);
				i++;
			}
			$('.set-display', this).html(html);
		});
	}

	$(document).ready(function() {

		// Field sets UI
        $(".set-ui .add-field").click(function(e){
            e.preventDefault();
			
			var setui = $(this).parents('.set-ui');
			var c = setui.find('.one-field').length;
			var label = setui.find('.set-label').val();
			var field = createField(label, '', '', c);
			setui.find('.set-display').append(field);
        });
        
        $(".set-display").on("click", ".remove-field", function(e) {
            e.preventDefault();
			var setui = $(this).parents('.set-ui');
			$(this).parents('.one-field').remove();
			var c = setui.find('.one-field').length;
			if (c == 0) { console.log('adding empty');
				var label = setui.find('.set-label').val();
				var field = createField(label, '', '', c);
				setui.find('.set-display').append(field);
			}
        });
        
        $(".set-ui .set-display").sortable({
			handle: ".move-icon",
			containment: "parent",
			update: function(event, ui) {
				// Renumber the fields
				var setui = $(this).parents('.set-ui');
				var fields = setui.find('.one-field');
				var label = setui.find('.set-label').val();

				fields.each(function(index) {
					$(this).find('.field-key input').attr('name', 'vem_sets['+label+']['+index+'][key]');
					$(this).find('.field-value input').attr('name', 'vem_sets['+label+']['+index+'][value]');
				});
			}
        });

		updateSetUi();

        // Occurrences UI

	    updateHeaders();

	    // Make a copy of the last one in case they delete everything and then click Add
	    $(".vem-one-occurrence:last").clone(false).hide().attr("id","vem-spare").prependTo("body");

	    $("#_vem_010_display_dates_meta_box").on("click", ".vem-one-occurrence .vem-header-bar, .vem-one-occurrence .vem-toggle-button", function(e) {
	        $(this).closest(".vem-one-occurrence").toggleClass("open closed");
	    });

	    $("input.date-picker").datepicker({dateFormat: 'mm/dd/yy'});
	    
	    $("#add-another-date").click(function(e){
	        e.preventDefault();
	        // Get current last occurrence and clone it
	        var lastItem = $(".vem-one-occurrence:last");
	        var newItem = lastItem.clone(false).removeClass("open").addClass("closed");

	        // Adjust the form element names to have the correct array index
	        $("input, select", newItem).attr("name", function() {
	            return $(this).attr("name").replace(/\[\d+\]/g, "["+$("#_vem_010_display_dates_meta_box .vem-one-occurrence").length+"]");
	        });

	        // Selects don't retain their updates values when cloned, so set them
	        $(".vem-venue", newItem).val($(".vem-venue", lastItem).val());
	        $(".vem-dates-meridian-from", newItem).val($(".vem-dates-meridian-from", lastItem).val());
	        $(".vem-dates-meridian-to", newItem).val($(".vem-dates-meridian-to", lastItem).val());
	        $(".vem-dates-gsd-status", newItem).val($(".vem-dates-gsd-status", lastItem).val());
	        $(".vem-dates-gsd-meridian-from", newItem).val($(".vem-dates-gsd-meridian-from", lastItem).val());

	        // Apply the datepicker functionality
	        $("input.date-picker", newItem)
	            .attr("id",null)
	            .removeClass("hasDatepicker")
	            .removeData("datepicker")
	            .unbind()
	            .datepicker({dateFormat: 'mm/dd/yy'});

	        // Remove the hidden ID input, if any, and add the cloned item to the display
	        $("#_vem_010_display_dates_meta_box .inside .vem-action-bar").before(newItem);
	        newItem.show().find("input[type=hidden]").remove();
	    });

	    $("#_vem_010_display_dates_meta_box").on("click", ".vem-remove", function(e) {
	        e.preventDefault();
	        if (confirm("Are you sure you want to delete this occurrence?")) {
	            var item = $(this).closest(".vem-one-occurrence");
	            var id = $("input[type=hidden]", item).val();
	            if (typeof(id) !== 'undefined') {
	                $('<input type="hidden" name="vem_dates_remove[]" value="' + id + '">').appendTo($("#_vem_010_display_dates_meta_box"));
	            }
	            item.fadeOut("normal", function() { item.remove() });
	        }
	    });

		$("#_vem_010_display_dates_meta_box").on("change", ".vem-dates-gsd-status", function () { 
			var newStatus = $(this).val().toLowerCase();
			$(this).parents('.vem-form-gsd').find('.vem-dates-gsd-detail').hide().filter('.vem-dates-gsd-' + newStatus).show();
		});
	    
	    $("#_vem_010_display_dates_meta_box").on("change", ".vem-venue", function() { updateHeaders(); });
	    $("#_vem_010_display_dates_meta_box").on("change", ".vem-dates-date", function() { updateHeaders(); });
	    $("#_vem_010_display_dates_meta_box").on("change", ".vem-dates-meridian-from", function() { updateHeaders(); });
	    $("#_vem_010_display_dates_meta_box").on("change", ".vem-dates-start_time", function() { updateHeaders(); });

	    function updateHeaders() {
	        $(".vem-one-occurrence").each(function() {
	            var venue = $(this).find(".vem-venue option:selected").text();
	            var theDate = new Date($(this).find(".vem-dates-date").val());
	            var theTime = $(this).find(".vem-dates-start_time").val();
	            var theMeridian = $(this).find(".vem-dates-meridian-from option:selected").text();

	            if (theDate instanceof Date && isFinite(theDate) && theTime.length > 0) {
	                var display = theDate.toISOString().slice(0,10)+', '+theTime+theMeridian.toLowerCase();
	            } else {
	                var display = '[No time set]';
	            }
	            display += ' at '+venue;
	            $(".vem-header-bar", this).text(display);
	        });
	    }

		createFieldManager($('#venture-event-system_event-layout-fields'), vemEventSettingsFields.indexPageFields, 'event', 'page');
		createFieldManager($('#venture-event-system_event-layout-occurrence-fields'), vemEventSettingsFields.indexOccurrenceFields, 'event', 'occurrence');

	});

} (window.ventureEvents = window.ventureEvents || {}, jQuery));

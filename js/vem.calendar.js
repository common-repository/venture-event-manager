function getCalendarStorageKey(calendarId, eventId) {
	if (eventId && eventId != "0") {
		return 'calendar-'+calendarId+'-'+eventId+'-last-viewed';
	} else {
		return 'calendar-'+calendarId+'-last-viewed';
	}
}

function resetHeights(calendar) {
	for (i = 0; i < 5; i++) {
		var maxHeight = 0;
		jQuery('.week-' + i + ':visible', calendar).each(function () {
			maxHeight = Math.max(maxHeight, jQuery(this).height());
		});
		jQuery('.week-' + i + ':visible', calendar).height(maxHeight);
	}
}

function getEvents(calendar, m) {
	jQuery.post(
		calendar.attr("vem-admin-url")+"?r="+(new Date().getTime()),
		{ 
			action: 'vem_get_events', 
			id: calendar.attr("vem-calendar-id"),
			event: calendar.attr("vem-event"),
			start: moment(m).startOf('month').format('X'),
			end: moment(m).endOf('month').format('X'),
			moment: jQuery('.vem-header', calendar).attr('vem-moment'),
			futureOnly: (calendar.attr('vem-future-only') == 'yes')
		},
		function(data) {
			data = JSON.parse(data);

			// If the response doesn't correspond to the calendar's current vem-moment,
			// then the user switched months before the data could be displayed, so 
			// just throw this result away
			var vemMoment = jQuery('.vem-header', calendar).attr('vem-moment');
			if (vemMoment != data.moment) return;

			retrieved = data.events;
			timezone = data.timezone;

			calendarId = calendar.attr("vem-calendar-unique");
			var showTickets = (calendar.attr("vem-show-tickets") == "yes");

			var events = {};
			jQuery.each(retrieved,function() {
				var m = moment(this.start,'X').tz(timezone);
				var m2 = moment(this.end,'X').tz(timezone);
				var id = '.day-'+m.format('D');

				events[this.id] = this;
				var display = '<div class="vem-single-event '+this.categoryClasses+' vem-lightness-'+this.colors.lightness+'" vem-url="'+this.url+'" vem-single-event-id="'+this.id+'" style="background-color:'+this.colors.back+'; color:'+this.colors.fore+';" title="'+this.category+'">';
				display += '<div class="vem-single-event-time">';
				if (this.showend) {
					display += m.format('h:mma')+'-'+m2.format('h:mma');
				} else {
					display += m.format('h:mma');
				}
				display += '</div>';
				if (this.pretitle) display += '<div class="vem-single-event-pretitle">'+this.pretitle+'</div>';
				display += '<div class="vem-single-event-title">'+this.title+'</div>';
				if (this.posttitle) display += '<div class="vem-single-event-posttitle">'+this.posttitle+'</div>';
				if (!!this.occurrence_categories) display += this.occurrence_categories;
				if (showTickets) {
					if (!!this.tickets) {
						display += '<div class="vem-single-event-tickets">';
						display += '<a href="'+this.tickets+'" class="vem-single-event-tickets">'+(this.buytext || 'Tickets')+'</a>';
						display += '</div>';
					}
					if (!!this.tickets2) {
						display += '<div class="vem-single-event-tickets">';
						display += '<a href="'+this.tickets2+'" class="vem-single-event-tickets">'+(this.buytext2 || 'Tickets')+'</a>';
						display += '</div>';
					}
				}
				display += '</div>';

				// Allow other plugins to modify the display
				var event = this;
				jQuery.each(VentureExtensionScripts.scripts, function(index, script) {
					display = window[script]['renderDisplay'](calendarId, m, m2, event, showTickets, display);
				});

				jQuery(id,calendar).children('.vem-day-content')
					.append(display);
			});
			calendars[calendarId].events = events;

			resetHeights(calendar);

			jQuery(".vem-day", calendar).removeClass("has-events");
			jQuery(".vem-single-event", calendar).parents(".vem-day").addClass("has-events");
			jQuery('.vem-days', calendar).removeClass('loading');
			if (Object.keys(events).length == 0) {
				jQuery('.vem-days', calendar).addClass('no-events');
			}

			var filter = jQuery('.vem-occurrence-category-filters[calendar=' + calendarId + ']');
			var active = jQuery(".one-date-term-filter.active", filter);

			if (active.length) {
				var terms = [];
				active.each(function (index) { terms.push('.one-date-term.term-id-'+jQuery(this).attr("term")); });
				jQuery(".vem-single-event", calendar).addClass('filtered');
				jQuery(terms.join(), calendar).closest(".vem-single-event").removeClass('filtered');
			} else {
				jQuery(".vem-single-event", calendar).removeClass('filtered');
			}
			jQuery(".vem-day.has-events", calendar).removeClass('has-filtered-events').removeClass('has-unfiltered-events');
			jQuery(".vem-day.has-events:has(.vem-single-event.filtered)", calendar).addClass('has-filtered-events');
			jQuery(".vem-day.has-events:has(.vem-single-event:not(.filtered))", calendar).addClass('has-unfiltered-events');

		}
	);
}

function setStackMode() {
	jQuery(".vem-calendar").each(function() {
		var calendar = jQuery(this);
		if (calendar.width() < calendar.attr("vem-calendar-stack")) {
			calendar.addClass('stacking');
		} else {
			calendar.removeClass('stacking');
		}
	});
}

function getDialogContent(calendarId, event) {
	var output = '';
	var margin = '';

	if (!!event.thumb) {
		output += '<img class="vem-single-event-thumbnail" src="'+event.thumb+'" />';
		margin = ' style="margin-left:80px;"';
	}
	output += '<div class="vem-single-event-details"'+margin+'>';
	if(event.pretitle) {
		output += '<div class="vem-single-event-pretitle">'+event.pretitle+'</div>';
	}
	output += '<div class="vem-single-event-title">'+event.title+'</div>';
	if(event.posttitle) {
		output += '<div class="vem-single-event-posttitle">'+event.posttitle+'</div>';
	}
	if (!!event.occurrence_categories) output += event.occurrence_categories;
	if (event.showend) {
		output += '<div class="vem-single-event-start">'+moment(event.start,'X').tz(timezone).format('dddd h:mma')+'-'+moment(event.end,'X').tz(timezone).format('h:mma')+'</div>';
	} else {
		output += '<div class="vem-single-event-start">'+moment(event.start,'X').tz(timezone).format('dddd h:mma')+'</div>';
	}
	output += '<div class="vem-single-event-url">';
	output += '<a href="'+event.url+'" class="vem-single-event-url">Event Details</a>';
	output += '</div>';
	if (!!event.tickets) {
		output += '<div class="vem-single-event-tickets">';
		output += '<a href="'+event.tickets+'" class="vem-single-event-tickets">'+(event.buytext || 'Tickets')+'</a>';
		if (!!event.prices) {
			output += ' <span class="vem-single-event-prices">'+event.prices+'</span>';
		}
		output += '</div>';
	}
	if (!!event.tickets2) {
		output += '<div class="vem-single-event-tickets">';
		output += '<a href="'+event.tickets2+'" class="vem-single-event-tickets">'+(event.buytext2 || 'Tickets')+'</a>';
		if (!!event.prices2) {
			output += ' <span class="vem-single-event-prices">'+event.prices2+'</span>';
		}
		output += '</div>';
	}
	output += '</div>';

	// Allow other plugins to modify the dialog content
	jQuery.each(VentureExtensionScripts.scripts, function(index, script) {
		output = window[script]['renderDialog'](calendarId, event, timezone, output);
	});
	
	return output;
}

function renderDialogContent(calendarId, event,dialog) {
	var output = getDialogContent(calendarId, event);
	dialog.html(output);
}

function renderCalendar(calendar,m) {

	var daysOfWeek = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
	var output = '';

	output += '<div class="vem-header" vem-moment="'+m.format('X')+'">';
		output += '<div class="vem-header-nav-next">';
		output += '<a href="#"><i class="fa fa-chevron-right"></i></a>';
		output += '</div>';
		output += '<div class="vem-header-nav-prev">';
		output += '<a href="#"><i class="fa fa-chevron-left"></i></a>';
		output += '</div>';
		output += '<div class="vem-header-month">';
		output += m.format("MMMM YYYY");
		output += '</div>';
	output += '</div>';

	output += '<div class="vem-daylabels">';
		for (i=0; i<7; i++) {
			output += '<div class="vem-daylabel">';
			output += daysOfWeek[i];
			output += '</div>';
		}
	output += '</div>';

	var firstDayOfMonth = moment(m).startOf('month').format('d');
	var daysInMonth = m.daysInMonth();
	var isCurrentMonth = (m.format('MMYYYYY') == moment().format('MMYYYYY'));
	var activeDay = moment(m).startOf('month');
	var week = 0;
	var noEventsMessage = calendar.attr('vem-no-events-message');
	output += '<div class="vem-days loading">';
	output += '<div class="vem-no-events" style="display:block;">'+noEventsMessage+'</div>';
	if (firstDayOfMonth > 0) {
		// Render some previous month's days
		for (i=0; i<firstDayOfMonth; i++) {
			output += '<div class="vem-day vem-not-current-month week-0"></div>';
		}
	}

	for (i=1; i<=daysInMonth; i++) {
		var c = isCurrentMonth ? ((i==m.format('D')) ? ' current-date' : '') : '';
		if (activeDay.format('d') == 0 && activeDay.format('D') != 1) week++;
		var w = ' week-' + week; 
		// Render days
		output += '<div class="vem-day vem-current-month day-'+i+c+w+'"><div class="vem-day-number as-number">'+i+'</div><div class="vem-day-number as-text">'+activeDay.format('dddd')+', '+i+'</div><div class="vem-day-content"></div><div class="vem-calendar-end"></div></div>';
		activeDay.add(1,'days');
	}

	var extraDays = 7 - ((parseInt(firstDayOfMonth,10)+parseInt(daysInMonth,10)) % 7)
	if (extraDays < 7) {
		// Render additional days to make full rows
		for (i=0; i<extraDays; i++) {
			output += '<div class="vem-day vem-not-current-month'+w+'"></div>';
		}
	}
	output += '</div>';
	output += '<div class="vem-detail-panel" style="display:none;">Panel</div>';
	output += '<div class="vem-calendar-end'+w+'"></div>';

	calendar.html(output);
	jQuery('.vem-days', calendar).prepend(jQuery('.venture-spinner').first().clone().show());
	getEvents(calendar,m);
}

var calendars = {};
var dialogs = {};
var timezone = '';

jQuery(document).ready(function() {
	var m = moment();

	setStackMode();

	jQuery(".vem-calendar").each(function() {
		var calendar = jQuery(this);
		var uniqueId = calendar.attr('vem-calendar-unique');
		var calendarId = calendar.attr('vem-calendar-id');
		var eventId = calendar.attr('vem-event');
		calendars[uniqueId] = calendar;
		var start = jQuery(this).attr('vem-start');
		if (start != 'default') {
			m = moment(start, 'MM/YYYY');
		}

		var stored = sessionStorage.getItem(getCalendarStorageKey(calendarId, eventId));
		if (stored && stored != 'Invalid date') {
			m = moment.unix(stored);
		}
		sessionStorage.setItem(getCalendarStorageKey(calendarId, eventId), m.format('X'));
		renderCalendar(calendar,m);
	})
	.on('click','.vem-header-nav-prev a',function(event) {
		var ts = jQuery(this).parents('.vem-header').attr('vem-moment');
		var m = moment(ts,'X').subtract(1,'months');
		var calendar = jQuery(this).parents('.vem-calendar');
		var calendarId = calendar.attr('vem-calendar-id');
		var eventId = calendar.attr('vem-event');
		sessionStorage.setItem(getCalendarStorageKey(calendarId, eventId), m.format('X'));
		renderCalendar(calendar,m);
		event.stopPropagation();
		return false;
	})
	.on('click','.vem-header-nav-next a',function(event) {
		var ts = jQuery(this).parents('.vem-header').attr('vem-moment');
		var m = moment(ts,'X').add(1,'months');
		var calendar = jQuery(this).parents('.vem-calendar');
		var calendarId = calendar.attr('vem-calendar-id');
		var eventId = calendar.attr('vem-event');
		sessionStorage.setItem(getCalendarStorageKey(calendarId, eventId), m.format('X'));
		renderCalendar(calendar,m);
		event.stopPropagation();
		return false;
	}).on('click','.vem-single-event',function() {
		calendar = jQuery(this).parents(".vem-calendar");
		calendarId = calendar.attr("vem-calendar-unique");
		eventDateId = jQuery(this).attr("vem-single-event-id");

		switch (calendar.attr("vem-click-action")) {
			case 'popup':
				renderDialogContent(calendarId, calendars[calendarId].events[eventDateId],dialogs[calendarId]);

				var backColor = calendars[calendarId].events[eventDateId].colors.back;
				if (backColor == "transparent") backColor = "#cfcfcf";

				var foreColor = calendars[calendarId].events[eventDateId].colors.fore;

				dialogs[calendarId].parent()
				.attr("data-back-color", backColor)
				.attr("data-fore-color", backColor)
				.css("border-color",backColor)
					.children(".ui-dialog-titlebar")
					.css("background-color",backColor)
					.css("border-color",backColor)
					.css("color",foreColor);

				dialogs[calendarId]
					.dialog( "option", "title", jQuery(this).attr('title') )
					.dialog("open");

				break;

			case 'panel':
				// Do nothing, we only want to respond to day clicks
				break;

			default:
				location.href = jQuery(this).attr("vem-url");
		}
	});

	jQuery('.vem-calendar[vem-click-action="panel"]').on('click', '.vem-day.has-events', function() {
		var calendar = jQuery(this).parents(".vem-calendar");
		var day = this;

		jQuery('.vem-day', calendar).removeClass('panel-open');
		jQuery(day).addClass('panel-open');

		jQuery('.vem-detail-panel', calendar)
			.hide(400)
			.html("")
			.addClass('vem-single-event-dialog');
			
		jQuery('.vem-single-event', day).each(function() {
			var calendarId = calendar.attr("vem-calendar-unique");
			var eventDateId = jQuery(this).attr('vem-single-event-id');
			jQuery('.vem-detail-panel', calendar)
				.append('<div class="vem-one-occurrence">'
					+getDialogContent(calendarId, calendars[calendarId].events[eventDateId])
					+'</div>');
		});
		jQuery('.vem-detail-panel', calendar).show(400)
	});

	jQuery(".vem-single-event-dialog").each(function() {
		calendarId = jQuery(this).attr('vem-calendar-id');
		var dialogOptions = {
			autoOpen:false,
			width:310,
			resizable:false,
			dialogClass: "vem-calendar-dialog dialog-"+calendarId 
		};

		// Allow other plugins to modify the dialog options
		jQuery.each(VentureExtensionScripts.scripts, function(index, script) {
			dialogOptions = window[script]['getDialogOptions'](calendarId, dialogOptions);
		});

		dialogs[calendarId] = jQuery(this).dialog(dialogOptions);
	});

	jQuery(window).resize(function() {
		setStackMode();
	});

	jQuery( ".shortcode-tabs" ).on( "tabsactivate", function( event, ui ) {
		var uniqueId = jQuery(".vem-calendar",ui.newPanel).attr('vem-calendar-unique');
		var c = calendars[uniqueId];
		if (typeof(c) == 'undefined') return;
		var m = jQuery(".vem-header",c).attr("vem-moment");
		renderCalendar(c,moment(m,'X'));
	});

	jQuery(".date-term-filter-all").click(function (e) {
		var calendar = jQuery("div[vem-calendar-unique=" + jQuery(this).closest(".vem-occurrence-category-filters").attr("calendar") + "]");
		jQuery(this).addClass("active");
		jQuery(".one-date-term-filter").removeClass("active");
		jQuery(".vem-single-event", calendar).removeClass('filtered');
		jQuery(".vem-day.has-events", calendar).removeClass('has-filtered-events').addClass('has-unfiltered-events');
		resetHeights(calendar);
	});

	jQuery(".one-date-term-filter").click(function (e) {
		var filter = jQuery(this).closest('.vem-occurrence-category-filters');
		var calendar = jQuery("div[vem-calendar-unique=" + jQuery(this).closest(".vem-occurrence-category-filters").attr("calendar") + "]");
		jQuery(this).toggleClass("active");
		var active = jQuery(".one-date-term-filter.active", filter);
		if (active.length) {
			var terms = [];
			active.each(function (index) { terms.push('.one-date-term.term-id-'+jQuery(this).attr("term")); });
			jQuery(".date-term-filter-all", filter).removeClass("active");
			jQuery(".vem-single-event", calendar).addClass('filtered');
			jQuery(terms.join(), calendar).closest(".vem-single-event").removeClass('filtered');
		} else {
			jQuery(".date-term-filter-all", filter).addClass("active");
			jQuery(".vem-single-event", calendar).removeClass('filtered');
		}
		jQuery(".vem-day.has-events", calendar).removeClass('has-filtered-events').removeClass('has-unfiltered-events');
		jQuery(".vem-day.has-events:has(.vem-single-event.filtered)", calendar).addClass('has-filtered-events');
		jQuery(".vem-day.has-events:has(.vem-single-event:not(.filtered))", calendar).addClass('has-unfiltered-events');
		resetHeights(calendar);
	});
});


(function(ventureEventCalendars, $, undefined) {

	$(document).ready(function() {
		$(".event-calendar-id").text($("#post_ID").val()).parent('div').unwrap('td').unwrap('tr').unwrap('tbody').unwrap('table').show();
		$(".shortcode-wrapper").each(function(index) { $(this).text($(this).text()) });
	});

} (window.ventureEventCalendars = window.ventureEventCalendars || {}, jQuery));
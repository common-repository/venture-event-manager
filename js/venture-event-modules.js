(function(ventureEventModules, $, undefined) {

	$(document).ready(function() {
		$(".event-module-id").text($("#post_ID").val()).parent('div').unwrap('td').unwrap('tr').unwrap('tbody').unwrap('table').show();
		$(".shortcode-wrapper").each(function(index) { $(this).text($(this).text()) });
	});

} (window.ventureEventModules = window.ventureEventModules || {}, jQuery));
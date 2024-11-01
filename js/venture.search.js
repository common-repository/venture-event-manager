(function(ventureSearch, $, undefined) {

	$(document).ready(function() {
		var useNative = (ventureSearchSettings.useNativeDatepicker == '1');

		var defaultStart = ((ventureSearchSettings.defaultStartDate < 0) ? ventureSearchSettings.defaultStartDate : '+'+ventureSearchSettings.defaultStartDate) + 'd';
		var defaultEnd = ((ventureSearchSettings.defaultEndDate < 0) ? ventureSearchSettings.defaultEndDate : '+'+ventureSearchSettings.defaultEndDate) + 'd';

		if (useNative && /Mobi|Android/i.test(navigator.userAgent)) {
			// $(".vem-search .datepicker").attr("type", "date");
		} else {
			$(".vem-search .datepicker").attr("type", "text");
			$(".vem-search .datepicker.start").datepicker({
				dateFormat: 'mm/dd/yy',
				defaultDate: defaultStart
			});
			$(".vem-search .datepicker.end").datepicker({
				dateFormat: 'mm/dd/yy',
				defaultDate: defaultEnd
			});
		}
	});

} (window.ventureSearch = window.ventureSearch || {}, jQuery));
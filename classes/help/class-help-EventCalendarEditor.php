<?php

class VentureHelpEventCalendarEditor extends VentureHelpBase {

	public function setHelp($screen) {
		
		 $text = <<<BLOCK
<p>By default, the calendar goes from grid to stacked view automatically for browser screens smaller than 667px wide. You can change that threshold manually by using the stack=”450″ shortcode attribute (a tiny bit of info you add to the shortcode manually).</p>
<p>For example, if you want the calendar to appear in grid view for something like a standard sidebar width or smartphone in landscape mode, your shortcode would look like this: [vemcalendar id=”537″ stack=”349″]. Note, the id number should be the id for your calendar.</p>
BLOCK;
        $screen->add_help_tab( array(
            'id'      => 'vem-calendar-stacked',
            'title'   => 'Calendar Stacked View',
            'content' => $text
        ));

        $text = <<<BLOCK
<p>The calendar will always load the current month by default but there are instances where you may want it to load a different month. For example, if you have a calendar that displays an event with numerous occurrences set in the event’s single event page but it doesn’t begin until a point in the future, you can set it up to open on the month with the first scheduled occurrence.</p>
<p>All it takes is the following attribute: start=”MM/YYYY”. You’ll need to use the two digit month and four digit year format and a complete shortcode would look like this: [vemcalendar id=”537″ start=”07/2017″]. In that example, the calendar will always load July, 2017 as the first display month.</p>
BLOCK;
        $screen->add_help_tab( array(
            'id'      => 'vem-calendar-manual-month',
            'title'   => 'Manually set initial display month',
            'content' => $text
        ));

    }

}
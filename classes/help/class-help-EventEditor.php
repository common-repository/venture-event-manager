<?php

class VentureHelpEventEditor extends VentureHelpBase {

	public function setHelp($screen) {

        $text = <<<BLOCK
<p><strong>Ticket pricing options</strong></p>
<ul>
<li>Price Range: enter lowest price in first field and highest in second field; example $[15.00] - $[200.00]</li>
<li>Tickets From: enter lowest ticket price in first field and leave second field blank; example $[15.00] - $[empty]</li>
<li>Single Ticket Price: Enter the single ticket price into both fields; example $[15.00] - $[15.00]</li>
<li>Free events: enter $0 into both fields; example $[0] - $[0]</li>
<li>Free and priced tickets: enter $0 into the first field and a price value in the second; example $[0] - $[15] shows as "Free" to "$15"</li>
<li>If empty, ticket prices will not show on frontend.</li>
<li>Decimal points may be used but are not necessary.</li>
</ul>
BLOCK;
        $screen->add_help_tab( array(
            'id'      => 'vem-ticketing',
            'title'   => 'Ticket Price Settings',
            'content' => $text
        ));

        $text = <<<BLOCK
<p>Featured images can appear in the following locations using the image sizes you set via the WordPress <a href="/wp-admin/options-media.php">Settings &gt; Media</a> admin panel:</p>
<ul>
<li>Single Event Pages: thumbnail, medium, large, and full</li>
<li>Event Lists (both default and per list layout override) : thumbnail, medium, large, and full</li>
<li>Event Calendars: thumbnail (Pro users can upload a custom calendar image) </li>
<li>Event Modules (Pro Only): full</li>
</ul>
<p>Some themes may automatically insert post Featured Images at the top of the single event page. If so, please consult your theme documentation on available display options. </p>

BLOCK;
        $screen->add_help_tab( array(
            'id'      => 'vem-featured-images',
            'title'   => 'Featured Images',
            'content' => $text
        ));
	}

}
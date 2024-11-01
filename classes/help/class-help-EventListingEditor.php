<?php

class VentureHelpEventListingEditor extends VentureHelpBase {

	public function setHelp($screen) {
		 
		$text = <<<BLOCK
<p>Using List in widgets to create upcoming or recently added widgets is easy. Just paste the Listing Shortcode into a Custom HTML or Text widget. We recommend tweaking some of the display styles so as to better accomodate available space inside most sidebar and footer widget areas. You can find useful CSS snippets on how to make those adjustments at the Documentation <a href="https://docs.ventureeventmanager.com/">site</a>.</p>
BLOCK;
        $screen->add_help_tab( array(
            'id'      => 'vem-list-widgets',
            'title'   => 'Using Lists In Widgets',
            'content' => $text
        ));

    }

}
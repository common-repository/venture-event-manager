<?php

class VentureHelpEventModuleEditor extends VentureHelpBase {

	public function setHelp($screen) {

 $text = <<<BLOCK
<p>When using the three event layout with text in the first column, limit the amount of copy so it matches the same visual baselaine as the event content.</p>
BLOCK;

        $screen->add_help_tab( array(
            'id'      => 'vem-sample',  // Change vem-sample to a value unique in this file
            'title'   => 'Design Tip', // Change Sample Tab Text to the text you want on tab
            'content' => $text 
        ));
		 $text = <<<BLOCK
<p>By default, VEM will display an event's Featured Image at 100% available column width and the original aspect ratio. As such, we recommend using the same aspect ratio for all event Featured Images. Alternatively, you can remove the image entirely using some custom CSS available for you at the VEM Module documentation <a href="https://docs.ventureeventmanager.com/event-modules/">article</a>.</p>
BLOCK;

        $screen->add_help_tab( array(
            'id'      => 'vem-replace-this',
            'title'   => 'Image size', 
            'content' => $text
        ));
    }

}
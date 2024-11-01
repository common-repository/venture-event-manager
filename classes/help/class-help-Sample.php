<?php

/*
This is a sample, including instructions, for what a help file should look like.

The VentureHelp class (in ../class-help.php) figures out what screen we're on and
sets a value, $topic. A real one is "AllEventCalendars" and for this sample, it would
be "Sample".

The filename should be class-help-Topic.php, where Topic is the value of $topic.

The class name below on line 16 should be VentureHelpTopic, where Topic is
again the value of $topic.
*/

class VentureHelpSample extends VentureHelpBase {

	public function setHelp($screen) {
	// Do not edit anything above this line except to change the class name
	// when creating an entirely new help file.

		// This is a single help tab
		// Edit only the content that is shown below on line 26.
		// You can use as many lines as you want and it is just regular HTML.
        $text = <<<BLOCK
<p>This is the <strong>Help Text</strong> that will appear on the tab to the right.</p>
BLOCK;

        $screen->add_help_tab( array(
            'id'      => 'vem-sample',  // Change vem-sample to a value unique in this file
            'title'   => 'Sample Tab Text', // Change Sample Tab Text to the text you want on tab
            'content' => $text // Do not change this line, you set this above in the BLOCK section
        ));
        // End of the first tab

        // If you want a second tab, you repeat these things again
        // This one has no comments so you can use it for copy/paste, once per tab
        // Copy lines 39-47 and then edit in the three places where you see replace-this type text
        $text = <<<BLOCK
<p>Replace this help text with HTML.</p>
BLOCK;

        $screen->add_help_tab( array(
            'id'      => 'vem-replace-this',
            'title'   => 'Replace This', 
            'content' => $text
        ));
        // End of the second tab

	// Do not edit anything below this line
    }

}
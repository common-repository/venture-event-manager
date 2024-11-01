<?php

class VentureWhatWhenWhere extends WP_Widget {
	function __construct() {
		parent::__construct( 'who_what_where_widget', $name = 'Venture What/When/Where' );
	}

	function form($instance) {
		if ($instance) {
			$title = esc_attr( $instance[ 'title' ] );
			$include = array_key_exists('include',$instance) ? esc_attr( $instance[ 'include' ] ) : 'all';
			$dateformat = array_key_exists('dateformat',$instance) ? esc_attr( $instance[ 'dateformat' ] ) : 'F j, Y';
		} else {
			$title = __( 'New title', 'text_domain' );
			$include = 'all';
			$dateformat = 'F j, Y';
		}
		echo "<p>";
		echo	'<label for="' . $this->get_field_id('title') . '">' . __('Title:') . '</label>';
		echo 	'<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
		echo "</p>";
		echo "<p>";
		echo	'<label for="' . $this->get_field_id('include') . '">' . __('Include:') . '</label>';
		echo 	'<select id="'.$this->get_field_id('include').'" name="'.$this->get_field_name( 'include' ).'" class="widefat" style="width:100%;">';
		$s = ($include == 'all') ? ' selected="selected"' : '';
		echo 		'<option value="all"'.$s.'>All Occurrences</option>';
		$s = ($include == 'future') ? ' selected="selected"' : '';
		echo 		'<option value="future"'.$s.'>Future Occurrences Only</option>';
		echo 	"</select>";
		echo "</p>";
		echo "<p>";
		echo	'<label for="' . $this->get_field_id('dateformat') . '">' . __('Date Format:') . '</label>';
		echo 	'<input class="widefat" id="' . $this->get_field_id('dateformat') . '" name="' . $this->get_field_name('dateformat') . '" type="text" value="' . $dateformat . '" />';
		echo "</p>";
		echo "<p>";
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['include'] = strip_tags($new_instance['include']);
		$instance['dateformat'] = strip_tags($new_instance['dateformat']);
		return $instance;
	}

	function widget($args, $instance) {
		
		global $post;
		global $ventureEventSystem;

		if (empty($post) || empty($post->ID) || $post->post_type != 'event') return '';
		
		extract($args);
		$title = apply_filters( 'widget_title', $instance['title'] );

		$vem3 = new VentureEventManager3();
		$vem3->setContext('www-widget');
		if (array_key_exists('include',$instance)) {
	        switch ($instance['include']) {
	            case 'future':
	                $vem3->setOccurrenceRange($vem3::UPCOMING);
	                break;

	            case 'all':
	            default:
	                $vem3->setOccurrenceRange($vem3::ALL);
	                break;

	        }
		}
		
		if (array_key_exists('dateformat',$instance)) {
			$dateFormat = $instance['dateformat'];
		} else {
			$dateFormat = 'F j, Y';
		}
		$vem3->setDatetimeFormatOverride($dateFormat);
		$vem3->setEventId($post->ID);
		$vem3->retrieveDates();

		$dates = $vem3->getOccurrences();
		if (sizeof($dates) == 0) return '';

		echo $before_widget;
		if ($title) {
			echo $before_title . $title . $after_title;
		}
		
		echo '<div class="vem-what-when-where">';
		echo '<div id="event-what" class="event-what-when-where">';
		echo 	'<h2>' . __("What") . '</h2>';
		echo 	'<div class="widget-data-content">' . $post->post_title . '</div>';
		echo '</div>';
		echo '<div id="event-when" class="event-what-when-where">';
		echo 	'<h2>' . __("When") . '</h2>';

		$venues = [];
		foreach ($dates as $o) {

	        $meta = [
				'vem_display_end_times' => $o['display_end_times'],
				'vem_max_occurrences' => $o['max_occurrences'],
				'vem_max_message' => $o['max_message'],
				'vem_homepage_title' => $o['homepage_title'],
				'vem_include_in_archives' => $o['include_in_archives'],
				'vem_pretitle' => $o['pretitle'],
				'vem_posttitle' => $o['posttitle'],
				'vem_event_details' => $o['details'],
				'vem_event_media' => $o['media'],
				'vem_fields_set_one' => $o['fields_set_one'],
				'vem_fields_set_two' => $o['fields_set_two'],
				'vem_fields_set_three' => $o['fields_set_three'],
				'vem_index_thumb' => apply_filters('vem-get-post-thumbnail-url', get_the_post_thumbnail_url($o['event_id'], 'full'), $o['event_id'], 'full'),
				'vem_calendar_thumb' => apply_filters('vem-get-calendar-thumbnail-url', get_the_post_thumbnail_url($o['event_id'], 'thumbnail'), $o['event_id'], 'thumbnail'),
	        ];

			echo '<div class="occurrence-wrapper">';
			echo $vem3->getOccurrenceChunk('vem_dates_date_time', $o, $post, $meta);
			echo '<div class="ticket-info">';
			echo $vem3->getOccurrenceChunk('vem_dates_ticket_url', $o, $post, $meta);
			echo $vem3->getOccurrenceChunk('vem_dates_ticket_price', $o, $post, $meta);
			echo '</div>';
			echo '<div class="ticket-info">';
			echo $vem3->getOccurrenceChunk('vem_dates_ticket2_url', $o, $post, $meta);
			echo $vem3->getOccurrenceChunk('vem_dates_ticket2_price', $o, $post, $meta);
			echo '</div>';
			echo $vem3->getOccurrenceChunk('vem_dates_note', $o, $post, $meta);
			echo '</div>';

			$venues[$o['venue_id']] = [
				'id' => $o['venue_id'],
				'name' => $o['venue'],
				'address' => $o['venue_address'],
				'city' => $o['venue_city'],
				'state' => $o['venue_state'],
				'zip' => $o['venue_zip'],
				'country' => $o['venue_country']
			];
		}

		echo '</div>';
		echo '<div id="event-where" class="event-what-when-where">';
		echo 	'<h2>' . __("Where") . '</h2>';
		foreach ($venues as $venue) {
			echo '<div class="vem-single-event-date-venue-address">';
			echo $vem3->formatVenueAddress($venue, true);
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
			
		
		echo $after_widget;
	}
}

class VentureWhatWhenWhereInitializer {

	public function __construct() {
		add_action('widgets_init', [$this, 'register']);
	}

   	public function register() {
        register_widget('VentureWhatWhenWhere');
    }
}
$vwwwi = new VentureWhatWhenWhereInitializer();

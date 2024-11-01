<?php

class VentureEventManager3 {

	const UPCOMING = 'upcoming';
	const RECENT = 'recent';
	const DATES = 'dates';
	const ALL = 'all';

	const STATUSES = [
		'EventScheduled' => [
			'class' => 'scheduled',
			'label' => 'Scheduled'
		],
		'EventPostponed' => [
			'class' => 'postponed',
			'label' => 'Postponed'
		],
		'EventRescheduled' => [
			'class' => 'rescheduled',
			'label' => 'Rescheduled'
		],
		'EventMovedOnline' => [
			'class' => 'moved-online',
			'label' => 'Moved Online'
		],
		'EventCancelled' => [
			'class' => 'cancelled',
			'label' => 'Cancelled'
		],
	];

	private $occurrences = [];
	private $currentOccurrence = 0;
	private $events = [];
	private $howRecent = 86400; // Ten days

	private $dateColumns = [];
	private $datesWhere = [];
	private $datesOrderby = 'occurrences.start_time';
	private $datesOrder = 'ASC';
	private $datesLimit = 0;
	private $datesOffset = 0;
	private $datetimeFormatOverride = [
		'datetime' => '',
		'date' => '',
		'time' => ''
	];

	private $eventsWhere = [];
	private $eventsOrderby = 'events.ID';
	private $eventsOrder = 'ASC';
	private $eventsLimit = 0;
	private $eventsOffset = 0;
	private $excludeEvents = [];

	private $taxonomyFilters = [];

	private $calendarId = 0;
	private $listingId = 0; 

	private $defaultMeta = [
		'vem_display_end_times' => 'default',
		'vem_max_occurrences' => 1000,
		'vem_max_message' => 'view all event dates',
		'vem_homepage_title' => '',
		'vem_pretitle' => '',
		'vem_posttitle' => '',
		'vem_event_transcript' => 0,
		'vem_event_details' => '',
		'vem_fields_set_one' => 'a:1:{i:0;a:2:{s:3:"key";s:0:"";s:5:"value";s:0:"";}}',
		'vem_fields_set_two' => 'a:1:{i:0;a:2:{s:3:"key";s:0:"";s:5:"value";s:0:"";}}',
		'vem_fields_set_three' => 'a:1:{i:0;a:2:{s:3:"key";s:0:"";s:5:"value";s:0:"";}}',
		'vem_index_thumb' => '',
		'vem_calendar_thumb' => ''
	];

	private $occurrenceRange = self::ALL;
	private $startTime = null;
	private $endTime = null;
	private $eventId = 0;
	private $archives = false;
	private $keyword = '';
	private $occurrenceId = 0;
	private $isGrouped = true;

	private $customChunks = [
		'use' => false,
		'page' => '',
		'date' => ''
	];

	private $toggleAfter = 0;
	private $toggleMessage = 'view more event dates';

	private $catColors = [];

	private $query = '';

	private $context = '';

	public function __construct() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$termTable = VEM_DATE_TERM_TABLE;
		$this->dateColumns = [
			'event_id' => 'events.ID',
			'event_name' => 'events.post_title',
			'event_name_alt' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_homepage_title' limit 1)",
			'event_excerpt' => 'events.post_excerpt',
			'occurrence_id' => 'occurrences.id',
			'venue_id' => 'occurrences.venue_ID',
			'venue' => "(select name from {$prefix}terms where term_id = occurrences.venue_ID)",
			'venue_address' => "(select meta_value from {$prefix}termmeta where term_id = occurrences.venue_ID and meta_key = 'address' limit 1)",
			'venue_city' => "(select meta_value from {$prefix}termmeta where term_id = occurrences.venue_ID and meta_key = 'city' limit 1)",
			'venue_state' => "(select meta_value from {$prefix}termmeta where term_id = occurrences.venue_ID and meta_key = 'state' limit 1)",
			'venue_zip' => "(select meta_value from {$prefix}termmeta where term_id = occurrences.venue_ID and meta_key = 'zip' limit 1)",
			'venue_country' => "(select meta_value from {$prefix}termmeta where term_id = occurrences.venue_ID and meta_key = 'country' limit 1)",
			'ticket_price_from' => 'occurrences.ticket_price_from',
			'ticket_price_to' => 'occurrences.ticket_price_to',
			'ticket2_price_from' => 'occurrences.ticket2_price_from',
			'ticket2_price_to' => 'occurrences.ticket2_price_to',
			'note' => 'occurrences.note',
			'start_time' => 'occurrences.start_time',
			'end_time' => 'occurrences.end_time',
			'create_time' => 'occurrences.create_time',
			'details' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_event_details' limit 1)",
			'pretitle' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_pretitle' limit 1)",
			'posttitle' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_posttitle' limit 1)",
			'event_transcript' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_event_transcript' limit 1)",
			'display_end_times' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_display_end_times' limit 1)",
			'max_occurrences' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_max_occurrences' limit 1)",
			'max_message' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_max_message' limit 1)",
			'homepage_title' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_homepage_title' limit 1)",
			'fields_set_one' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_fields_set_one' limit 1)",
			'fields_set_two' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_fields_set_two' limit 1)",
			'fields_set_three' => "(select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_fields_set_three' limit 1)",
			'buytext' => "occurrences.ticket_button_text",
			'buytext2' => "occurrences.ticket2_button_text",
			'tickets' => "occurrences.ticket_url",
			'tickets2' => "occurrences.ticket2_url",
			'gsd_status' => "occurrences.gsd_status",
			'gsd_url' => "occurrences.gsd_url",
			'gsd_previous_start_time' => "occurrences.gsd_previous_start_time",
			'url' => 'events.guid',
			'event_categories' => "(select group_concat(t.term_id) from {$prefix}term_relationships as tr join {$prefix}term_taxonomy as tt on tr.term_taxonomy_id = tt.term_taxonomy_id join {$prefix}terms as t on tt.term_id = t.term_id where tt.taxonomy = 'event_category' and tr.object_id = events.ID)",
			'occurrence_category_ids' => "(select group_concat(t.term_id) from {$prefix}{$termTable} as t where t.occurrence_id = occurrences.id)",
			'occurrence_categories' => "(select group_concat(concat(terms.term_id, '|||', terms.name, '|||', terms.slug, '|||',(select meta_value from {$prefix}termmeta as meta where meta.term_id = terms.term_id and meta_key = 'term-image' limit 1))) from {$prefix}vem_date_terms as t join {$prefix}terms as terms on t.term_id = terms.term_id where t.occurrence_id = occurrences.id)",
		];

	}

	private function getDatesWhere() {

		global $wpdb;

		$prefix = $wpdb->prefix;

		$where = $this->datesWhere;

		// The where clauses added in here are temporary because we might do multiple requests
		$where = array_merge($where, $this->getTaxonomyDatesWhere());
		$exclude = $this->getDatesExclusionWhere();
		if (!empty($exclude)) $where[] = $exclude;

		$statuses = apply_filters('vem_date_allowed_event_status', ['publish']);
		if (sizeof($statuses) > 0) {
			$statusString = "('".implode("','", $statuses)."')";
			$where[] = "events.post_status in $statusString";
		}
		if ($this->eventId > 0) {
			$where[] = 'events.ID = '.$this->eventId;
		}
		if ($this->occurrenceId > 0) {
			$where[] = 'occurrences.id = '.$this->occurrenceId;
		}

		switch($this->occurrenceRange) {
			case self::UPCOMING:
				$where[] = 'occurrences.start_time > '.current_time('timestamp', true);
				break;

			case self::RECENT:
				$howRecent = apply_filters('vem_define_recent', $this->howRecent, $this->context); // 10 days default
				$where[] = 'occurrences.create_time > '.(current_time('timestamp', true) - $howRecent);
				break;

			case self::DATES:
				$where[] = 'occurrences.start_time >= '.$this->startTime.' AND occurrences.start_time <= '.$this->endTime;
		}

		if ($this->archives) {

			$venture = VentureFramework::getInstance('venture-event-system');
			$globalDefault = $venture->getOption('event-archives-default');
	
			$where[] = <<<BLOCK
((select case 
when (select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_id and meta_key = 'vem_include_in_archives' limit 1) = 'true' then 'true' 
when (select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_id and meta_key = 'vem_include_in_archives' limit 1) = 'after' and (select max(end_time) from {$prefix}vem_event_dates as dates where post_id = occurrences.post_id) < unix_timestamp() then 'true' 
when (select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_id and meta_key = 'vem_include_in_archives' limit 1) = 'default' and '{$globalDefault}' = 'true' then 'true' 
when (select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_id and meta_key = 'vem_include_in_archives' limit 1) = 'default' and '{$globalDefault}' = 'after' and (select max(end_time) from {$prefix}vem_event_dates as dates where post_id = occurrences.post_id) < unix_timestamp() then 'true' 
else 'false' end as result)) = 'true'
BLOCK;
		}

		if ($this->keyword != '') {

			$keywordColumns = [
				"events.post_title",
				"ifnull(events.post_excerpt,'')",
				"ifnull((select meta_value from {$prefix}postmeta as meta where post_id = occurrences.post_ID and meta_key = 'vem_event_details' limit 1), '')"
			];

			$keywordColumns = apply_filters('vem_occurrence_keyword_search_fields', $keywordColumns, $this);
			$concat = implode(", ' ', ", $keywordColumns);
			$where[] = "(concat(".$concat.")) like '%{$this->keyword}%'";
		}

		$where = apply_filters('vem_occurrence_where', $where, $this->archives, $this->context);
		return $where;
	}

	private function getDatesQuery($count=false) {
		global $wpdb;

		$prefix = $wpdb->prefix;

		if ($count) {
			$query = 'select count(*) as cnt';
		} else {
			$query = 'select 1';

			// Allow others to adjust the default columns
			$this->dateColumns = apply_filters('vem_occurrence_columns', $this->dateColumns, $this->context);

			foreach ($this->dateColumns as $alias => $original) {
				$query .= ', '.$original.' as '.$alias;
			}
		}

		$query .= " from {$prefix}vem_event_dates as occurrences";
		$query .= " join {$prefix}posts as events on occurrences.post_ID = events.ID";

		$where = $this->getDatesWhere();
		if (sizeof($where) > 0) {
			$query .= ' where '.implode(' and ', $where);
		}

		if (!$count) {
			$query .= ' order by '.$this->datesOrderby;
			$query .= ' '.$this->datesOrder;

			if ($this->datesLimit > 0) {
				$query .= ' limit '.$this->datesLimit;
			}

			if ($this->datesOffset > 0) {
				$query .= ' offset '.$this->datesOffset;
			}
		}

		return $query;
	}

	private function getEventsWhere() {

		global $wpdb;
		$prefix = $wpdb->prefix;
		$where = $this->eventsWhere;

		// Clauses added here are temporary
		$where = array_merge($where, $this->getTaxonomyEventsWhere());
		$where[] = "events.post_type = 'event'";
		$exclude = $this->getEventExclusionWhere();
		if (!empty($exclude)) $where[] = $exclude;

		$statuses = apply_filters('vem_event_allowed_event_status', ['publish']);
		if (sizeof($statuses) > 0) {
			$statusString = "('".implode("','", $statuses)."')";
			$where[] = "events.post_status in $statusString";
		}
		$where[] = "events.post_status = 'publish'";

		switch($this->occurrenceRange) {
			case self::UPCOMING:
				$where[] = "exists (select * from {$prefix}vem_event_dates as occurrences where occurrences.post_id = events.ID and occurrences.start_time > ".current_time('timestamp', true).')';
				break;

			case self::RECENT:
				$howRecent = apply_filters('vem_define_recent', $this->howRecent, $this->context); // 10 days default
				$where[] = "exists (select * from {$prefix}vem_event_dates as occurrences where occurrences.post_id = events.ID and occurrences.create_time > ".(current_time('timestamp', true) - $howRecent).')';
				break;

			case self::DATES:
				$where[] = "exists (select * from {$prefix}vem_event_dates as occurrences where occurrences.post_id = events.ID and occurrences.start_time >= ".$this->startTime." AND occurrences.start_time <= ".$this->endTime.')';
		}

		if ($this->archives) {

			$venture = VentureFramework::getInstance('venture-event-system');
			$globalDefault = $venture->getOption('event-archives-default');
				
			$where[] = <<<BLOCK
(select case 
when (select meta_value from {$prefix}postmeta as meta where post_id = events.ID and meta_key = 'vem_include_in_archives' limit 1) = 'true' then 'true' 
when (select meta_value from {$prefix}postmeta as meta where post_id = events.ID and meta_key = 'vem_include_in_archives' limit 1) = 'after' and (select max(end_time) from {$prefix}vem_event_dates as dates where post_id = events.ID) < unix_timestamp() then 'true' 
when (select meta_value from {$prefix}postmeta as meta where post_id = events.ID and meta_key = 'vem_include_in_archives' limit 1) = 'default' and '{$globalDefault}' = 'true' then 'true' 
when (select meta_value from {$prefix}postmeta as meta where post_id = events.ID and meta_key = 'vem_include_in_archives' limit 1) = 'default' and '{$globalDefault}' = 'after' and (select max(end_time) from {$prefix}vem_event_dates as dates where post_id = events.ID) < unix_timestamp() then 'true' 
else 'false' end as result) = 'true'
BLOCK;

		}

		if ($this->keyword != '') {
			$keywordColumns = [
				"events.post_title",
				"ifnull(events.post_excerpt,'')",
				"ifnull((select meta_value from {$prefix}postmeta as meta where post_id = events.ID and meta_key = 'vem_event_details' limit 1), '')"
			];

			$keywordColumns = apply_filters('vem_events_keyword_search_fields', $keywordColumns, $this);
			$concat = implode(", ' ', ", $keywordColumns);
			$where[] = "(concat(".$concat.")) like '%{$this->keyword}%'";
		}

		$where = apply_filters('vem_events_where', $where, $this->archives, $this->context);

		return $where;
	}

	private function getEventsQuery($count=false) {
		global $wpdb;

		$prefix = $wpdb->prefix;
		if ($count) {
			$query = 'select count(events.ID) as cnt';
		} else {
			$query = 'select events.ID';
		}

		$query .= " from {$prefix}posts as events";

		$where = $this->getEventsWhere();
		if (sizeof($where) > 0) {
			$query .= ' where '.implode(' and ', $where);
		}

		if (!$count) {
			$query .= ' order by '.$this->eventsOrderby;
			$query .= ' '.$this->eventsOrder;

			if ($this->eventsLimit > 0) {
				$query .= ' limit '.$this->eventsLimit;
			}

			if ($this->eventsOffset > 0) {
				$query .= ' offset '.$this->eventsOffset;
			}
		}

		return $query;
	}

	public function getOccurrences() {
		return $this->occurrences;
	}

	public function getBoundingTimes($eventId) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'vem_event_dates';

		$q = "SELECT min(`{$table_name}`.`start_time`) as earliest, max(`{$table_name}`.`start_time`) as latest FROM `{$table_name}` WHERE `{$table_name}`.`post_ID` = {$eventId} ";

		$bounding = $wpdb->get_results($q, ARRAY_A);
		return $bounding[0];
	}

	public function setEventId($id) {
		$this->eventId = $id;
	}

	public function setExcludeEvents($ids = []) {
		if (!is_array($ids)) $ids = [parseInt($ids)];
		$this->excludeEvents = $ids;
	}

	public function getEventExclusionWhere() {
		if (sizeof($this->excludeEvents) == 0) return '';
		return apply_filters('vem-event-exclusion-where', 'events.ID not in ('.implode(',',$this->excludeEvents).')', $this->excludeEvents, $this, $this->context);
	}

	public function getDatesExclusionWhere() {
		if (sizeof($this->excludeEvents) == 0) return '';
		return apply_filters('vem-dates-exclusion-where', 'occurrences.post_ID not in ('.implode(',',$this->excludeEvents).')', $this->excludeEvents, $this, $this->context);
	}

	public function setOccurrenceId($id) {
		$this->occurrenceId = $id;
	}

	public function setContext($context) {
		$this->context = $context;
	}

	public function setKeyword($keyword) {
		$this->keyword = $keyword;
	}

	public function setDatetimeFormatOverride($datetime = '', $date = '', $time = '') {
		$this->datetimeFormatOverride = [
			'datetime' => $datetime,
			'date' => $date,
			'time' => $time
		];
	}

	public function setArchives($archives = false) {
		$this->archives = $archives;
	}

	public function setGrouped($isGrouped = false) {
		$this->isGrouped = $isGrouped;
	}

	public function setCustomChunks($use = false, $page = '', $date = '') {
		$this->customChunks = [
			'use' => $use,
			'page' => $page,
			'date' => $date
		];
	}

	public function setOccurrenceRange($range, $start=null, $end=null) {
		$range = in_array($range, [self::UPCOMING, self::RECENT, self::DATES]) ? $range : self::ALL;
		$this->occurrenceRange = $range;

		if ($range == self::DATES) {
			$this->startTime = $start;
			$this->endTime = $end;
		}

		return $range;
	}

	private function getTaxonomyDatesWhere() {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$where = [];
		$dateTermTable = VEM_DATE_TERM_TABLE;

		foreach ($this->taxonomyFilters as $taxonomy => $ids) {
			if (!is_array($ids) || sizeof($ids) == 0) continue;
		
			$terms = implode(',',$ids);
			switch ($taxonomy) {
				case 'event_venue':
					$datesWhere = "exists (
						select * 
						from {$prefix}vem_event_dates as vemdates
						where vemdates.venue_ID in ({$terms})
						and vemdates.post_id = events.ID
					)";
					break;

				case 'occurrence_category':
					$datesWhere = "exists (
						select * 
						from {$prefix}{$dateTermTable} as terms
						where terms.term_id in ({$terms})
						and terms.occurrence_id = occurrences.id
					)";
					break;

				default:
					$datesWhere = "exists (
						select * 
						from {$prefix}term_relationships as relations
						join {$prefix}term_taxonomy as termtax on relations.term_taxonomy_id = termtax.term_taxonomy_id
						join {$prefix}terms as terms on termtax.term_id = terms.term_id
						where termtax.taxonomy = '{$taxonomy}'
						and terms.term_id in ({$terms})
						and relations.object_id = events.ID
					)";
					break;
			}

			$where[] = apply_filters('vem_occurrence_taxonomy_where', $datesWhere, $taxonomy, $ids, $this, $this->context);
		}

		return $where;
	}

	private function getTaxonomyEventsWhere() {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$where = [];
		$dateTermTable = VEM_DATE_TERM_TABLE;

		foreach ($this->taxonomyFilters as $taxonomy => $ids) {

			if (!is_array($ids) || sizeof($ids) == 0) continue;
		
			$terms = implode(',',$ids);
			switch ($taxonomy) {
				case 'event_venue':
					$eventsWhere = "exists (
						select * 
						from {$prefix}vem_event_dates as occurrences
						where occurrences.venue_ID in ({$terms})
						and occurrences.post_id = events.ID
					)";
					break;

				case 'occurrence_category':
					$eventsWhere = "exists (
						select * 
						from {$prefix}{$dateTermTable} as terms
						where terms.term_id in ({$terms})
						and terms.event_id = events.ID
					)";
					break;

				default:
					$eventsWhere = "exists (
						select * 
						from {$prefix}term_relationships as relations
						join {$prefix}term_taxonomy as termtax on relations.term_taxonomy_id = termtax.term_taxonomy_id
						join {$prefix}terms as terms on termtax.term_id = terms.term_id
						where termtax.taxonomy = '{$taxonomy}'
						and terms.term_id in ({$terms})
						and relations.object_id = events.ID
					)";
					break;
			}

			$where[] = apply_filters('vem_event_taxonomy_where', $eventsWhere, $taxonomy, $ids, $this, $this->context);
		}

		return $where;
	}

	public function setTaxonomyCriterion($taxonomy, $ids = []) {
		$this->taxonomyFilters[$taxonomy] = $ids;
	}

	public function setHowRecent($days) {
		$this->howRecent = 60*60*24*$days;		
	}

	public function setEventsOrderby($orderby) {
		$this->eventsOrderby = $orderby;		
	}

	public function setEventsOrder($order) {
		$this->eventsOrder = $order;		
	}

	public function setDatesOrderby($orderby) {
		$this->datesOrderby = $orderby;		
	}

	public function setDatesOrder($order) {
		$this->datesOrder = $order;		
	}

	public function setDatesLimit($limit) {
		$this->datesLimit = $limit;
	}

	public function setDatesOffset($offset) {
		$this->datesOffset = $offset;
	}

	public function setEventsLimit($limit) {
		$this->eventsLimit = $limit;
	}

	public function setEventsOffset($offset) {
		$this->eventsOffset = $offset;
	}

	public function setCalendarId($id) {
		$this->calendarId = $id;
	}	

	public function getCalendarId() {
		return $this->calendarId;
	}	

	public function setListingId($id) {
		$this->listingId = $id;
	}	

	public function getListingId() {
		return $this->listingId;
	}	

	public function getContext() {
		return $this->context;
	}	

	public function setToggle($toggleAfter = null, $toggleMessage = null) {
		if ($toggleAfter) $this->toggleAfter = $toggleAfter;
		if ($toggleMessage) $this->toggleMessage = $toggleMessage;
	}

	public function retrieveDates() {
		global $wpdb;

		$this->query = $this->getDatesQuery();
		$this->occurrences = $wpdb->get_results($this->query, ARRAY_A);
	}

	public function getDatesCount() {
		global $wpdb;
		$this->query = $this->getDatesQuery(true);
		$results = $wpdb->get_results($this->query, ARRAY_A);
		return (int)$results[0]['cnt'];
	}

	public function retrieveEvents() {
		global $wpdb;

		$this->query = $this->getEventsQuery();
		$results = $wpdb->get_results($this->query, ARRAY_A);
		$this->events = array_map(function($event) {
			return (int)$event['ID'];
		}, $results);
	}

	public function getEventsCount() {
		global $wpdb;
		$this->query = $this->getEventsQuery(true);
		$results = $wpdb->get_results($this->query, ARRAY_A);
		return (int)$results[0]['cnt'];
	}

	public function getSingleEventContent($event, $multiple) {
        $venture = VentureFramework::getInstance('venture-event-system');

        // Normalize all the meta so we can make sure we have default values 
        $meta = get_post_meta($event->ID);
        $defaults = apply_filters('vem_event_meta_defaults', $this->defaultMeta, $this->context);
        $meta = wp_parse_args($meta, $defaults);
		$meta = apply_filters('vem_event_meta_values', $meta, $event, $this->context);
		
        $globalDefault = $venture->getOption('event-archives-default');
		$archiveMeta = VentureUtility::getSingleMeta($meta['vem_include_in_archives'] ?? '');

		if ($archiveMeta == 'default') $archiveMeta = $globalDefault;

		switch ($archiveMeta) {
			case 'true':
				$archive = true;
				break;

			case 'after':
				// Check bounding times
				$bounds = $this->getBoundingTimes($event->ID);
				$now = current_time('timestamp', true);
				$archive = ($now > $bounds['latest']);
				break;

			case 'false':
			default:
				$archive = false;
				break;
		}

        if ($multiple) {
        	$option = ($archive ? 'event-archive-index-fields' : 'index-page-fields');
        } else {
	        $option = ($archive ? 'event-archive-fields' : 'single-page-fields');
        	$this->toggleAfter = intval(VentureUtility::getSingleMeta($meta['vem_max_occurrences']));
        	$this->toggleMessage = VentureUtility::getSingleMeta($meta['vem_max_message']);
        }

        $pageChunks = ($this->customChunks['use']) ? explode(',',$this->customChunks['page']) : explode(',',$venture->getOption($option));

        $this->setEventId($event->ID);
        $this->setArchives($archive);

        $output = '';
		foreach ($pageChunks as $chunk) {
			$output .= $this->getPageChunk($chunk, $event, $meta, $archive, $multiple);
		}        

		return $output;
	}

	public function getSingleDateSchema($date) {
		$excerpt = str_replace('"', '\"', strip_tags((strlen($date['event_excerpt']) > 0) ?
					$date['event_excerpt'] :
					$this->getExcerpt(VentureUtility::getSingleMeta($date['details']))));
		$name = str_replace('"', '\"', (strip_tags($date['event_name'])));
		$imageSource = apply_filters('vem-get-post-thumbnail', get_the_post_thumbnail_url($date['event_id'], 'full'), $date['event_id'], 'full');
		$image = ($imageSource ? '"image": ["'.$imageSource.'"],' : '');
		
		$timezone = wp_timezone_string();

		$dt = new DateTime("now", new DateTimeZone($timezone));
		$dt->setTimestamp($date['start_time']);
		$start = $dt->format('c');

		$dt->setTimestamp($date['end_time']);
		$end = $dt->format('c');

		$dt->setTimestamp($date['gsd_previous_start_time']);
		$prev = $dt->format('c');

		$status = $date['gsd_status'];

		$location = ($status == 'EventMovedOnline') ?
			VentureUtility::getTemplate('gsd-location-online', $date) :
			VentureUtility::getTemplate('gsd-location-place', $date) ;

		$previous = ($status == 'EventRescheduled') ? '"previousStartDate": "'.$prev.'",' : '';

		$output = VentureUtility::getTemplate('gsd-schema', [
			'name' => $name,
			'start' => $start,
			'end' => $end,
			'previous' => $previous,
			'status' => $status,
			'location' => $location,
			'image' => $image,
			'excerpt' => $excerpt
		]);

		return $output;
	}

	// For full listings, grouped is separate
	public function getGroupedEventContent($showMoreDetailsLink = true, $moreDetailsText = 'More details', $showNoEventsMessage = true, $noEventsMessage = 'No events found') {
		$this->retrieveEvents();

		$events = (sizeof($this->events) > 0) ? get_posts([
			'post__in' => $this->events,
			'orderby' => 'post__in',
			'ignore_sticky_posts' => 1,
			'posts_per_page' => -1,
			'post_type' => 'event'
		]) : [];

		$output = '';
		if (sizeof($events) > 0) {
			foreach ($events as $event) {
				$catClasses = $this->getEventCategoriesClasses($event->ID);
				$output .= '<div class="vem-single-event grouped'.($catClasses ? ' '.$catClasses : '').'" vem-event-id="'.$event->ID.'">';
				$output .= $this->getSingleEventContent($event, true);
				if ($showMoreDetailsLink) {
					$output .= '<div class="vem-more-details"><a href="'.get_permalink($event->ID).'">'.$moreDetailsText.'</a></div>';
				}
				$output .= '</div>';
			}
		} else {
			if ($showNoEventsMessage) {
				$output .= '<div class="vem-no-events-message">'.$noEventsMessage.'</div>';
			}
		}

		return $output;
	}

	public function getListingEventContent($showMoreDetailsLink = true, $moreDetailsText = 'More details', $showNoEventsMessage = true, $noEventsMessage = 'No events found') {
		$this->retrieveDates();
		if (sizeof($this->occurrences) == 0) {
			if ($showNoEventsMessage) {
				return '<div class="vem-no-events-message">'.$noEventsMessage.'</div>';
			}
		}

        $ventureSystem = VentureFramework::getInstance('venture-event-system');
        $multiple = true;

		$output = '';
		foreach($this->occurrences as $key => $o) {
			$this->currentOccurrence = $key;

	        $meta = [
				'vem_display_end_times' => $o['display_end_times'],
				'vem_max_occurrences' => $o['max_occurrences'],
				'vem_max_message' => $o['max_message'],
				'vem_homepage_title' => $o['homepage_title'],
				'vem_include_in_archives' => $o['include_in_archives'],
				'vem_pretitle' => $o['pretitle'],
				'vem_posttitle' => $o['posttitle'],
				'vem_event_transcript' => $o['event_transcript'],
				'vem_event_details' => $o['details'],
				'vem_event_media' => $o['media'],
				'vem_fields_set_one' => $o['fields_set_one'],
				'vem_fields_set_two' => $o['fields_set_two'],
				'vem_fields_set_three' => $o['fields_set_three'],
				'vem_index_thumb' => apply_filters('vem-get-post-thumbnail-url', get_the_post_thumbnail_url($o['event_id'], 'full'), $o['event_id'], 'full'),
				'vem_calendar_thumb' => apply_filters('vem-get-calendar-thumbnail-url', get_the_post_thumbnail_url($o['event_id'], 'thumbnail'), $o['event_id'], 'thumbnail')
	        ];
			$meta = apply_filters('vem_event_meta', $meta, $o, $this->context);

	        $event = (object)[
	        	'ID' => $o['event_id'],
	        	'post_title' => $o['event_name'],
	        	'post_excerpt' => $o['event_excerpt']
	        ];

			$globalDefault = $ventureSystem->getOption('event-archives-default');
			$archiveMeta = VentureUtility::getSingleMeta($meta['vem_include_in_archives'] ?? '');
	
			if ($archiveMeta == 'default') $archiveMeta = $globalDefault;
	
			switch ($archiveMeta) {
				case 'true':
					$archive = true;
					break;
	
				case 'after':
					// Check bounding times
					$bounds = $this->getBoundingTimes($event->ID);
					$now = current_time('timestamp', true);
					$archive = ($now > $bounds['latest']);
					break;
	
				case 'false':
				default:
					$archive = false;
					break;
			}

			$option = ($archive ? 'event-archive-index-fields' : 'index-page-fields');
	       	$pageChunks = ($this->customChunks['use']) ? explode(',',$this->customChunks['page']) : explode(',',$ventureSystem->getOption($option));

	        $this->setEventId($event->ID);
			$this->setArchives($archive);
			
			$catClasses = $this->getEventCategoriesClasses($event->ID);
	        $output .= '<div class="vem-single-event ungrouped'.($catClasses ? ' '.$catClasses : '').'" vem-event-id="'.$event->ID.'">';
			foreach ($pageChunks as $chunk) {
				$output .= $this->getPageChunk($chunk, $event, $meta, $archive, $multiple);
			}
			if ($showMoreDetailsLink) {
				$output .= '<div class="vem-more-details"><a href="'.get_permalink($event->ID).'">'.$moreDetailsText.'</a></div>';
			}
			$output .= '</div>';
		}

		return $output;
	}

	public function getNextOccurrenceContent() {

		$key = $this->currentOccurrence;
		$venture = VentureFramework::getInstance('venture-event-system');

		if ($key > (sizeof($this->occurrences)-1)) return false; // No more left
		$o = $this->occurrences[$key];

        $meta = [
			'vem_display_end_times' => $o['display_end_times'],
			'vem_max_occurrences' => $o['max_occurrences'],
			'vem_max_message' => $o['max_message'],
			'vem_homepage_title' => $o['homepage_title'],
			'vem_include_in_archives' => $o['include_in_archives'],
			'vem_pretitle' => $o['pretitle'],
			'vem_posttitle' => $o['posttitle'],
			'vem_event_transcript' => $o['event_transcript'],
			'vem_event_details' => $o['details'],
			'vem_event_media' => $o['media'],
			'vem_fields_set_one' => $o['fields_set_one'],
			'vem_fields_set_two' => $o['fields_set_two'],
			'vem_fields_set_three' => $o['fields_set_three'],
			'vem_index_thumb' => apply_filters('vem-get-post-thumbnail-url', get_the_post_thumbnail_url($o['event_id'], 'full'), $o['event_id'], 'full'),
			'vem_calendar_thumb' => apply_filters('vem-get-calendar-thumbnail-url', get_the_post_thumbnail_url($o['event_id'], 'thumbnail'), $o['event_id'], 'thumbnail'),
        ];
		$meta = apply_filters('vem_event_meta', $meta, $o, $this->context);

        $event = (object)[
        	'ID' => $o['event_id'],
        	'post_title' => $o['event_name'],
        	'post_excerpt' => $o['event_excerpt']
        ];

        $globalDefault = $venture->getOption('event-archives-default');
		$archiveMeta = VentureUtility::getSingleMeta($meta['vem_include_in_archives'] ?? '');

		if ($archiveMeta == 'default') $archiveMeta = $globalDefault;

		switch ($archiveMeta) {
			case 'true':
				$archive = true;
				break;

			case 'after':
				// Check bounding times
				$bounds = $this->getBoundingTimes($event->ID);
				$now = current_time('timestamp', true);
				$archive = ($now > $bounds['latest']);
				break;

			case 'false':
			default:
				$archive = false;
				break;
		}

		$option = ($archive ? 'event-archive-index-fields' : 'index-page-fields');
       	$pageChunks = ($this->customChunks['use']) ? explode(',',$this->customChunks['page']) : explode(',',$venture->getOption($option));

        $this->setEventId($event->ID);
        $this->setArchives($archive);

        $output = '';
		foreach ($pageChunks as $chunk) {
			$output .= $this->getPageChunk($chunk, $event, $meta, $archive, true);
		}

		$this->currentOccurrence++;
		return $output;
	}

	public function getPageChunk($chunk, $event, $meta, $archive, $multiple) {
		global $ventureEventSystem;

		switch ($chunk) {
			case 'title':
				$x = $event->post_title;
				$formatted = $x ? '<div class="vem-single-event-title">'.$x.'</div>' : '';
				break;

			case 'excerpt':
				$x = (strlen($event->post_excerpt) > 0) ?
					$event->post_excerpt :
					$this->getExcerpt(VentureUtility::getSingleMeta($meta['vem_event_details']));
				$formatted = $x ? '<div class="vem-single-event-excerpt">'.$x.'</div>' : '';
				break;

			case 'vem_pre_title':
				$x = VentureUtility::getSingleMeta($meta['vem_pretitle']);
				$formatted = $x ? '<div class="vem-single-event-pretitle">'.$x.'</div>' : '';
				break;

			case 'vem_post_title':
				$x = VentureUtility::getSingleMeta($meta['vem_posttitle']);
				$formatted = $x ? '<div class="vem-single-event-posttitle">'.$x.'</div>' : '';
				break;

			case 'vem_event_transcript':
				$x = VentureUtility::getSingleMeta($meta['vem_event_transcript']);
				$formatted = ($x && $x != 0) ? '<div class="vem-single-event-transcript"><a href="'.get_permalink($x).'">Event Transcript</a></div>' : '';
				break;

			case 'full_image':
				$x = apply_filters('vem-get-post-thumbnail', get_the_post_thumbnail($event->ID, 'full'), $event->ID, 'full');
				// class "vem-single-event-thumbnail" is only included for backwards compatibility
				$formatted = $x ? '<div class="vem-single-event-thumbnail vem-full-image"><a href="'.get_permalink($event->ID).'">'.$x.'</a></div>' : '';
				break;

			case 'medium_image':
				$x = apply_filters('vem-get-post-thumbnail', get_the_post_thumbnail($event->ID, 'medium'), $event->ID, 'medium');
				$formatted = $x ? '<div class="vem-medium-image"><a href="'.get_permalink($event->ID).'">'.$x.'</a></div>' : '';
				break;

			case 'large_image':
				$x = apply_filters('vem-get-post-thumbnail', get_the_post_thumbnail($event->ID, 'large'), $event->ID, 'large');
				$formatted = $x ? '<div class="vem-large-image"><a href="'.get_permalink($event->ID).'">'.$x.'</a></div>' : '';
				break;

			case 'thumbnail_image':
				$x = apply_filters('vem-get-post-thumbnail', get_the_post_thumbnail($event->ID, 'thumbnail'), $event->ID, 'thumbnail');
				// class "vem-calendar-thumbnail" is only included for backwards compatibility
				$formatted = $x ? '<div class="vem-calendar-thumbnail vem-thumbnail-image"><a href="'.get_permalink($event->ID).'">'.$x.'</a></div>' : '';
				break;

			case 'vem_event_details':
				$x = VentureUtility::getSingleMeta($meta['vem_event_details']);
				$formatted = $x ? '<div class="vem-single-event-details">'.wpautop($x).'</div>' : '';
				break;

			case 'vem_fields_set_one':
			case 'vem_fields_set_two':
			case 'vem_fields_set_three':
				$set = substr($chunk,15);
				$x = unserialize(VentureUtility::getSingleMeta($meta[$chunk]));
				$formatted = '';
				if (sizeof($x) > 0) {
					$setContent = '';
					foreach ($x as $y) {
						if ((trim($y['key']) != '') || (trim($y['value']) != '')) {
							$setContent .= '<div class="one-field">';
							if (trim($y['key']) != '') {
								$setContent .= '<span class="field-set-key">'.trim($y['key']).'</span>';
							}
							if (trim($y['value']) != '') {
								$setContent .= '<span class="field-set-value">'.trim($y['value']).'</span>';
							}
							$setContent .= '</div>';
						}
					}
					if (!empty($setContent)) {
						$formatted .='<div class="vem-single-event-field-set field-set-'.$set.'">';
						$formatted .= $setContent;
						$formatted .= '</div>';

					}
				} else {
					$formatted = '';
				}
				break;

			case 'occurrences':
		        $venture = VentureFramework::getInstance('venture-event-system');

				if ($multiple) {
		        	$option = 'index-page-occurrence-fields';
		        } else {
			        $option = ($archive ? 'event-archive-occurrence-fields' : 'single-page-occurrence-fields');
		        }
		        $dateChunks = ($this->customChunks['use']) ? explode(',',$this->customChunks['date']) : explode(',',$venture->getOption($option));

		        // We only do the retrieve if we really, really have to!
		        if ($this->isGrouped) {
	        		$this->retrieveDates();
					$x = $this->occurrences;
		        } else {
		        	$x = [$this->occurrences[$this->currentOccurrence]];
		        }

				$formatted = '<div class="vem-occurrences">';

				$key = 1;
				$toggleAfter = $this->toggleAfter;
				$needToggle = (($toggleAfter != 0) && ($toggleAfter < sizeof($x)));
				$toggleMessage = apply_filters('vem-show-more-label', $this->toggleMessage, $this->context);

				foreach ($x as $o) {
					if ($needToggle && ($key == $toggleAfter+1)) {
						$formatted .= '[toggle label="'.$toggleMessage.'"]';
					}
					$status = $o['gsd_status'] ?? 'EventScheduled';
					$statusClass = self::STATUSES[$status]['class'];
					$formatted .= '<div class="vem-one-occurrence '.$statusClass.'">';
					foreach ($dateChunks as $dchunk) {
						$formatted .= $this->getOccurrenceChunk($dchunk, $o, $event, $meta);
					}
					$formatted .= '</div>';
					$key++;
				}
				if ($needToggle) $formatted .= '[/toggle]';

				$formatted .= '</div>';
				break;

			case 'vem_calendar':
				$venture = VentureFramework::getInstance('venture-event-system');
				$x = $venture->getOption('single-event-calendar');
				if (!$x) {
					$formatted = '';
				} else {
					$x = $formatted = do_shortcode('[vemcalendar id="'.$x.'" event="'.$event->ID.'"]');
				}
				break;

			default:
				$x = 'Your event layout options are incorrect, you should <a href="'.admin_url('admin.php?page=venture-options&tab=default-event-lists').'">set those</a> to get rid of this message ('.$chunk.')';
				$formatted = '<div class="vem-single-event-unhandled">'.$x.'</div>';
				break;
		}

		// General filter for all chunks
		$formatted = apply_filters('vem_page_chunk', $formatted, $x, $chunk, $event, $meta, $this->context, $this);

		// Filter for just this specific chunk
		$formatted = apply_filters('vem_page_chunk_'.$chunk, $formatted, $x, $event, $meta, $this->context, $this);

		return $formatted;
	}

	public function getOccurrenceChunk($chunk, $o, $event, $meta) {

		global $ventureEventSystem;
        $venture = VentureFramework::getInstance('venture-event-system');

		switch ($chunk) {
			case 'vem_dates_address':
				$x = [
					'id' => $o['venue_id'],
					'name' => $o['venue'],
					'address' => $o['venue_address'],
					'city' => $o['venue_city'],
					'state' => $o['venue_state'],
					'zip' => $o['venue_zip'],
					'country' => $o['venue_country']
				];

				// Suppress address display if event was moved online
				if ($o['gsd_status'] == 'EventMovedOnline') {
					$formatted = '';
				} else {
					$formatted = $x['id'] ? '<div class="vem-single-event-date-venue-address">'.$this->formatVenueAddress($x, false).'</div>' : '';
				}
				break;

			case 'vem_dates_ticket_url':
				$x = [
					'url' => $o['tickets'],
					'image' => $venture->getOption('buy-ticket-icon'),
					'buttonText' => $o['buytext']
				];
				$formatted = $x['url'] ? '<div class="vem-single-event-date-ticket-link">'.$this->formatTicketLink($x).'</div>' : '';
				break;

			case 'vem_dates_ticket2_url':
				$x = [
					'url' => $o['tickets2'],
					'image' => $venture->getOption('buy-ticket-icon'),
					'buttonText' => $o['buytext2']
				];
				$formatted = $x['url'] ? '<div class="vem-single-event-date-ticket-link">'.$this->formatTicketLink($x).'</div>' : '';
				break;

			case 'vem_dates_date_time':
				$x = [
					'start' => $o['start_time'],
					'end' => $o['end_time'],
					'showEndTimes' => (VentureUtility::getSingleMeta($meta['vem_display_end_times']) ?? 'default'),
					'style' => 'datetime'
				];
				$display = $this->formatDisplayTimes($x);
				$formatted = $display ? '<div class="vem-single-event-date-start">'.$display.'</div>' : '';
				break;

			case 'vem_dates_date':
				$x = [
					'start' => $o['start_time'],
					'end' => $o['end_time'],
					'showEndTimes' => (VentureUtility::getSingleMeta($meta['vem_display_end_times']) ?? 'default'),
					'style' => 'date'
				];
				$display = $this->formatDisplayTimes($x);
				$formatted = $display ? '<div class="vem-single-event-date-start">'.$display.'</div>' : '';
				break;

			case 'vem_dates_time':
				$x = [
					'start' => $o['start_time'],
					'end' => $o['end_time'],
					'showEndTimes' => (VentureUtility::getSingleMeta($meta['vem_display_end_times']) ?? 'default'),
					'style' => 'time'
				];
				$display = $this->formatDisplayTimes($x);
				$formatted = $display ? '<div class="vem-single-event-date-start">'.$display.'</div>' : '';
				break;

			case 'vem_dates_status':
				$x = [
					'status' => $o['gsd_status'],
					'url' => $o['gsd_url'],
					'previous' => $o['gsd_previous_start_time']
				];

				$display = $this->formatEventStatus($x);
				$formatted = $display ? '<div class="vem-single-event-date-status">'.$display.'</div>' : '';
				break;

			case 'vem_dates_ticket_price':
				$x = [
					'from' => $o['ticket_price_from'],
					'to' => $o['ticket_price_to'],
					'cs' => $venture->getOption('default-currency-symbol')
				];
				$display = $this->formatTicketPriceRange($x);
				$formatted = $display ? '<div class="vem-single-event-date-ticket-pricing">'.$display.'</div>' : '';
				break;

			case 'vem_dates_ticket2_price':
				$x = [
					'from' => $o['ticket2_price_from'],
					'to' => $o['ticket2_price_to'],
					'cs' => $venture->getOption('default-currency-symbol')
				];
				$display = $this->formatTicketPriceRange($x);
				$formatted = $display ? '<div class="vem-single-event-date-ticket-pricing">'.$display.'</div>' : '';
				break;

			case 'vem_dates_note':
				$x = $o['note'];
				$formatted = $x ? '<div class="vem-single-occurrence-note">'.$x.'</div>' : '';
				break;

			case 'vem_dates_import':
				$venue = [
					'id' => $o['venue_id'],
					'name' => $o['venue'],
					'address' => $o['venue_address'],
					'city' => $o['venue_city'],
					'state' => $o['venue_state'],
					'zip' => $o['venue_zip'],
					'country' => $o['venue_country']
				];

				$x = [
					'id' => $o['event_id'],
					'occurrence_id' => $o['occurrence_id'],
					'title' => $o['event_name'],
					'start' => $o['start_time'],
					'end' => $o['end_time'],
					'excerpt' => $this->getExcerpt($o['details']),
					'url' => get_permalink($o['event_id']),
					'address' => $this->formatIcsAddress($venue)
				];

				$display = $this->formatImportLink($x);
				$formatted = $display ? '<div class="vem-single-event-date-import-link">'.$display.'</div>' : '';
				break;

			case 'vem_dates_venue_ID':
				$x = $o['venue'];

				// Suppress Venue display if the event was moved online
				if ($o['gsd_status'] == 'EventMovedOnline') {
					$formatted = '';
				} else {
					$formatted = $x ? '<div class="vem-single-occurrence-venue">'.$x.'</div>' : '';
				}
				break;

			case 'vem_dates_terms':
				$x = $o['occurrence_categories'];
				$display = '';
				if ($x) {
					$cats = explode(',', $o['occurrence_categories']);
					foreach ($cats as $cat) {
						$c = explode('|||', $cat);
						$display .= '<div class="one-date-term term-id-'.$c[0].' term-slug-'.$c[2].'">';
						if ($c[3]) $display .= wp_get_attachment_image($c[3], 'full', false, ['class' => 'term-image']);
						$display .= '<span class="term-name">'.$c[1].'</span>';
						$display .= '</div>';
					}
				}
				$formatted = $x ? '<div class="vem-single-occurrence-categories">'.$display.'</div>' : '';
				break;

			default:
				$x = 'Your event layout options are incorrect, you should <a href="'.admin_url('admin.php?page=venture-options-layouts').'">set those</a> to get rid of this message ('.$chunk.')';
				$formatted = '<div class="vem-single-occurrence-unhandled">'.$x.'</div>';
				break;
		}

		// General filter for all chunks
		$formatted = apply_filters('vem_date_chunk', $formatted, $x, $chunk, $o, $event, $meta, $this->context, $this);

		// Filter for just this specific chunk
		$formatted = apply_filters('vem_date_chunk_'.$chunk, $formatted, $x, $o, $event, $meta, $this->context, $this);

		return $formatted;
	}

	public function getDisplay() {
		global $ventureEventSystem;

		$output = '';
		$output .= '<div>'.sizeof($this->occurrences).' occurrences</div>';
		$output .= '<table>';
		$output .= '<tr>';
		$output .= '<th>Event ID</th>';
		$output .= '<th>Name</th>';
		$output .= '<th>Occurrence ID</th>';
		$output .= '<th>Start Time</th>';
		$output .= '<th>End Time</th>';
		$output .= '<th>Details</th>';
		$output .= '<tr>';

		foreach ($this->occurrences as $o) {
			$output .= '<tr>';
			$output .= '<td>'.$o['event_id'].'</td>';
			$output .= '<td>'.$o['event_name'].'</td>';
			$output .= '<td>'.$o['occurrence_id'].'</td>';
			$output .= '<td>'.$ventureEventSystem->getFormattedDateTime($o['start_time']).'</td>';
			$output .= '<td>'.$ventureEventSystem->getFormattedDateTime($o['end_time']).'</td>';
			$output .= '<td>'.$o['details'].'</td>';
			$output .= '<tr>';
		}

		$output .= '</table>';

		//$output = var_export($this->occurrences, true);
		return $output;
	}

	public function getCalendarData($topCategory = 0) {
		$venture = VentureFramework::getInstance('venture-event-system');

		$output = [];
		$showEnd = (($venture->getOption('display-end-times') ?: 'no') == 'yes');
		$cs = $venture->getOption('default-currency-symbol');

		foreach ($this->occurrences as $o) {
			$category = $this->getDisplayCategory($topCategory, explode(',', $o['event_categories']));
			$x = $o['occurrence_categories'];
			$oCats = '';
			if ($x) {
				$cats = explode(',', $o['occurrence_categories']);
				foreach ($cats as $cat) {
					$c = explode('|||', $cat);
					$oCats .= '<div class="one-date-term term-id-'.$c[0].' term-slug-'.$c[2].'">';
					if ($c[3]) $oCats .= wp_get_attachment_image($c[3], 'full', false, ['class' => 'term-image']);
					$oCats .= '<span class="term-name">'.$c[1].'</span>';
					$oCats .= '</div>';
				}
			}

			$default = [
				'id' => $o['occurrence_id'],
				'eventId' => $o['event_id'],
				'title' => $o['event_name'],
				'pretitle' => $o['pretitle'],
				'posttitle' => $o['posttitle'],
				'start' => $o['start_time'],
				'end' => $o['end_time'],
				'showend' => $showEnd,
				'colors' => $category['colors'],
				'thumb' => apply_filters('vem-get-calendar-thumbnail-url', get_the_post_thumbnail_url($o['event_id'], 'thumbnail'), $o['event_id'], 'thumbnail'),
				'url' => $o['url'],
				'tickets' => $o['tickets'],
				'buytext' => $o['buytext'],
				'prices' => $this->formatTicketPriceRange([
					'from' => $o['ticket_price_from'],
					'to' => $o['ticket_price_to'],
					'cs' => $cs
				]),
				'tickets2' => $o['tickets2'],
				'buytext2' => $o['buytext2'],
				'prices2' => $this->formatTicketPriceRange([
					'from' => $o['ticket2_price_from'],
					'to' => $o['ticket2_price_to'],
					'cs' => $cs
				]),
				'category' => (is_wp_error($category['term']) || $category['term']->term_id == 0) ? 'Uncategorized' : $category['term']->name,
				'occurrence_categories' => $oCats,
				'categoryClasses' => $this->getEventCategoriesClasses($o['event_id'])
			];
			$output[] = apply_filters('vem_occurrence_calendar_data', $default, $o, $category, $this->context);

		}

		return $output;
	}

	public function getExcerpt($content) {

	    $excerptLength = apply_filters('excerpt_length', 55, $this->context); //Sets excerpt length by word count
	    $excerpt = strip_tags(strip_shortcodes($content)); //Strips tags and images
	    $words = explode(' ', $excerpt, $excerptLength + 1);

	    if(count($words) > $excerptLength) {
	        array_pop($words);
	        array_push($words, '…');
	        $excerpt = implode(' ', $words);
	    }

	    $excerpt = '<p>' . $excerpt . '</p>';

	    return $excerpt;
	}

	// Maintained for backwards compatibility, but deprecated
	public function getSingleMeta($metaValue) {
		return VentureUtility::getSingleMeta($metaValue);
	}

	public function getCatGroup($topCategory = 0, $eventCats = []) {

		$valid = get_term_children($topCategory, 'event_category');
		$valid[] = $topCategory;

		$c = [];
		foreach ($eventCats as $cat) {
			if (in_array($cat, $valid)) {
				$c[] = $cat;
			}
		}

		return $c;
	}

	public function getCatColors($category) {

		if (!array_key_exists($category, $this->catColors)) {
			$back = get_term_meta($category, 'color', true);
			if ($back == "") $back = "transparent";
			$fore = VentureUtility::getContrastYIQ($back);
			$lightness = ($fore == '#ffffff' ? 'dark' : 'light');
			$lightness = apply_filters("vem_get_contrast_color_lightness", $lightness, $back, $fore);
			$this->catColors[$category] = ['back' => $back, 'fore' => $fore, 'lightness' => $lightness];
		}

		return $this->catColors[$category];
	}

	public function getDisplayCategory($topCategory = 0, $eventCats = []) {

		if ($topCategory == 0) {
			$c = $eventCats;
		} else {
			$c = $this->getCatGroup($topCategory, $eventCats);
		}

		$colors = ['colors' => ['back' => '#ffffff', 'fore' => '#000000'], 'term' => 0];
		switch (sizeof($c)) {
			case 0: // No categories
				// Use default
				break;

			case 1: // Only one, easy!
				$colors = ['colors' => $this->getCatColors($c[0]), 'term' => get_term($c[0])];
				break;

			default: // OK, more than one category, have to choose
				$min = 10000000;
				$gotColor = false;
				foreach ($c as $cat) {
					$category = get_term($cat, 'event_category');
					if ($category->parent == 0) {
						$colors = ['colors' => $this->getCatColors($cat), 'term' => get_term($cat)];
						$gotColor = true;
					} else {
						if ($category->parent < $min) {
							$useThis = $cat;
							$min = $category->parent;
						}
					}
				}
				if (!$gotColor) {
					$colors = ['colors' => $this->getCatColors($useThis), 'term' => get_term($useThis)];
				}
				break;
		}

		if ($colors['colors']['back'] == "") $colors['colors']['back'] = "transparent";
		if ($colors['colors']['fore'] == "") $colors['colors']['fore'] = "#000000";

		return apply_filters('vem_apply_calendar_colors', $colors, $topCategory, $eventCats);
	}

	public function getEventCategoriesClasses($eventId) {
		$cats = wp_get_post_terms($eventId, 'event_category', [
			'fields' => 'slugs'
		]);

		if (sizeof($cats) == 0) return '';

		$cats = array_map(function($c) {
			return 'vem-cat-'.$c;
		}, $cats);

		$cats = apply_filters('vem_event_category_classes', $cats, $eventId);
		return implode(' ', $cats);
	}

	public function formatImportLink($args = []) {

		$a = wp_parse_args($args, [
			'id' => 0,
			'occurrence_id' => 0,
			'title' => '',
			'start' => 0,
			'end' => 0,
			'excerpt' => '',
			'url' => '',
			'address' => ''
		]);

		$glink = 'https://www.google.com/calendar/event?action=TEMPLATE&text='.urlencode($a['title'])
			.'&dates='.date('Ymd\THis\Z', $a['start']).'/'.date('Ymd\THis\Z', $a['end'])
			.'&details='.urlencode(strip_tags($a['excerpt']).' '.$a['url'])
			.'&location='.urlencode($a['address'])
			.'&sprop=website:'.urlencode($a['url']).'&trp=false';

		return '<a href="'.$glink.'" class="google" target="_blank"><i class="fa fa-calendar"></i> Google</a> <a href="'.home_url('/ics/'.$a['occurrence_id']).'" class="ics"><i class="fa fa-calendar"></i> iCalendar</a>';
	}

	public function formatVenueAddress($venue, $includeName = true) {

		$display = '';

		$fields = array();
		foreach (array($venue['address'], $venue['city'], $venue['state'], $venue['zip'], $venue['country']) as $f) {
			if (!empty($f)) {
				$fields[] = str_replace(' ', '+', $f);
			}
		}
		$map_url = (!empty($fields)) ? implode(',', $fields) : '';
		
		if ($includeName) {
			$display .= '<div class="occurrence-venue-name">' . $venue['name'] .  '</div>';
		}
		$display .= '<div class="venue-address-wrapper">';
		if (isset($venue['address']) && strlen($venue['address']) > 0) {
			$display .= 	'<div class="venue-address">' . $venue['address'] . '</div>';
		}
		if ((isset($venue['city']) && strlen($venue['city']) > 0) || (isset($venue['state']) && strlen($venue['state']) > 0) || (isset($venue['zip']) && strlen($venue['zip']) > 0)) {
			$display .= '<div class="venue-city">';
			if (isset($venue['city']) && strlen($venue['city']) > 0) {
				$display .= $venue['city'];
				if ((isset($venue['state']) && strlen($venue['state']) > 0) || (isset($venue['zip']) && strlen($venue['zip']) > 0)) {
					$display .= ', ';
				}
			}
			if (isset($venue['state']) && strlen($venue['state']) > 0) {
				$display .= $venue['state'];
				if (isset($venue['zip']) && strlen($venue['zip']) > 0) {
					$display .= ' ';
				}
			}
			if (isset($venue['zip']) && strlen($venue['zip']) > 0) {
				$display .= $venue['zip'];
			}
			$display .= '</div>';
		}

		if ($map_url) {
			$display .= '<div class="venue-directions"><a href="https://www.google.com/maps/dir//'.$map_url.'">Google Maps Directions</a></div>';
		}

		$display .= '</div>';

		return $display;
	}

	public function formatIcsAddress($address) {
		$output = $address['name'];

		$fields = array();
		foreach ($address as $key => $f) {
			if ((!in_array($key, ['id', 'name'])) && !empty($f)) {
				$fields[] = $f;
			}
		}
		$address = (!empty($fields)) ? implode(', ', $fields) : '';

		if ($address) $output .= ', '.$address;

		return $output;
	}

	public function formatTicketLink($args = []) {

		$a = wp_parse_args($args, [
			'url' => false,
			'image' => false,
			'buttonText' => 'Buy Now'
		]);

		if ($a['url']) {
			if ($a['image']) {
				$ticketLink = '<a href="' . $a['url'] . '" class="purchase-tickets-icon"><img class="buy-tickets-image" src="'.$a['image'].'" alt="' . $a['buttonText'] . '" title="' . $a['buttonText'] . '" /></a>';
			} else {
				$ticketLink = '<a href="' . $a['url'] . '" class="purchase-tickets-link">' . $a['buttonText'] . '</a>';
			}
		} else {
			$ticketLink = '';
		}

		return $ticketLink;
	}

	public function formatTicketPriceRange($args) {

		$a = wp_parse_args($args, [
			'from' => '',
			'to' => '',
			'cs' => '$'
		]);

		$cs = $a['cs'];
		$from = ($a['from'] === '0') ? 'Free' : $a['from']; 
		$to = ($a['to'] === '0') ? 'Free' : $a['to'];

		if ($from === '0') $from = __("Free");
		if ($to === '0') $to = __("Free");

		if ($from != '' || $to != '') {

			if (!empty($from) && !empty($to) && $to != $from)  {
				return '(' . ($from==__("Free") ? $from : ($cs . $from)) . ' &ndash; ' . ($to==__("Free") ? $to : ($cs . $to)) . ')';
			}

			if (!empty($from) && empty($to)) {
				return 'from ' . ($from==__("Free") ? $from : ($cs . $from));
			}

			if (($from == $to) && (!empty($from) && !empty($to) || $from != 0 && $to != 0 )) {
				return ($from==__("Free") ? $from : ($cs . $from));
			}
		}
		return '';
	}

	public function formatDisplayTimes($args = []) {

		global $ventureEventSystem;
		$venture = VentureFramework::getInstance('venture-event-system');

		$a = wp_parse_args($args, [
			'start' => 0,
			'end' => 0,
			'showEndTimes' => 'default',
			'style' => 'datetime'
		]);

		$output = '';
		$globalShowEndTimes = $venture->getOption('display-end-times');

		switch ($a['showEndTimes']) {
			case 'yes':
				$showEndTimes = true;
				break;

			case 'no':
				$showEndTimes = false;
				break;

			case 'default':
			default:
				$showEndTimes = ($globalShowEndTimes == 'yes');
				break;
		}

		$datetimeFormat = ($this->datetimeFormatOverride['datetime'] == '') ? 'default' : $this->datetimeFormatOverride['datetime'];
		$dateFormat = ($this->datetimeFormatOverride['date'] == '') ? 'default' : $this->datetimeFormatOverride['date'];
		$timeFormat = ($this->datetimeFormatOverride['time'] == '') ? 'default' : $this->datetimeFormatOverride['time'];

		switch ($a['style']) {
			case 'date':
				$startTime = $ventureEventSystem->getFormattedDateTime($a['start'], true, $dateFormat);
				break;

			case 'time':
				$startTime = $ventureEventSystem->getFormattedDateTime($a['start'], false, '', $timeFormat, '');
				break;

			default: // 'datetime'
				if ($this->datetimeFormatOverride['datetime'] != '') {
					$startTime = $ventureEventSystem->getFormattedDateTime($a['start'], true, $datetimeFormat);
				} else {
					$startTime = $ventureEventSystem->getFormattedDateTime($a['start'], false);
				}
				break;
		}

		switch ($a['style']) {
			case 'date':
				$endTime = '';
				break;

			case 'time':
				$endTime = $ventureEventSystem->getFormattedDateTime($a['end'], false, '', $timeFormat, '');
				break;

			default: // 'datetime'
				$endTime = $ventureEventSystem->getFormattedDateTime($a['end'], false, '', $timeFormat, '', 'default');
				break;
		}

		if ($showEndTimes && $endTime != '') {
			$output .= $startTime . ' - ' . $endTime;
		} else {
			$output .= $startTime;
		}

		return $output;
	}

	public function formatEventStatus($args = []) {

		$a = wp_parse_args($args, [
			'status' => 'EventScheduled',
			'url' => '',
			'previous' => 0
		]);

		$label = '<div class="vem-single-event-date-status-label '.self::STATUSES[$a['status']]['class'].'">'.self::STATUSES[$a['status']]['label'].'</div>';

		switch($a['status']) {

			case 'EventPostponed':
			case 'EventCancelled':
				$output = $label;
			break;

			case 'EventRescheduled':
				$date = $this->formatDisplayTimes(['start' => $a['previous'], 'showEndTimes' => 'no', 'style' => 'datetime']);
				$output = $label.'<div class="vem-single-event-date-status-previous">Originally scheduled for '.$date.'</div>';
			break;

			case 'EventMovedOnline':
				$output = $label.'<div class="vem-single-event-date-status-url"><a href="'.$a['url'].'">Online Event Information</a></div>';
			break;

			case 'EventScheduled':
			default:
				$output = '';
			break;
		}

		return $output;

	}
}

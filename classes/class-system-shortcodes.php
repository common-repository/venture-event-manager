<?php

class VentureEventSystemShortcodes
{

    private $searchArgs = [];

    public function __construct()
    {

        // Toggle shortcode (all CSS!)
        add_shortcode('toggle', [$this, 'shortcodeToggle']);

        // Columns
        add_shortcode('twocol_one', [$this, 'twocol_one']);
        add_shortcode('twocol_one_last', [$this, 'twocol_one_last']);
        add_shortcode('threecol_one', [$this, 'threecol_one']);
        add_shortcode('threecol_one_last', [$this, 'threecol_one_last']);
        add_shortcode('threecol_two', [$this, 'threecol_two']);
        add_shortcode('threecol_two_last', [$this, 'threecol_two_last']);
        add_shortcode('fourcol_one', [$this, 'fourcol_one']);
        add_shortcode('fourcol_one_last', [$this, 'fourcol_one_last']);
        add_shortcode('fourcol_two', [$this, 'fourcol_two']);
        add_shortcode('fourcol_two_last', [$this, 'fourcol_two_last']);
        add_shortcode('fourcol_three', [$this, 'fourcol_three']);
        add_shortcode('fourcol_three_last', [$this, 'fourcol_three_last']);
        add_shortcode('fivecol_one', [$this, 'fivecol_one']);
        add_shortcode('fivecol_one_last', [$this, 'fivecol_one_last']);
        add_shortcode('fivecol_two', [$this, 'fivecol_two']);
        add_shortcode('fivecol_two_last', [$this, 'fivecol_two_last']);
        add_shortcode('fivecol_three', [$this, 'fivecol_three']);
        add_shortcode('fivecol_three_last', [$this, 'fivecol_three_last']);
        add_shortcode('fivecol_four', [$this, 'fivecol_four']);
        add_shortcode('fivecol_four_last', [$this, 'fivecol_four_last']);
        add_shortcode('sixcol_one', [$this, 'sixcol_one']);
        add_shortcode('sixcol_one_last', [$this, 'sixcol_one_last']);
        add_shortcode('sixcol_two', [$this, 'sixcol_two']);
        add_shortcode('sixcol_two_last', [$this, 'sixcol_two_last']);
        add_shortcode('sixcol_three', [$this, 'sixcol_three']);
        add_shortcode('sixcol_three_last', [$this, 'sixcol_three_last']);
        add_shortcode('sixcol_four', [$this, 'sixcol_four']);
        add_shortcode('sixcol_four_last', [$this, 'sixcol_four_last']);
        add_shortcode('sixcol_five', [$this, 'sixcol_five']);
        add_shortcode('sixcol_five_last', [$this, 'sixcol_five_last']);

        // VEM shortcodes
        add_shortcode('vemcalendar', [$this, 'shortcodeCalendar']);
        add_shortcode('vemlisting', [$this, 'shortcodeListing']);
        add_shortcode('vemsearch', [$this, 'shortcodeSearch']);
        add_shortcode('vemkey', [$this, 'shortcodeCalendarKey']);

        $searchArgs = apply_filters('vem-search-args', [
            'listing' => '0',
            'keyword' => '',
            'category' => '',
            'season' => '',
            'venue' => '',
            'tag' => '',
            'oc' => '',
            'start' => '',
            'end' => ''
        ]);

        // Parse search args from $_GET
        $this->searchArgs = wp_parse_args($_GET['vemsearch'] ?? [], $searchArgs);

    }

    public function enqueueCalendar() {
        wp_enqueue_style('jquery-ui-base', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', '1.12.1');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('moment');
        wp_enqueue_script('moment-timezone');
        wp_enqueue_script('vem-calendar');
        wp_enqueue_style('vem-listing');
        wp_enqueue_style('vem-calendar');
        wp_enqueue_style('vem-calendar-dialog');
        wp_enqueue_style('vem-widgets');

        do_action('vem_enqueue_calendar_scripts');
        $calendarExtensionScripts = apply_filters('vem_calendar_scripts', []);
        wp_localize_script('vem-calendar', 'VentureExtensionScripts', [
            'scripts' => $calendarExtensionScripts
        ]);
    }

    public function shortcodeToggle($atts, $content = null) {

        $a = shortcode_atts([
            'label' => 'Show more events',
            'open' => 'false',
        ], $atts);

        wp_enqueue_style('vem-toggle');
        $uniqueKey = uniqid();

        $output = '<div class="vem-toggle-wrapper"><input id="vem-toggle-'.$uniqueKey.'" class="vem-toggle" type="checkbox"';
        if ($a['open'] == 'true') $output .= ' checked="checked"';
        $output .= '><label for="vem-toggle-'.$uniqueKey.'" class="vem-toggle">'.$a['label'].'</label><div class="vem-toggle">';
        $output .= do_shortcode($content);
        $output .= '</div></div>';

        return $output;   
    }

    public function shortcodeCalendar( $atts ) {
        $a = shortcode_atts( array(
            'id' => 0,
            'event' => 0,
            'stack' => 667,
            'start' => 'default'
        ), $atts );

        // We really need an ID
        if ($a['id'] == 0) return '';

        // Scripts get loaded in another function because it's easier to maintain that way
        $this->enqueueCalendar();

        $u = uniqid();
        $output = '';

        $venture = VentureFramework::getInstance('venture-event-calendar');

        $class = $venture->getOption('calendar-css-class', $a['id']);
        $clickaction = $venture->getOption('calendar-event-click-action', $a['id']);
        $showtickets = $venture->getOption('calendar-buy-ticket-link', $a['id']);
        $displayonlyfuture = $venture->getOption('calendar-future-only', $a['id']);
        $noEventsMessage = $venture->getOption('calendar-no-events-message', $a['id']);
        $useDateTermFilter = $venture->getOption('calendar-use-date-term-filter', $a['id']);

        $start = $a['start'];
        $event = intval($a['event']);
        if ($event > 0) {
            if (get_post_type($event) != 'event') {
                $event = 0;
            } else {
                $vem3 = new VentureEventManager3();
                $bounding = $vem3->getBoundingTimes($event);
                $newStart = max($bounding['earliest'], time());
                $start = date("m/Y", $newStart);
            }
        }

        // On panels, if there was no explicit stack attribute, we want a default of 1 instead of 667 to force calendar view always
        if ($clickaction == 'panel' && !array_key_exists('stack', $atts)) {
            $a['stack'] = 1;             
        }

        if ($useDateTermFilter) {
            $oc = get_terms([
                'taxonomy' => 'occurrence_category',
                'hide_empty' => false
            ]);
            if (sizeof($oc) > 0) {
                $output .= '<div class="vem-occurrence-category-filters" calendar="'.$u.'">';
                $output .= '<span class="filter-title">'.apply_filters('vem_occurrence_category_filter_title', 'Show Dates: ').'</span>';
                $output .= '<a class="button date-term-filter-all active">All</a>';
                foreach ($oc as $c) {
                    $output .= '<a class="button one-date-term-filter filter-term-id-'.$c->term_id.' filter-term-slug-'.$c->slug.'" term="'.$c->term_id.'">';
                    $img = get_term_meta($c->term_id, 'term-image', true);
					if ($img) $output .= wp_get_attachment_image($img, 'full', false, ['class' => 'term-image']);
					$output .= '<span class="term-name">'.$c->name.'</span></a>';
                }
                $output .= '</div>';
            }
        }

        $output .= '<div class="vem-calendar'.($class ? ' '.$class : '').'" id="vem-calendar-'.$a['id'].'" vem-event="'.$a['event'].'" vem-calendar-stack="'.$a['stack'].'" vem-calendar-id="'.$a['id'].'" vem-calendar-unique="'.$u.'" vem-admin-url="'.admin_url().'admin-ajax.php" vem-click-action="'.$clickaction.'" vem-show-tickets="'.$showtickets.'" vem-no-events-message="'.esc_html($noEventsMessage).'" vem-future-only="'.$displayonlyfuture.'" vem-start="'.$start.'"></div>';
        $output .= VentureUtility::ventureSpinner('vem-spinner-'.$u,'display:none;');
        $output .= '<div class="vem-single-event-dialog" vem-calendar-id="'.$u.'" title="Event Title"></div>';
        return $output;
    }

    function shortcodeCalendarKey( $atts ) {
        $a = shortcode_atts( array(
            'id' => 0
        ), $atts );

        if ($a['id']==0) {
            $cats = get_terms('event_category',array('hide_empty' => true, 'fields' => 'all'));
        } else {
            $cats = wp_get_post_terms($a['id'],'event_category',array('fields' => 'all'));
        }

        $output = '<ul class="vem-calendar-key" id="vem-calendar-key-'.$a['id'].'">';
        foreach ($cats as $c) {
            $color = get_term_meta($c->term_id, 'color', true);
            $output .= '<li><i class="fa fa-square" style="color:'.$color.';"></i> '.$c->name.'</li>';
        }
        $output .= '</ul>';

        return $output;
    }

    function shortcodeSearch( $atts ) {
        $a = shortcode_atts( array(
            'id' => 0
        ), $atts );

        if ($a['id'] == 0) return ''; // Require this ID
        $listing = get_post($a['id']);
        if ($listing->post_type != 'event_listing') return '';

        $venture = VentureFramework::getInstance('venture-event-listing');
        $criteria = apply_filters('vem-event-listing-search-criteria', $venture->getOption('search-filters', $a['id']), $a['id']);

        $start = $venture->getOption('search-date-start-default', $a['id']);
        $end = $venture->getOption('search-date-end-default', $a['id']);

        $c = (in_array('category', $criteria)) ? wp_get_post_terms($a['id'],'event_category',array('fields' => 'ids')) : [];
        $s = (in_array('season', $criteria)) ? wp_get_post_terms($a['id'],'event_season',array('fields' => 'ids')) : [];
        $v = (in_array('venue', $criteria)) ? wp_get_post_terms($a['id'],'event_venue',array('fields' => 'ids')) : [];
        $oc = (in_array('oc', $criteria)) ? wp_get_post_terms($a['id'],'occurrence_category',array('fields' => 'ids')) : [];

        $formArgs = apply_filters('vem-search-form-args', [
            'id' => $a['id'],
            'action' => $venture->getOption('search-target-url', $a['id']),
            'categories' => $c,
            'seasons' => $s,
            'venues' => $v,
            'oc' => $oc,
            'criteria' => $criteria,
            'start' => $start,
            'end' => $end
        ]);

        $output = $this->getSearchForm($formArgs);

        return $output;
    }

    function getSearchForm($args) {

        $defaults = apply_filters('vem-search-form-defaults', [
            'id' => 0,
            'action' => '',
            'categories' => [],
            'seasons' => [],
            'venues' => [],
            'oc' => [],
            'start' => '-30',
            'end' => '30'
        ]);

        $a = wp_parse_args($args, $defaults);

        $output = '<form class="vem-search" id="vem-search-'.$a['id'].'" ';
        $output .= ($a['action'] != '') ? 'action="'.$a['action'].'" ' : '';
        $output .= 'method="get"><input type="hidden" name="vemsearch[listing]" value="'.$a['id'].'" />';

        if (in_array('keyword', $a['criteria'])) {
            $output .= '<div class="vem-form-field vem-text vem-keyword"><label for="vemsearch[keyword]">Keyword</label><input type="text" value="'.$this->searchArgs['keyword'].'" name="vemsearch[keyword]" /></div>';
        }

        if (in_array('dates', $a['criteria'])) {
            $venture = VentureFramework::getInstance('venture-event-system');
            $useNative = $venture->getOption('use-native-datepicker');

            wp_enqueue_script('vem-search');
            wp_localize_script( 'vem-search', 'ventureSearchSettings', [
                'defaultStartDate' => $a['start'],
                'defaultEndDate' => $a['end'],
                'useNativeDatepicker' => $useNative ? 1 : 0
            ]);
            wp_enqueue_style('jquery-ui-base');

            $output .= '<div class="vem-form-field vem-text vem-dates"><label for="vemsearch[start]">Date Range</label>';
            if ($useNative) {
                $output .= '<input class="datepicker start" type="date" value="'.$this->searchArgs['start'].'" name="vemsearch[start]" autocomplete="off" />';
                $output .= '<input class="datepicker end" type="date" value="'.$this->searchArgs['end'].'" name="vemsearch[end]" autocomplete="off" />';
            } else {
                $output .= '<input class="datepicker start" type="text" value="'.$this->searchArgs['start'].'" name="vemsearch[start]" autocomplete="off" />';
                $output .= '<input class="datepicker end" type="text" value="'.$this->searchArgs['end'].'" name="vemsearch[end]" autocomplete="off" />';
            }
            $output .= '</div>';
        }

        if (in_array('category', $a['criteria'])) {
            $categorySelect = wp_dropdown_categories([
                'show_option_all' => 'Any Category',
                'orderby' => 'name',
                'include' => $a['categories'],
                'echo' => false,
                'hierarchical' => true,
                'name' => 'vemsearch[category]',
                'taxonomy' => 'event_category',
                'selected' => $this->searchArgs['category']
            ]);
            $output .= '<div class="vem-form-field vem-select vem-categories"><label for="vemsearch[category]">Category</label>'.$categorySelect.'</div>';
        }
        
        if (in_array('season', $a['criteria'])) {
            $seasonSelect = wp_dropdown_categories([
                'show_option_all' => 'Any Season',
                'orderby' => 'name',
                'include' => $a['seasons'],
                'echo' => false,
                'hierarchical' => true,
                'name' => 'vemsearch[season]',
                'taxonomy' => 'event_season',
                'selected' => $this->searchArgs['season']
            ]);
            $output .= '<div class="vem-form-field vem-select vem-seasons"><label for="vemsearch[season]">Season</label>'.$seasonSelect.'</div>';
        }

        if (in_array('venue',$a['criteria'])) {
            $venueSelect = wp_dropdown_categories([
                'show_option_all' => 'Any Venue',
                'orderby' => 'name',
                'include' => $a['venues'],
                'echo' => false,
                'hierarchical' => true,
                'name' => 'vemsearch[venue]',
                'taxonomy' => 'event_venue',
                'selected' => $this->searchArgs['venue']
            ]);
            $output .= '<div class="vem-form-field vem-select vem-venues"><label for="vemsearch[venue]">Venue</label>'.$venueSelect.'</div>';
        }

        if (in_array('tag',$a['criteria'])) {
            $tagSelect = wp_dropdown_categories([
                'show_option_all' => 'Any Tag',
                'orderby' => 'name',
                'echo' => false,
                'hierarchical' => true,
                'name' => 'vemsearch[tag]',
                'taxonomy' => 'post_tag',
                'selected' => $this->searchArgs['tag']
            ]);
            $output .= '<div class="vem-form-field vem-select vem-tags"><label for="vemsearch[tag]">Tag</label>'.$tagSelect.'</div>';
        }

        if (in_array('oc',$a['criteria'])) {
            $ocSelect = wp_dropdown_categories([
                'show_option_all' => 'Any Occurrence Category',
                'orderby' => 'name',
                'hide_empty' => false,
                'echo' => false,
                'hierarchical' => true,
                'name' => 'vemsearch[oc]',
                'taxonomy' => 'occurrence_category',
                'selected' => $this->searchArgs['oc']
            ]);
            $output .= '<div class="vem-form-field vem-select vem-ocs"><label for="vemsearch[oc]">Occurrence Category</label>'.$ocSelect.'</div>';
        }

        $output = apply_filters('vem-search-form-extend', $output, $a, $this->searchArgs);

        $output .= '<div class="vem-form-field vem-submit"><input type="submit" value="Search"></div>';
        $output .= '</form>';

        return $output;
    }

    function shortcodeListing($atts, $content = null) {

        $a = shortcode_atts( array(
            'id' => 0
        ), $atts );

        // We really need an ID
        if ($a['id'] == 0) return '';

        wp_enqueue_style('vem-listing');

        global $wpdb, $ventureEventSystem;
        $venture = VentureFramework::getInstance('venture-event-listing');
        $table = $wpdb->prefix . VEM_EVENT_DATES_TABLE;
        $vem3 = new VentureEventManager3();

        $isSearch = $venture->getOption('is-search', $a['id']);
        $vem3->setContext($isSearch ? 'search-listing' : 'shortcode-listing');
        $vem3->setListingId($a['id']);

        $output = '';

        $c = wp_get_post_terms($a['id'],'event_category',array('fields' => 'ids'));
        $s = wp_get_post_terms($a['id'],'event_season',array('fields' => 'ids'));
        $v = wp_get_post_terms($a['id'],'event_venue',array('fields' => 'ids'));
        $oc = wp_get_post_terms($a['id'],'occurrence_category',array('fields' => 'ids'));
        if (taxonomy_exists('media_type')) {
            $m = wp_get_post_terms($a['id'],'media_type',array('fields' => 'ids'));
        } else {
            $m = [];
        }
        $t = [];

        $class = apply_filters('vem-event-listing-class', $venture->getOption('listing-css-class', $a['id']), $a['id']);
        $customLayout = $venture->getOption('use-custom', $a['id']);
        $toShow = $venture->getOption('listing-to-show', $a['id']) ?: 'future';
        $maxToShow = $venture->getOption('listing-max-in-list', $a['id']) ?: 0;
        $datetimeFormat = $venture->getOption('listing-date-format-override', $a['id']) ?: ''; // legacy name was date, should have been datetime
        $dateFormat = $venture->getOption('listing-date-format-override-date-only', $a['id']) ?: '';
        $timeFormat = $venture->getOption('listing-date-format-override-time-only', $a['id']) ?: '';
        $daysRecent = $venture->getOption('listing-days-recent', $a['id']) ?: 10;
        $toggleAfter = $venture->getOption('listing-toggle-max', $a['id']) ?: 0;
        $toggleMessage = $venture->getOption('listing-toggle-text', $a['id']) ?: 'view more event dates';
        $archive = ($venture->getOption('listing-archives', $a['id']) == 'true');
        $grouped = ($venture->getOption('listing-grouped', $a['id']) == 'grouped');
        $includeMoreDetailsLink = $venture->getOption('listing-include-more-details', $a['id']);
        $moreDetailsText = $venture->getOption('listing-more-details-text', $a['id']) ?: 'more details';
        $includeNoEventsText = $venture->getOption('listing-include-no-events', $a['id']);
        $noEventsText = $venture->getOption('listing-no-events-message', $a['id']) ?: 'No events to show';
        $aggregate = ($venture->getOption('listing-order-date', $a['id']) == 'earliest') ? 'min' : 'max';
        $displayOrder = $venture->getOption('listing-order', $a['id']);
        $pagination = $venture->getOption('listing-pagination', $a['id']);
        $pageSize = $venture->getOption('listing-page-size', $a['id']);
        $pageRequested = intval($_GET['p'.$a['id']] ?? 1);
        $isDateSearch = false;
        if ($venture->getOption('use-custom', $a['id'])) {
            $pageChunks = $venture->getOption('listing-event-fields', $a['id']);
            $dateChunks = $venture->getOption('listing-occurrence-fields', $a['id']);
            $vem3->setCustomChunks(true, $pageChunks, $dateChunks);
        } else {
            $vem3->setCustomChunks(false);
        }

        if ($grouped) {
            if ($toShow == 'future') {
                $orderby = "(select {$aggregate}(occurrences.start_time) from {$table} as occurrences where occurrences.post_ID = events.ID and occurrences.start_time > ".current_time('timestamp', true).") {$displayOrder}, events.post_title";
            } else {
                $orderby = "(select {$aggregate}(occurrences.start_time) from {$table} as occurrences where occurrences.post_ID = events.ID) {$displayOrder}, events.post_title";
            }
            $orderby = apply_filters('vem_event_order_by', $orderby, $toShow, $aggregate, $displayOrder);
            $vem3->setEventsOrderby($orderby);
            $vem3->setDatesOrder('asc');
        } else {
            $vem3->setDatesOrder($displayOrder);
        }

        if ($isSearch) { 

            // Do we have search terms that match this listing?
            // For now, we are going to always show results and later make it configurable what behavior we choose
            $doSearch = true; // ($a['id'] == $this->searchArgs['listing']); 

            $autoform = $venture->getOption('search-autoform', $a['id']);
            if ($autoform) {
                $output .= $this->shortcodeSearch($a);
            }

            if ($doSearch) {
                $criteria = apply_filters('vem-event-listing-search-criteria', $venture->getOption('search-filters', $a['id']), $a['id']);

                if (in_array('keyword', $criteria) && !empty($this->searchArgs['keyword'])) $vem3->setKeyword($this->searchArgs['keyword']);
                if (in_array('category', $criteria) && !empty($this->searchArgs['category'])) $c = [(int)$this->searchArgs['category']];
                if (in_array('season', $criteria) && !empty($this->searchArgs['season'])) $s = [(int)$this->searchArgs['season']];
                if (in_array('venue', $criteria) && !empty($this->searchArgs['venue'])) $v = [(int)$this->searchArgs['venue']];
                if (in_array('tag', $criteria) && !empty($this->searchArgs['tag'])) $t = [(int)$this->searchArgs['tag']];
                if (in_array('oc', $criteria) && !empty($this->searchArgs['oc'])) $oc = [(int)$this->searchArgs['oc']];
                if (in_array('dates', $criteria) && (!empty($this->searchArgs['start']) || !empty($this->searchArgs['end']))) {
                    $isDateSearch = true;
                    $futureStartTime = $ventureEventSystem->getStorableTime($this->searchArgs['start']);
                }
            } else {
                $output .= '<div class="vem-search-pending" id="'.$a['id'].'">Perform a search to view results</div>';
                return $output;
            }
        }

        if ($isDateSearch) {
            $toShow = 'date-search';
        }

        switch ($toShow) {
            case 'recent':
                $vem3->setOccurrenceRange($vem3::RECENT);
                break;

            case 'all':
                $vem3->setOccurrenceRange($vem3::ALL);
                break;

            case 'date-search':
                // Set the date range here from search args
                if (empty($this->searchArgs['start'])) {
                    $start = 0;
                } else {
                    $start = $ventureEventSystem->getStorableTime($this->searchArgs['start']);
                }

                if (empty($this->searchArgs['end'])) {
                    $end = PHP_INT_MAX; // 2038, so we're good until we start getting close
                } else {
                    // Adding a full day of seconds so that end time is inclusive in search
                    $end = $ventureEventSystem->getStorableTime($this->searchArgs['end'])+86400;
                }

                $vem3->setOccurrenceRange($vem3::DATES, $start, $end);
                break;

            case 'future':
            default:
                $vem3->setOccurrenceRange($vem3::UPCOMING);
                break;
        }

        if (sizeof($c) > 0) $vem3->setTaxonomyCriterion('event_category', $c); 
        if (sizeof($s) > 0) $vem3->setTaxonomyCriterion('event_season', $s); 
        if (sizeof($v) > 0) $vem3->setTaxonomyCriterion('event_venue', $v);
        if (sizeof($t) > 0) $vem3->setTaxonomyCriterion('post_tag', $t);
        if (sizeof($oc) > 0) $vem3->setTaxonomyCriterion('occurrence_category', $oc);
        if (sizeof($m) > 0) $vem3->setTaxonomyCriterion('media_type', $m);

        $vem3->setArchives($archive);
        $vem3->setDatesOrder($displayOrder);
        $vem3->setHowRecent($daysRecent);
        $vem3->setDatetimeFormatOverride($datetimeFormat, $dateFormat, $timeFormat);
        $vem3->setGrouped($grouped);

        if ($pagination) {
            $count = $grouped ? $vem3->getEventsCount() : $vem3->getDatesCount();
            $pages = ceil($count / $pageSize);
            $currentPage = max(min($pageRequested, $pages), 1);

            if ($grouped) {
                $vem3->setEventsLimit($pageSize);
                $vem3->setEventsOffset(($currentPage-1) * $pageSize);
            } else {
                $vem3->setDatesLimit($pageSize);
                $vem3->setDatesOffset(($currentPage-1) * $pageSize);                
            }
        } else {
            if ($grouped) {
                $vem3->setEventsLimit($maxToShow);
            } else {
                $vem3->setDatesLimit($maxToShow);               
            }
        }

        $output .= '<div id="vem-listing-'.$a['id'].'" class="vem-listing'.($class ? ' '.$class : '').'" vem-listing-id="'.$a['id'].'">';
        if ($grouped) {
            $vem3->setToggle($toggleAfter, $toggleMessage);
            $vem3 = apply_filters('vem-grouped-event-content-prep', $vem3, $a['id'], $this->searchArgs, $atts);
            $output .= $vem3->getGroupedEventContent($includeMoreDetailsLink, $moreDetailsText, $includeNoEventsText, $noEventsText);
        } else {
            $vem3 = apply_filters('vem-listing-event-content-prep', $vem3, $a['id'], $this->searchArgs, $atts);
            $output .= $vem3->getListingEventContent($includeMoreDetailsLink, $moreDetailsText, $includeNoEventsText, $noEventsText);
        }
        $output .= '</div>';

        if ($pagination && ($pages > 1)) {
            $query = $_GET;
            $output .= '<div class="vem-paginator">';
            if ($currentPage != 1) {
                $query['p'.$a['id']]='1'; $newQuery = http_build_query($query);
                $output .= '<a class="vem-page-first" href="'.get_page_link().'?'.$newQuery.'">&lt;&lt;</a>';
                $query['p'.$a['id']]=max(1, $currentPage - 1); $newQuery = http_build_query($query);
                $output .= '<a class="vem-page-prev" href="'.get_page_link().'?'.$newQuery.'">&lt;</a>';
            }
            if ($currentPage != $pages) {
                $query['p'.$a['id']]=min($pages, $currentPage + 1); $newQuery = http_build_query($query);
                $output .= '<a class="vem-page-next" href="'.get_page_link().'?'.$newQuery.'">&gt;</a>';
                $query['p'.$a['id']]=$pages; $newQuery = http_build_query($query);
                $output .= '<a class="vem-page-last" href="'.get_page_link().'?'.$newQuery.'">&gt;&gt;</a>';
            }
            $output .= '</div>';    
        }

        return do_shortcode($output);
    }

    /* ============= Two Columns ============= */

    function twocol_one($atts, $content = null) {
        return '<div class="twocol-one">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function twocol_one_last($atts, $content = null) {
        return '<div class="twocol-one last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    /* ============= Three Columns ============= */

    function threecol_one($atts, $content = null) {
        return '<div class="threecol-one">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function threecol_one_last($atts, $content = null) {
        return '<div class="threecol-one last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function threecol_two($atts, $content = null) {
        return '<div class="threecol-two">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function threecol_two_last($atts, $content = null) {
        return '<div class="threecol-two last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    /* ============= Four Columns ============= */

    function fourcol_one($atts, $content = null) {
        return '<div class="fourcol-one">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function fourcol_one_last($atts, $content = null) {
        return '<div class="fourcol-one last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function fourcol_two($atts, $content = null) {
        return '<div class="fourcol-two">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function fourcol_two_last($atts, $content = null) {
        return '<div class="fourcol-two last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function fourcol_three($atts, $content = null) {
        return '<div class="fourcol-three">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function fourcol_three_last($atts, $content = null) {
        return '<div class="fourcol-three last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    /* ============= Five Columns ============= */

    function fivecol_one($atts, $content = null) {
        return '<div class="fivecol-one">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function fivecol_one_last($atts, $content = null) {
        return '<div class="fivecol-one last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function fivecol_two($atts, $content = null) {
        return '<div class="fivecol-two">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function fivecol_two_last($atts, $content = null) {
        return '<div class="fivecol-two last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function fivecol_three($atts, $content = null) {
        return '<div class="fivecol-three">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function fivecol_three_last($atts, $content = null) {
        return '<div class="fivecol-three last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function fivecol_four($atts, $content = null) {
        return '<div class="fivecol-four">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function fivecol_four_last($atts, $content = null) {
        return '<div class="fivecol-four last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }


    /* ============= Six Columns ============= */

    function sixcol_one($atts, $content = null) {
        return '<div class="sixcol-one">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function sixcol_one_last($atts, $content = null) {
        return '<div class="sixcol-one last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function sixcol_two($atts, $content = null) {
        return '<div class="sixcol-two">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function sixcol_two_last($atts, $content = null) {
        return '<div class="sixcol-two last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function sixcol_three($atts, $content = null) {
        return '<div class="sixcol-three">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function sixcol_three_last($atts, $content = null) {
        return '<div class="sixcol-three last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function sixcol_four($atts, $content = null) {
        return '<div class="sixcol-four">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function sixcol_four_last($atts, $content = null) {
        return '<div class="sixcol-four last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }

    function sixcol_five($atts, $content = null) {
        return '<div class="sixcol-five">' . VentureUtility::removeWpAutoP($content) . '</div>';
    }

    function sixcol_five_last($atts, $content = null) {
        return '<div class="sixcol-five last">' . VentureUtility::removeWpAutoP($content) . '</div><div class="clear"></div>';
    }
}

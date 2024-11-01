<?php

class VentureEventSystemOptions
{

    public function __construct()
    {

        // Add options page and post type metaboxes, using Venture Framework
        // We don't use VF for event because it would make our fast direct queries for event
        // metadata unworkable, thus making those queries less-fast
        add_action('vf_create_options', [$this, 'addOptions']);

        // Set up metaboxes on event post, not using VF
        add_action('admin_head', [$this, 'removeEventVenueMetabox']);
        add_action('add_meta_boxes_event', [$this, 'addEventMetaboxes']);

        // Event save of metadata
        add_action('save_post', [$this, 'saveMetadata']);
        add_action('save_post', [$this, 'saveOccurrences']);
        add_action('save_post', [$this, 'maybeChangeSlug']);

        // Venue taxonomy add-ons
        add_action('event_venue_add_form', [$this, 'addVenueOptions'], 10, 2);
        add_action('event_venue_edit_form', [$this, 'addVenueOptions'], 10, 2);
        add_action('create_event_venue', [$this, 'saveVenueOptions'], 10, 2);
        add_action('edit_event_venue', [$this, 'saveVenueOptions'], 10, 2);

        // Category taxonomy add-ons
        add_action('event_category_add_form', [$this, 'addCategoryOptions'], 10, 2);
        add_action('event_category_edit_form', [$this, 'addCategoryOptions'], 10, 2);
        add_action('create_event_category', [$this, 'saveCategoryOptions'], 10, 2);
        add_action('edit_event_category', [$this, 'saveCategoryOptions'], 10, 2);

        // Occurrence category add-ons
        add_action('occurrence_category_add_form_fields', [$this, 'addDateTermOptions'], 10, 2);
		add_action('occurrence_category_edit_form_fields', [$this, 'addDateTermOptions'], 10, 2);
		add_action('create_occurrence_category', [$this, 'saveDateTermOptions'], 10, 2);
        add_action('edit_occurrence_category', [$this, 'saveDateTermOptions'], 10, 2);
        add_action('admin_enqueue_scripts', [$this,'addDateTermAdminScripts']);
        add_filter('manage_edit-occurrence_category_columns', [$this, 'customTermImageColumns']);
        add_filter('manage_occurrence_category_custom_column', [$this, 'customTermImageColumn'], 10, 3);

    }

    public function addOptions()
    {
        $venture = VentureFramework::getInstance('venture-event-system');

        $panel = $venture->createAdminPanel(array(
            'name' => 'Venture Options',
            'id' => 'venture-options'
        ));

        global $ventureEventSystem;
        $ventureEventSystem->setOptionsPanel('general', $panel);

        $tab = $panel->createTab([
            'name' => 'General Settings'
        ]);

        $tab->createOption([
            'name'    => 'Default Currency Symbol',
            'id'      => 'default-currency-symbol',
            'type'    => 'select',
            'options' => [
                '$'   => '$ (Dollar)',
                '€' => '€ (Euro)',
                '£'  => '£ (Pound)',
                '¥'  => '¥ (Yen)',
            ],
            'desc'    => 'Select the default currency symbol for ticket prices.',
            'default' => '$',
        ]);

        $tab->createOption([
            'name'    => 'Default Time Display',
            'id'      => 'default-time-display',
            'type'    => 'select',
            'options' => [
                '12' => '12 hour times',
                '24' => '24 hour times',
            ],
            'desc'    => 'Select the default display method for event start times.',
            'default' => '12',
        ]);

        $tab->createOption([
            'name'    => 'Time and Date Order',
            'id'      => 'default-time-date-order',
            'type'    => 'select',
            'options' => [
                'time' => 'Time, then date',
                'date' => 'Date, then time',
            ],
            'desc'    => 'When displaying event start times, select whether to display the time before or after the date.',
            'default' => 'time',
        ]);

        $tab->createOption([
            'name'    => 'Display End Times',
            'id'      => 'display-end-times',
            'type'    => 'select',
            'options' => [
                'no'  => 'Display only start times',
                'yes' => 'Display start and end times',
            ],
            'desc'    => 'When displaying event times, select whether to display just the start time or both start and end times.',
            'default' => 'no',
        ]);

        $tab->createOption([
            'name' => 'Native Datepickers on Search',
            'id' => 'use-native-datepicker',
            'type' => 'checkbox',
            'desc' => 'Use native datepickers on mobile for event listing search fields. This option may require additional styling, as it uses HTML5 date inputs.',
            'default' => 'false'
        ]);

        $tab->createOption([
            'name' => 'Buy Ticket Icon',
            'id'   => 'buy-ticket-icon',
            'type' => 'upload',
            'size' => 'full',
            'desc' => 'Upload an icon that can be used in place of the default CSS-based "Buy Ticket" label.',
        ]);

        $tab->createOption([
            'name' => 'Single Event Calendar',
            'id'   => 'single-event-calendar',
            'type' => 'select-posts',
            'post_type' => 'event_calendar',
            'post_status' => 'publish',
            'order_by' => 'post_title',
            'order' => 'asc',
            'desc' => 'Using the Single Event Calendar in layouts requires picking an event calendar definition'
        ]);

        $tab->createOption([
            'name' => 'Use Venture Gallery',
            'id'   => 'venture-gallery',
            'type' => 'checkbox',
            'default' => false,
            'desc' => 'Enabling Venture Gallery will load galleries with a masonry style and lightbox-style image popups'
        ]);

        do_action('vem-general-options-panel', $tab);

        $tab->createOption(array(
            'type' => 'save',
            'use_reset' => false
        ));

        $tab = $panel->createTab([
            'name' => 'Welcome'
        ]);

        if (class_exists('VentureEventManagerPro')) {

            ob_start();
            do_action('vem-update-notices');
            include('welcome/welcome-pro.php');
            $output = ob_get_clean();

            $tab->createOption([
                'type'   => 'custom',
                'custom' => $output
            ]);
        } else {
            ob_start();
            do_action('vem-update-notices');
            include('welcome/welcome.php');
            $output = ob_get_clean();

           $tab->createOption([
                'type'   => 'custom',
                'custom' => $output
            ]);
        }

        $panel = $venture->createAdminPanel(array(
            'name' => 'Layouts',
            'parent' => 'venture-options'
        ));
        $ventureEventSystem->setOptionsPanel('layout', $panel);

        $tab = $panel->createTab([
            'name' => 'Listings'
        ]);

        $tab->createOption(array(
            'name' => 'Event Fields',
            'id'   => 'index-page-fields',
            'type' => 'text',
            'desc' => 'Organize the items you want to have appear for each event on event listings.',
            'default' => 'title,excerpt,occurrences'
        ));

        $tab->createOption(array(
            'name' => 'Occurrence Fields',
            'id'   => 'index-page-occurrence-fields',
            'type' => 'text',
            'desc' => 'Organize the items you want to have appear for each occurrence of each event on event listings.',
            'default' => 'vem_dates_date_time'
        ));

        $tab->createOption(array(
            'type' => 'save',
            'use_reset' => false
        ));

        $tab = $panel->createTab([
            'name' => 'Single Page'
        ]);

        $tab->createOption([
            'name'    => 'Page Title',
            'id'      => 'single-event-page-title',
            'type'    => 'select',
            'options' => [
                'title'    => 'Event Title',
                'category' => 'Event Category',
                'none'     => 'No Title'
            ],
            'default' => 'title',
            'desc'    => 'Select what should appear as the page title on a single event page.'
        ]);

        $tab->createOption([
            'name'    => 'Page Title Category Display',
            'id'      => 'single-event-page-title-category-display',
            'type'    => 'select',
            'options' => [
                'top'  => 'Top level category only',
                'list' => 'Comma separated list of categories',
            ],
            'desc'    => 'When the single event page title is a category, select what should display.',
        ]);

        $tab->createOption(array(
            'name' => 'Event Fields',
            'id'   => 'single-page-fields',
            'type' => 'text',
            'desc' => 'Organize the items you want to have appear for an event on single event pages.',
            'default' => 'vem_event_details,occurrences'
        ));

        $tab->createOption(array(
            'name' => 'Occurrence Fields',
            'id'   => 'single-page-occurrence-fields',
            'type' => 'text',
            'desc' => 'Organize the items you want to have appear for each occurrence of an event on single event pages.',
            'default' => 'vem_dates_date_time'
        ));

        $tab->createOption(array(
            'type' => 'save',
            'use_reset' => false
        ));

        $venture = VentureFramework::getInstance('venture-event-listing');
        $eventListingShortcodeMetabox = $venture->createMetaBox([
            'name'      => 'Listing Shortcode',
            'post_type' => 'event_listing',
            'desc'      => 'Place this event listing on a page using the shortcode below',
            'priority'  => 'high',
        ]);

        $eventListingShortcodeMetabox->createOption([
            'type'   => 'custom',
            'custom' => '<div class="shortcode-wrapper" style="display:none">[vemlisting id="<span class="event-listing-id">0</span>"]</div>',
        ]);

        $eventSearchShortcodeMetabox = $venture->createMetaBox([
            'name'      => 'Search Shortcode',
            'post_type' => 'event_listing',
            'desc'      => 'Place a search box that leads to this event listing on a page using the shortcode below',
            'priority'  => 'high',
        ]);

        $eventSearchShortcodeMetabox->createOption([
            'type'   => 'custom',
            'custom' => '<div class="shortcode-wrapper" style="display:none">[vemsearch id="<span class="event-listing-id">0</span>"]</div>',
        ]);

        $eventListingSettingsMetabox = $venture->createMetaBox([
            'name'      => 'General Listing Settings',
            'post_type' => 'event_listing',
            'desc'      => 'Settings that apply to all event listings',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name' => 'Custom CSS Class',
            'id'   => 'listing-css-class',
            'type' => 'text',
            'desc' => 'This value will be added to container <div> of the event listing.'
        ]);

        $eventListingSettingsMetabox->createOption([
            'name'    => 'Occurrences to Show',
            'id'      => 'listing-to-show',
            'type'    => 'select',
            'options' => [
                'future' => 'Upcoming occurrences only',
                'recent' => 'Recently added occurrences',
                'all'    => 'All occurrences',
            ],
            'default' => 'future',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name'    => 'Occurrence Grouping',
            'id'      => 'listing-grouped',
            'type'    => 'select',
            'options' => [
                'full'    => 'Show all occurrences separately',
                'grouped' => 'Group occurrences by event',
            ],
            'default' => 'full',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name'    => 'Display Order',
            'id'      => 'listing-order',
            'type'    => 'select',
            'options' => [
                'asc'  => 'Ascending (chronological) order',
                'desc' => 'Descending (reverse chronological)',
            ],
            'default' => 'asc',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name'    => 'Event Archive Display',
            'id'      => 'listing-archives',
            'type'    => 'select',
            'options' => [
                'false' => 'Show all matching events',
                'true'  => 'Show only events marked for archive',
            ],
            'desc' => 'This option only applies to VEM Pro users',
            'default' => 'false',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name'    => 'How Many Days Counts as Recent?',
            'id'      => 'listing-days-recent',
            'type'    => 'number',
            'min'     => '1',
            'default' => '10',
            'max'     => '90',
            'unit'    => 'days',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name'    => 'Absolute Max Items',
            'id'      => 'listing-max-in-list',
            'type'    => 'number',
            'min'     => '0',
            'default' => '0',
            'max'     => '100',
            'unit'    => 'items',
            'desc'    => 'Leave at zero to have no limits, does not apply when pagination is on',
        ]);

        $eventListingSettingsMetabox = $venture->createMetaBox([
            'name'      => 'Date Formats',
            'post_type' => 'event_listing',
            'desc'      => 'Override the defaults for date display with these settings. See <a href="https://php.net/manual/en/function.date.php" target="_blank">this reference</a> for formats. This works best when you define all three, or none.',
            'priority'  => 'high',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name' => 'Override Date/Time Format',
            'id'   => 'listing-date-format-override',
            'type' => 'text',
            'desc' => 'Provide a date and time formatting string to use instead of the default options, blank for defaults.',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name' => 'Override Date Only Format',
            'id'   => 'listing-date-format-override-date-only',
            'type' => 'text',
            'desc' => 'Provide a date formatting string to use instead of the default options, blank for defaults.',
        ]);

        $eventListingSettingsMetabox->createOption([
            'name' => 'Override Time Only Format',
            'id'   => 'listing-date-format-override-time-only',
            'type' => 'text',
            'desc' => 'Provide a time formatting string to use instead of the default options, blank for defaults.',
        ]);

        $eventPaginationSettingsMetabox = $venture->createMetaBox([
            'name'      => 'Pagination Settings',
            'post_type' => 'event_listing',
            'desc'      => 'Settings that control pagination or overall limits on number of events or occurrences displayed',
        ]);

        $eventPaginationSettingsMetabox->createOption([
            'name'    => 'Use Pagination',
            'id'      => 'listing-pagination',
            'type'    => 'checkbox',
            'desc'    => 'When checked, breaks results into multiple pages',
            'default' => false,
        ]);

        $eventPaginationSettingsMetabox->createOption([
            'name'    => 'Items per Page',
            'id'      => 'listing-page-size',
            'type'    => 'number',
            'min'     => '1',
            'default' => '1',
            'max'     => '30',
            'unit'    => 'items',
            'desc'    => 'Only applies when pagination is on',
        ]);

        $eventListingGroupedMetabox = $venture->createMetaBox([
            'name'      => 'Grouped Listing Settings',
            'post_type' => 'event_listing',
            'desc'      => 'Settings that apply to event listings where occurrences are grouped by event',
        ]);

        $eventListingGroupedMetabox->createOption([
            'name'    => 'Date to Use for Ordering',
            'id'      => 'listing-order-date',
            'type'    => 'select',
            'options' => [
                'earliest' => 'Earliest occurrence date',
                'latest'   => 'Latest occurrence date',
            ],
            'default' => 'earliest',
        ]);

        $eventListingGroupedMetabox->createOption([
            'name'    => 'Max Occurrences Before Toggle',
            'id'      => 'listing-toggle-max',
            'type'    => 'number',
            'min'     => '0',
            'default' => '0',
            'max'     => '20',
            'unit'    => 'occurrences',
            'desc'    => 'Leave at zero to never put occurrences in a toggle section',
        ]);

        $eventListingGroupedMetabox->createOption([
            'name'    => 'Text on Toggle Section',
            'id'      => 'listing-toggle-text',
            'type'    => 'text',
            'default' => 'Show more dates',
        ]);

        $eventListingPostContentMetabox = $venture->createMetaBox([
            'name'      => 'Post-Content Settings',
            'post_type' => 'event_listing',
            'desc'      => 'Settings that determine content to display at the end of the event listing',
        ]);

        $eventListingPostContentMetabox->createOption([
            'name'    => 'Include More Details Link',
            'id'      => 'listing-include-more-details',
            'type'    => 'checkbox',
            'desc'    => 'When checked, the event or occurrence will include a link to the single event page',
            'default' => true,
        ]);

        $eventListingPostContentMetabox->createOption([
            'name'    => 'More Details Link Text',
            'id'      => 'listing-more-details-text',
            'type'    => 'text',
            'default' => 'more details',
        ]);

        $eventListingPostContentMetabox->createOption([
            'name'    => 'Include No Events Message',
            'id'      => 'listing-include-no-events',
            'type'    => 'checkbox',
            'desc'    => 'When checked, if there are no events or occurrences to display, display alternate text below',
            'default' => true,
        ]);

        $eventListingPostContentMetabox->createOption([
            'name'    => 'No Events Message',
            'id'      => 'listing-no-events-message',
            'type'    => 'text',
            'default' => 'No events to show',
        ]);

        $eventListingSearchMetabox = $venture->createMetaBox([
            'name'      => 'Search Settings',
            'post_type' => 'event_listing',
            'desc'      => 'Settings for listings used as search results.',
            'priority' => 'low'
        ]);
        $ventureEventSystem->setOptionsPanel('event-listings-search-metabox', $eventListingSearchMetabox);

        $eventListingSearchMetabox->createOption([
            'name'    => 'Use as Search Results',
            'id'      => 'is-search',
            'type'    => 'checkbox',
            'desc'    => 'When checked, this listing will only show events after a search is performed. The rest of these settings only apply if this is checked.',
            'default' => false,
        ]);

        $eventListingSearchMetabox->createOption([
            'name'    => 'Include Search Form',
            'id'      => 'search-autoform',
            'type'    => 'checkbox',
            'desc'    => 'Automatically include a search form above the results',
            'default' => false
        ]);

        $eventListingSearchMetabox->createOption([
            'name'    => 'Form Target URL',
            'id'      => 'search-target-url',
            'type'    => 'text',
            'default' => '',
            'desc' => 'When the search form is on a different page from results, enter the target URL to the search results. The target page should have the listing shortcode on it. Leave blank to use the same page for the form and results.'
        ]);

        $eventListingSearchMetabox->createOption([
            'name'    => 'Default Date Range Start',
            'id'      => 'search-date-start-default',
            'type'    => 'number',
            'min'     => '-365',
            'default' => '-30',
            'max'     => '365',
            'unit'    => 'days from current date',
            'desc'    => 'When the form loads, this sets what date will be used by default for date range start dates',
        ]);

        $eventListingSearchMetabox->createOption([
            'name'    => 'Default Date Range End',
            'id'      => 'search-date-end-default',
            'type'    => 'number',
            'min'     => '-365',
            'default' => '30',
            'max'     => '365',
            'unit'    => 'days from current date',
            'desc'    => 'When the form loads, this sets what date will be used by default for date range end dates',
        ]);

        $eventListingSearchMetabox->createOption([
            'name'    => 'Basic Search Filters',
            'id'      => 'search-filters',
            'type'    => 'multicheck',
            'default' => ['keyword'],
            'options' => [
                'keyword' => 'Keyword Text',
                'dates' => 'Date Range (overrides "Occurrences to Show" above)'
            ],
            'select_all' => true
        ]);

        $venture = VentureFramework::getInstance('venture-event-calendar');
        $eventCalendarShortcodeMetabox = $venture->createMetaBox([
            'name'      => 'Calendar Shortcode',
            'post_type' => 'event_calendar',
            'desc'      => 'Place this event calendar on a page using the shortcode below',
            'priority'  => 'high',
        ]);

        $eventCalendarShortcodeMetabox->createOption([
            'type'   => 'custom',
            'custom' => '<div class="shortcode-wrapper" style="display:none">[vemcalendar id="<span class="event-calendar-id">0</span>"]</div>',
        ]);

        $eventKeyShortcodeMetabox = $venture->createMetaBox([
            'name'      => 'Calendar Key Shortcode',
            'post_type' => 'event_calendar',
            'desc'      => 'Place a color-coded category key for this calendar on a page using the shortcode below',
            'priority'  => 'high',
        ]);

        $eventKeyShortcodeMetabox->createOption([
            'type'   => 'custom',
            'custom' => '<div class="shortcode-wrapper" style="display:none">[vemkey id="<span class="event-calendar-id">0</span>"]</div>',
        ]);

        $eventCalendarSettingsMetabox = $venture->createMetaBox([
            'name'      => 'Calendar Settings',
            'post_type' => 'event_calendar',
            'desc'      => 'General settings to guide calendar interaction and display'
        ]);

        $eventCalendarSettingsMetabox->createOption([
            'name' => 'Custom CSS Class',
            'id'   => 'calendar-css-class',
            'type' => 'text',
            'desc' => 'This value will be added to container <div> of the event calendar.'
        ]);

        $eventCalendarSettingsMetabox->createOption([
            'name'    => 'Event Click Action',
            'id'      => 'calendar-event-click-action',
            'type'    => 'select',
            'options' => [
                'popup' => 'Display event details in a popup',
                'panel' => 'Display event details in a panel appended below the calendar',
                'single' => 'Navigate directly to the single event page',
            ],
            'default' => 'popup',
        ]);

        $eventCalendarSettingsMetabox->createOption([
            'name'    => 'Buy Ticket Links',
            'id'      => 'calendar-buy-ticket-link',
            'type'    => 'select',
            'options' => [
                'no' => 'Do not show buy ticket links on calendar',
                'yes' => 'Show buy ticket links directly on calendar'
            ],
            'default' => 'no',
        ]);

        $eventCalendarSettingsMetabox->createOption([
            'name'    => 'Occurrence Visibility',
            'id'      => 'calendar-future-only',
            'type'    => 'select',
            'options' => [
                'no' => 'Show all event occurrences',
                'yes' => 'Only show future occurrences, hide past occurrences'
            ],
            'default' => 'no',
        ]);

        $eventCalendarSettingsMetabox->createOption([
            'name'    => 'Category Colors',
            'id'      => 'calendar-top-category',
            'type'    => 'select-categories',
            'taxonomy' => 'event_category',
            'desc' => 'Select a category to restrict color choices to only that category and its children',
            'default' => 0
        ]);

        $eventCalendarSettingsMetabox->createOption(array(
            'name' => 'No Events Message',
            'id'   => 'calendar-no-events-message',
            'type' => 'text',
            'default' => 'No events this month',
            'desc' => 'Text to display when no events are found for the current month'
        ));

        $eventCalendarSettingsMetabox->createOption([
            'name' => 'Occurrence Category Filter',
            'id' => 'calendar-use-date-term-filter',
            'type' => 'checkbox',
            'desc' => 'Include an occurrence category filter above the calendar',
            'default' => 'false'
        ]);

    }

    public function addEventMetaboxes() {
        add_meta_box( 
            '_vem_030_display_details_meta_box',
            __('Event Details'),
            [$this, 'addMetaboxDetails'],
            'event',
            'normal',
            'core'
        );
        add_meta_box( 
            '_vem_051_display_fields_meta_box',
            __(apply_filters('vem_field_set_metabox_title', 'Event Custom Field Set One', 1)),
            [$this, 'addMetaboxFieldSetOne'],
            'event',
            'normal',
            'core'
        );
        add_meta_box( 
            '_vem_052_display_fields_meta_box',
            __(apply_filters('vem_field_set_metabox_title', 'Event Custom Field Set Two', 2)),
            [$this, 'addMetaboxFieldSetTwo'],
            'event',
            'normal',
            'core'
        );
        add_meta_box( 
            '_vem_053_display_fields_meta_box',
            __(apply_filters('vem_field_set_metabox_title', 'Event Custom Field Set Three', 3)),
            [$this, 'addMetaboxFieldSetThree'],
            'event',
            'normal',
            'core'
        );
        add_meta_box( 
            '_vem_010_display_dates_meta_box',
            __('Event Dates'),
            [$this, 'addMetaboxOccurrences'],
            'event',
            'normal',
            'high'
        );
        add_meta_box( 
            '_vem_020_date_display_settings_meta_box',
            __('Occurrence Display Settings'),
            [$this, 'addMetaboxOccurrenceDisplay'],
            'event',
            'normal',
            'high'
        );
        add_meta_box( 
            '_vem_025_optional_tile_settings_meta_box',
            __('Title Settings'),
            [$this, 'addMetaboxTitle'],
            'event',
            'normal',
            'high'
        );
        add_meta_box( 
            '_vem_030_optional_tile_settings_meta_box',
            __('Event Transcript'),
            [$this, 'addMetaboxTranscript'],
            'event',
            'side',
            'default'
        );
    }

    public function removeEventVenueMetabox() {
        remove_meta_box( 'event_venuediv', 'event', 'side' );
    }

    public function addMetaboxDetails() {
        
        global $post;
        
        $vem_event_details = get_post_meta($post->ID, 'vem_event_details', true);
        
        wp_editor( $vem_event_details, 'details', $settings = array(
            'textarea_name' => 'vem_event_details'
        ));

    }

    public function addMetaboxOccurrenceDisplay() {
        
        global $post;
        
        $vem_max_occurrences = get_post_meta($post->ID, 'vem_max_occurrences', true);
        $vem_max_message = get_post_meta($post->ID, 'vem_max_message', true);
        $vem_homepage_title = get_post_meta($post->ID, 'vem_homepage_title', true);
        $showEndTimes = (get_option('vem_display_end_times', 'no') == 'yes');
        $vem_display_end_times = get_post_meta($post->ID, 'vem_display_end_times', true);

        // Defaults
        if (empty($vem_max_occurrences)) $vem_max_occurrences = 0;
        if (empty($vem_max_message)) $vem_max_message = 'view all event dates';
        if (empty($vem_homepage_title)) $vem_homepage_title = '';
        if (empty($vem_display_end_times)) $vem_display_end_times = 'default';
            
        $displayEndTimeSelect = [
            'default' => selected($vem_display_end_times, 'default', false),
            'no' => selected($vem_display_end_times, 'no', false),
            'yes' => selected($vem_display_end_times, 'yes', false)
        ];

echo <<<BLOCK
<table class="form-table vf-form-table" id="date-display-settings">
    <tbody>
        <tr class="row-1 odd" valign="top">
            <th class="first" scope="row">
                <label for="vem_display_end_times">
                    End Time Display
                </label>
            </th>
            <td class="second vf-select">
                <select class="vf-select" name="vem_display_end_times">
                    <option value="default" {$displayEndTimeSelect['default']}>
                        Use global settings
                    </option>
                    <option value="no" {$displayEndTimeSelect['no']}>
                        Display only start times
                    </option>
                    <option value="yes" {$displayEndTimeSelect['yes']}>
                        Display start and end times
                    </option>
                </select>
            </td>
        </tr>
        <tr class="row-2 even" valign="top">
            <th class="first" scope="row">
                <label for="venture-events_event-max-to-show">
                    Max Occurrences to Show
                </label>
            </th>
            <td class="second vf-number">
                <span class='number-slider'></span>
                <input class="small-text" id="vem_max_occurrences" max="100" min="0" name="vem_max_occurrences" placeholder="" step="1" type="number" value="{$vem_max_occurrences}">
                    occurrences
                    <p class="description">
                        Leave at zero to have no limits
                    </p>
                </input>
            </td>
        </tr>
        <tr class="row-3 odd" valign="top">
            <th class="first" scope="row">
                <label for="vem_max_message">
                    More Occurrences Message
                </label>
            </th>
            <td class="second vf-text">
                <input class="regular-text" id="vem_max_message" maxlength="" name="vem_max_message" placeholder="" type="text" value="{$vem_max_message}">
                </input>
            </td>
        </tr>
        <tr class="row-4 even" valign="top">
            <th class="first" scope="row">
                <label for="vem_homepage_title">
                    Title for Home Page
                </label>
            </th>
            <td class="second vf-text">
                <input class="regular-text" id="vem_homepage_title" maxlength="" name="vem_homepage_title" placeholder="" type="text" value="{$vem_homepage_title}">
                    <p class="description">
                        The event title will be used unless you provide an alternative here
                    </p>
                </input>
            </td>
        </tr>
    </tbody>
</table>
BLOCK;

    }

    public function addMetaboxTitle() {
        
        global $post;
        
        $vem_pretitle = get_post_meta($post->ID, 'vem_pretitle', true);
        $vem_posttitle = get_post_meta($post->ID, 'vem_posttitle', true);

        // Defaults
        if (empty($vem_pretitle)) $vem_pretitle = '';
        if (empty($vem_posttitle)) $vem_posttitle = '';

    echo <<<BLOCK
<table class="form-table vf-form-table" id="optional-title-settings">
    <tbody>
        <tr class="row-1 odd" valign="top">
            <th class="first" scope="row">
                <label for="vem_pretitle">
                    Pre-title
                </label>
            </th>
            <td class="second vf-text">
                <input class="regular-text" id="vem_pretitle" maxlength="" name="vem_pretitle" placeholder="" type="text" value="{$vem_pretitle}">
                </input>
            </td>
        </tr>
        <tr class="row-2 even" valign="top">
            <th class="first" scope="row">
                <label for="vem_posttitle">
                    Post-title
                </label>
            </th>
            <td class="second vf-text">
                <input class="regular-text" id="vem_posttitle" maxlength="" name="vem_posttitle" placeholder="" type="text" value="$vem_posttitle">
                </input>
            </td>
        </tr>
    </tbody>
</table>
BLOCK;

    }

    public function addMetaboxTranscript() {
        global $post;

        $value = get_post_meta($post->ID, 'vem_event_transcript', true);
        if (!$value) $value = 0;

        $transcripts = get_posts([
            'post_type' => 'event_transcript',
            'nopaging' => true,
            'orderby' => 'post_title',
            'order' => 'asc'
        ]);

        $options = '';
        $hasValid = false;
        foreach($transcripts as $t) {
            $selected = '';
            if ($t->ID == $value) {
                $hasValid == true;
                $selected = ' selected';
            }
            $options .= '<option value="'.$t->ID.'"'.$selected.'>'.$t->post_title.'</option>';
        }

        $selected = $hasValid ? '' : ' selected';
        echo <<<BLOCK
            <select class="vf-select" name="vem_event_transcript">
                <option value="0"{$selected}>No transcript</option>
                {$options}
            </select>
BLOCK;
    }

    public function addMetaboxFieldSetOne() {
        $this->addMetaboxFieldSet('one');
    }

    public function addMetaboxFieldSetTwo() {
        $this->addMetaboxFieldSet('two');
    }

    public function addMetaboxFieldSetThree() {
        $this->addMetaboxFieldSet('three');
    }

    public function addMetaboxFieldSet($set) {
        
        global $post;

        $current_set = get_post_meta($post->ID, 'vem_fields_set_'.$set);

        $current_set = !empty($current_set[0]) ? $current_set[0] : [
            0 => [
                'key' => '',
                'value' => ''
            ]
        ];

        echo '<p>Enter label/value pairs of custom data. Drag fields to reorder.</p>';
        echo '<div class="set-ui">';
        echo '<input class="set-label" value="'.$set.'" style="display:none;">';
        echo '<textarea class="set-value-raw" style="width:100%; margin-bottom:10px; display:none;" rows="5">'.json_encode($current_set).'</textarea>';
        echo '<div class="set-display"></div>';
        echo '<button type="button" class="add-field">Add Field</button>';
        echo '</div>';
    }

    public function addMetaboxOccurrences() {
        
        global $post, $wpdb, $ventureEventSystem;
        $venture = VentureFramework::getInstance('venture-event-system');

        // Setup dynamic venues
        $venues = get_terms('event_venue', array('hide_empty' => false));
        $venue_options = array();
        if (!empty($venues)) {
            foreach ($venues as $venue) {
                $venue_options[$venue->term_id] = $venue->name;
            }
        }
        
        $defaultCurrencySymbol = $venture->getOption('default-currency-symbol') ?: '$';
        $defaultCurrencySymbol = ($defaultCurrencySymbol != '$') ? '&' . $defaultCurrencySymbol . ';' : $defaultCurrencySymbol;
        $globalShowEndTimes = (($venture->getOption('display-end-times') ?: 'no') == 'yes');

        $postShowEndTimes = get_post_meta($post->ID, 'vem_display_end_times', true);
        $showEndTimes = ($postShowEndTimes == "yes" || ($postShowEndTimes == "default" && $globalShowEndTimes));

        // We don't want to limit dates retrieved based on status
        add_filter('vem_date_allowed_event_status', function($statuses) { return []; });
        $vem3 = new VentureEventManager3();
        $vem3->setContext('event-dates-metabox');
        $vem3->setEventId($post->ID);
        $vem3->retrieveDates();
        $occurrences = $vem3->getOccurrences();

        if (empty($occurrences)) {
            $ts = current_time('timestamp',1);
            $ts = $ts + (3600 - ($ts % 3600));
            $occurrences = array(
                array(
                    'date' => null,
                    'start_time' => $ts,
                    'end_time' => $ts+7200,
                    'price' => null,
                    'url' => null,
                    'venue_id' => null,
                    'note' => '',
                    'buytext' => '',
                    'ticket_price_from' => '',
                    'ticket_price_to' => '',
                    'tickets' => '',
                    'buytext2' => '',
                    'ticket2_price_from' => '',
                    'ticket2_price_to' => '',
                    'tickets2' => '',
                    'gsd_status' => 'EventScheduled',
                    'gsd_url' => '',
                    'gsd_previous_start_time' => $ts,
                    'occurrence_category_ids' => '',
                    'occurrence_categories' => '',
                    'isNew' => true
                )
            );
        }

        // Get a generic terms box for checklist style
        $terms = wp_terms_checklist(0, [
            'taxonomy' => 'occurrence_category',
            'echo' => false
        ]);
        // Normalize the names
        $terms = str_replace('occurrence_category', 'vem_dates_occurrence_category', $terms);

        foreach ($occurrences as $i => $o) {
            if ($o['isNew'] ?? false) {
                $headerTitle = 'New Occurrence';
                $hiddenIdField = '';
            } else {
                $headerTitle = $ventureEventSystem->getFormattedDateTime($o['start_time'], true, 'Y-m-d, g:ia');
                if ($showEndTimes == 'yes') $headerTitle .= $ventureEventSystem->getFormattedDateTime($o['end_time'], true, '-g:ia');
                $headerTitle .= ' at '.$venue_options[$o['venue_id']];
                $hiddenIdField = '<input type="hidden" name="vem_dates['.$i.'][vem_dates_id]" value="'.$o['occurrence_id'].'">';
            }
            
            $displayDate = $ventureEventSystem->getFormattedDateTime($o['start_time'], true, 'm/d/Y');
            $displayStartTime = $ventureEventSystem->getFormattedDateTime($o['start_time'], false, '', 'g:i', '');
            $displayStartMeridian = $ventureEventSystem->getFormattedDateTime($o['start_time'], false, '', 'A', '');
            $displayEndTime = $ventureEventSystem->getFormattedDateTime($o['end_time'], false, '', 'g:i', '');
            $displayEndMeridian = $ventureEventSystem->getFormattedDateTime($o['end_time'], false, '', 'A', '');

            $prevStart = $o['gsd_previous_start_time'] ?? $o['start_time'];
            $displayPreviousDate = $ventureEventSystem->getFormattedDateTime($prevStart, true, 'm/d/Y');
            $displayPreviousTime = $ventureEventSystem->getFormattedDateTime($prevStart, false, '', 'g:i', '');
            $displayPreviousMeridian = $ventureEventSystem->getFormattedDateTime($prevStart, false, '', 'A', '');

            $displayTerms = str_replace('tax_input', 'vem_dates['.$i.']', $terms);
            if ($o['occurrence_category_ids'] != '') {
                $dateCats = explode(',',$o['occurrence_category_ids']);
                foreach ($dateCats as $d) {
                    $displayTerms = str_replace('<input value="'.$d.'"', '<input value="'.$d.'" checked', $displayTerms);
                }
            }

            echo VentureUtility::getTemplate('one-occurrence', [
                'i' => $i,
                'headerTitle' => $headerTitle,
                'hiddenIdField' => $hiddenIdField,
                'venues' => $venue_options,
                'venue' => $o['venue_id'],
                'note' => $o['note'],
                'displayDate' => $displayDate,
                'displayStartTime' => $displayStartTime,
                'displayStartMeridian' => $displayStartMeridian,
                'displayEndTime' => $displayEndTime,
                'displayEndMeridian' => $displayEndMeridian,
                'defaultCurrencySymbol' => $defaultCurrencySymbol,
                'buytext' => $o['buytext'],
                'ticket_price_from' => $o['ticket_price_from'],
                'ticket_price_to' => $o['ticket_price_to'],
                'tickets' => $o['tickets'],
                'buytext2' => $o['buytext2'],
                'ticket2_price_from' => $o['ticket2_price_from'],
                'ticket2_price_to' => $o['ticket2_price_to'],
                'tickets2' => $o['tickets2'],
                'displayTerms' => $displayTerms,
                'displayPreviousDate' => $displayPreviousDate,
                'displayPreviousTime' => $displayPreviousTime,
                'displayPreviousMeridian' => $displayPreviousMeridian,
                'gsd_status' => $o['gsd_status'],
                'gsd_url' => $o['gsd_url'],
            ]);

        }

        echo VentureUtility::getTemplate('occurrence-action-bar');

    }

    function saveMetadata($postId) {

        // Skip new posts
        if (empty($_POST)) return;

        // Only do this for events
        if (!array_key_exists('post_type', $_POST) || $_POST['post_type'] != 'event') return $postId;
        
        // Skip auto-save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $postId;

        // Permissions check
        if (!current_user_can('edit_page', $postId)) return $postId;

        if (!empty($_POST['vem_sets'])) {
            foreach ($_POST['vem_sets'] as $set_name => $values) {
                update_post_meta($postId, 'vem_fields_set_'.$set_name, $values);
            }
        }

        if (!empty($_POST['vem_event_details'])) {
            update_post_meta($postId, 'vem_event_details', $_POST['vem_event_details']);
        } elseif (array_key_exists('vem_event_details', $_POST)) {
            delete_post_meta($postId, 'vem_event_details');
        }
        
        if (!empty($_POST['vem_display_end_times'])) {
            update_post_meta($postId, 'vem_display_end_times', $_POST['vem_display_end_times']);  
        }
        
        if (!empty($_POST['vem_max_occurrences'])) {
            update_post_meta($postId, 'vem_max_occurrences', $_POST['vem_max_occurrences']);
        } elseif (array_key_exists('vem_max_occurrences', $_POST)) {
            delete_post_meta($postId, 'vem_max_occurrences');
        }
        
        if (!empty($_POST['vem_max_message'])) {
            update_post_meta($postId, 'vem_max_message', $_POST['vem_max_message']);
        } elseif (array_key_exists('vem_max_message', $_POST)) {
            delete_post_meta($postId, 'vem_max_message');
        }
        
        if (!empty($_POST['vem_homepage_title'])) {
            update_post_meta($postId, 'vem_homepage_title', $_POST['vem_homepage_title']);
        } elseif (array_key_exists('vem_homepage_title', $_POST)) {
            delete_post_meta($postId, 'vem_homepage_title');
        }
        
        if (!empty($_POST['vem_pretitle'])) {
            update_post_meta($postId, 'vem_pretitle', $_POST['vem_pretitle']);
        } elseif (array_key_exists('vem_pretitle', $_POST)) {
            delete_post_meta($postId, 'vem_pretitle');
        }
        
        if (!empty($_POST['vem_posttitle'])) {
            update_post_meta($postId, 'vem_posttitle', $_POST['vem_posttitle']);
        } elseif (array_key_exists('vem_posttitle', $_POST)) {
            delete_post_meta($postId, 'vem_posttitle');
        }

        if (!empty($_POST['vem_event_transcript'])) {
            update_post_meta($postId, 'vem_event_transcript', $_POST['vem_event_transcript']);
        }
    }

    function saveOccurrences($postId) {
        
        global $wpdb;
        
        // Skip new posts
        if (empty($_POST)) return;

        // Only do this for events
        if (!array_key_exists('post_type', $_POST) || $_POST['post_type'] != 'event') return $postId;

        // Skip auto-save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $postId;

        // Permissions check
        if (!current_user_can('edit_page', $postId)) return $postId;

        $table_name = $wpdb->prefix . VEM_EVENT_DATES_TABLE;
        
        // Delete removed event dates
        if (!empty($_POST['vem_dates_remove'])) {
            $wpdb->query(
                $wpdb->prepare("DELETE FROM `{$table_name}` 
                    WHERE `post_ID` = %d AND `id` IN(" . implode(',', $_POST['vem_dates_remove']) . ");", 
                    $postId
                )
            );
        }

        if (!empty($_POST['vem_dates'])) {
        
            global $ventureEventSystem;
            $allDateCats = [];

            // Multiple dates
            foreach ($_POST['vem_dates'] as $dates) {
                
                $insert = array(
                    'post_ID' => $postId
                );

                $dateCats = [];

                // Fields within those dates
                foreach ($dates as $key => $value) {
                    
                    $key = str_replace('vem_dates_', '', $key);
                    
                    switch ($key) {
                        case 'ticket_url':
                        case 'ticket2_url':
                        case 'gsd_url':
                            $url = $value;
                            if ((substr($url,0,4) != 'http') && (substr($url,0,1) != '/') && ($url != '')) {
                                $insert[$key] = 'https://'.$url;
                            } else {
                                $insert[$key] = $url;
                            }
                            break;
                            
                        case 'id':
                            if (!empty($value)) {
                                $insert[$key] = $value;
                            }
                            break;

                        case 'occurrence_category':
                            $dateCats = $value;
                            break;
                        
                        default:
                            $insert[$key] = $value;
                            break;
                    }
                    
                }
                // Convert date/time to storable Unix time in UTC timezone
                $s = $ventureEventSystem->getStorableTime(date('Y-m-d', strtotime($dates['vem_dates_date'])),date('H:i:s', strtotime(implode(' ', $dates['vem_dates_start_time']))));
                $e = $ventureEventSystem->getStorableTime(date('Y-m-d', strtotime($dates['vem_dates_date'])),date('H:i:s', strtotime(implode(' ', $dates['vem_dates_end_time']))));
                $gsd = $ventureEventSystem->getStorableTime(date('Y-m-d', strtotime($dates['vem_dates_gsd_previous_date'])),date('H:i:s', strtotime(implode(' ', $dates['vem_dates_gsd_previous_start_time']))));

                $insert['start_time'] = $s;
                $insert['end_time'] = $e;
                $insert['gsd_previous_start_time'] = $gsd;
                unset($insert['gsd_previous_date']);
                unset($insert['date']);
                $dateId = 0;

                // Update
                if (array_key_exists('id',$insert) && $insert['id'] != '') {
                    
                    $dateId = $insert['id'];
                    $insert['update_time'] = current_time('timestamp',true);

                    $result = $wpdb->update(
                        $table_name, 
                        $insert,
                        array(
                            'id' => $insert['id']
                        )
                    );
                } else { // Insert
                    
                    $insert['created'] = date('Y-m-d H:i:s', current_time('timestamp'));
                    $insert['create_time'] = $insert['update_time'] = current_time('timestamp',true);
                    
                    $result = $wpdb->insert(
                        $table_name, 
                        $insert
                    );

                    $dateId = $wpdb->insert_id;
                }

                if ($dateId && sizeof($dateCats) > 0) {
                    foreach ($dateCats as $d) {
                        $allDateCats[] = [
                            'eventId' => $postId,
                            'dateId' => $dateId,
                            'catId' => $d
                        ];
                    }
                }
            }

            // Finished with all the other metadata, now save occurrence categories, aka "date cats"
            $insertCatQuery = 'insert into '.$wpdb->prefix.VEM_DATE_TERM_TABLE.' (event_id, occurrence_id, term_id) values ';
            $catRows = [];
            foreach ($allDateCats as $d) {
                $catRows[] = '('.intval($d['eventId']).', '.intval($d['dateId']).', '.intval($d['catId']).')';
            }
            $insertCatQuery .= implode(', ',$catRows);

            $wpdb->delete($wpdb->prefix.VEM_DATE_TERM_TABLE, ['event_id' => $postId]);
            $wpdb->query($insertCatQuery);
        }
    }

    public function addVenueOptions($tag, $taxonomy=null) {
        
        // Being edited
        if (is_object($tag)) {
            
            $address = get_term_meta($tag->term_id, 'address', true);
            $city = get_term_meta($tag->term_id, 'city', true);
            $state = get_term_meta($tag->term_id, 'state', true);
            $zip = get_term_meta($tag->term_id, 'zip', true);
            $country = get_term_meta($tag->term_id, 'country', true);

            echo '<table class="form-table"><tbody>';
            
            echo    '<tr id="vem_event_venue_address" class="form-field"><th scope="row" valign="top"><label for="vem_event_venue_address">' . __("Address") . '</label></th>';
            echo    '<td><input name="vem_event_venue_address" class="address" type="text" value="' . $address . '" />';
            echo    '<p class="description">' . __("The address of the location.") . '</p></td></tr>';
            
            echo    '<tr id="vem_event_venue_city" class="form-field"><th scope="row" valign="top"><label for="vem_event_venue_city">' . __("City") . '</label></th>';
            echo    '<td><input name="vem_event_venue_city" class="address" type="text" value="' .$city . '" />';
            echo    '<p class="description">' . __("The town of the location.") . '</p></td></tr>';
            
            echo    '<tr id="vem_event_venue_state" class="form-field"><th scope="row" valign="top"><label for="vem_event_venue_state">' . __("State/Province") . '</label></th>';
            echo    '<td><input name="vem_event_venue_state" class="address" type="text" value="' . $state . '" />';
            echo    '<p class="description">' . __("The state or province of the location.") . '</p></td></tr>';
            
            echo    '<tr id="vem_event_venue_zip" class="form-field"><th scope="row" valign="top"><label for="vem_event_venue_zip">' . __("Zip/Postal Code") . '</label></th>';
            echo    '<td><input name="vem_event_venue_zip" class="address" type="text" value="' . $zip . '" />';
            echo    '<p class="description">' . __("The zip code or postal code of the location.") . '</p></td></tr>';
            
            echo    '<tr id="vem_event_venue_country" class="form-field"><th scope="row" valign="top"><label for="vem_event_venue_country">' . __("Country") . '</label></th>';
            echo    '<td><input name="vem_event_venue_country" class="address" type="text" value="' . $country . '" />';
            echo    '</td></tr>';
            
            $fields = array();
            foreach (array($address, $city, $state, $zip, $country) as $f) {
                if (!empty($f)) {
                    $fields[] = str_replace(' ', '+', $f);
                }
            }
            $map_url = (!empty($fields)) ? implode(',', $fields) : 'Los+Angeles,CA';
            $map_url = "center=" . $map_url . "&markers=" . $map_url;
            
            echo    '<tr class="form-field"><th scope="row" valign="top">&nbsp;</th>';
            echo    '<td><img id="venue-gmap" src="https://maps.googleapis.com/maps/api/staticmap?zoom=13&size=' . get_option('vem_map_dimensions_x', 150) . 'x' . get_option('vem_map_dimensions_y', 150) . '&sensor=false&' . $map_url . '" />';
            echo    '</td></tr>';
            
            echo '</tbody></table>';
            
        } else { // being added
            
            echo '<div class="form-field additional-fields" id="vem_event_venue_address">';
            echo    '<label for="vem_event_venue_address">' . __("Address") . '</label>';
            echo    '<input name="vem_event_venue_address" type="text" value="" />';
            echo    '<p class="description">' . __("The address of the location.") . '</p>';
            echo '</div>';
            
            echo '<div class="form-field additional-fields" id="vem_event_venue_city">';
            echo    '<label for="vem_event_venue_city">' . __("City") . '</label>';
            echo    '<input name="vem_event_venue_city" type="text" value="" />';
            echo    '<p class="description">' . __("The town of the location.") . '</p>';
            echo '</div>';
            
            echo '<div class="form-field additional-fields" id="vem_event_venue_state">';
            echo    '<label for="vem_event_venue_state">' . __("State/Province") . '</label>';
            echo    '<input name="vem_event_venue_state" type="text" value="" />';
            echo    '<p class="description">' . __("The state or province of the location.") . '</p>';
            echo '</div>';
            
            echo '<div class="form-field additional-fields" id="vem_event_venue_zip">';
            echo    '<label for="vem_event_venue_zip">' . __("Zip/Postal Code") . '</label>';
            echo    '<input name="vem_event_venue_zip" type="text" value="" />';
            echo    '<p class="description">' . __("The zip code or postal code of the location.") . '</p>';
            echo '</div>';
            
            echo '<div class="form-field additional-fields" id="vem_event_venue_country">';
            echo    '<label for="vem_event_venue_country">' . __("Country") . '</label>';
            echo    '<input name="vem_event_venue_country" type="text" value="" />';
            echo '</div>';
            
            // Rearrange element for consistency (solely visual)
            echo '<script type="text/javascript" charset="utf-8">
                jQuery(document).ready(function($){
                    $(".additional-fields").insertBefore("p.submit");
                });
            </script>';
            
        }
        
    }

    public function saveVenueOptions($term_id, $tt_id) {
    
        // Save fields
        if (isset($_POST['vem_event_venue_address'])) {
            update_term_meta($term_id, 'address', $_POST['vem_event_venue_address']);
        }
        if (isset($_POST['vem_event_venue_city'])) {
            update_term_meta($term_id, 'city', $_POST['vem_event_venue_city']);
        }
        if (isset($_POST['vem_event_venue_state'])) {
            update_term_meta($term_id, 'state', $_POST['vem_event_venue_state']);
        }
        if (isset($_POST['vem_event_venue_zip'])) {
            update_term_meta($term_id, 'zip', $_POST['vem_event_venue_zip']);
        }
        if (isset($_POST['vem_event_venue_country'])) {
            update_term_meta($term_id, 'country', $_POST['vem_event_venue_country']);
        }

    }

    public function addCategoryOptions($tag, $taxonomy=null) {
        
        // Being edited
        if (is_object($tag)) {
            
            $color = get_term_meta($tag->term_id, 'color', true);

            echo '<table class="form-table"><tbody><tr id="vem_event_category_color" class="form-field">';
            echo    '<th scope="row" valign="top"><label for="vem_event_category_color">' . __("Category Color") . '</label></th>';
            echo    '<td><input name="vem_event_category_color" id="vem_color_picker" type="text" value="' . $color . '" size="10" style="width:100px;">';
            echo    '<p class="description">' . __("This determines the color of the widget and full page calendar icons associated with each event assigned to the respective category. The calendar widget and full page calendars display a key to each color in the form of the category name.") . '</p></td>';
            echo '</tr></tbody></table>';
            
        } else { // being added
            
            echo '<div class="form-field" id="vem_event_category_color_wrapper">';
            echo    '<label for="vem_event_category_color">&nbsp;' . __("Category Color") . '</label>';
            echo    '<input name="vem_event_category_color" value="" id="vem_color_picker"  size="10" style="width:100px;"/>';
            echo '<p>This determines the color of the widget and full page calendar icons associated with each event assigned to the respective category. The calendar widget and full page calendars display a key to each color in the form of the category name.</p>';
            echo '</div>';
            
        }
        
    }

    public function saveCategoryOptions($term_id, $tt_id) {
        
        // Save color
        if (isset($_POST['vem_event_category_color'])) {
            update_term_meta($term_id, 'color', $_POST['vem_event_category_color']);
        }

    }

    public function addDateTermOptions($term, $taxonomy=null) {

        // Being edited
        if (is_object($term)) {
            
            $termImage = get_term_meta($term->term_id, 'term-image', true);
            $preview = $termImage ? wp_get_attachment_image($termImage, 'thumbnail') : '';

            echo <<<FIELDS
<tr class="form-field term-group-wrap">
    <th scope="row">
        <label for="term-image">
            Term Image
        </label>
    </th>
    <td>
        <input type="hidden" id="term-image" name="term-image" value="{$termImage}">
        <div id="term-image-wrapper">
            {$preview}
        </div>
        <p>
            <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="Add Image" />
            <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="Remove Image" />
        </p>
    </td>
</tr>
FIELDS;
            
        } else { // being added
            
		    echo <<<FIELDS
<div class="form-field term-image-wrap">
	<input type="hidden" id="term-image" name="term-image" value="">
	<label for="term-image">Term Image</label>
        <div id="term-image-wrapper"></div>
        <p>
            <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="Add Image" />
            <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="Remove Image" />
        </p>
</div>
FIELDS;
            
        }
        
    }

    public function saveDateTermOptions($term_id, $tt_id) {
        
        // Save color
        if (isset($_POST['term-image'])) {
            update_term_meta($term_id, 'term-image', $_POST['term-image']);
        }

    }

	function addDateTermAdminScripts() {
        $screen = get_current_screen();
        if (!(($screen->base == 'term' || $screen->base == 'edit-tags')
            && in_array($screen->id, ['edit-occurrence_category']))) return;

        wp_enqueue_media();
        wp_enqueue_script('occurrence-category-image-admin', plugins_url('../js/venture-occurrence-category-edit.js', __FILE__), array('jquery'), VEM_VERSION, true);
	}

    public function customTermImageColumns($columns) {
        unset($columns['description']);
		$columns = array_slice($columns, 0, 1, true) + ['term-image' => 'Image'] + array_slice($columns, 1, count($columns)-1, true);
		return $columns;
	}

	public function customTermImageColumn($display, $column, $id) {

		switch($column) {
			case 'term-image':
				$termImage = get_term_meta($id, 'term-image', true);
				$preview = $termImage ? wp_get_attachment_image_src($termImage, 'term-image') : '';

				$display = $preview && $preview[0] ? '<img src="'.$preview[0].'" style="max-width:100%;" />' : 'No image';
				break;
		}

		return $display;
	}

    public function maybeChangeSlug() {

        global $post;
        
        // Skip new posts
        if (!$post) return;

        // Skip auto-save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post->ID;

        $duplicated = get_post_meta($post->ID, 'vem_duplicated', 'no'); 
        if ($duplicated != 'no') {
            if ($post->post_name == $duplicated && !empty($_POST['post_title'])) {
                $newSlug = wp_unique_post_slug(sanitize_title($_POST['post_title']), $post->ID, $post->post_status, $post->post_type, $post->post_parent);
                global $wpdb;
                $success = $wpdb->update($wpdb->posts, array('post_name' => $newSlug), array('ID' => $post->ID));
            }
            delete_post_meta($post->ID,'vem_duplicated');
        }
    }

}

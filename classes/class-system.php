<?php

require_once('class-system-initializer.php');
require_once('class-system-options.php');
require_once('class-system-shortcodes.php');
require_once('class-system-filters.php');
require_once('class-help.php');

class VentureEventSystem
{

    private $defaultEventFields = [];
    private $defaultOccurrenceFields = [];
    private $defaultFields = [];
    private $optionsPanels = [];

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'dbUpgrade']);
        add_action('init', [$this, 'initialize']);
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdmin'));

        $options = new VentureEventSystemOptions();
        $shortcodes = new VentureEventSystemShortcodes();
        $filters = new VentureEventSystemFilters();
        $help = new VentureHelp();

        // Ajax calls to get events for a calendar
        add_action('wp_ajax_vem_get_events', [$this, 'getCalendarEvents']);
        add_action('wp_ajax_nopriv_vem_get_events', [$this, 'getCalendarEvents']);

        // Support for duplicating events
        add_action('admin_action_duplicate_event', [$this, 'duplicateEvent']);
        add_filter('post_row_actions', [$this, 'addDuplicateEventLink'], 10, 2 );

        // ICS output
        add_action('parse_request', [$this, 'getIcs']);

        // Google Structured Data for events
        add_action('wp_head', [$this, 'getEventSchema']);

        // Delete occurrences when events are deleted
        add_action('deleted_post', [$this, 'deleteDates']);

        // Update create time for occurrences when a post is published so these match
        add_action('post_updated', [$this, 'setDatesCreateTime'], 10, 3);

        // Consider whether to display the upgrade nag
        add_action('load-toplevel_page_venture-options', [$this, 'maybeWelcome']);
    }

    /* Activation hook installer */
    public static function install($skipInitialize = false)
    {

        if (version_compare(PHP_VERSION, '7.0.0') == -1) {
            // Not compatible with pre-7 PHP
            wp_die('<p><strong>Venture Event Manager</strong> requires PHP v7.0 or later to activate. You are on <strong>v'.PHP_VERSION.'.</strong></p><p>PHP7 shows significantly higher performance, so it would be to your advantage for your hosting company to upgrade you to that today. <a href="https://winningwp.com/what-is-php7-and-how-to-use-it-with-wordpress/" target="_blank">This article</a> is a great primer on PHP on the server and the performance improvements gained from updating to the latest v7 release.</p><p>To learn more about <strong>Venture Event Manager</strong> or to get installation support, please visit us at <a href="https://www.ventureeventmanager.com" target="_blank">ventureeventmanager.com</a>.</p>');
        }

        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Occurrences table
        $table_name = $wpdb->prefix . VEM_EVENT_DATES_TABLE;
        $sql = "CREATE TABLE `{$table_name}` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `post_ID` bigint(20) unsigned NOT NULL,
              `start_time` int DEFAULT NULL,
              `end_time` int DEFAULT NULL,
              `ticket_price_from` varchar(255) DEFAULT NULL,
              `ticket_price_to` varchar(255) DEFAULT NULL,
              `ticket_url` varchar(255) DEFAULT NULL,
              `ticket_button_text` varchar(255) DEFAULT 'Buy Tickets',
              `ticket2_price_from` varchar(255) DEFAULT NULL,
              `ticket2_price_to` varchar(255) DEFAULT NULL,
              `ticket2_url` varchar(255) DEFAULT NULL,
              `ticket2_button_text` varchar(255) DEFAULT 'Buy Tickets',
              `note` varchar(255) DEFAULT NULL,
              `venue_ID` bigint(20) DEFAULT NULL,
              `transcript_id` bigint(20) DEFAULT NULL,
              `gsd_status` varchar(255) DEFAULT 'EventScheduled',
              `gsd_url` varchar(1000) DEFAULT NULL,
              `gsd_previous_start_time` int DEFAULT NULL,
              `created` datetime DEFAULT NULL,
              `create_time` int default null,
              `update_time` int default null,
              PRIMARY KEY  (`id`),
              KEY `idx_event_dates_start_time` (`start_time`),
              KEY `idx_event_dates_end_time` (`end_time`),
              KEY `idx_event_dates_post_id` (`post_ID`),
              KEY `idx_event_dates_transcript_id` (`transcript_ID`),
              KEY `idx_event_dates_venue_ID` (`venue_ID`)
            );";
        dbDelta($sql);

        // Occurrence taxonomy term link table
        $table_name = $wpdb->prefix . VEM_DATE_TERM_TABLE;
        $sql = "CREATE TABLE `{$table_name}` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `event_id` int(11) unsigned NOT NULL,
              `occurrence_id` int(11) unsigned NOT NULL,
              `term_id` int(11) unsigned NOT NULL,
              PRIMARY KEY  (`id`),
              KEY `idx_date_taxonomy_occurrence_id` (`occurrence_id`),
              KEY `idx_date_taxonomy_term_id` (`term_id`)
            );";
        dbDelta($sql);

        if (!$skipInitialize) {
            update_option('vem-db-version', VEM_DB_VERSION);
            $i = new VentureEventSystemInitializer();
            $i->register();
            $i->addCapabilities();
            flush_rewrite_rules();
            add_action('activated_plugin', ['VentureEventSystem', 'loadWelcome']);
        }
    }

    public function dbUpgrade() {
        $currentVersion = get_option('vem-db-version', '0.0.0');
        if (version_compare($currentVersion, VEM_DB_VERSION) == -1) {
            // Database upgrade required
            echo <<<MESSAGE
            <html><head><title>Upgrading...</title>
            <style link="https://fonts.googleapis.com/css?family=Open+Sans:300,300italic,regular,italic,600,600italic,700,700italic,800,800italic|Work+Sans:100,200,300,regular,500,600,700,800,900"></style>
            <style>body{font-family:"Open Sans", Arial, Helvetica, sans-serif;text-align:center;margin:200px auto;background-color:#ddd;}.hidden{position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;}.logo{margin-bottom:60px;}</style>
            </head><body>
            <p class="logo"><img src="https://ventureeventmanager.com/wp-content/uploads/2018/02/Venture-Event-Manager-Logo-Template-2018v6@2x-4.png" alt="Venture Event Manager" /></p>
            <h1 class="hidden">Venture Event Manager</h2>
            <p>Database upgrade in progress...</p>
            <body></html>
MESSAGE;
            ob_flush();
            flush();
            VentureEventSystem::install(true);
            update_option('vem-db-version', VEM_DB_VERSION);
            echo '<script type="text/javascript">window.location="'.get_admin_url(null, 'admin.php?page=venture-options&tab=welcome').'"</script>';
            exit;
        }
    }

    public static function uninstall()
    {
        flush_rewrite_rules();
    }

    public function initialize() {
        $i = new VentureEventSystemInitializer();
        $i->register();
        $this->defaultEventFields = $i->defaultEventFields();
        $this->defaultOccurrenceFields = $i->defaultOccurrenceFields();
        $this->defaultFields = $i->defaultFields($this->defaultEventFields, $this->defaultOccurrenceFields);
        $this->enqueue();
    }

    public function maybeWelcome() {
        $lastWelcome = get_option('vem-last-welcome', '0.0.0');
        $showWelcome = !!(version_compare($lastWelcome, VEM_VERSION) == -1);

        if ($showWelcome) {
            $this->loadWelcome();
        }
    }

    public static function loadWelcome() {
        // Update our option to the current version
        update_option('vem-last-welcome', VEM_VERSION, '', 'no');
        
        // Redirect to the welcome page
        $target = get_admin_url(null, 'admin.php?page=venture-options&tab=welcome');
        wp_redirect($target);
        exit;
    }

    public function deleteDates($eventId) {
        global $wpdb;

        $prefix = $wpdb->prefix;
        $result = $wpdb->query("delete from {$prefix}vem_event_dates where post_ID = {$eventId}");
    }

    public function setDatesCreateTime($eventId, $after, $before) {
        global $wpdb;

        $prefix = $wpdb->prefix;
        if ($after->post_status == 'publish') {
            if ($before->post_status != 'publish' || $before->post_date != $after->post_date) {
                $create = get_the_date('U', $eventId);
                $createDate = date('Y-m-d H:i:s', (int)$create); // Holdover from old system
                $update = current_time('timestamp',true);

                $result = $wpdb->query("update {$prefix}vem_event_dates set created = '{$createDate}', create_time = {$create}, update_time = {$update} where post_ID = {$eventId}");
            }
        }


    }

    public function getDefaultFields() {
        return $this->defaultFields;
    }

    /* Enqueue Functions */
    // Add scripts and CSS for admin pages
    public function enqueueAdmin()
    {
        $screen = get_current_screen();

        if ($screen->id == 'edit-event') {
            wp_enqueue_style('venture-edit-event-list', plugins_url('../css/venture-edit-event-list.css', __FILE__), [], VEM_VERSION);            
            wp_enqueue_script('venture-edit-event-list', plugins_url('../js/venture-edit-event-list.js', __FILE__), ['jquery'], VEM_VERSION, true);
        }

        if (stristr($screen->base, 'venture-options')) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable', null, array('jquery', 'jquery-ui-core', 'ui-widget', 'ui-mouse'));
            wp_enqueue_style('venture-welcome', plugins_url('../css/vem.welcome.css', __FILE__), [], VEM_VERSION);
            wp_enqueue_style('venture-options', plugins_url('../css/venture-options.css', __FILE__), [], VEM_VERSION);
            wp_enqueue_script('venture-options', plugins_url('../js/venture-options.js', __FILE__), ['jquery'], VEM_VERSION);
            wp_localize_script('venture-options', 'ventureAdminOptions', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
            ));

            $fields = apply_filters('vem_event_settings_fields', $this->defaultFields);
            wp_localize_script( 'venture-options', 'vemEventSettingsFields', $fields);
        }

        if ($screen->id == 'edit-event_category') {
            wp_enqueue_script('jquery');
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('venture-category-edit', plugins_url('../js/venture-category-edit.js', __FILE__), ['wp-color-picker'], VEM_VERSION);
        }

        if ($screen->base == 'post' && $screen->id == 'event_listing') {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable', null, array('jquery', 'jquery-ui-core', 'ui-widget', 'ui-mouse'));
            wp_enqueue_style('venture-options', plugins_url('../css/venture-options.css', __FILE__), [], VEM_VERSION);
            wp_enqueue_script('venture-event-listings', plugins_url('../js/venture-event-listings.js', __FILE__), ['jquery'], VEM_VERSION);

            $fields = apply_filters('vem_event_settings_fields', $this->defaultFields);
            wp_localize_script( 'venture-event-listings', 'vemEventSettingsFields', $fields);
        }

        if ($screen->base == 'post' && $screen->id == 'event_calendar') {
            wp_enqueue_script('jquery');
            wp_enqueue_style('venture-options', plugins_url('../css/venture-options.css', __FILE__), [], VEM_VERSION);
            wp_enqueue_script('venture-event-calendars', plugins_url('../js/venture-event-calendars.js', __FILE__), ['jquery'], VEM_VERSION);
        }

        if ($screen->base == 'post' && $screen->id == 'event_module') {
            wp_enqueue_script('jquery');
            wp_enqueue_style('venture-options', plugins_url('../css/venture-options.css', __FILE__), [], VEM_VERSION);
            wp_enqueue_script('venture-event-modules', plugins_url('../js/venture-event-modules.js', __FILE__), ['jquery'], VEM_VERSION);
        }

        if ($screen->base == 'post' && $screen->id == 'event') {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable', null, array('jquery', 'jquery-ui-core', 'ui-widget', 'ui-mouse'));
            wp_enqueue_style('font-awesome', 'https://use.fontawesome.com/releases/v5.2.0/css/all.css', [], '5.2.0');
            wp_enqueue_style('venture-events',plugins_url( '../css/venture-events.css', __FILE__ ));
            wp_enqueue_style('venture-options', plugins_url('../css/venture-options.css', __FILE__), [], VEM_VERSION);
            wp_enqueue_script('venture-events', plugins_url( '../js/venture-events.js', __FILE__ ), ['jquery', 'jquery-ui-core'], VEM_VERSION);
            wp_localize_script('venture-events', 'ventureEventsOptions', [
                'ajaxurl' => admin_url('admin-ajax.php'),
            ]);
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('smoothness', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', '1.12.1');
            wp_enqueue_style('editor-buttons');
            if (defined('WPB_VC_VERSION')) {
                wp_enqueue_script('vem-event-editor-vc', plugins_url('../js/vem.event-editor-vc.js', __FILE__), array('jquery'), VEM_VERSION, true);
                wp_enqueue_style('vem-event-editor-vc', plugins_url('../css/vem.event-editor-vc.css', __FILE__), null, VEM_VERSION);
            }

            $fields = apply_filters('vem_event_settings_fields', $this->defaultFields);
            wp_localize_script( 'venture-events', 'vemEventSettingsFields', $fields);
        }

    }

    public function enqueue() {
        wp_enqueue_style('vem-defaults', plugins_url('../css/vem.defaults.css', __FILE__), [], VEM_VERSION);
        wp_enqueue_style('vem-columns', plugins_url('../css/vem.columns.css', __FILE__), [], VEM_VERSION);
        wp_enqueue_style('font-awesome', 'https://use.fontawesome.com/releases/v5.2.0/css/all.css', [], '5.2.0');
        wp_register_style('vem-toggle', plugins_url('../css/vem.toggle.css', __FILE__), [], VEM_VERSION);

        // Calendar scripts and styles - register only, shortcode will enqueue
        wp_register_script('moment', plugins_url('../js/moment.min.js', __FILE__ ), array('jquery'), VEM_VERSION);
        wp_register_script('moment-timezone', plugins_url('../js/moment-timezone-with-data.min.js', __FILE__ ), array('jquery', 'moment'), VEM_VERSION);
        wp_register_script('vem-calendar', plugins_url('../js/vem.calendar.js', __FILE__ ), array('jquery','jquery-ui-dialog', 'moment'), VEM_VERSION);
        wp_register_style('vem-listing', plugins_url('../css/vem.listing.css', __FILE__ ), [], VEM_VERSION);
        wp_register_style('vem-calendar', plugins_url('../css/vem.calendar.css', __FILE__ ), [], VEM_VERSION);
        wp_register_style('vem-calendar-dialog', plugins_url('../css/vem.calendar-dialog.css', __FILE__ ), [], VEM_VERSION);
        wp_register_style('vem-widgets', plugins_url('../css/vem.widgets.css', __FILE__ ), [], VEM_VERSION);

        // Datepicker style, registered for possible use, such as in search forms
        wp_register_style('jquery-ui-base', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', '1.12.1');

        // Search form datepicker initialization script
        wp_register_script('vem-search', plugins_url('../js/venture.search.js', __FILE__ ), array('jquery', 'jquery-ui-datepicker'), VEM_VERSION);

        $options = get_option('venture-event-system_options', false);
        $useGallery = false;
        if ($options) {
            $options = unserialize($options);
            if ($options && is_array($options) && array_key_exists('venture-gallery', $options)) {
                $useGallery = ($options['venture-gallery'] == "1");
            }
        }

        if ($useGallery) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('masonry');
            wp_enqueue_script('imagesloaded');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('venture-gallery', plugins_url('../js/venture-gallery.js', __FILE__), ['jquery', 'imagesloaded', 'masonry', 'jquery-ui-core', 'jquery-ui-dialog'], VEM_VERSION);
            wp_enqueue_style('jquery-ui-base', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', '1.12.1');
        }
    }

    // Converts string date and optional time in current WP timezone to Unix timestamp UTC
    // Doesn't handle half-hour/quarter-hour time zones
    public function getStorableTime($date, $time=null) {
        $toConvert = $date;
        $toConvert .= ' '. (empty($time) ? '00:00:00' : $time);
        $tz = get_option('timezone_string') ?: 'UTC';
        $d = new DateTime($toConvert,new DateTimeZone($tz));

        return $d->getTimestamp();
    }

    // Get date and time formatted per options
    // Dates should be provided as Unix timestamps UTC
    public function getFormattedDateTime($datetime, $excludeTime=false, $dateFormat='default', $timeFormat='default', $separator='default', $timeDateOrder = 'default') {

        $venture = VentureFramework::getInstance('venture-event-system');

        $dt = new DateTime();
        $dt->setTimestamp((int)$datetime);
        $tz = get_option('timezone_string') ?: 'UTC';
        $tz = new DateTimeZone($tz);
        $dt->setTimezone($tz);

        if ($dateFormat == 'default') $dateFormat = 'l, F j, Y';
        $dateText = $dt->format($dateFormat);

        if ($excludeTime) return $dateText;

        $time_format = $venture->getOption('default-time-display') ?: 12;
        if ($timeDateOrder == 'default') {
            $time_date_order = $venture->getOption('default-time-date-order') ?: 'time';
        } else {
            $time_date_order = $timeDateOrder;
        }

        if ($timeFormat == 'default') {
            if ($time_format == 12) {
                $timeFormat = 'g:iA';
            } else {
                $timeFormat = 'H:i';
            }
        }
        $timeText = $dt->format($timeFormat);

        if ($separator == 'default') $separator = ', ';

        $result = (($time_date_order == 'time') ? $timeText.$separator.$dateText : $dateText.$separator.$timeText);

        $result = apply_filters('vem_format_date_time', $result, $datetime, $excludeTime, $dateFormat, $timeFormat, $separator, $timeDateOrder);

        return $result;
    }

    public function getCalendarEvents()
    {
        $calendarId = intval($_POST['id']);

        $c = wp_get_post_terms($calendarId, 'event_category', array('fields' => 'ids'));
        $s = wp_get_post_terms($calendarId, 'event_season', array('fields' => 'ids'));
        $v = wp_get_post_terms($calendarId, 'event_venue', array('fields' => 'ids'));
        $m = wp_get_post_terms($calendarId, 'media_type', array('fields' => 'ids'));
        $oc = wp_get_post_terms($calendarId,'occurrence_category',array('fields' => 'ids'));

        $startDate = $_POST['start'];
        $endDate   = $_POST['end'];
        if ($_POST['futureOnly'] == 'true') {
            $now       = time();
            $startDate = max($startDate, $now);
            $endDate   = max($startDate, $endDate);
        }

        $event = intval($_POST['event']);

        $venture = VentureFramework::getInstance('venture-event-calendar');
        $topCategory = $venture->getOption('calendar-top-category', $calendarId);
        if (!$topCategory) {
            $topCategory = 0;
        }

        $vem3 = new VentureEventManager3();
        $vem3->setContext('calendar-ajax');
        $vem3->setCalendarId($calendarId);

        // Taxonomies
        $vem3->setTaxonomyCriterion('event_category', $c);
        $vem3->setTaxonomyCriterion('event_season', $s);
        $vem3->setTaxonomyCriterion('event_venue', $v);
        $vem3->setTaxonomyCriterion('occurrence_category', $oc);
        $vem3->setTaxonomyCriterion('media_type', $m);

        // Dates
        $vem3->setOccurrenceRange($vem3::DATES, $startDate, $endDate);

        // Single event calendar
        if ($event > 0) {
            $vem3->setEventId($event);
        }

        $vem3 = apply_filters('vem-get-calendar-events-prep', $vem3, $calendarId, []);

        $vem3->retrieveDates();
        $results = $vem3->getCalendarData($topCategory);

        $results = apply_filters('vem_get_calendar_data', $results, $calendarId, $topCategory);

        echo json_encode([
            'events'   => $results,
            'timezone' => get_option('timezone_string') ?: 'UTC',
            'moment'   => $_POST['moment'],
        ]);
        wp_die();

    }

    public function duplicateEvent(){

        global $wpdb;
        if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'duplicate_event' == $_REQUEST['action'] ) ) ) {
            wp_die('No post to duplicate has been supplied!');
        }
     
        $post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
        $post = get_post( $post_id );

        if ($post->post_type != 'event') {
            wp_die('This process is for the duplication of events.');
        }

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
     
        if (isset( $post ) && $post != null) {
     
            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => $post->post_content,
                'post_excerpt'   => $post->post_excerpt,
                'post_name'      => $post->post_name,
                'post_parent'    => $post->post_parent,
                'post_password'  => $post->post_password,
                'post_status'    => 'draft',
                'post_title'     => $post->post_title,
                'post_type'      => $post->post_type,
                'to_ping'        => $post->to_ping,
                'menu_order'     => $post->menu_order
            );
     
            $new_post_id = wp_insert_post( $args );
     
            $taxonomies = get_object_taxonomies($post->post_type);
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }

            $sql = "insert into $wpdb->postmeta (post_id, meta_key, meta_value) select $new_post_id, meta_key, meta_value from $wpdb->postmeta where post_id = $post_id";
            $wpdb->query($sql);

            $table = $wpdb->prefix.VEM_EVENT_DATES_TABLE;

            // Direct transfer fields
            $fieldList = 'start_time, end_time, ticket_price_from, ticket_price_to, ticket_url, ticket_button_text, ';
            $fieldList .= 'ticket2_price_from, ticket2_price_to, ticket2_url, ticket2_button_text, ';
            $fieldList .= 'note, venue_ID';

            $currentTimestamp = current_time('timestamp',true);
            $createDate = date('Y-m-d H:i:s', $currentTimestamp);

            $sql = "insert into $table (post_ID, created, create_time, update_time, $fieldList) ";
            $sql .= "select $new_post_id, '$createDate', $currentTimestamp, $currentTimestamp, $fieldList from $table where post_ID = $post_id order by id";
            $wpdb->query($sql);

            add_post_meta($new_post_id, 'vem_duplicated', get_post_field('post_name',$new_post_id,'raw'), true);

            wp_redirect( admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        } else {
            wp_die('Event creation failed, could not find original event: ' . $post_id);
        }
    }

    function addDuplicateEventLink($actions, $post) {
        if (current_user_can('edit_posts') && $post->post_type == 'event') {
            $actions['duplicate'] = '<a href="admin.php?action=duplicate_event&amp;post=' . $post->ID . '" title="Duplicate this event" rel="permalink">Duplicate</a>';
        }
        return $actions;
    }

    public function getIcs($query) {
        if (array_key_exists('name', $query->query_vars) && $query->query_vars['name'] == 'ics') {

            // Remove anything non-numeric from here
            // Values less than 1000 were sending the data with a preceding slash
            $id =intval(preg_replace('/\D/', '', $query->query_vars['page']));

            $vem3 = new VentureEventManager3();
            $vem3->setOccurrenceId($id);
            $vem3->setContext('ics');

            $vem3->retrieveDates();
            $dates = $vem3->getOccurrences();
            if (sizeof($dates) == 0) wp_die('Not a valid event date');

            $o = $dates[0];
            $o = apply_filters('vem_ics_occurrence', $o);

            $summary = $o['event_name'];        //- text title of the event
            $datestart = $o['start_time'];              //- the starting date (in seconds since unix epoch)
            $dateend = $o['end_time'];              //- the ending date (in seconds since unix epoch)
            $uri = get_permalink($o['event_id']);
            $filename = sanitize_title_with_dashes($o['event_name'], '', 'save').'.ics';    //- the name of this file for saving (e.g. my-event-name.ics)

            $address = $o['venue'];
            if (isset($o['venue_address']) && strlen($o['venue_address']) > 0) {
                $address .= ', '.$o['venue_address'];
            }
            if ((isset($o['venue_city']) && strlen($o['venue_city']) > 0) || (isset($o['venue_state']) && strlen($o['venue_state']) > 0) || (isset($o['venue_zip']) && strlen($o['venue_zip']) > 0)) {
                $address .= ', ';
                if (isset($o['venue_city']) && strlen($o['venue_city']) > 0) {
                    $address .= $o['venue_city'];
                    if ((isset($o['venue_state']) && strlen($o['venue_state']) > 0) || (isset($o['venue_zip']) && strlen($o['venue_zip']) > 0)) {
                        $address .= ', ';
                    }
                }
                if (isset($o['venue_state']) && strlen($o['venue_state']) > 0) {
                    $address .= $o['venue_state'];
                    if (isset($o['venue_zip']) && strlen($o['venue_zip']) > 0) {
                        $address .= ' ';
                    }
                }
                if (isset($o['venue_zip']) && strlen($o['venue_zip']) > 0) {
                    $address .= $o['venue_zip'];
                }
            }

            header('Content-type: text/calendar; charset=utf-8');
            header('Content-Disposition: inline; filename=' . $filename);

    ?>BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTEND:<?php echo date('Ymd\THis\Z', $dateend); ?>

UID:<?php echo uniqid(); ?>

DTSTAMP:<?php echo date('Ymd\THis\Z', (int)current_time('timestamp',1)); ?>

SUMMARY:<?php echo preg_replace('/([\,;])/','\\\$1', $summary); ?>

DESCRIPTION:<?php echo preg_replace('/([\,;])/','\\\$1', $uri); ?>

LOCATION:<?php echo $address; ?>

URL;VALUE=URI:<?php echo preg_replace('/([\,;])/','\\\$1', $uri); ?>

DTSTART:<?php echo date('Ymd\THis\Z', $datestart); ?>

END:VEVENT
END:VCALENDAR
<?php
            exit();
        }
    }

    public function getEventSchema() {
        global $post;

        if (!$post || $post->post_type !== 'event' || !is_single()) return false;

        $vem3 = new VentureEventManager3();
        $vem3->setContext('schema');
        $vem3->setEventId($post->ID);
        $vem3->retrieveDates();

        $dates = $vem3->getOccurrences();

        $schema = '';
        foreach ($dates as $date) {
            $schema .= '<script type="application/ld+json">';
            $schema .= $vem3->getSingleDateSchema($date);
            $schema .= '</script>';
        }

        echo $schema;
        
        return true;
    }

    public function setOptionsPanel($key, $panel) {
        $this->optionsPanels[$key] = $panel;
    }

    public function getOptionsPanel($key) {
        return $this->optionsPanels[$key];
    }

}

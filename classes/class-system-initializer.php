<?php

class VentureEventSystemInitializer
{

    public function __construct() {
        // Mods to admin event list
        add_filter('manage_edit-event_columns', [$this, 'setEditEventColumns']);
        add_action('manage_posts_custom_column', [$this, 'setEventCustomColumns']);
        add_filter( 'posts_orderby', [$this, 'sortEventsByDate']);

        // Mods to admin event listing list
        add_filter('manage_edit-event_listing_columns', [$this, 'setEditEventListingColumns']);
        add_action('manage_posts_custom_column', [$this, 'setEventListingCustomColumns']);

        // Mods to admin event calendar list
        add_filter('manage_edit-event_calendar_columns', [$this, 'setEditEventCalendarColumns']);
        add_action('manage_posts_custom_column', [$this, 'setEventCalendarCustomColumns']);

        // Mods to admin event category list
        add_filter('manage_edit-event_category_columns', [$this, 'setEditEventCategoryColumns']);
        add_filter('manage_event_category_custom_column', [$this,'setEventCategoryCustomColumns'], 10, 3);

        add_action('admin_menu', [$this, 'adjustMenu'], 1000);

        add_action('admin_head-post.php', [$this, 'hidePreviewButton']);
        add_action('admin_head-post-new.php', [$this, 'hidePreviewButton']);

        // Show occurrence category admin under Events
        add_action('admin_menu', [$this, 'addOccurrenceCategoryToMenu']);
        add_action('parent_file', [$this, 'highlightOccurrenceCategoryParent']);
    }

    public function register()
    {

        register_post_type(
            'event',
            array(
                'labels'               => array(
                    'name'               => __('Events', 'post type general name'),
                    'singular_name'      => __('Event', 'post type singular name'),
                    'add_new'            => __('Add New Event', 'event'),
                    'add_new_item'       => __('Add New Event'),
                    'edit_item'          => __('Edit Event'),
                    'new_item'           => __('New Event'),
                    'all_items'          => __('All Events'),
                    'view_item'          => __('View Event'),
                    'search_items'       => __('Search Events'),
                    'not_found'          => __('No events found'),
                    'not_found_in_trash' => __('No events found in Trash'),
                    'parent_item_colon'  => '',
                    'menu_name'          => 'Events',
                ),
                'show_ui'              => true,
                'show_in_menu'         => true,
                'query_var'            => true,
                'rewrite'              => true,
                'publicly_queryable'   => true,
                'exclude_from_search'  => false,
                'capability_type'      => ['event','events'],
                'map_meta_cap'         => true,
                'has_archive'          => true,
                'hierarchical'         => false,
                'menu_icon'            => 'dashicons-calendar-alt',
                'menu_position'        => 10,
                'supports'             => array('title', 'excerpt', 'comments', 'thumbnail', 'author'),
                'public'               => true,
            )
        );

        // Add the editor for events if Visual Composer is active
        if (defined('WPB_VC_VERSION')) {
            add_post_type_support('event', 'editor');
        }

        register_post_type(
            'event_calendar',
            array(
                'labels'               => array(
                    'name'               => __('Event Calendar', 'post type general name'),
                    'singular_name'      => __('Event Calendar', 'post type singular name'),
                    'add_new'            => __('Add New', 'event'),
                    'add_new_item'       => __('Add New Event Calendar'),
                    'edit_item'          => __('Edit Event Calendar'),
                    'new_item'           => __('New Event Calendar'),
                    'all_items'          => __('All Event Calendars'),
                    'view_item'          => __('View Event Calendar'),
                    'search_items'       => __('Search Event Calendars'),
                    'not_found'          => __('No event calendars found'),
                    'not_found_in_trash' => __('No event calendars found in Trash'),
                    'parent_item_colon'  => '',
                    'menu_name'          => 'Event Calendars',
                ),
                'show_ui'              => true,
                'show_in_menu'         => true,
                'show_in_nav_menus'    => false,
                'query_var'            => true,
                'rewrite'              => true,
                'publicly_queryable'   => false,
                'exclude_from_search'  => true,
                'capability_type'      => ['event_calendar','event_calendars'],
                'map_meta_cap'         => true,
                'has_archive'          => false,
                'hierarchical'         => false,
                'menu_icon'            => 'dashicons-calendar-alt',
                'menu_position'        => 10,
                'supports'             => array('title'),
                'public'               => true,
            )
        );

        register_post_type(
            'event_listing',
            array(
                'labels'               => array(
                    'name'               => __('Event Listing', 'post type general name'),
                    'singular_name'      => __('Event Listing', 'post type singular name'),
                    'add_new'            => __('Add New', 'event'),
                    'add_new_item'       => __('Add New Event Listing'),
                    'edit_item'          => __('Edit Event Listing'),
                    'new_item'           => __('New Event Listing'),
                    'all_items'          => __('All Event Listings'),
                    'view_item'          => __('View Event Listing'),
                    'search_items'       => __('Search Event Listings'),
                    'not_found'          => __('No event listings found'),
                    'not_found_in_trash' => __('No event listings found in Trash'),
                    'parent_item_colon'  => '',
                    'menu_name'          => 'Event Listings',
                ),
                'show_ui'              => true,
                'show_in_menu'         => true,
                'show_in_nav_menus'    => false,
                'query_var'            => true,
                'rewrite'              => true,
                'publicly_queryable'   => false,
                'exclude_from_search'  => true,
                'capability_type'      => ['event_listing','event_listings'],
                'map_meta_cap'         => true,
                'has_archive'          => false,
                'hierarchical'         => false,
                'menu_icon'            => 'dashicons-calendar-alt',
                'menu_position'        => 10,
                'supports'             => array('title'),
                'public'               => true,
            )
        );

        register_post_type(
            'event_transcript',
            array(
                'labels'               => array(
                    'name'               => __('Event Transcript', 'post type general name'),
                    'singular_name'      => __('Event Transcript', 'post type singular name'),
                    'add_new'            => __('Add New', 'event'),
                    'add_new_item'       => __('Add New Event Transcript'),
                    'edit_item'          => __('Edit Event Transcript'),
                    'new_item'           => __('New Event Transcript'),
                    'all_items'          => __('All Event Transcripts'),
                    'view_item'          => __('View Event Transcript'),
                    'search_items'       => __('Search Event Transcripts'),
                    'not_found'          => __('No event transcripts found'),
                    'not_found_in_trash' => __('No event transcripts found in Trash'),
                    'parent_item_colon'  => '',
                    'menu_name'          => 'Event Transcripts',
                ),
                'show_ui'              => true,
                'show_in_menu'         => true,
                'query_var'            => true,
                'rewrite'              => true,
                'publicly_queryable'   => true,
                'exclude_from_search'  => false,
                'capability_type'      => ['event_transcript','event_transcripts'],
                'map_meta_cap'         => true,
                'has_archive'          => true,
                'hierarchical'         => false,
                'menu_icon'            => 'dashicons-edit',
                'menu_position'        => 10,
                'supports'             => array('title','editor','thumbnail','excerpt', 'custom-fields'),
                'public'               => true,
            )
        );

        unregister_taxonomy_for_object_type('post_tag', 'event_calendar');
        unregister_taxonomy_for_object_type('post_tag', 'event_listing');
        unregister_taxonomy_for_object_type('post_tag', 'event_transcript');
        register_taxonomy_for_object_type('post_tag', 'event');

        register_taxonomy(
            'event_category',
            array('event', 'event_calendar', 'event_listing'),
            array(
                'hierarchical' => true,
                'labels'       => array(
                    'name'              => _x('Categories', 'taxonomy general name'),
                    'singular_name'     => _x('Category', 'taxonomy singular name'),
                    'search_items'      => __('Search Categories'),
                    'all_items'         => __('All Categories'),
                    'parent_item'       => __('Parent Category'),
                    'parent_item_colon' => __('Parent Category:'),
                    'edit_item'         => __('Edit Category'),
                    'update_item'       => __('Update Category'),
                    'add_new_item'      => __('Add New Category'),
                    'new_item_name'     => __('New Category Name'),
                    'menu_name'         => __('Categories'),
                ),
                'show_ui'      => true,
                'query_var'    => true,
            )
        );

        register_taxonomy(
            'event_season',
            array('event', 'event_calendar', 'event_listing', 'event_module'),
            array(
                'hierarchical' => true,
                'labels'       => array(
                    'name'              => _x('Seasons', 'taxonomy general name'),
                    'singular_name'     => _x('Season', 'taxonomy singular name'),
                    'search_items'      => __('Search Seasons'),
                    'all_items'         => __('All Seasons'),
                    'parent_item'       => __('Parent Season'),
                    'parent_item_colon' => __('Parent Season:'),
                    'edit_item'         => __('Edit Season'),
                    'update_item'       => __('Update Season'),
                    'add_new_item'      => __('Add New Season'),
                    'new_item_name'     => __('New Season Name'),
                    'menu_name'         => __('Seasons'),
                ),
                'show_ui'      => true,
                'query_var'    => true,
            )
        );

        register_taxonomy(
            'event_venue',
            array('event', 'event_calendar', 'event_listing', 'event_module'),
            array(
                'hierarchical'          => true,
                'labels'                => array(
                    'name'              => _x('Venues', 'taxonomy general name'),
                    'singular_name'     => _x('Venue', 'taxonomy singular name'),
                    'search_items'      => __('Search Venues'),
                    'all_items'         => __('All Venues'),
                    'parent_item'       => __('Parent Venue'),
                    'parent_item_colon' => __('Parent Venue:'),
                    'edit_item'         => __('Edit Venue'),
                    'update_item'       => __('Update Venue'),
                    'add_new_item'      => __('Add New Venue'),
                    'new_item_name'     => __('New Venue Name'),
                    'menu_name'         => __('Venues'),
                ),
                'show_ui'               => true,
                'show_in_quick_edit'    => false,
                'update_count_callback' => '_update_post_term_count',
                'query_var'             => true,
            )
        );

        register_taxonomy(
            'occurrence_category',
            ['event_listing', 'event_calendar'],
            [
                'hierarchical'          => true,
                'labels'                => array(
                    'name'              => _x('Occurrence Categories', 'taxonomy general name'),
                    'singular_name'     => _x('Occurrence Category', 'taxonomy singular name'),
                    'search_items'      => __('Search Occurrence Categories'),
                    'all_items'         => __('All Occurrence Categories'),
                    'parent_item'       => __('Parent Occurrence Category'),
                    'parent_item_colon' => __('Parent Occurrence Category:'),
                    'edit_item'         => __('Edit Occurrence Category'),
                    'update_item'       => __('Update Occurrence Category'),
                    'add_new_item'      => __('Add New Occurrence Category'),
                    'new_item_name'     => __('New Occurrence Category Name'),
                    'menu_name'         => __('Occurrence Categories'),
                ),
                'show_ui'               => true,
                'show_in_quick_edit'    => false,
                'update_count_callback' => '_update_post_term_count',
                'query_var'             => true,
                'show_in_menu'          => false // added as separate function
            ]
        );

        do_action('vem_initializer_register');
    }

    public function addOccurrenceCategoryToMenu() {
        add_submenu_page('edit.php?post_type=event', 'Occurrence Categories', 'Occurrence Categories', 'manage_options', 'edit-tags.php?taxonomy=occurrence_category');
    }

    public function highlightOccurrenceCategoryParent($parentFile) {
        global $current_screen;

        $taxonomy = $current_screen->taxonomy;
        if ($taxonomy == 'occurrence_category') {
            $parentFile = 'edit.php?post_type=event';
        }

        return $parentFile;
    }

    public function addCapabilities() {

        if ( !function_exists( 'populate_roles' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/schema.php' );
        }
        populate_roles();

        $roles = [
            'administrator',
            'editor',
            'author',
            'contributor'
        ];

        foreach ($roles as $role) {
            $r = get_role($role);

            switch ($role) {
                case 'administrator':
                case 'editor':
                    $r->add_cap('edit_others_events');
                    $r->add_cap('delete_others_events');
                    $r->add_cap('delete_private_events');
                    $r->add_cap('edit_private_events');
                    $r->add_cap('read_private_events');
                    $r->add_cap('publish_events');
                    $r->add_cap('edit_published_events');
                    $r->add_cap('delete_published_events');
                    $r->add_cap('read_events');
                    $r->add_cap('edit_events');
                    $r->add_cap('delete_events');
                    $r->add_cap('edit_others_event_calendars');
                    $r->add_cap('delete_others_event_calendars');
                    $r->add_cap('delete_private_event_calendars');
                    $r->add_cap('edit_private_event_calendars');
                    $r->add_cap('read_private_event_calendars');
                    $r->add_cap('publish_event_calendars');
                    $r->add_cap('edit_published_event_calendars');
                    $r->add_cap('delete_published_event_calendars');
                    $r->add_cap('read_event_calendars');
                    $r->add_cap('edit_event_calendars');
                    $r->add_cap('delete_event_calendars');
                    $r->add_cap('edit_others_event_listings');
                    $r->add_cap('delete_others_event_listings');
                    $r->add_cap('delete_private_event_listings');
                    $r->add_cap('edit_private_event_listings');
                    $r->add_cap('read_private_event_listings');
                    $r->add_cap('publish_event_listings');
                    $r->add_cap('edit_published_event_listings');
                    $r->add_cap('delete_published_event_listings');
                    $r->add_cap('read_event_listings');
                    $r->add_cap('edit_event_listings');
                    $r->add_cap('delete_event_listings');
                    $r->add_cap('edit_others_event_transcripts');
                    $r->add_cap('delete_others_event_transcripts');
                    $r->add_cap('delete_private_event_transcripts');
                    $r->add_cap('edit_private_event_transcripts');
                    $r->add_cap('read_private_event_transcripts');
                    $r->add_cap('publish_event_transcripts');
                    $r->add_cap('edit_published_event_transcripts');
                    $r->add_cap('delete_published_event_transcripts');
                    $r->add_cap('read_event_transcripts');
                    $r->add_cap('edit_event_transcripts');
                    $r->add_cap('delete_event_transcripts');
                break;

                case 'author':
                    $r->remove_cap('edit_others_events');
                    $r->remove_cap('delete_others_events');
                    $r->remove_cap('delete_private_events');
                    $r->remove_cap('edit_private_events');
                    $r->remove_cap('read_private_events');
                    $r->remove_cap('publish_events');
                    $r->add_cap('edit_published_events');
                    $r->add_cap('delete_published_events');
                    $r->add_cap('read_events');
                    $r->add_cap('edit_events');
                    $r->add_cap('delete_events');
                    $r->remove_cap('edit_others_event_calendars');
                    $r->remove_cap('delete_others_event_calendars');
                    $r->remove_cap('delete_private_event_calendars');
                    $r->remove_cap('edit_private_event_calendars');
                    $r->remove_cap('read_private_event_calendars');
                    $r->remove_cap('publish_event_calendars');
                    $r->add_cap('edit_published_event_calendars');
                    $r->add_cap('delete_published_event_calendars');
                    $r->add_cap('read_event_calendars');
                    $r->add_cap('edit_event_calendars');
                    $r->add_cap('delete_event_calendars');
                    $r->remove_cap('edit_others_event_listings');
                    $r->remove_cap('delete_others_event_listings');
                    $r->remove_cap('delete_private_event_listings');
                    $r->remove_cap('edit_private_event_listings');
                    $r->remove_cap('read_private_event_listings');
                    $r->remove_cap('publish_event_listings');
                    $r->add_cap('edit_published_event_listings');
                    $r->add_cap('delete_published_event_listings');
                    $r->add_cap('read_event_listings');
                    $r->add_cap('edit_event_listings');
                    $r->add_cap('delete_event_listings');
                    $r->remove_cap('edit_others_event_transcripts');
                    $r->remove_cap('delete_others_event_transcripts');
                    $r->remove_cap('delete_private_event_transcripts');
                    $r->remove_cap('edit_private_event_transcripts');
                    $r->remove_cap('read_private_event_transcripts');
                    $r->remove_cap('publish_event_transcripts');
                    $r->add_cap('edit_published_event_transcripts');
                    $r->add_cap('delete_published_event_transcripts');
                    $r->add_cap('read_event_transcripts');
                    $r->add_cap('edit_event_transcripts');
                    $r->add_cap('delete_event_transcripts');
                    break;

                case 'contributor':
                    $r->remove_cap('edit_others_events');
                    $r->remove_cap('delete_others_events');
                    $r->remove_cap('delete_private_events');
                    $r->remove_cap('edit_private_events');
                    $r->remove_cap('read_private_events');
                    $r->remove_cap('publish_events');
                    $r->remove_cap('edit_published_events');
                    $r->remove_cap('delete_published_events');
                    $r->remove_cap('read_events');
                    $r->add_cap('edit_events');
                    $r->add_cap('delete_events');
                    $r->remove_cap('edit_others_event_calendars');
                    $r->remove_cap('delete_others_event_calendars');
                    $r->remove_cap('delete_private_event_calendars');
                    $r->remove_cap('edit_private_event_calendars');
                    $r->remove_cap('read_private_event_calendars');
                    $r->remove_cap('publish_event_calendars');
                    $r->remove_cap('edit_published_event_calendars');
                    $r->remove_cap('delete_published_event_calendars');
                    $r->remove_cap('read_event_calendars');
                    $r->remove_cap('edit_event_calendars');
                    $r->remove_cap('delete_event_calendars');
                    $r->remove_cap('edit_others_event_listings');
                    $r->remove_cap('delete_others_event_listings');
                    $r->remove_cap('delete_private_event_listings');
                    $r->remove_cap('edit_private_event_listings');
                    $r->remove_cap('read_private_event_listings');
                    $r->remove_cap('publish_event_listings');
                    $r->remove_cap('edit_published_event_listings');
                    $r->remove_cap('delete_published_event_listings');
                    $r->remove_cap('read_event_listings');
                    $r->remove_cap('edit_event_listings');
                    $r->remove_cap('delete_event_listings');
                    $r->remove_cap('edit_others_event_transcripts');
                    $r->remove_cap('delete_others_event_transcripts');
                    $r->remove_cap('delete_private_event_transcripts');
                    $r->remove_cap('edit_private_event_transcripts');
                    $r->remove_cap('read_private_event_transcripts');
                    $r->remove_cap('publish_event_transcripts');
                    $r->remove_cap('edit_published_event_transcripts');
                    $r->remove_cap('delete_published_event_transcripts');
                    $r->remove_cap('read_event_transcripts');
                    $r->remove_cap('edit_event_transcripts');
                    $r->remove_cap('delete_event_transcripts');
                    break;
            }
        }
    }

    public function hidePreviewButton() {
        global $post_type;

        $types = [
            'event',
            'event_listing',
            'event_calendar'
        ];

        if (in_array($post_type, $types)) {
            echo '<style>#preview-action, .vc_control-preview { display:none !important; }</style>';
        }
    }

    public function adjustMenu() {
        global $submenu;

        $toRemove = apply_filters('vem-remove-submenu-items', [
            ['postType' => 'event_calendar', 'taxonomy' => 'event_category'],
            ['postType' => 'event_calendar', 'taxonomy' => 'event_season'],
            ['postType' => 'event_calendar', 'taxonomy' => 'event_venue'],
            ['postType' => 'event_listing', 'taxonomy' => 'event_category'],
            ['postType' => 'event_listing', 'taxonomy' => 'event_season'],
            ['postType' => 'event_listing', 'taxonomy' => 'event_venue'],
            ['postType' => 'event_module', 'taxonomy' => 'event_category'],
            ['postType' => 'event_module', 'taxonomy' => 'event_season'],
            ['postType' => 'event_module', 'taxonomy' => 'event_venue'],
            ['postType' => 'event_feed', 'taxonomy' => 'event_category'],
            ['postType' => 'event_feed', 'taxonomy' => 'event_season'],
            ['postType' => 'event_feed', 'taxonomy' => 'event_venue']
        ]);

        foreach ($toRemove as $item) {
           remove_submenu_page("edit.php?post_type={$item['postType']}", "edit-tags.php?taxonomy={$item['taxonomy']}&amp;post_type={$item['postType']}");
        }
    }

    public function setEditEventColumns($columns) {
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'event_id' => __('Event ID'),
            'event_categories' => __('Categories'),
            'event_seasons' => __('Seasons'),
            'dates' => __('Event Dates with Venue')
        );
    }

    public function setEditEventListingColumns($columns) {
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'listing_shortcode' => 'Listing Shortcode',
            'search_shortcode' => 'Search Shortcode',
            'date' => 'Date'
        );
    }

    public function setEditEventCalendarColumns($columns) {
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'calendar_shortcode' => 'Calendar Shortcode',
            'key_shortcode' => 'Calendar Key Shortcode',
            'date' => 'Date'
        );
    }

    public function setEventCustomColumns($column) {
        
        global $post;
        if ($post->post_type != 'event') return;

        switch ($column) {
            case 'event_categories':
                $event_categories = get_the_terms(0, "event_category");
                if (is_array($event_categories)){
                    foreach ($event_categories as $event_category) {
                        echo '<a href="' . get_term_link($event_category->slug, "event_category") . '">' . $event_category->name . '</a>';
                    }
                }
                break;
                
            case 'event_seasons':
                $event_seasons = get_the_terms(0, "event_season");
                if (is_array($event_seasons)){
                    foreach ($event_seasons as $event_season) {
                        echo '<a href="' . get_term_link($event_season->slug, "event_season") . '">' . $event_season->name . '</a>';
                    }
                }
                break;

            case 'dates':
                global $ventureEventSystem;

                // We don't want to limit dates retrieved based on status
                add_filter('vem_date_allowed_event_status', function($statuses) { return []; });
                $vem3 = new VentureEventManager3();
                $vem3->setContext('admin-events-list');
                $vem3->setEventId($post->ID);
                $vem3->retrieveDates();
                $dates = $vem3->getOccurrences();

                $shown = 0;
                $max = apply_filters('vem_max_dates_in_admin', 6);
                foreach ($dates as $d) {
                    if ($shown == $max) {
                        echo 'First '.$max.' of '.sizeof($dates).' shown.';
                        break;
                    } else {
                        echo $ventureEventSystem->getFormattedDateTime($d['start_time'],true,'F j, Y').
                                '&nbsp;&nbsp;'.$ventureEventSystem->getFormattedDateTime($d['start_time'],false,'','default','').
                                '&nbsp;&nbsp;'.$d['venue'].'<br />';
                        $shown++;
                    }
                }
                break;
                
            case 'event_id':
                echo $post->ID;
                break;
                
        }
        
    }

    public function setEventListingCustomColumns($column) {
        
        global $post;
        if ($post->post_type != 'event_listing') return;

        switch ($column) {
            case 'listing_shortcode':
                echo '[vemlisting id="'.$post->ID.'"]';
                break;
                
            case 'search_shortcode':
                $venture = VentureFramework::getInstance('venture-event-listing');
                $isSearch = $venture->getOption('is-search', $post->ID);
                if ($isSearch) {
                    echo '[vemsearch id="'.$post->ID.'"]';
                }
                break;
                
        }
        
    }

    public function setEventCalendarCustomColumns($column) {
        
        global $post;
        if ($post->post_type != 'event_calendar') return;

        switch ($column) {
            case 'calendar_shortcode':
                echo '[vemcalendar id="'.$post->ID.'"]';
                break;
                
            case 'key_shortcode':
                echo '[vemkey id="'.$post->ID.'"]';
                break;
                
        }
        
    }

    public function setEditEventCategoryColumns($theme_columns) {
        $new_columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name'),
            'color' => __("Color"),
            'description' => __('Description'),
            'slug' => __('Slug'),
            'posts' => __('Posts')
            );
        return $new_columns;
    }

    public function setEventCategoryCustomColumns($out, $column_name, $term_id) {
        
        global $post;
        
        switch ($column_name) {
            case 'color':   
                $color = get_term_meta($term_id, 'color', true);
                $text = VentureUtility::getContrastYIQ($color);
                echo '<div style="height:100%;width:100%;margin:3px;padding:5px 0;text-align:center;background-color:' . $color . ';color:'.$text.'">' . $color . '</div>';
                break;
        }
    }

    public function sortEventsByDate($orderby) {
        global $pagenow, $wpdb;

        $table_name = $wpdb->prefix . VEM_EVENT_DATES_TABLE;
        if (is_admin() && $pagenow=='edit.php' && isset($_GET['post_type']) && ($_GET['post_type']=='event')) {
          return "(SELECT min(start_time) FROM `{$table_name}` WHERE `post_ID` = `{$wpdb->prefix}posts`.`ID`) DESC";
        }
            return $orderby;
    }

    public function defaultEventFields() {
        return apply_filters('vem_event_settings_default_event_fields', [
            'title' => [
                'key' => 'title',
                'value' => 'Title'
            ],
            'vem_pre_title' => [
                'key' => 'vem_pre_title',
                'value' => 'Pre-Title'
            ],
            'vem_post_title' => [
                'key' => 'vem_post_title',
                'value' => 'Post-Title'
            ],
            'thumbnail_image' => [
                'key' => 'thumbnail_image',
                'value' => 'Thumbnail Image'
            ],
            'medium_image' => [
                'key' => 'medium_image',
                'value' => 'Medium Image'
            ],
            'large_image' => [
                'key' => 'large_image',
                'value' => 'Large Image'
            ],
            'full_image' => [
                'key' => 'full_image',
                'value' => 'Full Image'
            ],
            'vem_event_details' => [
                'key' => 'vem_event_details',
                'value' => 'Event Details'
            ],
            'excerpt' => [
                'key' => 'excerpt',
                'value' => 'Excerpt'
            ],
            'vem_event_transcript' => [
                'key' => 'vem_event_transcript',
                'value' => 'Link to Transcript'
            ],
            'occurrences' => [
                'key' => 'occurrences',
                'value' => 'Occurrences'
            ],
            'vem_fields_set_one' => [
                'key' => 'vem_fields_set_one',
                'value' => 'Field Set 1'
            ],
            'vem_fields_set_two' => [
                'key' => 'vem_fields_set_two',
                'value' => 'Field Set 2'
            ],
            'vem_fields_set_three' => [
                'key' => 'vem_fields_set_three',
                'value' => 'Field Set 3'
            ],
            'vem_calendar' => [
                'key' => 'vem_calendar',
                'value' => 'Single Event Calendar'
            ]
        ]);
    }

    public function defaultOccurrenceFields() {
        return apply_filters('vem_event_settings_default_occurrence_fields', [
            'vem_dates_address' => [
                'key' => 'vem_dates_address',
                'value' => 'Address'
            ],
            'vem_dates_venue_ID' => [
                'key' => 'vem_dates_venue_ID',
                'value' => 'Venue'
            ],
            'vem_dates_date_time' => [
                'key' => 'vem_dates_date_time',
                'value' => 'Date and Time'
            ],
            'vem_dates_date' => [
                'key' => 'vem_dates_date',
                'value' => 'Date'
            ],
            'vem_dates_time' => [
                'key' => 'vem_dates_time',
                'value' => 'Time'
            ],
            'vem_dates_status' => [
                'key' => 'vem_dates_status',
                'value' => 'Event Status (GSD)'
            ],
            'vem_dates_ticket_price' => [
                'key' => 'vem_dates_ticket_price',
                'value' => 'First Ticket Price'
            ],
            'vem_dates_ticket_url' => [
                'key' => 'vem_dates_ticket_url',
                'value' => 'First Ticket URL'
            ],
            'vem_dates_ticket2_price' => [
                'key' => 'vem_dates_ticket2_price',
                'value' => 'Second Ticket Price'
            ],
            'vem_dates_ticket2_url' => [
                'key' => 'vem_dates_ticket2_url',
                'value' => 'Second Ticket URL'
            ],
            'vem_dates_note' => [
                'key' => 'vem_dates_note',
                'value' => 'Note'
            ],
            'vem_dates_import' => [
                'key' => 'vem_dates_import',
                'value' => 'Calendar Import'
            ],
            'vem_dates_terms' => [
                'key' => 'vem_dates_terms',
                'value' => 'Occurrence Categories'
            ]
        ]);
    }

    public function defaultFields($eventFields, $dateFields) {
        return [
            'indexPageFields' => apply_filters('vem_event_settings_index_page_fields', $eventFields),
            'indexOccurrenceFields' => apply_filters('vem_event_settings_index_occurrence_fields', $dateFields),
            'singlePageFields' => apply_filters('vem_event_settings_single_page_fields', $eventFields),
            'singleOccurrenceFields' => apply_filters('vem_event_settings_single_occurrence_fields', $dateFields),
            'archivePageFields' => apply_filters('vem_event_settings_archive_page_fields', $eventFields),
            'archiveOccurrenceFields' => apply_filters('vem_event_settings_archive_occurrence_fields', $dateFields),
            'archivesPageFields' => apply_filters('vem_event_settings_archives_page_fields', $eventFields),
            'archivesOccurrenceFields' => apply_filters('vem_event_settings_archives_occurrence_fields', $dateFields)
        ];
    }

}

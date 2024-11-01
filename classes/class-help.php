<?php

class VentureHelp
{

	public function __construct() {
        add_action('admin_head', [$this, 'setHelp']);
        add_action('admin_notices', [$this, 'notices']);
        add_action('vem-update-notices', [$this, 'welcome']);
        add_action('admin_footer', [$this, 'clearNotices']);
    }

    public function notices() {
        $lastVersion = get_option('vem-notices-last-version', '0.0.0');

        if (version_compare($lastVersion, '3.1.5', '<')) {
            $class = 'notice notice-warning';
            $message = 'Venture Event Manager: In some cases, after updating to v3.1.4+ some menu items may be hidden until you deactivate and reactivate the plugin. Visit the <a href="';
            $message .= get_admin_url(null, 'admin.php?page=venture-options&tab=welcome').'">Venture Welcome</a> page for more information and to dismiss this message.';

        	echo '<div class="'.$class.'"><p>'.$message.'</p></div>';
        }
    }

    public function welcome() {

        $lastVersion = get_option('vem-notices-last-version', '0.0.0');

        if (version_compare($lastVersion, '3.1.5', '<')) {
            echo <<<NOTICE
            <div class="notice-warning" style="border:1px solid #ddd; border-left:4px solid orange; padding:20px; background-color:#efefef">
                <p>In v3.1.4, some of the capabilities used to determine access to various post types (like events, event listings, etc) were decoupled from those used to
                determine access to the built in "post" post type. In some cases, this has meant that until the current user's capabilities are refreshed, some menu
                items might appear missing.</p>
                <p>Fixing this is usually as simple as deactivating and reactivating the Venture Event Manager plugin. We recommend you do this now.</p>
            </div>
NOTICE;
        }
    }

    public function clearNotices() {
        $screen = get_current_screen();
        if ($screen->id == 'toplevel_page_venture-options' && array_key_exists('tab', $_GET) && $_GET['tab'] == welcome) {
            update_option('vem-notices-last-version', VEM_VERSION);
        }
    }

    public function setHelp() {
        $screen = get_current_screen();

        $topic = 'Base';
        switch ($screen->base) {
        	case 'post':

        		switch ($screen->post_type) {
                    case 'event':
                        $topic = 'EventEditor';
                        break;

                    case 'event_calendar':
                        $topic = 'EventCalendarEditor';
                        break;

                    case 'event_listing':
                        $topic = 'EventListingEditor';
                        break;

                    case 'event_module':
                        $topic = 'EventModuleEditor';
                        break;
        		}
        		break;

            case 'edit':

                switch ($screen->id) {
                    case 'edit-event':
                        $topic = 'AllEvents';
                        break;

                    case 'edit-event_calendar':
                        $topic = 'AllEventCalendars';
                        break;

                    case 'edit-event_listing':
                        $topic = 'AllEventListings';
                        break;

                    case 'edit-event_module':
                        $topic = 'AllEventModules';
                        break;
                }
                break;

            case 'edit-tags':

                switch ($screen->id) {
                    case 'edit-event_category':
                        $topic = 'AllEventCategories';
                        break;

                    case 'edit-event_season':
                        $topic = 'AllEventSeasons';
                        break;

                    case 'edit-event_venue':
                        $topic = 'AllEventVenues';
                        break;

                    case 'edit-media_type':
                        $topic = 'AllEventMediaTypes';
                        break;
                }
                break;

            case 'term':

                switch ($screen->id) {
                    case 'edit-event_category':
                        $topic = 'AllEventCategories';
                        break;

                    case 'edit-event_season':
                        $topic = 'AllEventSeasons';
                        break;

                    case 'edit-event_venue':
                        $topic = 'AllEventVenues';
                        break;

                    case 'edit-media_type':
                        $topic = 'AllEventMediaTypes';
                        break;
                }
                break;

            case 'toplevel_page_venture-options':

                $tab = $_GET['tab'] ?? 'general-settings';
                switch ($tab) {
                    case 'general-settings':
                        $topic = 'VentureSettingsGeneral';
                        break;

                    case 'run-dates-display':
                        $topic = 'VentureSettingsRunDates';
                        break;

                    case 'occurrence-status-display':
                        $topic = 'VentureSettingsOccurrenceStatusDisplay';
                        break;

                    case 'default-event-lists':
                        $topic = 'VentureSettingsDefaultEventLists';
                        break;

                    case 'default-single-page':
                        $topic = 'VentureSettingsDefaultSinglePage';
                        break;

                    case 'archived-lists':
                        $topic = 'VentureSettingsArchivedLists';
                        break;

                    case 'archived-single-pages':
                        $topic = 'VentureSettingsArchivedSinglePage';
                        break;
                }
                break;

        }

        if ($topic != 'Base') {
        	require_once('help/class-help-'.$topic.'.php');

            // If we end up adding any default help, we will move this out of this if-block
            $class = 'VentureHelp'.$topic;
            $help = new $class();
            $help->setHelp($screen);
        }

    }
}

class VentureHelpBase {

	public function setHelp($screen) {
        // By default, no help screens (these would appear on every admin page)
	}

}
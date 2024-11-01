<?php
/*
Plugin Name: Venture Event Manager
Version: 3.3.1
Plugin URI: http://www.ventureeventmanager.com/
Description: Manage events, including lists, widgets, multiple dates, maps, and other totally excellent features, all so you can rock it like Robin Goodfellow
Author: Venture Industries Online
Author URI: http://www.ventureindustriesonline.com/
Text Domain: venture-event-manager
License:     GPL-3.0+
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

define('VEM_DB_VERSION', '3.2.2');
define('VEM_VERSION', '3.3.1');
define('VEM_PLUGIN_NAME', 'venture-event-manager');
define('VEM_EVENT_DATES_TABLE', 'vem_event_dates');
define('VEM_DATE_TERM_TABLE', 'vem_date_terms');

// Utility classes
require_once('classes/class-utilities.php');

// Venture Framework
require_once('includes/venture/venture-framework-embedder.php');

// Venture Event Manager Classes
require_once('classes/class-system.php');
require_once('classes/class-event-manager3.php');

// Widgets
require_once('classes/class-widget-what-when-where.php');

global $ventureEventSystem;
$ventureEventSystem = new VentureEventSystem();

register_activation_hook(__FILE__, ['VentureEventSystem', 'install']);
register_deactivation_hook(__FILE__, ['VentureEventSystem', 'uninstall']);

<?php
/**
 * Plugin Name: WPshop
 * Plugin URI:
 * Description:
 * Version: 2.0.0
 * Author:
 * Author URI:
 * License:
 * License URI:
 *
 * @package WPshop
 */

namespace wpshop;

defined( 'ABSPATH' ) || exit;

DEFINE( 'PLUGIN_WPSHOP_PATH', realpath( plugin_dir_path( __FILE__ ) ) . '/' );
DEFINE( 'PLUGIN_WPSHOP_URL', plugins_url( basename( __DIR__ ) ) . '/' );
DEFINE( 'PLUGIN_WPSHOP_DIR', basename( __DIR__ ) );
DEFINE( 'PLUGIN_WPSHOP_DEV_MODE', false );

if ( ! PLUGIN_WPSHOP_DEV_MODE ) {
	require_once 'core/external/eo-framework/eo-framework.php';
}


// Include composer component.
require_once 'vendor/autoload.php';

// Boot your plugin.
\eoxia\Init_Util::g()->exec( PLUGIN_WPSHOP_PATH, basename( __FILE__, '.php' ) );

<?php
/**
 * WCUganda functions and definitions.
 *
 * Bootstraps the theme by defining shared constants and loading the modules
 * under /inc/. Each concern lives in its own file: setup hooks, enqueue,
 * menus, widgets, helpers, and presentation filters.
 *
 * @package WCUganda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------------
// Theme constants.
// -----------------------------------------------------------------------------
if ( ! defined( 'WCU_THEME_VERSION' ) ) {
	$wcu_theme   = wp_get_theme( 'wcuganda' );
	$wcu_version = $wcu_theme instanceof WP_Theme ? (string) $wcu_theme->get( 'Version' ) : '1.0.0';
	define( 'WCU_THEME_VERSION', '' !== $wcu_version ? $wcu_version : '1.0.0' );
}

if ( ! defined( 'WCU_THEME_DIR' ) ) {
	define( 'WCU_THEME_DIR', get_template_directory() );
}

if ( ! defined( 'WCU_THEME_URI' ) ) {
	define( 'WCU_THEME_URI', get_template_directory_uri() );
}

// -----------------------------------------------------------------------------
// Require theme modules.
// -----------------------------------------------------------------------------
$wcu_includes = array(
	'/inc/hooks.php',
	'/inc/enqueue.php',
	'/inc/menus.php',
	'/inc/widgets.php',
	'/inc/helpers.php',
	'/inc/template-functions.php',
	'/inc/template-tags.php',
	'/inc/customizer.php',
	'/inc/api/class-wporg-events.php',
);

foreach ( $wcu_includes as $wcu_include ) {
	$wcu_include_path = WCU_THEME_DIR . $wcu_include;
	if ( file_exists( $wcu_include_path ) ) {
		require_once $wcu_include_path;
	}
}

unset( $wcu_includes, $wcu_include, $wcu_include_path, $wcu_theme, $wcu_version );

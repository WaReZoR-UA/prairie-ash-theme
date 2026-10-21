<?php
/**
 * Theme bootstrap.
 *
 * Registers and delegates. Each concern lives in its own file under inc/ and is
 * included from the bottom of this one.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * The theme version, read from the style.css header.
 *
 * Used for the theme's identity only. Assets are versioned on their own
 * modification time by prairie_ash_asset_version().
 */
define( 'PRAIRIE_ASH_VERSION', wp_get_theme()->get( 'Version' ) );

/**
 * Cache-busting version for a theme asset, from the file's own modification time.
 *
 * Versioning an asset on the release string serves the previous file from cache
 * for every edit made inside one version, and a hard reload does not beat it.
 *
 * @since 1.0.0
 *
 * @param string $relative_path Path relative to the theme root, e.g. 'assets/css/main.css'.
 * @return string A version string, or the theme version when the file is missing.
 */
function prairie_ash_asset_version( $relative_path ) {
	$absolute = get_theme_file_path( $relative_path );

	if ( file_exists( $absolute ) ) {
		return (string) filemtime( $absolute );
	}

	return PRAIRIE_ASH_VERSION;
}

require_once get_theme_file_path( 'inc/setup.php' );
require_once get_theme_file_path( 'inc/enqueue.php' );
require_once get_theme_file_path( 'inc/template-tags.php' );
require_once get_theme_file_path( 'inc/images.php' );
require_once get_theme_file_path( 'inc/site-settings.php' );
require_once get_theme_file_path( 'inc/navigation.php' );
require_once get_theme_file_path( 'inc/blocks.php' );
require_once get_theme_file_path( 'inc/fields.php' );
require_once get_theme_file_path( 'inc/forms.php' );
require_once get_theme_file_path( 'inc/performance.php' );
require_once get_theme_file_path( 'inc/seo.php' );

if ( class_exists( 'WooCommerce' ) ) {
	require_once get_theme_file_path( 'inc/woocommerce.php' );
	require_once get_theme_file_path( 'inc/shop-wording.php' );
	require_once get_theme_file_path( 'inc/checkout.php' );
}

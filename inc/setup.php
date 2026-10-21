<?php
/**
 * Theme supports, menus, editor styles and the field-plugin lock.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare what the theme supports.
 *
 * Add_theme_support() runs on after_setup_theme, because init is too late for
 * post thumbnails.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'comment-list', 'comment-form' )
	);

	// The canvas gets the design's stylesheets, the split ones included, and nothing reaches the admin chrome.
	add_theme_support( 'editor-styles' );
	add_editor_style( prairie_ash_editor_sheets() );

	// Patterns from wordpress.org carry core blocks this design does not style.
	remove_theme_support( 'core-block-patterns' );

	add_theme_support( 'woocommerce' );

	register_nav_menus(
		array(
			'primary' => __( 'Main navigation', 'prairie-ash' ),
			'legal'   => __( 'Footer legal links', 'prairie-ash' ),
		)
	);
}
add_action( 'after_setup_theme', 'prairie_ash_setup' );

/**
 * Load the theme's translations.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_load_textdomain() {
	load_theme_textdomain( 'prairie-ash', get_theme_file_path( 'languages' ) );
}
add_action( 'init', 'prairie_ash_load_textdomain' );

/**
 * Keep field-group JSON inside the theme, should anyone export a group.
 *
 * Groups are registered in PHP, so nothing is read from here. The save point
 * stops an accidental admin save landing in the plugin's own folder.
 *
 * @since 1.0.0
 *
 * @param string $path Default save path.
 * @return string
 */
function prairie_ash_acf_json_save_point( $path ) {
	unset( $path );

	return get_theme_file_path( 'acf-json' );
}
add_filter( 'acf/settings/save_json', 'prairie_ash_acf_json_save_point' );

/**
 * Hide the field-plugin admin on production.
 *
 * Field groups are code and arrive by deployment. A group edited on a live site
 * exists in no commit and the next deploy reverts it.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_lock_fields_on_production() {
	if ( 'production' === wp_get_environment_type() ) {
		add_filter( 'acf/settings/show_admin', '__return_false' );
	}
}
add_action( 'after_setup_theme', 'prairie_ash_lock_fields_on_production' );

/**
 * Drop an empty class attribute from an attachment image.
 *
 * Wp_get_attachment_image() prints every attribute it is given, so an empty
 * class lands as class="" where the accepted markup carries none. The image
 * slot helpers pass an empty class to ask for no attribute at all.
 *
 * @since 1.0.0
 *
 * @param array $attr Attributes for the image markup.
 * @return array
 */
function prairie_ash_drop_empty_image_class( $attr ) {
	if ( isset( $attr['class'] ) && '' === trim( (string) $attr['class'] ) ) {
		unset( $attr['class'] );
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'prairie_ash_drop_empty_image_class' );

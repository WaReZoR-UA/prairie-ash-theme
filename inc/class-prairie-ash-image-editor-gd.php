<?php
/**
 * Core's GD image editor with one addition: turning a palette image into a
 * true colour one, which GD needs before it can write WebP or AVIF.
 *
 * Loaded only while the theme writes the full-size copy of an image, and
 * offered to core through the wp_image_editors filter for that one call, so
 * every other image operation on the site uses core's own editor.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';

/**
 * GD image editor that can convert a palette image to true colour.
 *
 * @since 1.2.1
 */
class Prairie_Ash_Image_Editor_GD extends WP_Image_Editor_GD {

	/**
	 * Convert a palette image, such as an indexed PNG, to true colour.
	 *
	 * GD writes neither WebP nor AVIF from a palette image: the save fails
	 * and leaves an empty file. Transparency is kept, since the alpha
	 * channel is saved as it is after the conversion.
	 *
	 * @since 1.2.1
	 *
	 * @return bool Whether the image was converted.
	 */
	public function make_truecolor() {
		if ( ! is_gd_image( $this->image ) || imageistruecolor( $this->image ) ) {
			return false;
		}

		if ( ! imagepalettetotruecolor( $this->image ) ) {
			return false;
		}

		imagealphablending( $this->image, false );
		imagesavealpha( $this->image, true );

		return true;
	}
}

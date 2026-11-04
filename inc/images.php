<?php
/**
 * The media pipeline: the sub-sizes the design draws, the format and quality
 * they are saved in, and the sizes attribute of every image slot.
 *
 * The full account, with the measured slot widths behind every number, is in
 * PERFORMANCE.md.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * The widths every image slot in the theme is drawn from.
 *
 * One ladder for every slot, because every slot keeps the picture's own
 * proportions and crops with object-fit in the stylesheet, so a width-only
 * size serves them all and lands in every srcset. The rungs were chosen
 * against every slot width measured from 360 to 1920 pixels wide, at one and
 * two device pixels per CSS pixel: 160 answers the cart and receipt
 * thumbnails, 320 to 720 the phone, the cards and the desktop columns, 960 to
 * 1600 the tablet column and the two-pixel desktop. The top of the ladder is
 * the full image itself, in the same three formats as the rungs, and core
 * never writes a rung wider than the upload.
 *
 * @since 1.0.0
 *
 * @return int[] Widths in pixels, smallest first.
 */
function prairie_ash_image_widths() {
	return array( 160, 320, 480, 720, 960, 1280, 1600 );
}

/**
 * The sizes attribute of every image slot, keyed by the slot's own class.
 *
 * Each value is the width the picture is drawn at, measured in a browser at
 * thirteen viewport widths from 360 to 1920 and fitted per breakpoint of the
 * design: the phone and tablet column below 992 pixels, the desktop layout
 * up to 1440, and the capped container above it. Where a slot crops a
 * picture with object-fit, the value is the width of the cropped picture,
 * not of its box, because that is the width the file has to cover. A slot
 * whose layout changes in this table changes here and nowhere else.
 *
 * @since 1.0.0
 *
 * @param string $slot The slot's class, e.g. 'hero__media'.
 * @return string The sizes attribute, or 100vw for an unknown slot.
 */
function prairie_ash_slot_sizes( $slot ) {
	$column = '(max-width: 61.99875em) calc(93vw - 22px), ';

	$sizes = array(
		// The opening photo of Home is the one exception to measuring: each
		// art-directed export is offered at its own width. The opening scene
		// reads the picture's natural width to find the geometry of that
		// export, and with a width-described srcset the natural width is the
		// sizes value.
		'hero__media'              => '390px',
		'hero__media-tablet'       => '768px',
		'hero__media-desktop'      => '1440px',
		'roastery-title__media'    => '(max-width: 89.99875em) calc(93vw - 22px), 1312px',
		'visit-title__media'       => $column . '720px',
		'roastery__media'          => $column . '640px',
		'roastery__team-media'     => $column . '600px',
		'coffees__media'           => $column . '522px',
		'cupping__media'           => $column . '600px',
		'cupping__media-wide'      => 'calc(93vw - 22px)',
		'menu__media'              => $column . '600px',
		'wholesale__media'         => $column . '640px',
		'visit__media'             => $column . '600px',
		'shop-title__media'        => $column . '640px',
		'menu-title__media'        => '(max-width: 61.99875em) calc(90vw - 68px), 560px',
		'menu-pastries__media'     => $column . '640px',
		'roastery-roaster__media'  => $column . '640px',
		'origin__media'            => $column . '624px',
		'roastery-people__media'   => $column . '480px',
		'roastery-cupping__media'  => $column . '600px',
		'about-story__media'       => $column . '(max-width: 74.99875em) 768px, 640px',
		'contact-title__media'     => $column . '644px',
		'contact-message__media'   => $column . '664px',
		'wholesale-inquiry__media' => $column . '664px',
		'sent__media'              => $column . '668px',
		'wholesale-title__media'   => $column . '540px',
		'signup-done__media'       => $column . '718px',
		'shipping-title__media'    => $column . '644px',
		'shipping-part__media'     => $column . '524px',
		'cart-empty__media'        => $column . '560px',
		'product-story__media'     => $column . '720px',
		'product-brewing__media'   => $column . '640px',
		'person__media'            => '(max-width: 33.75em) calc(93vw - 22px), (max-width: 47.99875em) 484px, (max-width: 61.99875em) calc(39vw - 11px), 484px',
		'visit-access__photo'      => '(max-width: 47.99875em) calc(93vw - 22px), (max-width: 61.99875em) calc(49vw - 12px), (max-width: 89.99875em) calc(49vw - 14px), 676px',
		'visit-route__map'         => '(max-width: 44.5em) calc(93vw - 22px), 640px',
		'visit-route__map-wide'    => '640px',
		'product-card__media'      => '(max-width: 47.99875em) calc(93vw - 22px), (max-width: 61.99875em) calc(46vw - 12px), (max-width: 89.99875em) calc(31vw - 13px), 424px',
		'shop-list__next-media'    => '(max-width: 47.99875em) calc(156vw - 36px), (max-width: 61.99875em) calc(77vw - 25px), (max-width: 89.99875em) calc(62vw - 16px), 868px',
		'product-summary__media'   => '(max-width: 47.99875em) calc(93vw - 22px), (max-width: 61.99875em) 300px, 564px',
		'product-summary__detail'  => '(max-width: 47.99875em) calc(47vw - 18px), (max-width: 61.99875em) 144px, 276px',
		'cart-line__thumb'         => '(max-width: 47.99875em) 80px, (max-width: 61.99875em) 96px, 120px',
		'review-line__thumb'       => '(max-width: 47.99875em) 56px, (max-width: 61.99875em) 72px, 80px',
		'receipt-line__thumb'      => '(max-width: 47.99875em) 64px, 80px',
	);

	return $sizes[ $slot ] ?? '100vw';
}

/**
 * Register the theme's image sizes, one per width on the ladder.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_image_sizes() {
	foreach ( prairie_ash_image_widths() as $width ) {
		add_image_size( 'prairie-ash-' . $width, $width, 0, false );
	}
}
add_action( 'after_setup_theme', 'prairie_ash_register_image_sizes' );

/**
 * Keep only the theme's own sizes in the size registry.
 *
 * Core's thumbnail, medium, medium_large and large sizes are options rather
 * than registered sizes, so they cannot be removed with remove_image_size().
 * Filtering the registry removes them from every place that reads it: the
 * sub-sizes made on upload, the regeneration tools, and the size list the
 * block editor reads when it processes an upload in the browser, which reads
 * the registry and never intermediate_image_sizes_advanced.
 *
 * @since 1.0.0
 *
 * @param string[] $sizes Registered size names.
 * @return string[]
 */
function prairie_ash_keep_theme_image_sizes( $sizes ) {
	return array_values(
		array_filter(
			$sizes,
			static function ( $size ) {
				return str_starts_with( $size, 'prairie-ash-' );
			}
		)
	);
}
add_filter( 'intermediate_image_sizes', 'prairie_ash_keep_theme_image_sizes' );

/**
 * Unregister the sizes core and plugins add on top of the theme's.
 *
 * The registry filter already stops them being generated. Unregistering them
 * as well matters for WooCommerce, which resizes an image on the fly and saves
 * the file whenever one of its own sizes is requested and has_image_size()
 * still answers true. Runs after WooCommerce registers its sizes on init.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_remove_other_image_sizes() {
	foreach ( array_keys( wp_get_additional_image_sizes() ) as $size ) {
		if ( ! str_starts_with( $size, 'prairie-ash-' ) ) {
			remove_image_size( $size );
		}
	}
}
add_action( 'init', 'prairie_ash_remove_other_image_sizes', 20 );

/**
 * The formats written beside every JPEG and PNG upload and each of its sizes,
 * best first: AVIF, then WebP, each only where the image editor can write it.
 *
 * The upload's own format stays on disk as the fallback every browser can
 * show, so a host without AVIF support serves WebP and JPEG with no change to
 * the theme.
 *
 * @since 1.0.0
 *
 * @return string[] MIME types.
 */
function prairie_ash_image_formats() {
	static $formats = null;

	if ( null === $formats ) {
		$formats = array();

		foreach ( array( 'image/avif', 'image/webp' ) as $mime ) {
			if ( wp_image_editor_supports( array( 'mime_type' => $mime ) ) ) {
				$formats[] = $mime;
			}
		}
	}

	return $formats;
}

/**
 * The file name of an image in another format.
 *
 * The source extension stays in the name, so photo-720x480.jpg becomes
 * photo-720x480-jpg.avif and can never collide with a separate upload called
 * photo.avif.
 *
 * @since 1.0.0
 *
 * @param string $file File name or path of the source image.
 * @param string $mime Target MIME type.
 * @return string
 */
function prairie_ash_format_file_name( $file, $mime ) {
	$info = pathinfo( $file );
	$dir  = isset( $info['dirname'] ) && '.' !== $info['dirname'] ? trailingslashit( $info['dirname'] ) : '';

	return $dir . $info['filename'] . '-' . strtolower( $info['extension'] ) . '.' . ( 'image/avif' === $mime ? 'avif' : 'webp' );
}

/**
 * The full image an attachment's copies are made from.
 *
 * @since 1.2.1
 *
 * @param array $data Attachment metadata.
 * @return string Absolute path of a JPEG or PNG on disk, or an empty string
 *                when the attachment gets no copies.
 */
function prairie_ash_image_source( $data ) {
	if ( ! is_array( $data ) || empty( $data['file'] ) || empty( $data['width'] ) ) {
		return '';
	}

	$full = trailingslashit( wp_get_upload_dir()['basedir'] ) . $data['file'];
	$type = wp_check_filetype( $full )['type'];

	if ( ! in_array( $type, array( 'image/jpeg', 'image/png' ), true ) || ! file_exists( $full ) ) {
		return '';
	}

	return $full;
}

/**
 * The files an attachment has copies of: the full image and each size.
 *
 * @since 1.2.1
 *
 * @param array  $data Attachment metadata.
 * @param string $full Absolute path of the full image.
 * @return array Size name, or full, to the source path, its width and its
 *               height. Width and height are 0 for the full image, which is
 *               copied without a resize.
 */
function prairie_ash_image_targets( $data, $full ) {
	$dir     = trailingslashit( dirname( $full ) );
	$targets = array( 'full' => array( $full, 0, 0 ) );

	foreach ( (array) ( $data['sizes'] ?? array() ) as $name => $size ) {
		if ( ! empty( $size['file'] ) ) {
			$targets[ $name ] = array( $dir . $size['file'], (int) $size['width'], (int) $size['height'] );
		}
	}

	return $targets;
}

/**
 * Whether a file on disk is a usable copy in the given format.
 *
 * An encoder that runs out of memory or time can leave an empty file behind,
 * and a browser offered an empty source shows a broken image instead of
 * falling back to the next one. A copy therefore counts only when it is
 * larger than 0 bytes and its header reads as an image of that format with a
 * width and a height, which costs one short read and no decoding.
 *
 * @since 1.2.1
 *
 * @param string $path Absolute path of the copy.
 * @param string $mime MIME type the copy has to be.
 * @return bool
 */
function prairie_ash_is_image_copy( $path, $mime ) {
	clearstatcache( true, $path );

	if ( ! is_file( $path ) || wp_filesize( $path ) < 1 ) {
		return false;
	}

	$size = wp_getimagesize( $path );

	return is_array( $size ) && ! empty( $size[0] ) && ! empty( $size[1] ) && isset( $size['mime'] ) && $mime === $size['mime'];
}

/**
 * Switch the writing of copies off for the rest of the request, or ask
 * whether it is off.
 *
 * The demo import and the repair write copies in their own batches, one
 * file at a time against a time budget. While they run, a metadata write only
 * records the copies already on disk.
 *
 * @since 1.2.1
 *
 * @param bool|null $defer True or false to set, null to read.
 * @return bool Whether writing is deferred.
 */
function prairie_ash_defer_image_formats( $defer = null ) {
	static $deferred = false;

	if ( null !== $defer ) {
		$deferred = (bool) $defer;
	}

	return $deferred;
}

/**
 * Collect a line for the import or repair log, or take the lines collected.
 *
 * @since 1.2.1
 *
 * @param string|null $line A line to add, or null to take every line so far.
 * @return string[] The lines taken, or an empty array when a line was added.
 */
function prairie_ash_image_log( $line = null ) {
	static $lines = array();

	if ( null !== $line ) {
		$lines[] = $line;
		return array();
	}

	$taken = $lines;
	$lines = array();

	return $taken;
}

/**
 * The copies that could not be written, as recorded.
 *
 * @since 1.2.1
 *
 * @return array Key of the copy to its record: attachment, file relative to
 *               the uploads folder, reason, and the pass it failed in.
 */
function prairie_ash_image_failures() {
	$failures = get_option( 'prairie_ash_image_failures', array() );

	return is_array( $failures ) ? $failures : array();
}

/**
 * Record a copy that could not be written, or clear the record of one that
 * now has been.
 *
 * @since 1.2.1
 *
 * @param string     $key    Attachment ID, size name and MIME type.
 * @param array|null $record The failure, or null to clear it.
 * @return void
 */
function prairie_ash_set_image_failure( $key, $record ) {
	$failures = prairie_ash_image_failures();

	if ( null === $record ) {
		if ( ! isset( $failures[ $key ] ) ) {
			return;
		}

		unset( $failures[ $key ] );
	} else {
		$failures[ $key ] = $record;
		prairie_ash_image_log( sprintf( 'Could not write %1$s: %2$s', $record['file'], $record['reason'] ) );
	}

	update_option( 'prairie_ash_image_failures', $failures, false );
}

/**
 * The failures as lines for a screen or a terminal, file first.
 *
 * @since 1.2.1
 *
 * @return string[]
 */
function prairie_ash_image_failure_lines() {
	$lines = array();

	foreach ( prairie_ash_image_failures() as $record ) {
		$lines[] = $record['file'] . ': ' . $record['reason'];
	}

	sort( $lines );

	return $lines;
}

/**
 * The attachments whose copies a request ran out of time for.
 *
 * @since 1.2.1
 *
 * @return int[]
 */
function prairie_ash_image_pending() {
	$pending = get_option( 'prairie_ash_image_pending', array() );

	return is_array( $pending ) ? array_map( 'absint', $pending ) : array();
}

/**
 * Mark an attachment as waiting for copies, or as having all of them.
 *
 * @since 1.2.1
 *
 * @param int  $attachment_id Attachment ID.
 * @param bool $waiting       Whether copies are still to be written.
 * @return void
 */
function prairie_ash_set_image_pending( $attachment_id, $waiting ) {
	$pending = prairie_ash_image_pending();
	$listed  = in_array( (int) $attachment_id, $pending, true );

	if ( $waiting === $listed ) {
		return;
	}

	$pending = $waiting ? array_merge( $pending, array( (int) $attachment_id ) ) : array_diff( $pending, array( (int) $attachment_id ) );

	update_option( 'prairie_ash_image_pending', array_values( $pending ), false );
}

/**
 * Roughly how many bytes of PHP memory one copy takes to write.
 *
 * GD holds the decoded full image at four bytes a pixel, plus the file and
 * the decoder's buffers, and the resized image at four bytes a pixel.
 * Measured with GD on the demo photographs: decoding peaks at 5 to 7 bytes a
 * pixel of the full image, and in an admin request a 1280 pixel copy took
 * 26 MB from a 2.4 million pixel photograph and 17 MB from a 1.2 million
 * pixel one, counted in PHP's 2 MB blocks. The estimate takes 7 bytes a pixel
 * for the full image, 5 for the resized one and 6 MB for the blocks and the
 * rest, so it errs on the side of skipping.
 *
 * @since 1.2.1
 *
 * @param int $source_pixels Pixels of the full image.
 * @param int $target_pixels Pixels of the copy, 0 when it is not resized.
 * @return int Bytes.
 */
function prairie_ash_image_copy_memory( $source_pixels, $target_pixels ) {
	return (int) ( $source_pixels * 7 + $target_pixels * 5 + 6 * MB_IN_BYTES );
}

/**
 * Roughly how many seconds one copy takes to write.
 *
 * Measured with GD at the theme's quality settings on the demo photographs:
 * AVIF took 1.3 to 2.1 seconds for 1.2 to 3.4 million pixels, WebP 0.1 to 0.2
 * seconds. The estimate is set above both, since a shared host is slower.
 *
 * @since 1.2.1
 *
 * @param int    $pixels Pixels of the copy.
 * @param string $mime   Target MIME type.
 * @return float Seconds.
 */
function prairie_ash_image_copy_seconds( $pixels, $mime ) {
	$megapixels = $pixels / 1000000;

	return 'image/avif' === $mime ? 0.5 + 1.5 * $megapixels : 0.2 + 0.25 * $megapixels;
}

/**
 * The image editor a copy is written with.
 *
 * GD cannot write WebP or AVIF from a palette PNG: the save fails and leaves
 * an empty file. A copy that is resized is already true colour, because the
 * resize draws into a new true colour image, so only a full-size copy needs
 * the conversion, and the theme's own GD editor provides it.
 *
 * @since 1.2.1
 *
 * @param string $full      Absolute path of the full image.
 * @param bool   $truecolor Whether to convert a palette image to true colour.
 * @return WP_Image_Editor|WP_Error
 */
function prairie_ash_image_copy_editor( $full, $truecolor ) {
	if ( ! $truecolor ) {
		return wp_get_image_editor( $full );
	}

	$swap = static function ( $editors ) {
		require_once get_theme_file_path( 'inc/class-prairie-ash-image-editor-gd.php' );

		return array_map(
			static function ( $editor ) {
				return 'WP_Image_Editor_GD' === $editor ? 'Prairie_Ash_Image_Editor_GD' : $editor;
			},
			(array) $editors
		);
	};

	add_filter( 'wp_image_editors', $swap );
	$editor = wp_get_image_editor( $full );
	remove_filter( 'wp_image_editors', $swap );

	if ( ! is_wp_error( $editor ) && $editor instanceof Prairie_Ash_Image_Editor_GD ) {
		$editor->make_truecolor();
	}

	return $editor;
}

/**
 * Write one copy, or say why it was not written.
 *
 * Before the encoder starts, the copy has to fit: in the memory PHP has left,
 * and in the time the request has left. A copy that could never fit is
 * reported and skipped. A copy that does not fit in what is left of this
 * request waits for the next one, except as the first copy of a request, so
 * every request writes at least one. The copy being written is noted in an
 * option first: when the request dies inside the encoder, the next attempt
 * finds the note and reports the copy instead of dying on it again.
 *
 * A failed or empty output is deleted at once, so no empty file is ever on
 * disk to be taken for a copy.
 *
 * @since 1.2.1
 *
 * @param int    $attachment_id Attachment ID.
 * @param array  $data          Attachment metadata.
 * @param string $name          Size name, or full.
 * @param array  $target        Source path, width and height of the size.
 * @param string $mime          Target MIME type.
 * @param string $pass          The import or repair run this belongs to, so a
 *                              copy that failed is tried once per run.
 * @return true|WP_Error True when written, a prairie_ash_image_later error
 *                       when it waits for the next request, any other error
 *                       when it failed.
 */
function prairie_ash_write_image_copy( $attachment_id, $data, $name, $target, $mime, $pass = '' ) {
	static $started = false;
	static $tried   = array();

	list( $source, $width, $height ) = $target;

	$full     = prairie_ash_image_source( $data );
	$copy     = prairie_ash_format_file_name( $source, $mime );
	$key      = $attachment_id . ':' . $name . ':' . $mime;
	$folder   = dirname( $data['file'] );
	$relative = ( '.' === $folder ? '' : $folder . '/' ) . wp_basename( $copy );
	$failures = prairie_ash_image_failures();
	$fail     = static function ( $reason ) use ( $key, $attachment_id, $relative, $copy, $pass ) {
		if ( file_exists( $copy ) ) {
			wp_delete_file( $copy );
		}

		prairie_ash_set_image_failure(
			$key,
			array(
				'attachment' => (int) $attachment_id,
				'file'       => $relative,
				'reason'     => $reason,
				'pass'       => $pass,
			)
		);

		return new WP_Error( 'prairie_ash_image_failed', $reason );
	};

	if ( isset( $failures[ $key ] ) && ( isset( $tried[ $key ] ) || ( '' !== $pass && $pass === $failures[ $key ]['pass'] ) ) ) {
		return new WP_Error( 'prairie_ash_image_failed', $failures[ $key ]['reason'] );
	}

	$tried[ $key ] = true;

	if ( get_option( 'prairie_ash_image_busy' ) === $key ) {
		delete_option( 'prairie_ash_image_busy' );

		return $fail( 'the request stopped while this copy was being written, most likely at the memory or time limit of the server.' );
	}

	$source_pixels = (int) $data['width'] * (int) $data['height'];
	$target_pixels = $width ? $width * $height : $source_pixels;

	wp_raise_memory_limit( 'image' );

	$limit = wp_convert_hr_to_bytes( (string) ini_get( 'memory_limit' ) );
	$need  = prairie_ash_image_copy_memory( $source_pixels, $width ? $target_pixels : 0 );
	// Memory PHP freed and keeps cached is handed back when a new block needs it, so live usage is what counts.
	$free = $limit - memory_get_usage();

	if ( $limit > 0 && $need > $free ) {
		return $fail(
			sprintf(
				'skipped, it needs about %1$d MB of memory and %2$d MB are free under the memory limit of %3$d MB.',
				ceil( $need / MB_IN_BYTES ),
				max( 0, floor( $free / MB_IN_BYTES ) ),
				floor( $limit / MB_IN_BYTES )
			)
		);
	}

	$seconds = (int) ini_get( 'max_execution_time' );

	if ( $seconds > 0 ) {
		$estimate = prairie_ash_image_copy_seconds( $target_pixels, $mime );

		if ( $estimate > $seconds * 0.6 ) {
			return $fail( sprintf( 'skipped, it would take longer than the time limit of %d seconds allows.', $seconds ) );
		}

		if ( $started && timer_float() + max( $estimate * 3, $estimate + 5 ) > $seconds ) {
			return new WP_Error( 'prairie_ash_image_later', 'No time left in this request.' );
		}
	}

	$started = true;

	update_option( 'prairie_ash_image_busy', $key, false );

	/**
	 * Filters the writing of one AVIF or WebP copy before the encoder runs.
	 *
	 * Return true when the copy was written another way, or a WP_Error to
	 * report it as failed. Either way the file is checked afterwards, and an
	 * empty or unreadable one counts as a failure.
	 *
	 * @since 1.2.1
	 *
	 * @param null|true|WP_Error $written       Null to let the theme write it.
	 * @param string             $copy          Absolute path of the copy.
	 * @param string             $mime          Target MIME type.
	 * @param int                $attachment_id Attachment ID.
	 * @param string             $name          Size name, or full.
	 */
	$saved = apply_filters( 'prairie_ash_pre_write_image_copy', null, $copy, $mime, $attachment_id, $name );

	if ( null === $saved ) {
		$editor = prairie_ash_image_copy_editor( $full, ! $width );
		$saved  = $editor;

		if ( ! is_wp_error( $editor ) && $width ) {
			$saved = $editor->resize( $width, $height, true );
		}

		if ( ! is_wp_error( $saved ) ) {
			$saved = $editor->save( $copy, $mime );
		}

		unset( $editor );
	}

	delete_option( 'prairie_ash_image_busy' );

	if ( is_wp_error( $saved ) ) {
		return $fail( 'the image editor reported: ' . $saved->get_error_message() );
	}

	if ( ! prairie_ash_is_image_copy( $copy, $mime ) ) {
		return $fail( 'the image editor wrote an empty or unreadable file.' );
	}

	prairie_ash_set_image_failure( $key, null );

	return true;
}

/**
 * Write every copy of an attachment that is missing, empty or older than its
 * source.
 *
 * @since 1.2.1
 *
 * @param int    $attachment_id Attachment ID.
 * @param array  $data          Attachment metadata.
 * @param string $pass          The import or repair run, see
 *                              prairie_ash_write_image_copy().
 * @param float  $until         Time a batch has to stop by, or 0 for none.
 * @return bool True when every copy was written or reported, false when the
 *              request ran out of time and the rest waits.
 */
function prairie_ash_write_image_copies( $attachment_id, $data, $pass = '', $until = 0.0 ) {
	$full = prairie_ash_image_source( $data );

	if ( '' === $full ) {
		return true;
	}

	foreach ( prairie_ash_image_formats() as $mime ) {
		foreach ( prairie_ash_image_targets( $data, $full ) as $name => $target ) {
			if ( ! file_exists( $target[0] ) ) {
				continue;
			}

			$copy = prairie_ash_format_file_name( $target[0], $mime );

			if ( prairie_ash_is_image_copy( $copy, $mime ) && filemtime( $copy ) >= filemtime( $target[0] ) ) {
				prairie_ash_set_image_failure( $attachment_id . ':' . $name . ':' . $mime, null );
				continue;
			}

			if ( $until && microtime( true ) >= $until ) {
				return false;
			}

			$written = prairie_ash_write_image_copy( $attachment_id, $data, $name, $target, $mime, $pass );

			if ( is_wp_error( $written ) && 'prairie_ash_image_later' === $written->get_error_code() ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Record the copies that are on disk in the attachment metadata, and only
 * those.
 *
 * A copy is recorded under a sources key beside its file only when it is a
 * usable image of its format. A record of a missing or empty copy is
 * dropped, and an empty or unreadable file is deleted, so the picture never
 * offers a browser a source it cannot show.
 *
 * @since 1.2.1
 *
 * @param array $data Attachment metadata.
 * @return array
 */
function prairie_ash_record_image_formats( $data ) {
	$full = prairie_ash_image_source( $data );

	if ( '' === $full ) {
		return $data;
	}

	foreach ( prairie_ash_image_targets( $data, $full ) as $name => $target ) {
		$sources = 'full' === $name ? ( $data['sources'] ?? array() ) : ( $data['sizes'][ $name ]['sources'] ?? array() );

		foreach ( array( 'image/avif', 'image/webp' ) as $mime ) {
			$copy = prairie_ash_format_file_name( $target[0], $mime );
			unset( $sources[ $mime ] );

			if ( prairie_ash_is_image_copy( $copy, $mime ) ) {
				$sources[ $mime ] = array(
					'file'     => wp_basename( $copy ),
					'filesize' => (int) wp_filesize( $copy ),
				);
			} elseif ( file_exists( $copy ) ) {
				wp_delete_file( $copy );
			}
		}

		if ( 'full' === $name ) {
			$data['sources'] = $sources;
			if ( ! $sources ) {
				unset( $data['sources'] );
			}
		} else {
			$data['sizes'][ $name ]['sources'] = $sources;
			if ( ! $sources ) {
				unset( $data['sizes'][ $name ]['sources'] );
			}
		}
	}

	return $data;
}

/**
 * Write the AVIF and WebP copies of an image and each of its sizes, and record
 * them in the attachment metadata.
 *
 * Runs on every metadata write, which covers an upload, a regeneration and the
 * sizes the block editor uploads after processing an image in the browser.
 * Every copy is resized from the full image rather than from the JPEG size, so
 * it is compressed once. A usable copy newer than its source is kept, so the
 * repeated writes core makes while it builds the sizes encode each copy once.
 *
 * A copy that does not fit in what is left of the request marks the
 * attachment as waiting, and the Repair image copies button or the
 * wp prairie-ash-demo repair-images command writes it. While the demo import
 * or a repair runs, copies are written in their batches and this only
 * records them.
 *
 * Copies are recorded under a sources key beside each file, and
 * prairie_ash_delete_image_formats() removes them with the attachment.
 *
 * @since 1.0.0
 * @since 1.2.1 Writes only usable copies, records only what is on disk, and
 *              defers what does not fit in the request.
 *
 * @param array $data          Attachment metadata.
 * @param int   $attachment_id Attachment ID.
 * @return array
 */
function prairie_ash_add_image_formats( $data, $attachment_id ) {
	if ( '' === prairie_ash_image_source( $data ) ) {
		return $data;
	}

	if ( ! prairie_ash_defer_image_formats() ) {
		prairie_ash_set_image_pending( $attachment_id, ! prairie_ash_write_image_copies( $attachment_id, $data ) );
	}

	return prairie_ash_record_image_formats( $data );
}
add_filter( 'wp_update_attachment_metadata', 'prairie_ash_add_image_formats', 10, 2 );

/**
 * Write the missing copies of one attachment and record them.
 *
 * Used by the demo import and the repair, which call it once per attachment
 * against a time budget and come back to the same attachment when it
 * returns false.
 *
 * @since 1.2.1
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $pass          The import or repair run.
 * @param float  $until         Time the batch has to stop by, or 0 for none.
 * @return bool True when the attachment is finished.
 */
function prairie_ash_complete_image_copies( $attachment_id, $pass, $until = 0.0 ) {
	$data = wp_get_attachment_metadata( $attachment_id );

	if ( '' === prairie_ash_image_source( $data ) ) {
		prairie_ash_set_image_pending( $attachment_id, false );
		return true;
	}

	$deferred = prairie_ash_defer_image_formats();
	$done     = prairie_ash_write_image_copies( $attachment_id, $data, $pass, $until );

	prairie_ash_defer_image_formats( true );
	wp_update_attachment_metadata( $attachment_id, $data );
	prairie_ash_defer_image_formats( $deferred );
	prairie_ash_set_image_pending( $attachment_id, ! $done );

	return $done;
}

/**
 * Every JPEG and PNG attachment, in the order the repair walks them.
 *
 * @since 1.2.1
 *
 * @return int[] Attachment IDs, lowest first.
 */
function prairie_ash_image_attachments() {
	return array_map(
		'absint',
		get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => array( 'image/jpeg', 'image/png' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
			)
		)
	);
}

/**
 * Repair the copies of every image, as many as fit in a time budget.
 *
 * Finds each image whose sizes or copies are missing, empty or unreadable,
 * lets core write the missing sizes, and writes the missing copies. Called
 * again with the cursor it returns, it carries on where it stopped.
 *
 * @since 1.2.1
 *
 * @param int    $after Repair the attachments after this ID.
 * @param string $pass  The repair run.
 * @param float  $until Time to stop by, or 0 for none.
 * @return int|true The last attachment finished, or true when all are.
 */
function prairie_ash_repair_image_copies( $after, $pass, $until = 0.0 ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';

	prairie_ash_defer_image_formats( true );

	foreach ( prairie_ash_image_attachments() as $attachment_id ) {
		if ( $attachment_id <= $after ) {
			continue;
		}

		if ( $until && microtime( true ) >= $until ) {
			return $after;
		}

		if ( wp_get_missing_image_subsizes( $attachment_id ) ) {
			wp_update_image_subsizes( $attachment_id );
		}

		if ( ! prairie_ash_complete_image_copies( $attachment_id, $pass, $until ) ) {
			return $after;
		}

		$after = $attachment_id;
	}

	return true;
}

/**
 * How many copies are missing or empty right now, without writing any.
 *
 * @since 1.2.1
 *
 * @return int
 */
function prairie_ash_missing_image_copies() {
	$missing = 0;

	foreach ( prairie_ash_image_attachments() as $attachment_id ) {
		$data = wp_get_attachment_metadata( $attachment_id );
		$full = prairie_ash_image_source( $data );

		if ( '' === $full ) {
			continue;
		}

		foreach ( prairie_ash_image_formats() as $mime ) {
			foreach ( prairie_ash_image_targets( $data, $full ) as $target ) {
				$copy = prairie_ash_format_file_name( $target[0], $mime );

				clearstatcache( true, $copy );

				if ( file_exists( $target[0] ) && ( ! is_file( $copy ) || wp_filesize( $copy ) < 1 ) ) {
					++$missing;
				}
			}
		}
	}

	return $missing;
}

/**
 * Delete the AVIF and WebP copies with their attachment.
 *
 * Core deletes the files its own metadata names and nothing under sources.
 * Every file at a copy's name is deleted, recorded or not, so an empty file
 * an encoder left behind goes as well. Runs before core removes the
 * metadata.
 *
 * @since 1.0.0
 * @since 1.2.1 Deletes unrecorded copies and the attachment's failure records.
 *
 * @param int $post_id Attachment ID.
 * @return void
 */
function prairie_ash_delete_image_formats( $post_id ) {
	$meta = wp_get_attachment_metadata( $post_id );

	if ( ! is_array( $meta ) || empty( $meta['file'] ) ) {
		return;
	}

	$base  = wp_get_upload_dir()['basedir'];
	$full  = trailingslashit( $base ) . $meta['file'];
	$dir   = trailingslashit( dirname( $full ) );
	$files = array();

	foreach ( prairie_ash_image_targets( $meta, $full ) as $target ) {
		foreach ( array( 'image/avif', 'image/webp' ) as $mime ) {
			$files[] = prairie_ash_format_file_name( $target[0], $mime );
		}
	}

	foreach ( array_merge( array( $meta['sources'] ?? array() ), array_column( (array) ( $meta['sizes'] ?? array() ), 'sources' ) ) as $sources ) {
		foreach ( (array) $sources as $source ) {
			$files[] = $dir . $source['file'];
		}
	}

	foreach ( array_unique( $files ) as $file ) {
		if ( file_exists( $file ) ) {
			wp_delete_file_from_directory( $file, $base );
		}
	}

	foreach ( array_keys( prairie_ash_image_failures() ) as $key ) {
		if ( str_starts_with( $key, (int) $post_id . ':' ) ) {
			prairie_ash_set_image_failure( $key, null );
		}
	}

	prairie_ash_set_image_pending( $post_id, false );
}
add_action( 'delete_attachment', 'prairie_ash_delete_image_formats' );

/**
 * The srcset of an image in another format, candidate for candidate.
 *
 * Built from core's own srcset for the image, so every format offers the same
 * widths. A format offers a source only when it has a copy of every
 * candidate: with one missing, the browser would take a narrower copy in that
 * format over the right width in the next one, so the whole format is left
 * out and the picture falls back to the next format. A copy is recorded only
 * when it is a usable file, and a record with no size, which an older version
 * of the theme could write for an empty file, counts as no copy.
 *
 * @since 1.0.0
 * @since 1.2.1 Leaves the format out unless every candidate has a copy.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $mime          Target MIME type.
 * @return string The srcset, or an empty string when there is no complete
 *                set of copies.
 */
function prairie_ash_format_srcset( $attachment_id, $mime ) {
	$meta = wp_get_attachment_metadata( $attachment_id );

	if ( ! is_array( $meta ) || empty( $meta['file'] ) ) {
		return '';
	}

	$copies = array();

	if ( ! empty( $meta['sources'][ $mime ]['filesize'] ) ) {
		$copies[ wp_basename( $meta['file'] ) ] = $meta['sources'][ $mime ]['file'];
	}

	foreach ( (array) ( $meta['sizes'] ?? array() ) as $size ) {
		if ( ! empty( $size['sources'][ $mime ]['filesize'] ) ) {
			$copies[ $size['file'] ] = $size['sources'][ $mime ]['file'];
		}
	}

	$srcset = (string) wp_get_attachment_image_srcset( $attachment_id, 'full' );

	if ( '' === $srcset ) {
		$url  = (string) wp_get_attachment_image_url( $attachment_id, 'full' );
		$name = wp_basename( $url );

		return isset( $copies[ $name ] ) ? substr( $url, 0, -strlen( $name ) ) . $copies[ $name ] : '';
	}

	$out = array();

	foreach ( explode( ', ', $srcset ) as $candidate ) {
		list( $url, $descriptor ) = array_pad( explode( ' ', trim( $candidate ), 2 ), 2, '' );
		$name                     = wp_basename( $url );

		if ( ! isset( $copies[ $name ] ) ) {
			return '';
		}

		$out[] = trim( substr( $url, 0, -strlen( $name ) ) . $copies[ $name ] . ' ' . $descriptor );
	}

	return implode( ', ', $out );
}

/**
 * Print the source elements of a picture, best format first.
 *
 * With a media query the image's own format follows as a source of its own,
 * because an art-directed picture needs every format under its media query
 * before the default sources begin.
 *
 * @since 1.0.0
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $sizes         The sizes attribute.
 * @param string $media         Optional media query.
 * @return void
 */
function prairie_ash_picture_sources( $attachment_id, $sizes, $media = '' ) {
	$formats = prairie_ash_image_formats();

	if ( '' !== $media ) {
		$formats[] = (string) get_post_mime_type( $attachment_id );
	}

	foreach ( $formats as $mime ) {
		$srcset = in_array( $mime, array( 'image/avif', 'image/webp' ), true )
			? prairie_ash_format_srcset( $attachment_id, $mime )
			: (string) wp_get_attachment_image_srcset( $attachment_id, 'full' );

		if ( '' === $srcset && ! in_array( $mime, array( 'image/avif', 'image/webp' ), true ) ) {
			$srcset = (string) wp_get_attachment_image_url( $attachment_id, 'full' );
		}

		if ( '' === $srcset ) {
			continue;
		}

		printf(
			'<source%s type="%s" srcset="%s" sizes="%s">',
			'' !== $media ? ' media="' . esc_attr( $media ) . '"' : '',
			esc_attr( $mime ),
			esc_attr( $srcset ),
			esc_attr( $sizes )
		);
	}
}


/**
 * Encode AVIF at quality 75.
 *
 * Core encodes every format but WebP at 82, which for AVIF is far above the
 * JPEG it replaces. Measured on three of the design's photographs at 800
 * pixels wide: AVIF at 75 matched core's JPEG at 82 on SSIM (0.978, 0.974 and
 * 0.979 against 0.977, 0.973 and 0.981) at 43 to 56 percent of its bytes.
 * WebP keeps core's own default of 86.
 *
 * @since 1.0.0
 *
 * @param int    $quality   Quality level between 1 and 100.
 * @param string $mime_type The MIME type being written.
 * @return int
 */
function prairie_ash_image_quality( $quality, $mime_type ) {
	if ( 'image/avif' === $mime_type ) {
		return 75;
	}

	return $quality;
}
add_filter( 'wp_editor_set_quality', 'prairie_ash_image_quality', 10, 2 );

/**
 * Lazy-load every image except the one marked as the Largest Contentful Paint.
 *
 * Core guesses which images are in the first screen: it leaves the first
 * three content images eager, and treats every image printed before the main
 * loop as in view, which on the shop, an archive, makes the whole product grid
 * eager. This theme names the LCP image in every template and prints it with
 * loading off and fetchpriority high, so the guess is not needed: on a phone
 * every other image sits below the first screen, and fetching it eagerly takes
 * bandwidth from the LCP image. An image that already carries a loading or a
 * fetchpriority value is left exactly as it was given. Core still writes the
 * attribute.
 *
 * @since 1.0.0
 *
 * @param array  $loading_attrs The loading attributes core decided on.
 * @param string $tag_name      The tag name.
 * @param array  $attr          The attributes the tag was given.
 * @return array
 */
function prairie_ash_lazy_unless_lcp( $loading_attrs, $tag_name, $attr ) {
	if ( 'img' !== $tag_name || isset( $attr['loading'] ) || ! empty( $attr['fetchpriority'] ) ) {
		return $loading_attrs;
	}

	unset( $loading_attrs['fetchpriority'] );
	$loading_attrs['loading'] = 'lazy';

	return $loading_attrs;
}
add_filter( 'wp_get_loading_optimization_attributes', 'prairie_ash_lazy_unless_lcp', 10, 3 );

/*
 * No sizes="auto" on lazy images. Auto makes the browser size a lazy picture
 * by its box, while every value in prairie_ash_slot_sizes() is the measured
 * width of the drawn picture. Where a slot crops with object-fit the two
 * differ: with auto, the shop's next roast photo at 390 pixels fetched the
 * 480 wide file for a picture drawn 574 wide.
 */
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );

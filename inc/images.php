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
 * Write one copy, or say why it was not written.
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
 * @return true|WP_Error True when written.
 */
function prairie_ash_write_image_copy( $attachment_id, $data, $name, $target, $mime ) {
	list( $source, $width, $height ) = $target;

	$full = prairie_ash_image_source( $data );
	$copy = prairie_ash_format_file_name( $source, $mime );

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
		$editor = wp_get_image_editor( $full );
		$saved  = $editor;

		if ( ! is_wp_error( $editor ) && $width ) {
			$saved = $editor->resize( $width, $height, true );
		}

		if ( ! is_wp_error( $saved ) ) {
			$saved = $editor->save( $copy, $mime );
		}

		unset( $editor );
	}

	if ( is_wp_error( $saved ) || ! prairie_ash_is_image_copy( $copy, $mime ) ) {
		if ( file_exists( $copy ) ) {
			wp_delete_file( $copy );
		}

		return is_wp_error( $saved ) ? $saved : new WP_Error( 'prairie_ash_image_failed', 'The image editor wrote an empty or unreadable file.' );
	}

	return true;
}

/**
 * Write every copy of an attachment that is missing, empty or older than its
 * source.
 *
 * @since 1.2.1
 *
 * @param int   $attachment_id Attachment ID.
 * @param array $data          Attachment metadata.
 * @return void
 */
function prairie_ash_write_image_copies( $attachment_id, $data ) {
	$full = prairie_ash_image_source( $data );

	if ( '' === $full ) {
		return;
	}

	foreach ( prairie_ash_image_formats() as $mime ) {
		foreach ( prairie_ash_image_targets( $data, $full ) as $name => $target ) {
			if ( ! file_exists( $target[0] ) ) {
				continue;
			}

			$copy = prairie_ash_format_file_name( $target[0], $mime );

			if ( prairie_ash_is_image_copy( $copy, $mime ) && filemtime( $copy ) >= filemtime( $target[0] ) ) {
				continue;
			}

			prairie_ash_write_image_copy( $attachment_id, $data, $name, $target, $mime );
		}
	}
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
 * Copies are recorded under a sources key beside each file, and
 * prairie_ash_delete_image_formats() removes them with the attachment.
 *
 * @since 1.0.0
 * @since 1.2.1 Writes only usable copies and records only what is on disk.
 *
 * @param array $data          Attachment metadata.
 * @param int   $attachment_id Attachment ID.
 * @return array
 */
function prairie_ash_add_image_formats( $data, $attachment_id ) {
	if ( '' === prairie_ash_image_source( $data ) ) {
		return $data;
	}

	prairie_ash_write_image_copies( $attachment_id, $data );

	return prairie_ash_record_image_formats( $data );
}
add_filter( 'wp_update_attachment_metadata', 'prairie_ash_add_image_formats', 10, 2 );

/**
 * Delete the AVIF and WebP copies with their attachment.
 *
 * Core deletes the files its own metadata names and nothing under sources.
 * Every file at a copy's name is deleted, recorded or not, so an empty file
 * an encoder left behind goes as well. Runs before core removes the
 * metadata.
 *
 * @since 1.0.0
 * @since 1.2.1 Deletes unrecorded copies as well.
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

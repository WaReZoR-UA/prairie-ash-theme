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
 * Read an option from the database rather than from what this request
 * cached of it.
 *
 * Several requests can change the image records at the same time: an
 * upload, the import, the repair. A value cached at the start of a request
 * may already be out of date when it is changed and written back.
 *
 * @since 1.2.1
 *
 * @param string $option   Option name.
 * @param mixed  $fallback Value when the option does not exist.
 * @return mixed
 */
function prairie_ash_image_fresh_option( $option, $fallback = false ) {
	wp_cache_delete( $option, 'options' );

	$missing = wp_cache_get( 'notoptions', 'options' );

	if ( is_array( $missing ) && isset( $missing[ $option ] ) ) {
		unset( $missing[ $option ] );
		wp_cache_set( 'notoptions', $missing, 'options' );
	}

	return get_option( $option, $fallback );
}

/**
 * Take or give back one of the theme's image locks.
 *
 * Core's own upgrader lock: an INSERT IGNORE of an option, which only one
 * request can win, unlike add_option(), which checks and then writes. A lock
 * older than its timeout belongs to a request that died, and is taken over.
 *
 * @since 1.2.1
 *
 * @param string $name    Lock name.
 * @param bool   $take    True to take the lock, false to give it back.
 * @param int    $timeout Seconds after which a lock is taken over.
 * @param float  $wait    Seconds to keep trying, 0 for one try.
 * @return bool Whether the lock was taken, or given back.
 */
function prairie_ash_image_lock( $name, $take, $timeout = 30, $wait = 0.0 ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	if ( ! $take ) {
		return WP_Upgrader::release_lock( $name );
	}

	$until = microtime( true ) + $wait;

	do {
		if ( WP_Upgrader::create_lock( $name, $timeout ) ) {
			return true;
		}

		if ( $wait > 0 ) {
			usleep( 50000 );
		}
	} while ( microtime( true ) < $until );

	return false;
}

/**
 * Change one of the image record options without losing a change another
 * request made at the same time.
 *
 * The option is read fresh and written back while a short lock is held. When
 * the lock cannot be had within a few seconds, the change is written anyway,
 * which at worst loses a record that the next run writes again.
 *
 * @since 1.2.1
 *
 * @param string   $option Option name.
 * @param callable $change Receives the array stored and returns the new one.
 * @return void
 */
function prairie_ash_change_image_records( $option, $change ) {
	$locked  = prairie_ash_image_lock( 'prairie_ash_image_records', true, 30, 3.0 );
	$records = prairie_ash_image_fresh_option( $option, array() );
	$records = is_array( $records ) ? $records : array();
	$changed = call_user_func( $change, $records );

	if ( $changed !== $records ) {
		update_option( $option, $changed, false );
	}

	if ( $locked ) {
		prairie_ash_image_lock( 'prairie_ash_image_records', false );
	}
}

/**
 * The copies that could not be written, as recorded.
 *
 * @since 1.2.1
 *
 * @return array Key of the copy to its record: attachment, file relative to
 *               the uploads folder, reason, the kind of failure, and the pass
 *               it failed in.
 */
function prairie_ash_image_failures() {
	$failures = get_option( 'prairie_ash_image_failures', array() );

	return is_array( $failures ) ? $failures : array();
}

/**
 * The kind of a failure record.
 *
 * Crash is a copy that stopped the request writing it. Memory and time are
 * copies skipped because they would not fit the server's limits. Editor,
 * format and empty are copies the image editor failed to write. A record
 * written by version 1.2.0 carries no kind and is read from its reason.
 *
 * @since 1.2.1
 *
 * @param array $record Failure record.
 * @return string
 */
function prairie_ash_image_failure_type( $record ) {
	if ( ! empty( $record['type'] ) ) {
		return (string) $record['type'];
	}

	return str_starts_with( (string) ( $record['reason'] ?? '' ), 'the request stopped' ) ? 'crash' : 'editor';
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
	if ( null === $record && ! isset( prairie_ash_image_failures()[ $key ] ) ) {
		return;
	}

	prairie_ash_change_image_records(
		'prairie_ash_image_failures',
		static function ( $failures ) use ( $key, $record ) {
			if ( null === $record ) {
				unset( $failures[ $key ] );
			} else {
				$failures[ $key ] = $record;
			}

			return $failures;
		}
	);

	if ( null !== $record ) {
		prairie_ash_image_log( sprintf( 'Could not write %1$s: %2$s', $record['file'], $record['reason'] ) );
	}
}

/**
 * The failures as lines for a screen or a terminal, file first.
 *
 * @since 1.2.1
 *
 * @param int[]|null $attachments Only the failures of these attachments, or
 *                                null for every failure on the site.
 * @return string[]
 */
function prairie_ash_image_failure_lines( $attachments = null ) {
	$lines = array();

	foreach ( prairie_ash_image_failures() as $record ) {
		if ( null !== $attachments && ! in_array( (int) ( $record['attachment'] ?? 0 ), $attachments, true ) ) {
			continue;
		}

		$lines[] = $record['file'] . ': ' . $record['reason'];
	}

	sort( $lines );

	return $lines;
}

/**
 * Drop the failure records that no longer describe a copy the theme would
 * write.
 *
 * A record outlives its reason when the size it names was removed from the
 * metadata, when the source file is gone, when the image is no longer a JPEG
 * or a PNG, or when the server no longer writes that format. Runs when an
 * import or a repair starts, so the screen, the import and the command line
 * stop reporting copies nobody can repair. Waiting marks of attachments that
 * no longer get copies go the same way.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_prune_image_records() {
	$is_target = static function ( $attachment_id, $name, $mime ) {
		$data = wp_get_attachment_metadata( $attachment_id );
		$full = prairie_ash_image_source( $data );

		if ( '' === $full || ! in_array( $mime, prairie_ash_image_formats(), true ) ) {
			return false;
		}

		$targets = prairie_ash_image_targets( $data, $full );

		return isset( $targets[ $name ] ) && file_exists( $targets[ $name ][0] );
	};

	prairie_ash_change_image_records(
		'prairie_ash_image_failures',
		static function ( $failures ) use ( $is_target ) {
			foreach ( array_keys( $failures ) as $key ) {
				$parts = explode( ':', (string) $key, 3 );

				if ( 3 !== count( $parts ) || ! $is_target( (int) $parts[0], $parts[1], $parts[2] ) ) {
					unset( $failures[ $key ] );
				}
			}

			return $failures;
		}
	);

	prairie_ash_change_image_records(
		'prairie_ash_image_pending',
		static function ( $pending ) {
			return array_values(
				array_filter(
					array_map( 'absint', $pending ),
					static function ( $attachment_id ) {
						return '' !== prairie_ash_image_source( wp_get_attachment_metadata( $attachment_id ) );
					}
				)
			);
		}
	);
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
	$attachment_id = (int) $attachment_id;

	if ( in_array( $attachment_id, prairie_ash_image_pending(), true ) === $waiting ) {
		return;
	}

	prairie_ash_change_image_records(
		'prairie_ash_image_pending',
		static function ( $pending ) use ( $attachment_id, $waiting ) {
			$pending = array_diff( array_map( 'absint', $pending ), array( $attachment_id ) );

			if ( $waiting ) {
				$pending[] = $attachment_id;
			}

			return array_values( $pending );
		}
	);
}

/**
 * A token for this request, so its notes can be told from another's.
 *
 * @since 1.2.1
 *
 * @return string
 */
function prairie_ash_image_request_token() {
	static $token = '';

	if ( '' === $token ) {
		$token = wp_generate_uuid4();
	}

	return $token;
}

/**
 * Start an import share or a repair share, or end one.
 *
 * Only one share of either runs at a time on a site, whichever screen, tab or
 * terminal started it, because two of them would write the same copies at
 * once. The lock is given back when the share ends, also when it ends at a
 * fatal error, and a lock left by a request the server killed outright is
 * taken over after two minutes.
 *
 * @since 1.2.1
 *
 * @param bool $begin True to start a share, false to end it.
 * @return true|WP_Error True, or an error when another share is running.
 */
function prairie_ash_image_run( $begin ) {
	if ( ! $begin ) {
		if ( prairie_ash_image_holds_run() ) {
			prairie_ash_image_lock( 'prairie_ash_image_run', false );
			prairie_ash_image_holds_run( false );
		}

		return true;
	}

	if ( prairie_ash_image_holds_run() ) {
		return true;
	}

	if ( ! prairie_ash_image_lock( 'prairie_ash_image_run', true, 2 * MINUTE_IN_SECONDS ) ) {
		return new WP_Error(
			'prairie_ash_image_running',
			__( 'Another import or image copy repair is running on this site, or one stopped less than two minutes ago. Wait a little and press the button again.', 'prairie-ash' )
		);
	}

	prairie_ash_image_holds_run( true );
	prairie_ash_image_watch_shutdown();

	// The single marker of version 1.2.0, which any request could overwrite.
	if ( false !== get_option( 'prairie_ash_image_busy' ) ) {
		delete_option( 'prairie_ash_image_busy' );
	}

	return true;
}

/**
 * Whether this request holds the import and repair lock.
 *
 * @since 1.2.1
 *
 * @param bool|null $holds True or false to set, null to read.
 * @return bool
 */
function prairie_ash_image_holds_run( $holds = null ) {
	static $held = false;

	if ( null !== $holds ) {
		$held = (bool) $holds;
	}

	return $held;
}

/**
 * Whether the share running in this request has to stop now and answer.
 *
 * Set when the share found a copy that stopped an earlier request. The
 * share then ends at once and answers, so a screen that counts the requests
 * that never answered sees progress after each such copy rather than a run
 * of failures it gives up on.
 *
 * @since 1.2.1
 *
 * @param bool|null $stop True to stop, null to read.
 * @return bool
 */
function prairie_ash_image_share_stopped( $stop = null ) {
	static $stopped = false;

	if ( null !== $stop ) {
		$stopped = (bool) $stop;
	}

	return $stopped;
}

/**
 * Whether the previous share died while it wrote a copy, which the share
 * starting now reports instead of doing anything else. Reading it clears it.
 *
 * @since 1.2.1
 *
 * @return bool
 */
function prairie_ash_image_take_crash_note() {
	if ( ! prairie_ash_image_fresh_option( 'prairie_ash_image_crash_note' ) ) {
		return false;
	}

	delete_option( 'prairie_ash_image_crash_note' );

	return true;
}

/**
 * The option that marks a copy as being written.
 *
 * One option per copy, so two requests writing different copies never touch
 * each other's mark.
 *
 * @since 1.2.1
 *
 * @param string $key Attachment ID, size name and MIME type.
 * @return string
 */
function prairie_ash_image_marker_option( $key ) {
	return 'prairie_ash_image_busy_' . md5( $key );
}

/**
 * The copy this request is writing right now, for the shutdown check.
 *
 * @since 1.2.1
 *
 * @param array|false|null $write The copy as it starts, false when it is
 *                                finished, null to read.
 * @return array|null
 */
function prairie_ash_image_current_write( $write = null ) {
	static $current = null;

	if ( false === $write ) {
		$current = null;
	} elseif ( null !== $write ) {
		$current = $write;
	}

	return $current;
}

/**
 * Watch for the end of the request once, to catch a copy it dies on.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_image_watch_shutdown() {
	static $watching = false;

	if ( ! $watching ) {
		$watching = true;
		register_shutdown_function( 'prairie_ash_image_shutdown' );
	}
}

/**
 * Report the copy a request died on, and give its lock back.
 *
 * PHP runs shutdown functions after a fatal error, the memory and the time
 * limit included, so the request that dies records its own copy as failed,
 * deletes what the encoder left of it, and leaves a note so the next share
 * answers at once. A request the server kills outright runs nothing, and its
 * mark on the copy is found by the next request that reaches that copy.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_image_shutdown() {
	$write = prairie_ash_image_current_write();

	if ( $write ) {
		$limit = wp_convert_hr_to_bytes( (string) ini_get( 'memory_limit' ) );

		if ( $limit > 0 ) {
			// What the encoder took is still held, and the records need a little room of their own.
			add_filter(
				'prairie_ash_shutdown_memory_limit',
				static function () use ( $limit ) {
					return (string) ( $limit + 64 * MB_IN_BYTES );
				}
			);
			wp_raise_memory_limit( 'prairie_ash_shutdown' );
		}

		$error  = error_get_last();
		$reason = 'the request stopped while this copy was being written, most likely at the memory or time limit of the server.';

		if ( $error && str_contains( (string) $error['message'], 'Allowed memory size' ) ) {
			$reason = sprintf( 'the request ran out of memory while this copy was being written, at the memory limit of %d MB.', floor( $limit / MB_IN_BYTES ) );
		} elseif ( $error && str_contains( (string) $error['message'], 'Maximum execution time' ) ) {
			$reason = sprintf( 'the request ran out of time while this copy was being written, at the time limit of %d seconds.', (int) ini_get( 'max_execution_time' ) );
		}

		prairie_ash_image_current_write( false );
		delete_option( prairie_ash_image_marker_option( $write['key'] ) );

		if ( file_exists( $write['copy'] ) ) {
			wp_delete_file( $write['copy'] );
		}

		prairie_ash_set_image_failure(
			$write['key'],
			array(
				'attachment' => (int) $write['attachment'],
				'file'       => $write['file'],
				'reason'     => $reason,
				'type'       => 'crash',
				'pass'       => $write['pass'],
			)
		);

		if ( prairie_ash_image_holds_run() ) {
			update_option( 'prairie_ash_image_crash_note', time(), false );
		}
	}

	prairie_ash_image_run( false );
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
 * The editor is chosen for the format it has to write as well as the one it
 * reads. Without the output format, core takes the first editor that reads
 * the source, and an Imagick build without AVIF or WebP then saves the copy
 * as a JPEG under another name instead of failing.
 *
 * GD cannot write WebP or AVIF from a palette PNG: the save fails and leaves
 * an empty file. A copy that is resized is already true colour, because the
 * resize draws into a new true colour image, so only a full-size copy needs
 * the conversion, and the theme's own GD editor provides it when GD is the
 * editor chosen.
 *
 * @since 1.2.1
 *
 * @param string $full      Absolute path of the full image.
 * @param bool   $truecolor Whether to convert a palette image to true colour.
 * @param string $mime      MIME type the copy is written in.
 * @return WP_Image_Editor|WP_Error
 */
function prairie_ash_image_copy_editor( $full, $truecolor, $mime ) {
	$args = array(
		'mime_type'        => wp_check_filetype( $full )['type'],
		'output_mime_type' => $mime,
	);

	if ( ! $truecolor ) {
		return wp_get_image_editor( $full, $args );
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
	$editor = wp_get_image_editor( $full, $args );
	remove_filter( 'wp_image_editors', $swap );

	if ( ! is_wp_error( $editor ) && $editor instanceof Prairie_Ash_Image_Editor_GD ) {
		$editor->make_truecolor();
	}

	return $editor;
}

/**
 * Delete a file an image editor saved in another format than the one asked
 * for, and name that format.
 *
 * An editor that cannot write the format asked for falls back to its default
 * and saves under the matching extension, beside the copy's own name. That
 * file is never a copy and nothing else would ever remove it.
 *
 * @since 1.2.1
 *
 * @param mixed  $saved What the editor's save returned.
 * @param string $copy  Absolute path the copy was asked for at.
 * @param string $mime  MIME type the copy was asked for in.
 * @return string The MIME type the editor wrote instead, or an empty string
 *                when it wrote what was asked.
 */
function prairie_ash_image_stray_output( $saved, $copy, $mime ) {
	if ( ! is_array( $saved ) || empty( $saved['path'] ) ) {
		return '';
	}

	$path    = wp_normalize_path( $saved['path'] );
	$asked   = wp_normalize_path( $copy );
	$written = isset( $saved['mime-type'] ) ? (string) $saved['mime-type'] : $mime;

	if ( $asked === $path && $written === $mime ) {
		return '';
	}

	$stem = wp_normalize_path( dirname( $copy ) ) . '/' . pathinfo( $copy, PATHINFO_FILENAME ) . '.';

	if ( $asked !== $path && str_starts_with( $path, $stem ) && is_file( $path ) ) {
		wp_delete_file( $path );
	}

	return $written;
}

/**
 * Whether a copy on disk is usable and at least as new as its source.
 *
 * The one test both the writer and the count use, so the screen never says
 * every copy is there while the repair would write one again.
 *
 * @since 1.2.1
 *
 * @param string $copy   Absolute path of the copy.
 * @param string $source Absolute path of the file it is made from.
 * @param string $mime   MIME type the copy has to be.
 * @return bool
 */
function prairie_ash_image_copy_is_current( $copy, $source, $mime ) {
	return prairie_ash_is_image_copy( $copy, $mime ) && filemtime( $copy ) >= filemtime( $source );
}

/**
 * Whether another request is writing a copy right now.
 *
 * @since 1.2.1
 *
 * @param string $key Attachment ID, size name and MIME type.
 * @return bool
 */
function prairie_ash_image_copy_in_progress( $key ) {
	$mark = prairie_ash_image_fresh_option( prairie_ash_image_marker_option( $key ) );

	return is_array( $mark ) && ( $mark['token'] ?? '' ) !== prairie_ash_image_request_token() && time() <= (int) ( $mark['until'] ?? 0 );
}

/**
 * Write one copy, or say why it was not written.
 *
 * Before the encoder starts, the copy has to fit: in the memory and in the
 * time the request has. A copy that could never fit under the server's
 * limits is reported and skipped. A copy that does not fit in what is left of
 * this request waits for the next one, except as the first copy of an import
 * or repair share, which would otherwise wait for ever.
 *
 * The copy being written is marked in an option of its own first. When the
 * request dies inside the encoder, its shutdown check reports the copy; when
 * the server kills it outright, the next request to reach the copy finds the
 * mark and reports it. A copy that stopped a request is not tried again on
 * an ordinary metadata write, only by an import or a repair, so an image
 * edit never dies on it a second time.
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
 *                              copy that failed is tried once per run, or an
 *                              empty string outside a run.
 * @return true|WP_Error True when written; a prairie_ash_image_later error
 *                       when it waits for the next request; a
 *                       prairie_ash_image_busy error when another request is
 *                       writing it; any other error when it failed.
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
	$record   = prairie_ash_image_failures()[ $key ] ?? null;
	$marker   = prairie_ash_image_marker_option( $key );
	$fail     = static function ( $type, $reason ) use ( $key, $attachment_id, $relative, $copy, $pass ) {
		if ( file_exists( $copy ) ) {
			wp_delete_file( $copy );
		}

		prairie_ash_set_image_failure(
			$key,
			array(
				'attachment' => (int) $attachment_id,
				'file'       => $relative,
				'reason'     => $reason,
				'type'       => $type,
				'pass'       => $pass,
			)
		);

		return new WP_Error( 'prairie_ash_image_failed', $reason );
	};

	if ( $record && ( isset( $tried[ $key ] ) || ( '' === $pass && 'crash' === prairie_ash_image_failure_type( $record ) ) || ( '' !== $pass && $pass === $record['pass'] ) ) ) {
		return new WP_Error( 'prairie_ash_image_failed', $record['reason'] );
	}

	$mark = prairie_ash_image_fresh_option( $marker );

	if ( is_array( $mark ) && ( $mark['token'] ?? '' ) !== prairie_ash_image_request_token() ) {
		// A share holds the run lock alone, so another share's mark can only be left by a request that died.
		$dead = time() > (int) ( $mark['until'] ?? 0 ) || ( ! empty( $mark['run'] ) && prairie_ash_image_holds_run() );

		if ( ! $dead ) {
			return new WP_Error( 'prairie_ash_image_busy', 'Another request is writing this copy.' );
		}

		delete_option( $marker );

		if ( prairie_ash_image_holds_run() ) {
			prairie_ash_image_share_stopped( true );
		}

		return $fail( 'crash', 'the request stopped while this copy was being written, most likely at the memory or time limit of the server.' );
	}

	$tried[ $key ] = true;

	$source_pixels = (int) $data['width'] * (int) $data['height'];
	$target_pixels = $width ? $width * $height : $source_pixels;

	wp_raise_memory_limit( 'image' );

	$limit = wp_convert_hr_to_bytes( (string) ini_get( 'memory_limit' ) );
	$need  = prairie_ash_image_copy_memory( $source_pixels, $width ? $target_pixels : 0 );
	// Memory PHP freed and keeps cached is handed back when a new block needs it, so live usage is what counts.
	$free = $limit - memory_get_usage();

	if ( $limit > 0 && $need > $limit ) {
		return $fail(
			'memory',
			sprintf(
				'skipped, it needs about %1$d MB of memory, more than the memory limit of %2$d MB.',
				ceil( $need / MB_IN_BYTES ),
				floor( $limit / MB_IN_BYTES )
			)
		);
	}

	if ( $limit > 0 && $need > $free ) {
		// An upload holds the decoded original while core makes its sizes, so a later request has more room.
		if ( $started || '' === $pass ) {
			return new WP_Error( 'prairie_ash_image_later', 'Not enough memory left in this request.' );
		}

		return $fail(
			'memory',
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
			return $fail( 'time', sprintf( 'skipped, it would take longer than the time limit of %d seconds allows.', $seconds ) );
		}

		if ( $started && timer_float() + max( $estimate * 3, $estimate + 5 ) > $seconds ) {
			return new WP_Error( 'prairie_ash_image_later', 'No time left in this request.' );
		}
	}

	$started = true;

	update_option(
		$marker,
		array(
			'token' => prairie_ash_image_request_token(),
			'until' => time() + ( $seconds > 0 ? $seconds + 30 : 5 * MINUTE_IN_SECONDS ),
			'run'   => prairie_ash_image_holds_run(),
		),
		false
	);
	prairie_ash_image_current_write(
		array(
			'key'        => $key,
			'attachment' => (int) $attachment_id,
			'file'       => $relative,
			'copy'       => $copy,
			'pass'       => $pass,
		)
	);
	prairie_ash_image_watch_shutdown();

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
		$editor = prairie_ash_image_copy_editor( $full, ! $width, $mime );
		$saved  = $editor;

		if ( ! is_wp_error( $editor ) && $width ) {
			$saved = $editor->resize( $width, $height, true );
		}

		if ( ! is_wp_error( $saved ) ) {
			$saved = $editor->save( $copy, $mime );
		}

		unset( $editor );
	}

	prairie_ash_image_current_write( false );
	delete_option( $marker );

	if ( is_wp_error( $saved ) ) {
		return $fail( 'editor', 'the image editor reported: ' . $saved->get_error_message() );
	}

	$stray = prairie_ash_image_stray_output( $saved, $copy, $mime );

	if ( '' !== $stray ) {
		return $fail( 'format', sprintf( 'the image editor on this server wrote %1$s instead of %2$s.', $stray, 'image/avif' === $mime ? 'AVIF' : 'WebP' ) );
	}

	if ( ! prairie_ash_is_image_copy( $copy, $mime ) ) {
		return $fail( 'empty', 'the image editor wrote an empty or unreadable file.' );
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
 *              request ran out of time, a copy is being written by another
 *              request, or the share has to stop, and the rest waits.
 */
function prairie_ash_write_image_copies( $attachment_id, $data, $pass = '', $until = 0.0 ) {
	$full = prairie_ash_image_source( $data );
	$done = true;

	if ( '' === $full ) {
		return true;
	}

	foreach ( prairie_ash_image_formats() as $mime ) {
		foreach ( prairie_ash_image_targets( $data, $full ) as $name => $target ) {
			$key = $attachment_id . ':' . $name . ':' . $mime;

			if ( ! file_exists( $target[0] ) ) {
				prairie_ash_set_image_failure( $key, null );
				continue;
			}

			$copy = prairie_ash_format_file_name( $target[0], $mime );

			if ( prairie_ash_image_copy_is_current( $copy, $target[0], $mime ) ) {
				prairie_ash_set_image_failure( $key, null );
				continue;
			}

			if ( prairie_ash_image_share_stopped() || ( $until && microtime( true ) >= $until ) ) {
				return false;
			}

			$written = prairie_ash_write_image_copy( $attachment_id, $data, $name, $target, $mime, $pass );

			if ( is_wp_error( $written ) && 'prairie_ash_image_later' === $written->get_error_code() ) {
				return false;
			}

			if ( is_wp_error( $written ) && 'prairie_ash_image_busy' === $written->get_error_code() ) {
				$done = false;
			}
		}
	}

	return $done && ! prairie_ash_image_share_stopped();
}

/**
 * Record the copies that are on disk in the attachment metadata, and only
 * those.
 *
 * A copy is recorded under a sources key beside its file only when it is a
 * usable image of its format. A record of a missing or empty copy is
 * dropped, and an empty or unreadable file is deleted, so the picture never
 * offers a browser a source it cannot show. A file another request is
 * writing right now is left alone.
 *
 * @since 1.2.1
 *
 * @param array $data          Attachment metadata.
 * @param int   $attachment_id Attachment ID, 0 when unknown.
 * @return array
 */
function prairie_ash_record_image_formats( $data, $attachment_id = 0 ) {
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
			} elseif ( file_exists( $copy ) && ! prairie_ash_image_copy_in_progress( $attachment_id . ':' . $name . ':' . $mime ) ) {
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

	return prairie_ash_record_image_formats( $data, (int) $attachment_id );
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
 * Read once per request, so a share that counts and repairs asks the
 * database once.
 *
 * @since 1.2.1
 *
 * @param bool $refresh Whether to read the list again.
 * @return int[] Attachment IDs, lowest first.
 */
function prairie_ash_image_attachments( $refresh = false ) {
	static $ids = null;

	if ( null === $ids || $refresh ) {
		$ids = array_map(
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

	return $ids;
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

		if ( prairie_ash_image_share_stopped() || ( $until && microtime( true ) >= $until ) ) {
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
 * How many copies of one attachment are missing, empty, unreadable or older
 * than their source, leaving out the ones recorded as failed.
 *
 * @since 1.2.1
 *
 * @param int   $attachment_id Attachment ID.
 * @param array $failures      The failure records.
 * @return int
 */
function prairie_ash_image_missing_copies_of( $attachment_id, $failures ) {
	$data    = wp_get_attachment_metadata( $attachment_id );
	$full    = prairie_ash_image_source( $data );
	$missing = 0;

	if ( '' === $full ) {
		return 0;
	}

	foreach ( prairie_ash_image_formats() as $mime ) {
		foreach ( prairie_ash_image_targets( $data, $full ) as $name => $target ) {
			if ( isset( $failures[ $attachment_id . ':' . $name . ':' . $mime ] ) || ! file_exists( $target[0] ) ) {
				continue;
			}

			if ( ! prairie_ash_image_copy_is_current( prairie_ash_format_file_name( $target[0], $mime ), $target[0], $mime ) ) {
				++$missing;
			}
		}
	}

	return $missing;
}

/**
 * Count the copies that are missing, as many images as fit in a time budget.
 *
 * The count is kept in an option with the place it reached, so a library of
 * any size is counted in shares, and the Demo content screen shows the last
 * count instead of reading every file on every visit. The repair sets it to
 * none when it finishes, because a finished repair leaves no copy missing
 * that is not listed as failed.
 *
 * @since 1.2.1
 *
 * @param bool  $carry_on Whether to carry on a count that is part way.
 * @param float $until    Time to stop by, or 0 for none.
 * @return array The count: missing, the last attachment counted, whether it
 *               is done, and when it finished.
 */
function prairie_ash_count_image_copies( $carry_on, $until = 0.0 ) {
	$state = get_option( 'prairie_ash_image_copies_count' );

	if ( ! $carry_on || ! is_array( $state ) || ! empty( $state['done'] ) ) {
		$state = array(
			'missing' => 0,
			'cursor'  => 0,
			'done'    => false,
			'time'    => 0,
		);
	}

	$failures = prairie_ash_image_failures();
	$ids      = array_values(
		array_filter(
			prairie_ash_image_attachments(),
			static function ( $attachment_id ) use ( $state ) {
				return $attachment_id > $state['cursor'];
			}
		)
	);

	foreach ( array_chunk( $ids, 100 ) as $chunk ) {
		// One query for the posts and one for their metadata, rather than two per image.
		_prime_post_caches( $chunk, false, true );

		foreach ( $chunk as $attachment_id ) {
			if ( $until && microtime( true ) >= $until ) {
				update_option( 'prairie_ash_image_copies_count', $state, false );
				return $state;
			}

			$state['missing'] += prairie_ash_image_missing_copies_of( $attachment_id, $failures );
			$state['cursor']   = $attachment_id;
		}

		foreach ( $chunk as $attachment_id ) {
			clean_post_cache( $attachment_id );
		}
	}

	$state['done'] = true;
	$state['time'] = time();

	update_option( 'prairie_ash_image_copies_count', $state, false );

	return $state;
}

/**
 * The last finished count of missing copies, counted now when there is none
 * yet and the library is small enough to count in a moment.
 *
 * @since 1.2.1
 *
 * @return array|null The count, or null when it is not known.
 */
function prairie_ash_image_copy_count() {
	$state = get_option( 'prairie_ash_image_copies_count' );

	if ( is_array( $state ) && ! empty( $state['done'] ) ) {
		return $state;
	}

	if ( ! is_array( $state ) && count( prairie_ash_image_attachments() ) <= 300 ) {
		return prairie_ash_count_image_copies( false );
	}

	return null;
}

/**
 * Store a count of missing copies known without reading the files.
 *
 * @since 1.2.1
 *
 * @param int $missing Copies missing.
 * @return void
 */
function prairie_ash_set_image_copy_count( $missing ) {
	update_option(
		'prairie_ash_image_copies_count',
		array(
			'missing' => (int) $missing,
			'cursor'  => 0,
			'done'    => true,
			'time'    => time(),
		),
		false
	);
}

/**
 * Delete the AVIF and WebP copies with their attachment.
 *
 * Core deletes the files its own metadata names and nothing under sources.
 * Every file at a copy's name is deleted, recorded or not, so an empty file
 * an encoder left behind goes as well, and so do the copies of the sizes an
 * image had before it was edited, which core keeps as backup sizes. Runs
 * before core removes the metadata.
 *
 * @since 1.0.0
 * @since 1.2.1 Deletes unrecorded copies, the copies of backup sizes, and the
 *              attachment's failure records.
 *
 * @param int $post_id Attachment ID.
 * @return void
 */
function prairie_ash_delete_image_formats( $post_id ) {
	$meta = wp_get_attachment_metadata( $post_id );

	if ( ! is_array( $meta ) || empty( $meta['file'] ) ) {
		return;
	}

	$base    = wp_get_upload_dir()['basedir'];
	$full    = trailingslashit( $base ) . $meta['file'];
	$dir     = trailingslashit( dirname( $full ) );
	$sources = array();
	$files   = array();

	foreach ( prairie_ash_image_targets( $meta, $full ) as $target ) {
		$sources[] = $target[0];
	}

	foreach ( (array) get_post_meta( $post_id, '_wp_attachment_backup_sizes', true ) as $backup ) {
		if ( is_array( $backup ) && ! empty( $backup['file'] ) ) {
			$sources[] = $dir . wp_basename( $backup['file'] );
		}
	}

	foreach ( $sources as $source ) {
		foreach ( array( 'image/avif', 'image/webp' ) as $mime ) {
			$files[] = prairie_ash_format_file_name( $source, $mime );
		}
	}

	foreach ( array_merge( array( $meta['sources'] ?? array() ), array_column( (array) ( $meta['sizes'] ?? array() ), 'sources' ) ) as $recorded ) {
		foreach ( (array) $recorded as $source ) {
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

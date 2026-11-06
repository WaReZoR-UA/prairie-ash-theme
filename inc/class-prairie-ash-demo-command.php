<?php
/**
 * The wp prairie-ash-demo command: the same import and image copy repair as
 * the Demo content screen, and the export that writes the demo folder.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Imports or exports the Prairie Ash demo content, and repairs the image
 * copies.
 *
 * @since 1.2.0
 */
class Prairie_Ash_Demo_Command {

	/**
	 * Import the demo content shipped in the theme's demo folder.
	 *
	 * Creates or updates the pages, products, menus, forms, shipping, settings
	 * and images of the demo site. Running it again updates the same records
	 * and creates nothing twice. Runs as the user given with --user, or as the
	 * first administrator when none is given, and that user has to be allowed
	 * to post unfiltered HTML, which on a multisite network only a super
	 * admin is.
	 *
	 * On a site with orders, products, pages or posts of its own, the import
	 * names them and asks before it replaces the site's settings.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Replace the settings of a site with content of its own without asking.
	 *
	 * ## EXAMPLES
	 *
	 *     wp prairie-ash-demo import
	 *
	 * @since 1.2.0
	 * @since 1.2.1 Checks for unfiltered HTML, and asks before it replaces the
	 *              settings of a site with content of its own.
	 *
	 * @param array $args       Positional arguments, unused.
	 * @param array $assoc_args Named arguments.
	 * @return void
	 */
	public function import( $args, $assoc_args ) {
		unset( $args );

		$this->act_as_administrator();

		if ( ! current_user_can( 'unfiltered_html' ) ) {
			WP_CLI::error( 'The forms and the pages carry markup that only a user allowed to post unfiltered HTML can save, and this user is not. Run the command again with --user=<login> naming such a user: an administrator, or a super admin on a multisite network.' );
		}

		$importer = prairie_ash_demo_importer();
		$check    = $importer->check();

		if ( is_wp_error( $check ) ) {
			WP_CLI::error( $check->get_error_message() );
		}

		$existing = $importer->existing_content();

		if ( $existing ) {
			foreach ( $existing as $line ) {
				WP_CLI::warning( $line );
			}

			WP_CLI::confirm( 'The import replaces the site title, the front page, the permalinks, the store address, currency and countries, the checkout settings and the payment methods with the demo\'s. Carry on?', $assoc_args );
		}

		// The command line has no request to time out, and the images alone
		// take minutes on a slow host.
		set_time_limit( 0 );

		$running = prairie_ash_image_run( true );

		if ( is_wp_error( $running ) ) {
			WP_CLI::error( $running->get_error_message() );
		}

		$importer->start();
		$steps = Prairie_Ash_Demo_Importer::steps();
		$names = array_keys( $steps );

		do {
			$state = $importer->run( 30 );

			if ( is_wp_error( $state ) ) {
				WP_CLI::error( $state->get_error_message() );
			}

			foreach ( $importer->log as $line ) {
				WP_CLI::log( $line );
			}

			$importer->log = array();

			if ( ! $state['done'] ) {
				WP_CLI::log( '... ' . $steps[ $names[ $state['step'] ] ] );
			}
		} while ( ! $state['done'] );

		$this->report_image_failures( $importer->demo_attachment_ids() );

		$others = count( prairie_ash_image_failure_lines() ) - count( prairie_ash_image_failure_lines( $importer->demo_attachment_ids() ) );

		if ( $others > 0 ) {
			WP_CLI::log( sprintf( '%d image copies of other images on this site could not be written. Run wp prairie-ash-demo repair-images to list them and try them again.', $others ) );
		}

		WP_CLI::success( 'The demo content is in place.' );
	}

	/**
	 * Write every AVIF and WebP image copy that is missing, empty or unreadable.
	 *
	 * Walks every JPEG and PNG in the media library, the demo's and any other,
	 * lets WordPress write the sizes an image is missing, and writes the
	 * missing copies one at a time, checking the memory left before each.
	 * Copies that still cannot be written are listed, and the pages offer the
	 * next format in their place. Same as the Repair image copies button under
	 * Appearance, Demo content.
	 *
	 * ## EXAMPLES
	 *
	 *     wp prairie-ash-demo repair-images
	 *
	 * @since 1.2.1
	 *
	 * @subcommand repair-images
	 *
	 * @param array $args       Positional arguments, unused.
	 * @param array $assoc_args Named arguments, unused.
	 * @return void
	 */
	public function repair_images( $args, $assoc_args ) {
		unset( $args, $assoc_args );

		$carry_on = false;

		do {
			$share    = prairie_ash_image_repair_share( $carry_on, 30 );
			$carry_on = true;

			if ( is_wp_error( $share ) ) {
				WP_CLI::error( $share->get_error_message() );
			}

			foreach ( $share['log'] as $line ) {
				WP_CLI::log( $line );
			}

			WP_CLI::log( sprintf( '... %1$d of %2$d images checked', $share['checked'], $share['total'] ) );
		} while ( ! $share['done'] );

		if ( $this->report_image_failures() ) {
			WP_CLI::error( 'Some image copies could not be written. The pages offer the next format in their place.' );
		}

		WP_CLI::success( 'Every image has its AVIF and WebP copies.' );
	}

	/**
	 * List the image copies that could not be written.
	 *
	 * @since 1.2.1
	 *
	 * @param int[]|null $attachments Only the copies of these attachments, or
	 *                                null for every image on the site.
	 * @return int How many there are.
	 */
	private function report_image_failures( $attachments = null ) {
		$failures = prairie_ash_image_failure_lines( $attachments );

		foreach ( $failures as $line ) {
			WP_CLI::warning( 'Image copy not written: ' . $line );
		}

		if ( $failures ) {
			WP_CLI::warning( sprintf( '%d image copies could not be written. Run wp prairie-ash-demo repair-images to try them again.', count( $failures ) ) );
		}

		return count( $failures );
	}

	/**
	 * Write the demo folder from this site.
	 *
	 * Run on a copy of the reference site. Writes content.json, forms.xml
	 * and the uploaded image files, and nothing about users, orders, form
	 * entries, comments, mail or keys.
	 *
	 * ## OPTIONS
	 *
	 * [--dir=<path>]
	 * : Folder to write. Defaults to the theme's demo folder.
	 *
	 * ## EXAMPLES
	 *
	 *     wp prairie-ash-demo export
	 *
	 * @since 1.2.0
	 *
	 * @param array $args       Positional arguments, unused.
	 * @param array $assoc_args Named arguments.
	 * @return void
	 */
	public function export( $args, $assoc_args ) {
		unset( $args );

		require_once get_theme_file_path( 'inc/class-prairie-ash-demo-exporter.php' );

		$dir      = isset( $assoc_args['dir'] ) ? $assoc_args['dir'] : get_template_directory() . '/demo';
		$exporter = new Prairie_Ash_Demo_Exporter();
		$counts   = $exporter->export( $dir );

		foreach ( array_unique( $exporter->notes ) as $note ) {
			WP_CLI::warning( $note );
		}

		foreach ( $counts as $kind => $count ) {
			WP_CLI::log( sprintf( '%s: %d', $kind, $count ) );
		}

		WP_CLI::success( 'Demo folder written.' );
	}

	/**
	 * Make the first administrator the current user when none was given.
	 *
	 * The forms carry the design's own markup, which Formidable keeps only
	 * for a user allowed to post unfiltered HTML.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	private function act_as_administrator() {
		if ( get_current_user_id() ) {
			return;
		}

		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'orderby' => 'ID',
				'fields'  => 'ID',
			)
		);

		if ( ! $admins ) {
			WP_CLI::error( 'No administrator found. Pass one with --user.' );
		}

		wp_set_current_user( (int) $admins[0] );
	}
}

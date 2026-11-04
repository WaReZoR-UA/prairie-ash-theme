<?php
/**
 * The wp prairie-ash-demo command: the same import as the Demo content
 * screen, and the export that writes the demo folder.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Imports or exports the Prairie Ash demo content.
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
	 * first administrator when none is given.
	 *
	 * ## EXAMPLES
	 *
	 *     wp prairie-ash-demo import
	 *
	 * @since 1.2.0
	 *
	 * @param array $args       Positional arguments, unused.
	 * @param array $assoc_args Named arguments, unused.
	 * @return void
	 */
	public function import( $args, $assoc_args ) {
		unset( $args, $assoc_args );

		$this->act_as_administrator();

		$importer = prairie_ash_demo_importer();
		$check    = $importer->check();

		if ( is_wp_error( $check ) ) {
			WP_CLI::error( $check->get_error_message() );
		}

		// The command line has no request to time out, and the images alone
		// take minutes on a slow host.
		set_time_limit( 0 );

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

		$this->report_image_failures();

		WP_CLI::success( 'The demo content is in place.' );
	}

	/**
	 * List the image copies that could not be written.
	 *
	 * @since 1.2.1
	 *
	 * @return int How many there are.
	 */
	private function report_image_failures() {
		$failures = prairie_ash_image_failure_lines();

		foreach ( $failures as $line ) {
			WP_CLI::warning( 'Image copy not written: ' . $line );
		}

		if ( $failures ) {
			WP_CLI::warning( sprintf( '%d image copies could not be written. Run the import again to try them again.', count( $failures ) ) );
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

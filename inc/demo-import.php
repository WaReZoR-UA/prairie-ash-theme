<?php
/**
 * The demo content: the plugins the theme needs, the notice shown while one
 * is missing, the Appearance screen that imports the demo with one button,
 * and the wp prairie-ash-demo command that does the same from the command
 * line.
 *
 * The data and the importer live in the theme, in demo/ and in
 * class-prairie-ash-demo-importer.php, so a copy of the theme is all a new
 * site needs besides the plugins.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'inc/class-prairie-ash-demo-exporter.php' );
require_once get_theme_file_path( 'inc/class-prairie-ash-demo-importer.php' );

/**
 * What the theme and its demo were built and tested with.
 *
 * Kept here and nowhere else: the admin notice, the Appearance screen and
 * the exporter read it, and README.md repeats it for a reader of the
 * repository.
 *
 * @since 1.2.0
 *
 * @return array
 */
function prairie_ash_demo_requirements() {
	return array(
		'wordpress' => '7.1.4',
		'php'       => '8.1',
		'plugins'   => array(
			array(
				'slug'     => 'secure-custom-fields',
				'name'     => 'Secure Custom Fields',
				'version'  => '6.9.8',
				'required' => true,
			),
			array(
				'slug'     => 'woocommerce',
				'name'     => 'WooCommerce',
				'version'  => '11.2.1',
				'required' => true,
			),
			array(
				'slug'     => 'formidable',
				'name'     => 'Formidable Forms',
				'version'  => '7.0',
				'required' => true,
			),
			array(
				'slug'     => 'fluent-smtp',
				'name'     => 'FluentSMTP',
				'version'  => '2.4.1',
				'required' => false,
			),
		),
	);
}

/**
 * Whether a plugin the theme works with is running.
 *
 * Tested by what the plugin provides rather than by its folder, so ACF PRO
 * counts for Secure Custom Fields, which shares its functions.
 *
 * @since 1.2.0
 *
 * @param string $slug Plugin slug from prairie_ash_demo_requirements().
 * @return bool
 */
function prairie_ash_demo_plugin_active( $slug ) {
	switch ( $slug ) {
		case 'secure-custom-fields':
			return function_exists( 'acf_add_local_field_group' );
		case 'woocommerce':
			return class_exists( 'WooCommerce' );
		case 'formidable':
			return class_exists( 'FrmAppHelper' );
		case 'fluent-smtp':
			return defined( 'FLUENTMAIL' );
	}

	return false;
}

/**
 * The required plugins that are not running.
 *
 * @since 1.2.0
 *
 * @return array Plugin records.
 */
function prairie_ash_demo_missing_plugins() {
	$missing = array();

	foreach ( prairie_ash_demo_requirements()['plugins'] as $plugin ) {
		if ( $plugin['required'] && ! prairie_ash_demo_plugin_active( $plugin['slug'] ) ) {
			$missing[] = $plugin;
		}
	}

	return $missing;
}

/**
 * Where a missing plugin is installed or switched on.
 *
 * @since 1.2.0
 *
 * @param array $plugin Plugin record.
 * @return string Admin URL, or an empty string when the user may do neither.
 */
function prairie_ash_demo_plugin_link( $plugin ) {
	if ( is_dir( WP_PLUGIN_DIR . '/' . $plugin['slug'] ) ) {
		return current_user_can( 'activate_plugins' ) ? admin_url( 'plugins.php' ) : '';
	}

	if ( ! current_user_can( 'install_plugins' ) ) {
		return '';
	}

	return add_query_arg(
		array(
			's'    => rawurlencode( $plugin['name'] ),
			'tab'  => 'search',
			'type' => 'term',
		),
		admin_url( 'plugin-install.php' )
	);
}

/**
 * Tell an administrator which plugins the theme still needs.
 *
 * Shown on every admin screen until each required plugin runs, because
 * without them the sections have no fields, the shop has no store and the
 * forms have nothing to print.
 *
 * @since 1.2.0
 *
 * @return void
 */
function prairie_ash_demo_plugin_notice() {
	$missing = prairie_ash_demo_missing_plugins();

	if ( ! $missing || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>';
	esc_html_e( 'The Prairie Ash theme needs these plugins, each tested at the version given:', 'prairie-ash' );
	echo '</p><ul class="ul-disc">';

	foreach ( $missing as $plugin ) {
		$link = prairie_ash_demo_plugin_link( $plugin );

		/* translators: 1: plugin name, 2: version number. */
		$label = sprintf( __( '%1$s %2$s', 'prairie-ash' ), $plugin['name'], $plugin['version'] );

		echo '<li>';
		if ( '' !== $link ) {
			echo '<a href="' . esc_url( $link ) . '">' . esc_html( $label ) . '</a>';
		} else {
			echo esc_html( $label );
		}
		echo '</li>';
	}

	echo '</ul></div>';
}
add_action( 'admin_notices', 'prairie_ash_demo_plugin_notice' );

/**
 * The importer, set up on the theme's demo folder.
 *
 * @since 1.2.0
 *
 * @return Prairie_Ash_Demo_Importer
 */
function prairie_ash_demo_importer() {
	return new Prairie_Ash_Demo_Importer();
}

/**
 * Add Demo content under Appearance.
 *
 * @since 1.2.0
 *
 * @return void
 */
function prairie_ash_demo_menu() {
	add_theme_page(
		__( 'Demo content', 'prairie-ash' ),
		__( 'Demo content', 'prairie-ash' ),
		'manage_options',
		'prairie-ash-demo',
		'prairie_ash_demo_screen'
	);
}
add_action( 'admin_menu', 'prairie_ash_demo_menu' );

/**
 * Load the script that runs the import and the repair step by step on the
 * Demo content screen, and nowhere else.
 *
 * @since 1.2.0
 *
 * @param string $hook_suffix The current admin screen.
 * @return void
 */
function prairie_ash_demo_assets( $hook_suffix ) {
	if ( 'appearance_page_prairie-ash-demo' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_script(
		'prairie-ash-demo-import',
		get_theme_file_uri( 'assets/js/demo-import.js' ),
		array(),
		prairie_ash_asset_version( 'assets/js/demo-import.js' ),
		array( 'strategy' => 'defer' )
	);

	wp_localize_script(
		'prairie-ash-demo-import',
		'prairieAshDemo',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'prairie_ash_demo_import' ),
			'repairNonce' => wp_create_nonce( 'prairie_ash_image_repair' ),
			'countNonce'  => wp_create_nonce( 'prairie_ash_image_count' ),
			'steps'       => Prairie_Ash_Demo_Importer::steps(),
			'text'        => array(
				/* translators: %s: name of the step being imported. */
				'running'       => __( 'Importing: %s', 'prairie-ash' ),
				/* translators: 1: number of images imported so far, 2: number of images in all. */
				'media'         => __( 'Importing: Media, %1$d of %2$d', 'prairie-ash' ),
				/* translators: 1: number of images whose copies are written, 2: number of images in all. */
				'images'        => __( 'Importing: Image copies, %1$d of %2$d', 'prairie-ash' ),
				'done'          => __( 'The demo content is in place.', 'prairie-ash' ),
				/* translators: %d: number of image copies that could not be written. */
				'doneFailures'  => __( 'The demo content is in place, but %d image copies could not be written. They are listed under Image copies.', 'prairie-ash' ),
				/* translators: %s: the reason the import stopped. */
				'failed'        => __( 'The import stopped: %s', 'prairie-ash' ),
				'network'       => __( 'The server did not answer. Press the button again to carry on where the import stopped.', 'prairie-ash' ),
				/* translators: 1: number of images checked so far, 2: number of images in all. */
				'repairing'     => __( 'Repairing image copies: %1$d of %2$d images checked', 'prairie-ash' ),
				'repaired'      => __( 'Every image has its AVIF and WebP copies.', 'prairie-ash' ),
				/* translators: %d: number of image copies that could not be written. */
				'repairFailed'  => __( 'The repair is finished, but %d image copies could not be written. They are listed below.', 'prairie-ash' ),
				/* translators: %s: the reason the repair stopped. */
				'repairStopped' => __( 'The repair stopped: %s', 'prairie-ash' ),
				'repairNetwork' => __( 'The server did not answer. Press the button again to carry on where the repair stopped.', 'prairie-ash' ),
				/* translators: 1: number of images checked so far, 2: number of images in all. */
				'counting'      => __( 'Counting image copies: %1$d of %2$d images checked', 'prairie-ash' ),
				'counted'       => __( 'The count is finished.', 'prairie-ash' ),
				/* translators: %s: the reason the count stopped. */
				'countStopped'  => __( 'The count stopped: %s', 'prairie-ash' ),
				'countNetwork'  => __( 'The server did not answer. Press the button again to carry on where the count stopped.', 'prairie-ash' ),
				'expired'       => __( 'The session has expired. Reload the page, sign in if asked, and press the button again.', 'prairie-ash' ),
				'unknown'       => __( 'the server sent an answer the screen cannot read.', 'prairie-ash' ),
				/* translators: %d: number of image copies that could not be written. */
				'failureCount'  => __( 'Image copies that could not be written: %d. The pages offer the next format in their place.', 'prairie-ash' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'prairie_ash_demo_assets' );

/**
 * The Demo content screen.
 *
 * The button is a real form: with the script it runs the import step by
 * step and shows the progress, and without it every press runs as much as
 * fits in one request and the screen offers to carry on.
 *
 * @since 1.2.0
 *
 * @return void
 */
function prairie_ash_demo_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$importer = prairie_ash_demo_importer();
	$check    = $importer->check();
	$state    = Prairie_Ash_Demo_Importer::state();
	$running  = $state && ! $state['done'];
	$counts   = prairie_ash_demo_counts();
	$can_html = current_user_can( 'unfiltered_html' );
	$existing = $running || is_wp_error( $check ) ? array() : $importer->existing_content();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Demo content', 'prairie-ash' ); ?></h1>

		<p>
			<?php
			printf(
				/* translators: 1: number of pages, 2: number of products, 3: number of images. */
				esc_html__( 'Builds the theme\'s demo site: %1$d pages with their sections, %2$d products with their sizes and grinds, the menus, the three forms, shipping and the demo payment method, the Site settings and Shop wording, and %3$d images with every size the theme uses.', 'prairie-ash' ),
				absint( $counts['pages'] ),
				absint( $counts['products'] ),
				absint( $counts['media'] )
			);
			?>
		</p>
		<p><?php esc_html_e( 'The import sets the site title, the front page, the permalinks and the store settings, so it belongs on a new site. Running it again updates the same records and adds nothing twice. The sample post and pages a new site starts with go to the trash.', 'prairie-ash' ); ?></p>

		<h2><?php esc_html_e( 'Plugins', 'prairie-ash' ); ?></h2>
		<table class="widefat striped" style="max-width: 40rem;">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Plugin', 'prairie-ash' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Tested with', 'prairie-ash' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'prairie-ash' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( prairie_ash_demo_requirements()['plugins'] as $plugin ) : ?>
					<tr>
						<td><?php echo esc_html( $plugin['name'] ); ?></td>
						<td><?php echo esc_html( $plugin['version'] ); ?></td>
						<td>
							<?php
							if ( prairie_ash_demo_plugin_active( $plugin['slug'] ) ) {
								esc_html_e( 'Active', 'prairie-ash' );
							} elseif ( $plugin['required'] ) {
								esc_html_e( 'Required, not active', 'prairie-ash' );
							} else {
								esc_html_e( 'Recommended for sending mail, not active', 'prairie-ash' );
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( is_wp_error( $check ) ) : ?>
			<div class="notice notice-error inline"><p><?php echo esc_html( $check->get_error_message() ); ?></p></div>
		<?php elseif ( ! $can_html ) : ?>
			<div class="notice notice-error inline"><p><?php esc_html_e( 'The forms carry the design\'s own markup, which only a user allowed to post unfiltered HTML can save. Run the import as such an administrator.', 'prairie-ash' ); ?></p></div>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-prairie-ash-demo>
				<input type="hidden" name="action" value="prairie_ash_demo_import">
				<input type="hidden" name="continue" value="<?php echo $running ? '1' : '0'; ?>">
				<?php wp_nonce_field( 'prairie_ash_demo_import' ); ?>
				<?php if ( $existing ) : ?>
					<div class="notice notice-warning inline">
						<p><?php esc_html_e( 'This site is not new:', 'prairie-ash' ); ?></p>
						<ul class="ul-disc">
							<?php foreach ( $existing as $line ) : ?>
								<li><?php echo esc_html( $line ); ?></li>
							<?php endforeach; ?>
						</ul>
						<p><?php esc_html_e( 'The import replaces the site title, the front page, the permalinks, the store address, currency and countries, the checkout settings and the payment methods with the demo\'s, and writes over any page listed above as carrying a demo page\'s address. The site\'s other pages, its products and its orders stay.', 'prairie-ash' ); ?></p>
						<p>
							<label>
								<input type="checkbox" name="confirm" value="1" required>
								<?php esc_html_e( 'Replace these settings with the demo\'s', 'prairie-ash' ); ?>
							</label>
						</p>
					</div>
				<?php endif; ?>
				<p>
					<button type="submit" class="button button-primary">
						<?php $running ? esc_html_e( 'Carry on with the import', 'prairie-ash' ) : esc_html_e( 'Import demo content', 'prairie-ash' ); ?>
					</button>
				</p>
			</form>
		<?php endif; ?>

		<p role="status" aria-live="polite" data-prairie-ash-demo-status>
			<?php
			if ( $state && $state['done'] ) {
				printf(
					/* translators: %s: date and time the last import finished. */
					esc_html__( 'The demo content is in place. Last imported %s.', 'prairie-ash' ),
					esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $state['finished'] ) )
				);
			} elseif ( $running ) {
				esc_html_e( 'The import is part way through. Press the button to carry on.', 'prairie-ash' );
			}
			?>
		</p>
		<p data-prairie-ash-demo-view <?php echo ( $state && $state['done'] ) ? '' : 'hidden'; ?>>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'View the site', 'prairie-ash' ); ?></a>
		</p>

		<?php prairie_ash_image_copies_section(); ?>
	</div>
	<?php
}

/**
 * The Image copies part of the Demo content screen: what is missing, what
 * could not be written, and the button that repairs it.
 *
 * Works on every JPEG and PNG in the media library, not only the demo's, so
 * a site whose copies were left empty by a host's limits can heal itself
 * from the admin.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_image_copies_section() {
	$repair   = get_option( 'prairie_ash_image_repair' );
	$running  = is_array( $repair ) && empty( $repair['done'] );
	$count    = get_option( 'prairie_ash_image_copies_count' );
	$counting = is_array( $count ) && empty( $count['done'] );
	$failures = prairie_ash_image_failure_lines();
	?>
	<h2><?php esc_html_e( 'Image copies', 'prairie-ash' ); ?></h2>
	<p><?php esc_html_e( 'Every JPEG and PNG image is also saved as AVIF and WebP, the formats the pages offer first. The repair finds every copy that is missing, empty or unreadable and writes it again, a few at a time so it fits a short time limit, and lists any copy that still cannot be written. The count reads every copy on disk, and the screen shows the last count rather than reading every file on each visit.', 'prairie-ash' ); ?></p>
	<p data-prairie-ash-repair-summary><?php echo esc_html( prairie_ash_image_copies_summary() ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-prairie-ash-count>
		<input type="hidden" name="action" value="prairie_ash_image_count">
		<input type="hidden" name="continue" value="<?php echo $counting ? '1' : '0'; ?>">
		<?php wp_nonce_field( 'prairie_ash_image_count' ); ?>
		<p>
			<button type="submit" class="button">
				<?php $counting ? esc_html_e( 'Carry on with the count', 'prairie-ash' ) : esc_html_e( 'Count the copies again', 'prairie-ash' ); ?>
			</button>
		</p>
	</form>
	<p role="status" aria-live="polite" data-prairie-ash-count-status></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-prairie-ash-repair>
		<input type="hidden" name="action" value="prairie_ash_image_repair">
		<input type="hidden" name="continue" value="<?php echo $running ? '1' : '0'; ?>">
		<?php wp_nonce_field( 'prairie_ash_image_repair' ); ?>
		<p>
			<button type="submit" class="button">
				<?php $running ? esc_html_e( 'Carry on with the repair', 'prairie-ash' ) : esc_html_e( 'Repair image copies', 'prairie-ash' ); ?>
			</button>
		</p>
	</form>
	<p role="status" aria-live="polite" data-prairie-ash-repair-status>
		<?php
		if ( $running ) {
			esc_html_e( 'The repair is part way through. Press the button to carry on.', 'prairie-ash' );
		}
		?>
	</p>
	<div data-prairie-ash-image-failures <?php echo $failures ? '' : 'hidden'; ?>>
		<p data-prairie-ash-image-failures-count>
			<?php
			printf(
				/* translators: %d: number of image copies that could not be written. */
				esc_html__( 'Image copies that could not be written: %d. The pages offer the next format in their place.', 'prairie-ash' ),
				absint( count( $failures ) )
			);
			?>
		</p>
		<ul class="ul-disc" data-prairie-ash-image-failures-list>
			<?php foreach ( $failures as $line ) : ?>
				<li><?php echo esc_html( $line ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * How many pages, products and images the demo holds, for the screen.
 *
 * @since 1.2.0
 *
 * @return array
 */
function prairie_ash_demo_counts() {
	$file = get_template_directory() . '/demo/content.json';
	$data = is_readable( $file ) ? wp_json_file_decode( $file, array( 'associative' => true ) ) : null;

	return array(
		'pages'    => is_array( $data ) ? count( $data['pages'] ) : 0,
		'products' => is_array( $data ) ? count( $data['products'] ) : 0,
		'media'    => is_array( $data ) ? count( $data['media'] ) : 0,
	);
}

/**
 * One sentence on the state of the image copies, for the screen and for the
 * reply that ends an import, a repair or a count, so the screen never keeps a
 * summary from before the run.
 *
 * The copies missing come from the last count, which a library of up to 300
 * images gets on the first visit and a larger one gets from the count button
 * or from a finished repair. Copies that could not be written are counted
 * apart from the missing ones, because the repair lists them rather than
 * writing them, and so are the images still waiting for their copies.
 *
 * @since 1.2.1
 *
 * @return string
 */
function prairie_ash_image_copies_summary() {
	if ( ! prairie_ash_image_formats() ) {
		return __( 'The image editor on this server writes neither AVIF nor WebP, so the pages offer the original JPEG and PNG files.', 'prairie-ash' );
	}

	$count   = prairie_ash_image_copy_count();
	$failed  = count( prairie_ash_image_failures() );
	$pending = count( prairie_ash_image_pending() );

	if ( null === $count ) {
		return sprintf(
			/* translators: 1: number of copies that could not be written, 2: number of images waiting for copies. */
			__( 'The copies have not been counted yet: press Count the copies again to read every image. Copies that could not be written: %1$d. Images waiting for their copies: %2$d.', 'prairie-ash' ),
			$failed,
			$pending
		);
	}

	if ( ! $count['missing'] && ! $failed && ! $pending ) {
		return __( 'Every image has its AVIF and WebP copies.', 'prairie-ash' );
	}

	return sprintf(
		/* translators: 1: number of missing or empty copies, 2: date and time of the count, 3: number of copies that could not be written, 4: number of images waiting for copies. */
		__( 'Copies missing or empty: %1$d, counted %2$s. Copies that could not be written: %3$d. Images waiting for their copies: %4$d.', 'prairie-ash' ),
		(int) $count['missing'],
		wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $count['time'] ),
		$failed,
		$pending
	);
}

/**
 * Run one share of the import for the screen's button.
 *
 * Both routes land here: the script's requests and the form posted without
 * the script. Each checks the nonce and the user, starts a new run unless it
 * was asked to carry on, and runs as much as fits in the time budget. A new
 * run on a site with content of its own starts only when the box confirming
 * it was ticked, because the import replaces the store and payment settings
 * and the front page.
 *
 * @since 1.2.0
 * @since 1.2.1 Takes the confirmation, and holds the import and repair lock
 *              from before a new run resets the progress.
 *
 * @param bool $carry_on  Whether to carry on the import that is running.
 * @param bool $confirmed Whether replacing the site's own settings was
 *                        confirmed.
 * @return array|WP_Error The state after the share, with the log.
 */
function prairie_ash_demo_run_share( $carry_on, $confirmed = false ) {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
		return new WP_Error( 'prairie_ash_demo_cap', __( 'Only an administrator can import the demo content.', 'prairie-ash' ) );
	}

	$importer = prairie_ash_demo_importer();
	$state    = Prairie_Ash_Demo_Importer::state();
	$fresh    = ! $carry_on || ! $state || $state['done'];

	if ( $fresh && ! $confirmed && $importer->existing_content() ) {
		return new WP_Error( 'prairie_ash_demo_confirm', __( 'This site has content of its own. Tick the box to confirm that the import may replace its settings, or run the import on a new site.', 'prairie-ash' ) );
	}

	$running = prairie_ash_image_run( true );

	if ( is_wp_error( $running ) ) {
		return $running;
	}

	if ( $fresh ) {
		$importer->start();
	}

	$state = $importer->run( 20 );

	prairie_ash_image_run( false );

	if ( ! is_wp_error( $state ) ) {
		$state['log']            = $importer->log;
		$state['failures']       = prairie_ash_image_failure_lines();
		$state['importFailures'] = count( prairie_ash_image_failure_lines( $importer->demo_attachment_ids() ) );
	}

	return $state;
}

/**
 * The script's request for one share of the import.
 *
 * @since 1.2.0
 *
 * @return void
 */
function prairie_ash_demo_ajax() {
	check_ajax_referer( 'prairie_ash_demo_import' );

	$carry_on = ! empty( $_POST['continue'] );
	$state    = prairie_ash_demo_run_share( $carry_on, ! empty( $_POST['confirm'] ) );

	if ( is_wp_error( $state ) ) {
		wp_send_json_error( array( 'message' => $state->get_error_message() ) );
	}

	$data  = wp_json_file_decode( get_template_directory() . '/demo/content.json', array( 'associative' => true ) );
	$names = array_keys( Prairie_Ash_Demo_Importer::steps() );

	if ( $state['done'] && count( prairie_ash_image_attachments() ) <= 300 ) {
		prairie_ash_count_image_copies( false );
	}

	wp_send_json_success(
		array(
			'done'           => (bool) $state['done'],
			'step'           => $state['done'] ? '' : $names[ $state['step'] ],
			'cursor'         => (int) $state['cursor'],
			'media'          => is_array( $data ) ? count( $data['media'] ) : 0,
			'log'            => $state['log'],
			'failures'       => $state['failures'],
			'importFailures' => $state['importFailures'],
			'summary'        => $state['done'] ? prairie_ash_image_copies_summary() : '',
		)
	);
}
add_action( 'wp_ajax_prairie_ash_demo_import', 'prairie_ash_demo_ajax' );

/**
 * The form posted without the script: one share, then back to the screen.
 *
 * @since 1.2.0
 *
 * @return void
 */
function prairie_ash_demo_post() {
	check_admin_referer( 'prairie_ash_demo_import' );

	$state = prairie_ash_demo_run_share( ! empty( $_POST['continue'] ), ! empty( $_POST['confirm'] ) );

	if ( is_wp_error( $state ) ) {
		wp_die( esc_html( $state->get_error_message() ), '', array( 'back_link' => true ) );
	}

	if ( $state['done'] && count( prairie_ash_image_attachments() ) <= 300 ) {
		prairie_ash_count_image_copies( false );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'             => 'prairie-ash-demo',
				'prairie-ash-demo' => $state['done'] ? 'done' : 'continue',
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_post_prairie_ash_demo_import', 'prairie_ash_demo_post' );

/**
 * Run one share of the image copy repair.
 *
 * Shared by the screen's script, the form posted without it and the
 * command line. A new repair starts unless it was asked to carry on, and the
 * share stops at the time budget, capped at half of PHP's time limit, with
 * its place kept in an option. It holds the same lock as an import share, so
 * the two never write copies at the same time. A new repair first drops the
 * failure records that no longer describe a copy, and a finished one leaves
 * no copy missing that is not listed as failed, so it stores that count.
 *
 * @since 1.2.1
 *
 * @param bool $carry_on Whether to carry on the repair that is running.
 * @param int  $budget   Seconds the share may take, roughly.
 * @return array|WP_Error Whether it is done, how many images were checked of
 *                        how many, the log and the copies that could not be
 *                        written; an error when an import or another repair
 *                        is running.
 */
function prairie_ash_image_repair_share( $carry_on, $budget = 20 ) {
	$running = prairie_ash_image_run( true );

	if ( is_wp_error( $running ) ) {
		return $running;
	}

	$state = get_option( 'prairie_ash_image_repair' );

	if ( ! $carry_on || ! is_array( $state ) || ! empty( $state['done'] ) ) {
		$state = array(
			'cursor' => 0,
			'pass'   => 'repair-' . time(),
			'done'   => false,
		);

		// Saved before the first copy, so a share that dies on it leaves the run its next share carries on.
		update_option( 'prairie_ash_image_repair', $state, false );
		prairie_ash_prune_image_records();
	}

	$seconds = (int) ini_get( 'max_execution_time' );
	$budget  = $seconds > 0 ? min( (int) $budget, (int) floor( $seconds / 2 ) ) : (int) $budget;

	wp_raise_memory_limit( 'admin' );

	if ( prairie_ash_image_take_crash_note() ) {
		prairie_ash_image_log( 'The previous request stopped while it wrote an image copy. The copy is listed below, and the repair carries on.' );
	} else {
		$result = prairie_ash_repair_image_copies( (int) $state['cursor'], $state['pass'], microtime( true ) + max( 1, $budget ) );

		if ( true === $result ) {
			$state['done'] = true;
			prairie_ash_set_image_copy_count( 0 );
		} else {
			$state['cursor'] = (int) $result;
		}
	}

	update_option( 'prairie_ash_image_repair', $state, false );
	prairie_ash_image_run( false );

	$ids     = prairie_ash_image_attachments();
	$checked = $state['done'] ? count( $ids ) : count(
		array_filter(
			$ids,
			static function ( $id ) use ( $state ) {
				return $id <= $state['cursor'];
			}
		)
	);

	return array(
		'done'     => (bool) $state['done'],
		'checked'  => $checked,
		'total'    => count( $ids ),
		'log'      => prairie_ash_image_log(),
		'failures' => prairie_ash_image_failure_lines(),
		'summary'  => $state['done'] ? prairie_ash_image_copies_summary() : '',
	);
}

/**
 * The script's request for one share of the repair.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_image_repair_ajax() {
	check_ajax_referer( 'prairie_ash_image_repair' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Only an administrator can repair the image copies.', 'prairie-ash' ) ) );
	}

	$share = prairie_ash_image_repair_share( ! empty( $_POST['continue'] ) );

	if ( is_wp_error( $share ) ) {
		wp_send_json_error( array( 'message' => $share->get_error_message() ) );
	}

	wp_send_json_success( $share );
}
add_action( 'wp_ajax_prairie_ash_image_repair', 'prairie_ash_image_repair_ajax' );

/**
 * The repair form posted without the script: one share, then back to the
 * screen.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_image_repair_post() {
	check_admin_referer( 'prairie_ash_image_repair' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Only an administrator can repair the image copies.', 'prairie-ash' ) );
	}

	$share = prairie_ash_image_repair_share( ! empty( $_POST['continue'] ) );

	if ( is_wp_error( $share ) ) {
		wp_die( esc_html( $share->get_error_message() ), '', array( 'back_link' => true ) );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'               => 'prairie-ash-demo',
				'prairie-ash-repair' => $share['done'] ? 'done' : 'continue',
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_post_prairie_ash_image_repair', 'prairie_ash_image_repair_post' );

/**
 * Run one share of the count of missing copies.
 *
 * The count reads the copies of every image on disk, so on a large library
 * it runs in shares like the repair, and the screen shows the last finished
 * count instead of reading every file on each visit.
 *
 * @since 1.2.1
 *
 * @param bool $carry_on Whether to carry on the count that is part way.
 * @param int  $budget   Seconds the share may take, roughly.
 * @return array Whether it is done, how many images were checked of how
 *               many, and the summary once it is done.
 */
function prairie_ash_image_count_share( $carry_on, $budget = 20 ) {
	$seconds = (int) ini_get( 'max_execution_time' );
	$budget  = $seconds > 0 ? min( (int) $budget, (int) floor( $seconds / 2 ) ) : (int) $budget;
	$state   = prairie_ash_count_image_copies( $carry_on, microtime( true ) + max( 1, $budget ) );
	$ids     = prairie_ash_image_attachments();
	$checked = $state['done'] ? count( $ids ) : count(
		array_filter(
			$ids,
			static function ( $id ) use ( $state ) {
				return $id <= $state['cursor'];
			}
		)
	);

	return array(
		'done'     => (bool) $state['done'],
		'checked'  => $checked,
		'total'    => count( $ids ),
		'failures' => prairie_ash_image_failure_lines(),
		'summary'  => $state['done'] ? prairie_ash_image_copies_summary() : '',
	);
}

/**
 * The script's request for one share of the count.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_image_count_ajax() {
	check_ajax_referer( 'prairie_ash_image_count' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Only an administrator can count the image copies.', 'prairie-ash' ) ) );
	}

	wp_send_json_success( prairie_ash_image_count_share( ! empty( $_POST['continue'] ) ) );
}
add_action( 'wp_ajax_prairie_ash_image_count', 'prairie_ash_image_count_ajax' );

/**
 * The count form posted without the script: one share, then back to the
 * screen.
 *
 * @since 1.2.1
 *
 * @return void
 */
function prairie_ash_image_count_post() {
	check_admin_referer( 'prairie_ash_image_count' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Only an administrator can count the image copies.', 'prairie-ash' ) );
	}

	$share = prairie_ash_image_count_share( ! empty( $_POST['continue'] ) );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'              => 'prairie-ash-demo',
				'prairie-ash-count' => $share['done'] ? 'done' : 'continue',
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_post_prairie_ash_image_count', 'prairie_ash_image_count_post' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once get_theme_file_path( 'inc/class-prairie-ash-demo-command.php' );
	WP_CLI::add_command( 'prairie-ash-demo', 'Prairie_Ash_Demo_Command' );
}

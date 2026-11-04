<?php
/**
 * Builds the demo site from the theme's demo folder.
 *
 * Reads demo/content.json, demo/forms.xml and the files under demo/media,
 * which Prairie_Ash_Demo_Exporter wrote, and creates or updates every record
 * through the API of the plugin that owns it: core for media, pages, menus
 * and options, WooCommerce for products, attributes and shipping, and
 * Formidable's own XML import for the forms.
 *
 * The work is cut into steps and each call runs as many as fit in a time
 * budget, so the admin button and the command line both finish on hosts with
 * a short time limit, and an import broken off half way carries on where it
 * stopped. Every record is matched before it is written, by the ID it got on
 * an earlier run or by its slug, key or file, so a second run updates what is
 * there and creates nothing twice.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Importer of the demo content.
 *
 * @since 1.2.0
 */
class Prairie_Ash_Demo_Importer {

	/**
	 * Option holding the progress of the import that is running.
	 *
	 * @since 1.2.0
	 * @var string
	 */
	const STATE_OPTION = 'prairie_ash_demo_state';

	/**
	 * Option holding the ID each demo record received on this site.
	 *
	 * @since 1.2.0
	 * @var string
	 */
	const MAP_OPTION = 'prairie_ash_demo_map';

	/**
	 * Absolute path of the demo folder.
	 *
	 * @since 1.2.0
	 * @var string
	 */
	private $dir;

	/**
	 * The demo data, read once per request.
	 *
	 * @since 1.2.0
	 * @var array|null
	 */
	private $data = null;

	/**
	 * Old ID to new ID, by kind: post, form and term.
	 *
	 * @since 1.2.0
	 * @var array
	 */
	private $map = array();

	/**
	 * Lines describing what the last call did.
	 *
	 * @since 1.2.0
	 * @var string[]
	 */
	public $log = array();

	/**
	 * Set up the importer.
	 *
	 * @since 1.2.0
	 *
	 * The demo folder is the one in the theme that holds this file, also when
	 * a child theme is active.
	 */
	public function __construct() {
		$this->dir = get_template_directory() . '/demo';
		$this->map = wp_parse_args(
			(array) get_option( self::MAP_OPTION, array() ),
			array(
				'post' => array(),
				'form' => array(),
				'term' => array(),
			)
		);
	}

	/**
	 * The steps, in the order they run.
	 *
	 * Media comes first because pages, products and settings point at it,
	 * then the AVIF and WebP copies of the images, products and forms before
	 * pages because the sections point at both, and the settings that point
	 * at pages and menus last.
	 *
	 * @since 1.2.0
	 *
	 * @return string[] Step name to its label.
	 */
	public static function steps() {
		return array(
			'settings'   => __( 'Settings', 'prairie-ash' ),
			'media'      => __( 'Media', 'prairie-ash' ),
			'images'     => __( 'Image copies', 'prairie-ash' ),
			'terms'      => __( 'Product attributes and categories', 'prairie-ash' ),
			'products'   => __( 'Products', 'prairie-ash' ),
			'forms'      => __( 'Forms', 'prairie-ash' ),
			'pages'      => __( 'Pages', 'prairie-ash' ),
			'shipping'   => __( 'Shipping', 'prairie-ash' ),
			'menus'      => __( 'Menus', 'prairie-ash' ),
			'references' => __( 'Front page, shop pages and site settings', 'prairie-ash' ),
			'finish'     => __( 'Clean-up', 'prairie-ash' ),
		);
	}

	/**
	 * Whether the plugins the demo needs are active and the data is readable.
	 *
	 * @since 1.2.0
	 *
	 * @return true|WP_Error
	 */
	public function check() {
		$missing = array();

		foreach ( prairie_ash_demo_requirements()['plugins'] as $plugin ) {
			if ( empty( $plugin['required'] ) ) {
				continue;
			}

			if ( ! prairie_ash_demo_plugin_active( $plugin['slug'] ) ) {
				$missing[] = $plugin['name'];
			}
		}

		if ( $missing ) {
			return new WP_Error(
				'prairie_ash_demo_plugins',
				/* translators: %s: plugin names, separated by commas. */
				sprintf( __( 'Activate these plugins first: %s.', 'prairie-ash' ), implode( ', ', $missing ) )
			);
		}

		if ( ! is_array( $this->data() ) ) {
			return new WP_Error( 'prairie_ash_demo_data', __( 'The demo data in the theme folder could not be read.', 'prairie-ash' ) );
		}

		return true;
	}

	/**
	 * Start an import from the first step. IDs found on an earlier run are
	 * kept, so a new run updates the same records.
	 *
	 * @since 1.2.0
	 *
	 * @return array The state.
	 */
	public function start() {
		$state = array(
			'step'     => 0,
			'cursor'   => 0,
			'done'     => false,
			'started'  => time(),
			'finished' => 0,
		);

		update_option( self::STATE_OPTION, $state, false );

		return $state;
	}

	/**
	 * The progress of the current or the last import.
	 *
	 * @since 1.2.0
	 *
	 * @return array|null
	 */
	public static function state() {
		$state = get_option( self::STATE_OPTION, null );

		return is_array( $state ) ? $state : null;
	}

	/**
	 * Run steps until they are all done or the time budget is spent.
	 *
	 * @since 1.2.0
	 *
	 * Where PHP has a time limit, the budget is at most half of it, so a
	 * share ends well inside the limit on a host that allows 30 seconds.
	 *
	 * @since 1.2.0
	 * @since 1.2.1 The budget is capped at half the time limit, and the image
	 *              copies are written in their own step.
	 *
	 * @param int $budget Seconds this call may take, roughly. A step that is
	 *                    started is finished, and media are imported one file
	 *                    at a time, so a call can run over by one file.
	 * @return array|WP_Error The state after the call.
	 */
	public function run( $budget = 20 ) {
		$check = $this->check();

		if ( is_wp_error( $check ) ) {
			return $check;
		}

		$state   = self::state();
		$state   = $state ? $state : $this->start();
		$names   = array_keys( self::steps() );
		$seconds = (int) ini_get( 'max_execution_time' );
		$budget  = $seconds > 0 ? min( (int) $budget, (int) floor( $seconds / 2 ) ) : (int) $budget;
		$until   = microtime( true ) + max( 1, $budget );

		wp_raise_memory_limit( 'admin' );
		$this->quiet_mail();
		prairie_ash_defer_image_formats( true );

		while ( ! $state['done'] && microtime( true ) < $until ) {
			$name   = $names[ $state['step'] ];
			$method = 'step_' . $name;
			$cursor = $this->$method( (int) $state['cursor'], $until );

			$this->log = array_merge( $this->log, prairie_ash_image_log() );

			if ( true === $cursor ) {
				++$state['step'];
				$state['cursor'] = 0;
			} else {
				$state['cursor'] = (int) $cursor;
			}

			if ( $state['step'] >= count( $names ) ) {
				$state['done']     = true;
				$state['finished'] = time();
			}

			update_option( self::MAP_OPTION, $this->map, false );
			update_option( self::STATE_OPTION, $state, false );
		}

		return $state;
	}

	/**
	 * No mail leaves the site while the demo is built: WooCommerce and
	 * Formidable would otherwise mail the site's administrator about the
	 * records they see created.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	private function quiet_mail() {
		add_filter( 'pre_wp_mail', '__return_false' );
	}

	/**
	 * The demo data.
	 *
	 * @since 1.2.0
	 *
	 * @return array|null
	 */
	private function data() {
		if ( null === $this->data ) {
			$file = $this->dir . '/content.json';
			$data = is_readable( $file ) ? wp_json_file_decode( $file, array( 'associative' => true ) ) : null;

			$this->data = is_array( $data ) ? $data : null;
		}

		return $this->data;
	}

	/**
	 * Replace the tokens in a value with this site's address and IDs.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	private function resolve( $value ) {
		if ( is_array( $value ) ) {
			$out = array();

			foreach ( $value as $key => $one ) {
				$out[ $this->resolve( $key ) ] = $this->resolve( $one );
			}

			return $out;
		}

		if ( ! is_string( $value ) || false === strpos( $value, '{{' ) ) {
			return $value;
		}

		$home = untrailingslashit( home_url() );

		return (string) preg_replace_callback(
			'/\{\{(home|home_json|home_json2|post|form|term)(?::(\d+))?\}\}/',
			function ( $found ) use ( $home ) {
				switch ( $found[1] ) {
					case 'home':
						return $home;
					case 'home_json':
						return str_replace( '/', '\\/', $home );
					case 'home_json2':
						return str_replace( '/', '\\\\/', $home );
				}

				$id = (int) ( $found[2] ?? 0 );

				if ( isset( $this->map[ $found[1] ][ $id ] ) ) {
					return (string) $this->map[ $found[1] ][ $id ];
				}

				$this->log[] = sprintf( 'No %1$s was imported for the demo ID %2$d; the reference is left empty.', $found[1], $id );

				return '';
			},
			$value
		);
	}

	/**
	 * A post on this site that a demo record became, if it still exists.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $old_id Demo ID.
	 * @param string $type   Expected post type.
	 * @return int Post ID, or 0.
	 */
	private function mapped_post( $old_id, $type ) {
		$id = (int) ( $this->map['post'][ $old_id ] ?? 0 );

		if ( $id && get_post_type( $id ) === $type && 'trash' !== get_post_status( $id ) ) {
			return $id;
		}

		return 0;
	}

	/**
	 * Find a post by its slug within one post type, any status but the trash.
	 *
	 * @since 1.2.0
	 *
	 * @param string $slug Slug.
	 * @param string $type Post type.
	 * @return int Post ID, or 0.
	 */
	private function post_by_slug( $slug, $type ) {
		$found = get_posts(
			array(
				'post_type'        => array( $type ),
				'name'             => $slug,
				'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page'   => 1,
				'fields'           => 'ids',
				'suppress_filters' => true,
			)
		);

		return $found ? (int) $found[0] : 0;
	}

	/**
	 * Write the field values stored on a post.
	 *
	 * @since 1.2.0
	 *
	 * @param int   $post_id Post ID.
	 * @param array $meta    Meta key to value, tokens resolved here.
	 * @return void
	 */
	private function write_meta( $post_id, $meta ) {
		foreach ( (array) $this->resolve( $meta ) as $key => $value ) {
			update_post_meta( $post_id, $key, wp_slash( $value ) );
		}
	}

	/**
	 * Settings that point at nothing else: the site's name, time zone and
	 * permalinks, the shop's country, currency and checkout, the payment
	 * method, and Formidable's styling switch.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_settings() {
		$options = $this->data()['options'];

		foreach ( Prairie_Ash_Demo_Exporter::plain_options() as $name ) {
			if ( array_key_exists( $name, $options ) ) {
				$value   = $this->resolve( $options[ $name ] );
				$current = get_option( $name );

				// Payment method settings are merged, so a site's own account
				// details survive the switch being set.
				if ( is_array( $value ) && is_array( $current ) && str_ends_with( $name, '_settings' ) ) {
					$value = array_merge( $current, $value );
				}

				update_option( $name, $value );
			}
		}

		if ( class_exists( 'FrmAppHelper' ) && ! empty( $options['formidable']['load_style'] ) ) {
			$settings             = FrmAppHelper::get_settings();
			$settings->load_style = $options['formidable']['load_style'];
			$settings->store();
		}

		$this->log[] = 'Settings written.';

		return true;
	}

	/**
	 * Upload the demo images, one file per pass, so each request stays short.
	 *
	 * Each file goes through the same path as an upload in the media
	 * library: WordPress writes the sizes the theme registers. The AVIF and
	 * WebP copies wait for the next step, which writes them one at a time.
	 * The file lands in the year and month folder it had on the demo site,
	 * so its address is the same apart from the host.
	 *
	 * @since 1.2.0
	 *
	 * @param int   $cursor Index of the next file.
	 * @param float $until  Time the budget ends.
	 * @return int|true Next index, or true when every file is in.
	 */
	private function step_media( $cursor, $until ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$media = $this->data()['media'];
		$count = count( $media );

		while ( $cursor < $count ) {
			$this->import_attachment( $media[ $cursor ] );
			++$cursor;

			if ( microtime( true ) >= $until ) {
				break;
			}
		}

		return $cursor >= $count ? true : $cursor;
	}

	/**
	 * Write the AVIF and WebP copies of the demo images, one copy at a time,
	 * so each request stays inside a short time limit and a modest memory
	 * limit.
	 *
	 * Every copy that is missing, empty or unreadable is written, so running
	 * the import again also repairs the copies of an earlier run. A copy that
	 * cannot be written is reported in the log and skipped, and the picture
	 * falls back to the next format.
	 *
	 * @since 1.2.1
	 *
	 * @param int   $cursor Index of the next image.
	 * @param float $until  Time the budget ends.
	 * @return int|true Next index, or true when every image is done.
	 */
	private function step_images( $cursor, $until ) {
		$media = $this->data()['media'];
		$count = count( $media );
		$pass  = (string) self::state()['started'];

		while ( $cursor < $count ) {
			if ( microtime( true ) >= $until ) {
				return $cursor;
			}

			$id = $this->mapped_post( $media[ $cursor ]['id'], 'attachment' );

			if ( $id && ! prairie_ash_complete_image_copies( $id, $pass, $until ) ) {
				return $cursor;
			}

			++$cursor;
		}

		$failed = count( prairie_ash_image_failures() );

		$this->log[] = $failed
			? sprintf( '%d image copies could not be written.', $failed )
			: 'Every image has its AVIF and WebP copies.';

		return true;
	}

	/**
	 * Import one attachment, or find the one an earlier run imported.
	 *
	 * @since 1.2.0
	 *
	 * @param array $item Media record.
	 * @return void
	 */
	private function import_attachment( $item ) {
		$id = $this->mapped_post( $item['id'], 'attachment' );

		if ( ! $id ) {
			$id = attachment_url_to_postid( trailingslashit( wp_get_upload_dir()['baseurl'] ) . $item['attached'] );
		}

		if ( $id ) {
			$this->complete_sizes( $id );
		} else {
			$id = $this->upload( $item );
		}

		if ( ! $id ) {
			return;
		}

		$this->map['post'][ (int) $item['id'] ] = $id;
		update_post_meta( $id, '_wp_attachment_image_alt', wp_slash( $item['alt'] ) );
	}

	/**
	 * Finish the sizes of an image an earlier run imported.
	 *
	 * An import broken off while WordPress was writing an image's sizes leaves
	 * the image with some of them, and core writes the missing ones. The
	 * AVIF and WebP copies are the next step's work.
	 *
	 * @since 1.2.0
	 * @since 1.2.1 Leaves the copies to the image copies step.
	 *
	 * @param int $id Attachment ID.
	 * @return void
	 */
	private function complete_sizes( $id ) {
		if ( wp_attachment_is_image( $id ) && wp_get_missing_image_subsizes( $id ) ) {
			wp_update_image_subsizes( $id );
		}
	}

	/**
	 * Upload one demo file into its year and month folder.
	 *
	 * @since 1.2.0
	 *
	 * @param array $item Media record.
	 * @return int Attachment ID, or 0 on failure.
	 */
	private function upload( $item ) {
		$source = $this->dir . '/media/' . $item['file'];

		if ( ! is_readable( $source ) ) {
			$this->log[] = sprintf( 'The file %s is missing from the demo folder.', $item['file'] );
			return 0;
		}

		$tmp = wp_tempnam( wp_basename( $source ) );

		if ( ! $tmp || ! copy( $source, $tmp ) ) {
			$this->log[] = sprintf( 'The file %s could not be copied.', $item['file'] );
			return 0;
		}

		$folder = dirname( $item['file'] );
		$upload = array(
			'name'     => wp_basename( $source ),
			'tmp_name' => $tmp,
		);
		$moved  = wp_handle_sideload(
			$upload,
			array( 'test_form' => false ),
			preg_match( '#^\d{4}/\d{2}$#', $folder ) ? $folder : null
		);

		if ( isset( $moved['error'] ) ) {
			wp_delete_file( $tmp );
			$this->log[] = sprintf( 'The file %1$s could not be uploaded: %2$s', $item['file'], $moved['error'] );
			return 0;
		}

		$id = wp_insert_attachment(
			array(
				'post_mime_type' => $moved['type'],
				'guid'           => $moved['url'],
				'post_title'     => $item['title'],
				'post_name'      => $item['slug'],
				'post_excerpt'   => $item['caption'],
				'post_content'   => $item['description'],
				'post_date'      => $item['date'],
				'post_date_gmt'  => $item['date_gmt'],
				'post_status'    => 'inherit',
			),
			$moved['file'],
			0,
			true
		);

		if ( is_wp_error( $id ) ) {
			$this->log[] = sprintf( 'The attachment %1$s could not be created: %2$s', $item['file'], $id->get_error_message() );
			return 0;
		}

		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $moved['file'] ) );
		$this->log[] = sprintf( 'Imported %s.', $item['file'] );

		return (int) $id;
	}

	/**
	 * Product attributes, their terms and the product categories.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_terms() {
		$terms = $this->data()['terms'];

		foreach ( $terms['attributes'] as $attribute ) {
			if ( ! wc_attribute_taxonomy_id_by_name( $attribute['slug'] ) ) {
				$created = wc_create_attribute(
					array(
						'name'         => $attribute['name'],
						'slug'         => $attribute['slug'],
						'type'         => $attribute['type'],
						'order_by'     => $attribute['order_by'],
						'has_archives' => $attribute['has_archives'],
					)
				);

				if ( is_wp_error( $created ) ) {
					$this->log[] = sprintf( 'The attribute %1$s could not be created: %2$s', $attribute['slug'], $created->get_error_message() );
				}
			}

			$taxonomy = wc_attribute_taxonomy_name( $attribute['slug'] );

			// A new attribute's taxonomy is registered on the next request; this
			// one needs it now to take its terms.
			if ( ! taxonomy_exists( $taxonomy ) ) {
				register_taxonomy( $taxonomy, array( 'product', 'product_variation' ), array( 'hierarchical' => false ) );
			}
		}

		foreach ( $terms['terms'] as $term ) {
			if ( ! taxonomy_exists( $term['taxonomy'] ) ) {
				continue;
			}

			$args  = array(
				'slug'        => $term['slug'],
				'description' => $term['description'],
			);
			$found = get_term_by( 'slug', $term['slug'], $term['taxonomy'] );

			if ( $found ) {
				$result = wp_update_term( $found->term_id, $term['taxonomy'], $args + array( 'name' => $term['name'] ) );
			} else {
				$result = wp_insert_term( $term['name'], $term['taxonomy'], $args );
			}

			if ( is_wp_error( $result ) ) {
				$this->log[] = sprintf( 'The term %1$s could not be written: %2$s', $term['slug'], $result->get_error_message() );
				continue;
			}

			$this->map['term'][ (int) $term['id'] ] = (int) $result['term_id'];

			foreach ( $term['meta'] as $key => $value ) {
				update_term_meta( $result['term_id'], $key, wp_slash( $value ) );
			}
		}

		$this->log[] = 'Attributes and terms written.';

		return true;
	}

	/**
	 * The forms, through Formidable's own XML import, which finds a form by
	 * its key and updates it rather than adding a second one.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_forms() {
		$file = $this->dir . '/forms.xml';

		if ( ! class_exists( 'FrmXMLHelper' ) || ! is_readable( $file ) ) {
			return true;
		}

		// Formidable creates its tables on the first admin screen after it is
		// switched on, which a site set up from the command line never shows.
		if ( FrmAppController::needs_update() ) {
			FrmAppController::install();
		}

		$xml = (string) file_get_contents( get_template_directory() . '/demo/forms.xml' );
		$tmp = wp_tempnam( 'prairie-ash-forms.xml' );

		if ( ! $tmp ) {
			$this->log[] = 'The forms could not be prepared for import.';
			return true;
		}

		global $wp_filesystem;
		require_once ABSPATH . 'wp-admin/includes/file.php';

		if ( WP_Filesystem() ) {
			$wp_filesystem->put_contents( $tmp, $this->resolve( $xml ) );
		}

		$this->delete_form_actions();
		$result = FrmXMLHelper::import_xml( $tmp );
		wp_delete_file( $tmp );

		if ( is_wp_error( $result ) ) {
			$this->log[] = 'The forms could not be imported: ' . $result->get_error_message();
			return true;
		}

		foreach ( $this->data()['forms'] as $form ) {
			$id = (int) FrmForm::get_id_by_key( $form['key'] );

			if ( $id ) {
				$this->map['form'][ (int) $form['id'] ] = $id;
			}
		}

		$this->log[] = 'Forms imported.';

		return true;
	}

	/**
	 * Remove the actions of the demo forms before they are imported again.
	 *
	 * Formidable's import updates a form found by its key, but adds its
	 * actions again, so a second import would leave each form with two
	 * redirects. The XML carries every action the form has.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	private function delete_form_actions() {
		foreach ( $this->data()['forms'] as $form ) {
			$id = (int) FrmForm::get_id_by_key( $form['key'] );

			if ( ! $id ) {
				continue;
			}

			foreach ( FrmFormAction::get_action_for_form( $id, 'all' ) as $action ) {
				wp_delete_post( $action->ID, true );
			}
		}
	}

	/**
	 * Every page, found by its slug when it already exists. The shop, cart,
	 * checkout and privacy pages WordPress and WooCommerce create on a new
	 * site are updated rather than duplicated, because they carry the slug.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_pages() {
		$pages = $this->data()['pages'];

		// Two passes: parents get their IDs before children point at them.
		foreach ( array( false, true ) as $with_parent ) {
			foreach ( $pages as $page ) {
				if ( ( '' !== $page['parent'] ) !== $with_parent ) {
					continue;
				}

				$this->import_page( $page );
			}
		}

		$this->log[] = sprintf( '%d pages written.', count( $pages ) );

		return true;
	}

	/**
	 * Create or update one page.
	 *
	 * @since 1.2.0
	 *
	 * @param array $page Page record.
	 * @return void
	 */
	private function import_page( $page ) {
		$id = $this->mapped_post( $page['id'], 'page' );
		$id = $id ? $id : $this->post_by_slug( $page['slug'], 'page' );

		$postarr = array(
			'post_type'      => 'page',
			'post_status'    => $page['status'],
			'post_name'      => $page['slug'],
			'post_title'     => $page['title'],
			'post_excerpt'   => $page['excerpt'],
			'post_content'   => $this->resolve( $page['content'] ),
			'post_parent'    => (int) $this->resolve( $page['parent'] ),
			'menu_order'     => (int) $page['menu_order'],
			'post_date'      => $page['date'],
			'post_date_gmt'  => $page['date_gmt'],
			'comment_status' => $page['comment_status'],
			'ping_status'    => $page['ping_status'],
			'page_template'  => $page['template'],
		);

		if ( $id ) {
			$postarr['ID'] = $id;
		}

		$result = wp_insert_post( wp_slash( $postarr ), true );

		if ( is_wp_error( $result ) ) {
			$this->log[] = sprintf( 'The page %1$s could not be written: %2$s', $page['slug'], $result->get_error_message() );
			return;
		}

		$this->map['post'][ (int) $page['id'] ] = (int) $result;
		$this->write_meta( $result, $page['meta'] );
	}

	/**
	 * Every product and its variations, through WooCommerce's product
	 * objects, so its price, stock and attribute lookups are written too.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_products() {
		$products = $this->data()['products'];

		foreach ( $products as $item ) {
			$this->import_product( $item );
		}

		wc_delete_product_transients();
		$this->log[] = sprintf( '%d products written.', count( $products ) );

		return true;
	}

	/**
	 * Create or update one product with its variations.
	 *
	 * @since 1.2.0
	 *
	 * @param array $item Product record.
	 * @return void
	 */
	private function import_product( $item ) {
		$id = $this->mapped_post( $item['id'], 'product' );
		$id = $id ? $id : $this->post_by_slug( $item['slug'], 'product' );

		$product = $id ? wc_get_product( $id ) : null;

		if ( ! $product || $product->get_type() !== $item['type'] ) {
			$class   = WC_Product_Factory::get_product_classname( $id, $item['type'] );
			$product = new $class( $id );
		}

		$this->set_product_props( $product, $item );
		$product->set_category_ids( $this->term_ids( $item['categories'], 'product_cat' ) );
		$product->set_tag_ids( $this->term_ids( $item['tags'], 'product_tag' ) );
		$product->set_attributes( $this->product_attributes( $item['attributes'] ) );
		$product->set_default_attributes( $item['default_attributes'] );

		$id = $product->save();

		$this->map['post'][ (int) $item['id'] ] = (int) $id;
		$this->write_meta( $id, $item['meta'] );

		if ( empty( $item['variations'] ) ) {
			return;
		}

		$existing = array();
		foreach ( $product->get_children() as $child_id ) {
			$child = wc_get_product( $child_id );

			if ( $child ) {
				$existing[ $this->variation_signature( $child->get_attributes() ) ] = $child_id;
			}
		}

		foreach ( $item['variations'] as $row ) {
			$signature = $this->variation_signature( $row['attributes'] );
			$variation = new WC_Product_Variation( $existing[ $signature ] ?? 0 );

			$variation->set_parent_id( $id );
			$this->set_product_props( $variation, $row );
			$variation->set_attributes( $row['attributes'] );

			$child_id = $variation->save();

			$this->map['post'][ (int) $row['id'] ] = (int) $child_id;
			$this->write_meta( $child_id, $row['meta'] );
		}

		WC_Product_Variable::sync( $id );
	}

	/**
	 * Set the content properties of a product or a variation.
	 *
	 * @since 1.2.0
	 *
	 * @param WC_Product $product Product or variation.
	 * @param array      $item    Record.
	 * @return void
	 */
	private function set_product_props( $product, $item ) {
		$props = array(
			'name',
			'slug',
			'status',
			'featured',
			'catalog_visibility',
			'description',
			'short_description',
			'sku',
			'menu_order',
			'virtual',
			'downloadable',
			'tax_status',
			'tax_class',
			'manage_stock',
			'stock_quantity',
			'stock_status',
			'backorders',
			'low_stock_amount',
			'sold_individually',
			'weight',
			'length',
			'width',
			'height',
			'reviews_allowed',
			'purchase_note',
			'regular_price',
			'sale_price',
			'date_created',
		);

		foreach ( $props as $prop ) {
			if ( ! array_key_exists( $prop, $item ) || ( 'date_created' === $prop && '' === $item[ $prop ] ) ) {
				continue;
			}

			$setter = 'set_' . $prop;

			if ( is_callable( array( $product, $setter ) ) ) {
				$product->$setter( $item[ $prop ] );
			}
		}

		$product->set_image_id( (int) $this->resolve( $item['image_id'] ) );
		$product->set_gallery_image_ids( array_filter( array_map( 'intval', (array) $this->resolve( $item['gallery_image_ids'] ) ) ) );
	}

	/**
	 * Term IDs on this site for a list of slugs.
	 *
	 * @since 1.2.0
	 *
	 * @param string[] $slugs    Slugs.
	 * @param string   $taxonomy Taxonomy.
	 * @return int[]
	 */
	private function term_ids( $slugs, $taxonomy ) {
		$ids = array();

		foreach ( (array) $slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );

			if ( $term ) {
				$ids[] = (int) $term->term_id;
			}
		}

		return $ids;
	}

	/**
	 * Product attribute objects from their records.
	 *
	 * @since 1.2.0
	 *
	 * @param array $rows Attribute records.
	 * @return WC_Product_Attribute[]
	 */
	private function product_attributes( $rows ) {
		$attributes = array();

		foreach ( $rows as $row ) {
			$attribute = new WC_Product_Attribute();
			$options   = $row['options'];

			if ( $row['taxonomy'] ) {
				$attribute->set_id( wc_attribute_taxonomy_id_by_name( $row['name'] ) );
				$options = $this->term_ids( $options, $row['name'] );
			}

			$attribute->set_name( $row['name'] );
			$attribute->set_options( $options );
			$attribute->set_visible( $row['visible'] );
			$attribute->set_variation( $row['variation'] );
			$attribute->set_position( $row['position'] );

			$attributes[] = $attribute;
		}

		return $attributes;
	}

	/**
	 * One string for a variation's attribute values, to match it on a
	 * second run.
	 *
	 * @since 1.2.0
	 *
	 * @param array $attributes Attribute name to value.
	 * @return string
	 */
	private function variation_signature( $attributes ) {
		ksort( $attributes );

		return (string) wp_json_encode( $attributes );
	}

	/**
	 * Shipping zones, their places and their methods, the rest of the world
	 * included. A zone is found by its name and a method by its type within
	 * the zone, so a second run updates them.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_shipping() {
		foreach ( $this->data()['shipping'] as $row ) {
			$zone = new WC_Shipping_Zone( '' === $row['name'] ? 0 : null );

			if ( '' !== $row['name'] ) {
				foreach ( WC_Shipping_Zones::get_zones( 'admin' ) as $existing ) {
					if ( $existing['zone_name'] === $row['name'] ) {
						$zone = new WC_Shipping_Zone( (int) $existing['zone_id'] );
						break;
					}
				}

				$zone->set_zone_name( $row['name'] );
				$zone->set_zone_order( $row['order'] );
				$zone->clear_locations();

				foreach ( $row['locations'] as $location ) {
					$zone->add_location( $location['code'], $location['type'] );
				}

				$zone->save();
			}

			$taken = array();

			foreach ( $row['methods'] as $method ) {
				$instance = 0;

				foreach ( $zone->get_shipping_methods( false, 'admin' ) as $current ) {
					if ( $current->id === $method['type'] && ! in_array( $current->instance_id, $taken, true ) ) {
						$instance = (int) $current->instance_id;
						break;
					}
				}

				if ( ! $instance ) {
					$instance = (int) $zone->add_shipping_method( $method['type'] );
				}

				if ( ! $instance ) {
					$this->log[] = sprintf( 'The shipping method %s could not be added.', $method['type'] );
					continue;
				}

				$taken[]  = $instance;
				$shipping = WC_Shipping_Zones::get_shipping_method( $instance );

				update_option( $shipping->get_instance_option_key(), $this->resolve( $method['settings'] ) );

				if ( (int) $shipping->method_order !== (int) $method['order'] || ( 'yes' === $shipping->enabled ) !== (bool) $method['enabled'] ) {
					$this->set_method_order( $zone->get_id(), $instance, $method );
				}
			}
		}

		WC_Cache_Helper::invalidate_cache_group( 'shipping_zones' );
		WC_Cache_Helper::get_transient_version( 'shipping', true );

		$this->log[] = 'Shipping written.';

		return true;
	}

	/**
	 * Set a shipping method's place in its zone and its switch.
	 *
	 * WooCommerce keeps both in its own table and changes them only through
	 * its REST route, so the route is called here as the zone screen calls it.
	 * A method added to a new zone already has the right place and is on, so
	 * this runs only when a second import finds them changed.
	 *
	 * @since 1.2.0
	 *
	 * @param int   $zone_id  Zone ID.
	 * @param int   $instance Method instance ID.
	 * @param array $method   Method record.
	 * @return void
	 */
	private function set_method_order( $zone_id, $instance, $method ) {
		$request = new WP_REST_Request( 'PUT', sprintf( '/wc/v3/shipping/zones/%d/methods/%d', $zone_id, $instance ) );
		$request->set_body_params(
			array(
				'order'   => (int) $method['order'],
				'enabled' => (bool) $method['enabled'],
			)
		);

		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			$this->log[] = sprintf( 'The order of the shipping method %s could not be set.', $method['type'] );
		}
	}

	/**
	 * The menus, emptied and filled again, and the locations they fill.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_menus() {
		$locations = (array) get_theme_mod( 'nav_menu_locations', array() );

		foreach ( $this->data()['menus'] as $menu ) {
			$found   = wp_get_nav_menu_object( $menu['name'] );
			$menu_id = $found ? (int) $found->term_id : (int) wp_create_nav_menu( $menu['name'] );

			if ( ! $menu_id ) {
				continue;
			}

			foreach ( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $old_item ) {
				wp_delete_post( $old_item->ID, true );
			}

			$items = $menu['items'];
			usort(
				$items,
				static function ( $a, $b ) {
					return $a['order'] <=> $b['order'];
				}
			);

			$created = array();
			foreach ( $items as $item ) {
				$args = array(
					'menu-item-title'       => $item['title'],
					'menu-item-type'        => $item['type'],
					'menu-item-object'      => $item['object'],
					'menu-item-object-id'   => (int) $this->resolve( $item['object_id'] ),
					'menu-item-url'         => $this->resolve( $item['url'] ),
					'menu-item-parent-id'   => (int) ( $created[ $item['parent'] ] ?? 0 ),
					'menu-item-position'    => (int) $item['order'],
					'menu-item-classes'     => $item['classes'],
					'menu-item-target'      => $item['target'],
					'menu-item-xfn'         => $item['xfn'],
					'menu-item-attr-title'  => $item['attr_title'],
					'menu-item-description' => $item['description'],
					'menu-item-status'      => 'publish',
				);

				$item_id = wp_update_nav_menu_item( $menu_id, 0, wp_slash( $args ) );

				if ( ! is_wp_error( $item_id ) ) {
					$created[ (int) $item['id'] ] = (int) $item_id;
				}
			}

			$this->map['term'][ (int) $menu['id'] ] = $menu_id;

			foreach ( $menu['locations'] as $location ) {
				$locations[ $location ] = $menu_id;
			}
		}

		set_theme_mod( 'nav_menu_locations', $locations );
		$this->log[] = 'Menus written.';

		return true;
	}

	/**
	 * The settings that point at pages and attachments: the front page, the
	 * privacy page, WooCommerce's pages and the theme's Site settings and
	 * Shop wording.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_references() {
		$options = $this->data()['options'];

		foreach ( Prairie_Ash_Demo_Exporter::page_options() as $name ) {
			if ( array_key_exists( $name, $options ) ) {
				update_option( $name, $this->resolve( $options[ $name ] ) );
			}
		}

		foreach ( (array) $this->resolve( $this->data()['theme'] ) as $name => $value ) {
			$option = str_starts_with( $name, '_' ) ? '_options_' . substr( $name, 1 ) : 'options_' . $name;

			update_option( $option, $value, false );
		}

		$this->log[] = 'Front page, shop pages and site settings written.';

		return true;
	}

	/**
	 * Move the sample post and pages a new site starts with to the trash,
	 * where the demo has nothing of the kind, and rebuild the permalinks.
	 *
	 * Only records that the demo does not contain, still carrying the slug
	 * WordPress or WooCommerce gave them, are touched, and the trash keeps
	 * them for thirty days.
	 *
	 * @since 1.2.0
	 *
	 * @return true
	 */
	private function step_finish() {
		$demo_slugs = wp_list_pluck( $this->data()['pages'], 'slug' );
		$defaults   = array(
			array( 'post', 'hello-world' ),
			array( 'page', 'sample-page' ),
			array( 'page', 'refund_returns' ),
			array( 'page', 'my-account' ),
		);

		foreach ( $defaults as $default ) {
			if ( 'page' === $default[0] && in_array( $default[1], $demo_slugs, true ) ) {
				continue;
			}

			$id = $this->post_by_slug( $default[1], $default[0] );

			if ( $id && ! in_array( $id, $this->map['post'], true ) ) {
				wp_trash_post( $id );
				$this->log[] = sprintf( 'Moved the sample %1$s %2$s to the trash.', $default[0], $default[1] );
			}
		}

		// Formidable adds a sample contact form, with a mail action to the site's
		// administrator, the first time it sets up its tables. The demo has its
		// own three forms, so the sample goes while nobody has sent it.
		$sample = class_exists( 'FrmForm' ) ? (int) FrmForm::get_id_by_key( 'contact-form' ) : 0;

		if ( $sample && ! in_array( 'contact-form', wp_list_pluck( $this->data()['forms'], 'key' ), true ) && 0 === (int) FrmEntry::getRecordCount( $sample ) ) {
			FrmForm::destroy( $sample );
			$this->log[] = 'Removed Formidable\'s sample contact form.';
		}

		flush_rewrite_rules();
		wc_delete_product_transients();

		$this->log[] = 'Done.';

		return true;
	}
}

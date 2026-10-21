<?php
/**
 * The title and the description a page or a coffee shows in search results.
 *
 * Each page and each product carries two fields. An empty field publishes the
 * inherited value, and the edit screen shows that value as the placeholder,
 * says where it comes from and draws the search result with it, so the screen
 * always shows what the page publishes. The front end and the screen read the
 * same functions.
 *
 * The order confirmation is the checkout page with an order attached, so its
 * wording lives in Site settings rather than on a page of its own.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the search result fields on pages and products.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_search_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'        => 'group_pa_search',
			'title'      => 'Search result',
			'menu_order' => 90,
			'position'   => 'normal',
			'fields'     => array(
				array(
					'key'   => 'field_pa_search_title',
					'label' => 'Title in search results',
					'name'  => 'search_title',
					'type'  => 'text',
				),
				array(
					'key'       => 'field_pa_search_description',
					'label'     => 'Description in search results',
					'name'      => 'search_description',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'prairie_ash_register_search_fields' );

/**
 * Read one search field of an entry as plain text.
 *
 * @since 1.0.0
 *
 * @param string $name    Field name.
 * @param int    $post_id Post ID.
 * @return string The typed value, or an empty string.
 */
function prairie_ash_search_field( $name, $post_id ) {
	$value = function_exists( 'get_field' ) ? get_field( $name, $post_id, false ) : '';

	return is_string( $value ) ? trim( $value ) : '';
}

/**
 * The title an entry publishes when its own field is empty.
 *
 * Built the way WordPress builds its default document title, which this one
 * replaces for every entry: the site name and tagline on the front page, the
 * entry's title and the site name everywhere else, joined by a hyphen that
 * the texturize pass turns into a dash.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return string Plain text.
 */
function prairie_ash_search_title_fallback( $post_id ) {
	if ( (int) get_option( 'page_on_front' ) === (int) $post_id ) {
		$parts = array(
			'title'   => get_bloginfo( 'name', 'display' ),
			'tagline' => get_bloginfo( 'description', 'display' ),
		);
	} else {
		$parts = array(
			'title' => get_the_title( $post_id ),
			'site'  => get_bloginfo( 'name', 'display' ),
		);
	}

	$title = implode( ' - ', array_filter( $parts ) );

	return html_entity_decode( capital_P_dangit( convert_chars( wptexturize( $title ) ) ), ENT_QUOTES, 'UTF-8' );
}

/**
 * The description an entry publishes when its own field is empty.
 *
 * A coffee falls back to its short description, everything else to the site
 * tagline.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return string Plain text, possibly empty.
 */
function prairie_ash_search_description_fallback( $post_id ) {
	if ( 'product' === get_post_type( $post_id ) ) {
		$excerpt = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( get_post_field( 'post_excerpt', $post_id ) ) ) );
		if ( '' !== $excerpt ) {
			return $excerpt;
		}
	}

	return get_bloginfo( 'description', 'display' );
}

/**
 * Where an entry's fallback description comes from, in one line.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return string
 */
function prairie_ash_search_description_source( $post_id ) {
	if ( 'product' === get_post_type( $post_id ) && '' !== trim( (string) get_post_field( 'post_excerpt', $post_id ) ) ) {
		return 'Left empty, this reads the coffee\'s short description.';
	}

	if ( '' === get_bloginfo( 'description' ) ) {
		return 'Left empty, no description is published, because the site tagline under Settings, General is empty too.';
	}

	return 'Left empty, this reads the site tagline under Settings, General.';
}

/**
 * The title an entry publishes.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return string Plain text.
 */
function prairie_ash_search_title( $post_id ) {
	$typed = prairie_ash_search_field( 'search_title', $post_id );

	return '' !== $typed ? $typed : prairie_ash_search_title_fallback( $post_id );
}

/**
 * The description an entry publishes.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return string Plain text, possibly empty.
 */
function prairie_ash_search_description( $post_id ) {
	$typed = prairie_ash_search_field( 'search_description', $post_id );

	return '' !== $typed ? $typed : prairie_ash_search_description_fallback( $post_id );
}

/**
 * The entry whose search fields describe the current request.
 *
 * The shop is an archive that WooCommerce draws for the Shop page, so it
 * reads that page's fields.
 *
 * @since 1.0.0
 *
 * @return int Post ID, or 0 when the request is not one entry.
 */
function prairie_ash_search_entry() {
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return (int) wc_get_page_id( 'shop' );
	}

	if ( is_front_page() && 'page' === get_option( 'show_on_front' ) ) {
		return (int) get_option( 'page_on_front' );
	}

	if ( is_singular() ) {
		return (int) get_queried_object_id();
	}

	return 0;
}

/**
 * Whether the request is the order confirmation.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function prairie_ash_is_order_confirmation() {
	return function_exists( 'is_order_received_page' ) && is_order_received_page();
}

/**
 * Print the entry's own title in the document title.
 *
 * Returning a value from pre_get_document_title replaces the whole title,
 * and core prints it as returned, so it is escaped here.
 *
 * @since 1.0.0
 *
 * @param string $title The title core would otherwise build.
 * @return string
 */
function prairie_ash_document_title( $title ) {
	if ( prairie_ash_is_order_confirmation() ) {
		return esc_html( prairie_ash_setting( 'order_search_title' ) );
	}

	$entry = prairie_ash_search_entry();

	return $entry ? esc_html( prairie_ash_search_title( $entry ) ) : $title;
}
add_filter( 'pre_get_document_title', 'prairie_ash_document_title' );

/**
 * Print the meta description of the current request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_meta_description() {
	if ( prairie_ash_is_order_confirmation() ) {
		$description = prairie_ash_setting( 'order_search_description' );
	} else {
		$entry       = prairie_ash_search_entry();
		$description = $entry ? prairie_ash_search_description( $entry ) : get_bloginfo( 'description', 'display' );
	}

	if ( '' !== trim( (string) $description ) ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}
}
add_action( 'wp_head', 'prairie_ash_meta_description', 1 );

/**
 * Show the published title as the title field's placeholder, with its source.
 *
 * @since 1.0.0
 *
 * @param array $field The field about to be drawn.
 * @return array
 */
function prairie_ash_prepare_search_title( $field ) {
	$post = get_post();
	if ( ! $post ) {
		return $field;
	}

	$field['placeholder']  = prairie_ash_search_title_fallback( $post->ID );
	$field['instructions'] = (int) get_option( 'page_on_front' ) === (int) $post->ID
		? 'Left empty, this reads the site name followed by the tagline, as shown in grey.'
		: 'Left empty, this reads the title of this entry followed by the site name, as shown in grey.';

	return $field;
}
add_filter( 'acf/prepare_field/key=field_pa_search_title', 'prairie_ash_prepare_search_title' );

/**
 * Show the published description as the description field's placeholder.
 *
 * @since 1.0.0
 *
 * @param array $field The field about to be drawn.
 * @return array
 */
function prairie_ash_prepare_search_description( $field ) {
	$post = get_post();
	if ( ! $post ) {
		return $field;
	}

	$field['placeholder']  = prairie_ash_search_description_fallback( $post->ID );
	$field['instructions'] = prairie_ash_search_description_source( $post->ID );

	return $field;
}
add_filter( 'acf/prepare_field/key=field_pa_search_description', 'prairie_ash_prepare_search_description' );

/**
 * Draw the search result under the description field.
 *
 * Laid out at 600 pixels whatever the width of the panel, inside a frame that
 * scrolls, so a line ends where it would end on a desktop screen rather than
 * where the editor's side column ends. The counts are of the published text.
 * The script beside it redraws both as the fields are typed in.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_render_search_preview() {
	$post = get_post();
	if ( ! $post ) {
		return;
	}

	$title       = prairie_ash_search_title( $post->ID );
	$description = prairie_ash_search_description( $post->ID );
	$link        = 'publish' === get_post_status( $post ) ? get_permalink( $post ) : home_url( '/' );
	?>
	<div class="pa-search-preview" data-search-preview data-title-fallback="<?php echo esc_attr( prairie_ash_search_title_fallback( $post->ID ) ); ?>" data-description-fallback="<?php echo esc_attr( prairie_ash_search_description_fallback( $post->ID ) ); ?>" style="margin-top:16px">
		<p style="margin:0 0 8px"><strong>How the result reads</strong>, drawn 600 pixels wide. A search engine shortens a line that does not fit.</p>
		<div style="overflow-x:auto;border:1px solid #dcdcde;background:#fff;padding:12px">
			<div style="width:600px;font-family:Arial,sans-serif">
				<div style="font-size:12px;line-height:18px;color:#4d5156;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html( $link ); ?></div>
				<div data-preview-title style="font-size:20px;line-height:26px;color:#1a0dab;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html( $title ); ?></div>
				<div data-preview-description style="font-size:14px;line-height:22px;color:#4d5156;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?php echo esc_html( $description ); ?></div>
			</div>
		</div>
		<p data-preview-count style="margin:8px 0 0">
			<?php
			echo esc_html(
				sprintf(
					'Published title: %1$d characters. Published description: %2$d characters.',
					mb_strlen( $title ),
					mb_strlen( $description )
				)
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'acf/render_field/key=field_pa_search_description', 'prairie_ash_render_search_preview' );

/**
 * Load the script that redraws the search result as the fields are typed in.
 *
 * @since 1.0.0
 *
 * @param string $hook_suffix The current admin screen.
 * @return void
 */
function prairie_ash_search_preview_script( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'page', 'product' ), true ) ) {
		return;
	}

	wp_enqueue_script(
		'prairie-ash-search-preview',
		get_theme_file_uri( 'assets/js/search-preview.js' ),
		array(),
		prairie_ash_asset_version( 'assets/js/search-preview.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'admin_enqueue_scripts', 'prairie_ash_search_preview_script' );

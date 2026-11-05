<?php
/**
 * Writes the demo content of a finished site into the theme's demo folder.
 *
 * The importer in class-prairie-ash-demo-importer.php reads what this writes.
 * Run on a copy of the reference site, never on a live one, with
 * wp prairie-ash-demo export. What is written is content and settings only:
 * no user, no order, no form entry, no comment, no revision, no mail setting,
 * no key and no address of the site it ran on.
 *
 * References between records are written as tokens, so the importer can point
 * them at the IDs the records receive on the new site:
 * {{post:ID}} for a page, a product or an attachment, {{form:ID}} for a form,
 * {{term:ID}} for a menu, and {{home}} or {{home_json}} for the site address,
 * plain or with escaped slashes.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Exporter of the demo content.
 *
 * @since 1.2.0
 */
class Prairie_Ash_Demo_Exporter {

	/**
	 * The site address being exported, without a trailing slash.
	 *
	 * @since 1.2.0
	 * @var string
	 */
	private $home = '';

	/**
	 * Post IDs referenced by the exported records, for the attachment check.
	 *
	 * @since 1.2.0
	 * @var int[]
	 */
	private $referenced = array();

	/**
	 * Notes on what was changed on the way out, printed by the command.
	 *
	 * @since 1.2.0
	 * @var string[]
	 */
	public $notes = array();

	/**
	 * Options written as they are, read by the importer in this order.
	 *
	 * The general settings a visitor sees, the shop settings the demo store
	 * runs on, and the payment method the demo takes orders with. Mail
	 * settings, keys and anything WooCommerce tracks about its own install are
	 * left out on purpose.
	 *
	 * @since 1.2.0
	 *
	 * @return string[]
	 */
	public static function plain_options() {
		return array(
			'blogname',
			'blogdescription',
			'timezone_string',
			'gmt_offset',
			'date_format',
			'time_format',
			'start_of_week',
			'permalink_structure',
			'wp_attachment_pages_enabled',
			'woocommerce_store_address',
			'woocommerce_store_address_2',
			'woocommerce_store_city',
			'woocommerce_default_country',
			'woocommerce_store_postcode',
			'woocommerce_currency',
			'woocommerce_currency_pos',
			'woocommerce_price_decimal_sep',
			'woocommerce_price_thousand_sep',
			'woocommerce_price_num_decimals',
			'woocommerce_allowed_countries',
			'woocommerce_specific_allowed_countries',
			'woocommerce_ship_to_countries',
			'woocommerce_specific_ship_to_countries',
			'woocommerce_ship_to_destination',
			'woocommerce_default_customer_address',
			'woocommerce_calc_taxes',
			'woocommerce_enable_coupons',
			'woocommerce_enable_guest_checkout',
			'woocommerce_enable_checkout_login_reminder',
			'woocommerce_enable_signup_and_login_from_checkout',
			'woocommerce_enable_myaccount_registration',
			'woocommerce_cart_redirect_after_add',
			'woocommerce_enable_ajax_add_to_cart',
			'woocommerce_manage_stock',
			'woocommerce_hide_out_of_stock_items',
			'woocommerce_enable_reviews',
			'woocommerce_coming_soon',
			'woocommerce_store_pages_only',
			'woocommerce_weight_unit',
			'woocommerce_dimension_unit',
			'woocommerce_checkout_phone_field',
			'woocommerce_checkout_company_field',
			'woocommerce_checkout_address_2_field',
			'woocommerce_task_list_hidden',
			'woocommerce_onboarding_profile',
			'woocommerce_default_catalog_orderby',
			'woocommerce_feature_order_attribution_enabled',
			'woocommerce_cod_settings',
			'woocommerce_bacs_settings',
			'woocommerce_cheque_settings',
			'woocommerce_paypal_settings',
		);
	}

	/**
	 * Options that hold the ID of a page, written as a token.
	 *
	 * @since 1.2.0
	 *
	 * @return string[]
	 */
	public static function page_options() {
		return array(
			'show_on_front',
			'page_on_front',
			'page_for_posts',
			'wp_page_for_privacy_policy',
			'woocommerce_shop_page_id',
			'woocommerce_cart_page_id',
			'woocommerce_checkout_page_id',
			'woocommerce_myaccount_page_id',
			'woocommerce_terms_page_id',
		);
	}

	/**
	 * Write the demo folder.
	 *
	 * @since 1.2.0
	 *
	 * @param string $dir Absolute path of the demo folder.
	 * @return array Counts of what was written.
	 */
	public function export( $dir ) {
		global $wp_filesystem;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		$this->home = untrailingslashit( home_url() );
		$dir        = untrailingslashit( $dir );

		$data = array(
			'format'   => 1,
			'requires' => prairie_ash_demo_requirements(),
			'options'  => $this->options(),
			'theme'    => $this->theme_options(),
			'media'    => array(),
			'terms'    => $this->terms(),
			'pages'    => $this->pages(),
			'products' => $this->products(),
			'forms'    => $this->forms(),
			'menus'    => $this->menus(),
			'shipping' => $this->shipping(),
		);

		$data['media'] = $this->media( $dir . '/media' );
		$data          = $this->tokenize_home( $data );
		$data          = $this->scrub_addresses( $data );

		$xml = $this->forms_xml( wp_list_pluck( $data['forms'], 'id' ) );
		$xml = $this->scrub_addresses( $this->tokenize_home( $xml ) );

		$json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		foreach ( array( $json, $xml ) as $written ) {
			if ( false !== strpos( $written, wp_parse_url( $this->home, PHP_URL_HOST ) . ( wp_parse_url( $this->home, PHP_URL_PORT ) ? ':' . wp_parse_url( $this->home, PHP_URL_PORT ) : '' ) ) ) {
				$this->notes[] = 'The site address is still present after tokenizing. Check the output.';
			}
		}

		$wp_filesystem->put_contents( $dir . '/content.json', $json . "\n" );
		$wp_filesystem->put_contents( $dir . '/forms.xml', $xml );

		foreach ( array_unique( $this->referenced ) as $id ) {
			if ( 'attachment' === get_post_type( $id ) && ! in_array( $id, wp_list_pluck( $data['media'], 'id' ), true ) ) {
				$this->notes[] = sprintf( 'Attachment %d is referenced and was not exported.', $id );
			}
		}

		return array(
			'media'    => count( $data['media'] ),
			'pages'    => count( $data['pages'] ),
			'products' => count( $data['products'] ),
			'forms'    => count( $data['forms'] ),
			'menus'    => count( $data['menus'] ),
		);
	}

	/**
	 * The plain options and the page options.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	private function options() {
		$out = array();

		foreach ( self::plain_options() as $name ) {
			$value = get_option( $name, null );

			// A payment method the demo does not use carries only its switch: the
			// rest of its settings hold account addresses and keys.
			if ( is_array( $value ) && str_ends_with( $name, '_settings' ) && 'woocommerce_cod_settings' !== $name ) {
				$value = array( 'enabled' => $value['enabled'] ?? 'no' );
			}

			if ( null !== $value ) {
				$out[ $name ] = $value;
			}
		}

		foreach ( self::page_options() as $name ) {
			$value = get_option( $name, '' );

			if ( 'show_on_front' !== $name && is_numeric( $value ) && (int) $value > 0 ) {
				$this->referenced[] = (int) $value;
				$value              = '{{post:' . (int) $value . '}}';
			}

			$out[ $name ] = (string) $value;
		}

		$frm               = class_exists( 'FrmAppHelper' ) ? FrmAppHelper::get_settings() : null;
		$out['formidable'] = array(
			'load_style' => $frm ? $frm->load_style : 'none',
		);

		return $out;
	}

	/**
	 * The theme's own settings: every field of the Site settings and Shop
	 * wording screens, stored by the field plugin as options.
	 *
	 * @since 1.2.0
	 *
	 * @return array Option name to value.
	 */
	private function theme_options() {
		if ( ! function_exists( 'acf_get_option_meta' ) ) {
			return array();
		}

		$out = array();

		foreach ( acf_get_option_meta( 'options' ) as $name => $values ) {
			$out[ $name ] = maybe_unserialize( is_array( $values ) ? reset( $values ) : $values );
		}

		ksort( $out );

		return $this->tokenize_fields( $out );
	}

	/**
	 * Product attributes, their terms and the product categories.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	private function terms() {
		$out = array(
			'attributes' => array(),
			'terms'      => array(),
		);

		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return $out;
		}

		$taxonomies = array( 'product_cat', 'product_tag' );

		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$out['attributes'][] = array(
				'slug'         => $attribute->attribute_name,
				'name'         => $attribute->attribute_label,
				'type'         => $attribute->attribute_type,
				'order_by'     => $attribute->attribute_orderby,
				'has_archives' => (bool) $attribute->attribute_public,
			);
			$taxonomies[]        = wc_attribute_taxonomy_name( $attribute->attribute_name );
		}

		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'orderby'    => 'term_id',
				)
			);

			if ( is_wp_error( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				$meta = array();
				foreach ( get_term_meta( $term->term_id ) as $key => $values ) {
					if ( str_starts_with( $key, 'product_count_' ) ) {
						continue;
					}

					// A category's picture is an attachment, imported under a new ID.
					$meta[ $key ] = 'thumbnail_id' === $key ? $this->post_token( (int) $values[0] ) : maybe_unserialize( $values[0] );
				}

				$out['terms'][] = array(
					'id'          => $term->term_id,
					'taxonomy'    => $taxonomy,
					'slug'        => $term->slug,
					'name'        => $term->name,
					'description' => $term->description,
					'parent'      => $term->parent,
					'meta'        => $meta,
				);
			}
		}

		return $out;
	}

	/**
	 * Every published page, with its blocks and its fields.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	private function pages() {
		$out   = array();
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		foreach ( $pages as $page ) {
			$out[] = array(
				'id'             => $page->ID,
				'slug'           => $page->post_name,
				'title'          => $page->post_title,
				'status'         => $page->post_status,
				'parent'         => $page->post_parent ? '{{post:' . $page->post_parent . '}}' : '',
				'menu_order'     => $page->menu_order,
				'template'       => (string) get_post_meta( $page->ID, '_wp_page_template', true ),
				'date'           => $page->post_date,
				'date_gmt'       => $page->post_date_gmt,
				'comment_status' => $page->comment_status,
				'ping_status'    => $page->ping_status,
				'excerpt'        => $page->post_excerpt,
				'content'        => $this->tokenize_content( $this->content_media( $page->post_content ), $page->ID ),
				'meta'           => $this->field_meta( $page->ID ),
			);
		}

		return $out;
	}

	/**
	 * Every published product with its variations, through WooCommerce's own
	 * product objects.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	private function products() {
		$out = array();

		if ( ! function_exists( 'wc_get_products' ) ) {
			return $out;
		}

		$products = wc_get_products(
			array(
				'status'  => array( 'publish', 'private' ),
				'limit'   => -1,
				'orderby' => 'ID',
				'order'   => 'ASC',
			)
		);

		foreach ( $products as $product ) {
			$item = $this->product_props( $product );

			$this->content_media( $item['description'] . $item['short_description'] );

			$item['type']       = $product->get_type();
			$item['categories'] = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );
			$item['tags']       = wp_get_post_terms( $product->get_id(), 'product_tag', array( 'fields' => 'slugs' ) );
			$item['attributes'] = array();

			foreach ( $product->get_attributes() as $attribute ) {
				$options = $attribute->get_options();

				if ( $attribute->is_taxonomy() ) {
					$options = array();
					foreach ( $attribute->get_terms() as $term ) {
						$options[] = $term->slug;
					}
				}

				$item['attributes'][] = array(
					'name'      => $attribute->get_name(),
					'taxonomy'  => $attribute->is_taxonomy(),
					'options'   => $options,
					'visible'   => $attribute->get_visible(),
					'variation' => $attribute->get_variation(),
					'position'  => $attribute->get_position(),
				);
			}

			$item['default_attributes'] = $product->get_default_attributes();
			$item['meta']               = $this->field_meta( $product->get_id() );
			$item['variations']         = array();

			if ( $product->is_type( 'variable' ) ) {
				foreach ( $product->get_children() as $child_id ) {
					$variation = wc_get_product( $child_id );

					if ( ! $variation ) {
						continue;
					}

					$child               = $this->product_props( $variation );
					$child['attributes'] = $variation->get_attributes();
					$child['meta']       = $this->field_meta( $child_id );

					$item['variations'][] = $child;
				}
			}

			$out[] = $item;
		}

		return $out;
	}

	/**
	 * The properties of a product or a variation that are content.
	 *
	 * Sales counts, ratings and review counts are left out: they come from
	 * orders and reviews, which are not part of the demo.
	 *
	 * @since 1.2.0
	 *
	 * @param WC_Product $product Product or variation.
	 * @return array
	 */
	private function product_props( $product ) {
		$props = array(
			'id'                 => $product->get_id(),
			'name'               => $product->get_name( 'edit' ),
			'slug'               => $product->get_slug( 'edit' ),
			'status'             => $product->get_status( 'edit' ),
			'featured'           => $product->get_featured( 'edit' ),
			'catalog_visibility' => $product->get_catalog_visibility( 'edit' ),
			'description'        => $product->get_description( 'edit' ),
			'short_description'  => $product->get_short_description( 'edit' ),
			'sku'                => $product->get_sku( 'edit' ),
			'menu_order'         => $product->get_menu_order( 'edit' ),
			'virtual'            => $product->get_virtual( 'edit' ),
			'downloadable'       => $product->get_downloadable( 'edit' ),
			'tax_status'         => $product->get_tax_status( 'edit' ),
			'tax_class'          => $product->get_tax_class( 'edit' ),
			'manage_stock'       => $product->get_manage_stock( 'edit' ),
			'stock_quantity'     => $product->get_stock_quantity( 'edit' ),
			'stock_status'       => $product->get_stock_status( 'edit' ),
			'backorders'         => $product->get_backorders( 'edit' ),
			'low_stock_amount'   => $product->get_low_stock_amount( 'edit' ),
			'sold_individually'  => $product->get_sold_individually( 'edit' ),
			'weight'             => $product->get_weight( 'edit' ),
			'length'             => $product->get_length( 'edit' ),
			'width'              => $product->get_width( 'edit' ),
			'height'             => $product->get_height( 'edit' ),
			'reviews_allowed'    => $product->get_reviews_allowed( 'edit' ),
			'purchase_note'      => $product->get_purchase_note( 'edit' ),
			'regular_price'      => $product->get_regular_price( 'edit' ),
			'sale_price'         => $product->get_sale_price( 'edit' ),
			'date_created'       => $product->get_date_created( 'edit' ) ? $product->get_date_created( 'edit' )->format( DATE_ATOM ) : '',
			'image_id'           => $this->post_token( $product->get_image_id( 'edit' ) ),
			'gallery_image_ids'  => array_map( array( $this, 'post_token' ), $product->get_gallery_image_ids( 'edit' ) ),
		);

		return $props;
	}

	/**
	 * The field values stored on a post: every key the field plugin wrote,
	 * with the reference key beside it, and the values of image, file, gallery
	 * and post fields as tokens.
	 *
	 * @since 1.2.0
	 *
	 * @param int $post_id Post ID.
	 * @return array Meta key to value.
	 */
	private function field_meta( $post_id ) {
		$all  = get_post_meta( $post_id );
		$meta = array();

		foreach ( $all as $key => $values ) {
			if ( str_starts_with( $key, '_' ) ) {
				continue;
			}

			$reference = isset( $all[ '_' . $key ][0] ) ? $all[ '_' . $key ][0] : '';

			if ( ! str_starts_with( $reference, 'field_' ) ) {
				continue;
			}

			$meta[ $key ]       = maybe_unserialize( $values[0] );
			$meta[ '_' . $key ] = $reference;
		}

		ksort( $meta );

		return $this->tokenize_fields( $meta );
	}

	/**
	 * Replace the reference values in a flat set of field values with tokens.
	 *
	 * The set is the shape the field plugin stores, in a block's data, in post
	 * meta and in options alike: each value under its name, and under the
	 * name with an underscore in front, the key of the field that wrote it.
	 *
	 * @since 1.2.0
	 *
	 * @param array $values Flat values.
	 * @return array
	 */
	private function tokenize_fields( $values ) {
		foreach ( $values as $name => $value ) {
			if ( str_starts_with( $name, '_' ) || ! isset( $values[ '_' . $name ] ) || ! is_string( $values[ '_' . $name ] ) || ! function_exists( 'acf_get_field' ) ) {
				continue;
			}

			$field = acf_get_field( $values[ '_' . $name ] );

			if ( ! $field ) {
				continue;
			}

			if ( in_array( $field['type'], array( 'image', 'file', 'gallery', 'post_object', 'relationship', 'page_link' ), true ) ) {
				$values[ $name ] = $this->tokenize_ids( $value, 'post' );
			} elseif ( 'select' === $field['type'] && 'form' === $field['name'] ) {
				$values[ $name ] = $this->tokenize_ids( $value, 'form' );
			}
		}

		return $values;
	}

	/**
	 * Tokens for one ID or a list of them. Anything that is not a positive
	 * number, such as an address a page link field stores, is left as it is.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed  $value ID, list of IDs, or anything else.
	 * @param string $kind  'post' or 'form'.
	 * @return mixed
	 */
	private function tokenize_ids( $value, $kind ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $one ) {
				$value[ $index ] = $this->tokenize_ids( $one, $kind );
			}

			return $value;
		}

		if ( is_numeric( $value ) && (int) $value > 0 ) {
			if ( 'post' === $kind ) {
				$this->referenced[] = (int) $value;
			}

			return '{{' . $kind . ':' . (int) $value . '}}';
		}

		return $value;
	}

	/**
	 * A post token for one ID, or an empty string for none.
	 *
	 * @since 1.2.0
	 *
	 * @param int $id Post ID.
	 * @return string
	 */
	public function post_token( $id ) {
		return $id ? $this->tokenize_ids( (int) $id, 'post' ) : '';
	}

	/**
	 * Note the attachments a piece of content shows without a field: core
	 * image, cover, media and gallery blocks, the wp-image class core prints,
	 * and addresses of files in the uploads folder.
	 *
	 * Those references stay as they are in the content, so the importer cannot
	 * point them at new IDs, but the files are exported, so the page shows
	 * them on a new site once the address is the same.
	 *
	 * @since 1.2.1
	 *
	 * @param string $content Post content.
	 * @return string The content, unchanged.
	 */
	private function content_media( $content ) {
		$walk = function ( $blocks ) use ( &$walk ) {
			foreach ( $blocks as $block ) {
				foreach ( array( 'id', 'mediaId' ) as $attr ) {
					if ( ! empty( $block['attrs'][ $attr ] ) && is_numeric( $block['attrs'][ $attr ] ) && str_starts_with( (string) $block['blockName'], 'core/' ) ) {
						$this->referenced[] = (int) $block['attrs'][ $attr ];
					}
				}

				if ( 'core/gallery' === $block['blockName'] && ! empty( $block['attrs']['ids'] ) ) {
					$this->referenced = array_merge( $this->referenced, array_map( 'intval', (array) $block['attrs']['ids'] ) );
				}

				$walk( $block['innerBlocks'] );
			}
		};

		$walk( parse_blocks( (string) $content ) );

		if ( preg_match_all( '/\bwp-image-(\d+)\b/', (string) $content, $found ) ) {
			$this->referenced = array_merge( $this->referenced, array_map( 'intval', $found[1] ) );
		}

		$uploads = wp_get_upload_dir()['baseurl'];

		if ( preg_match_all( '#' . preg_quote( $uploads, '#' ) . '/[^"\'\s<>()]+#', (string) $content, $found ) ) {
			foreach ( array_unique( $found[0] ) as $url ) {
				$id = attachment_url_to_postid( (string) preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $url ) );

				if ( $id ) {
					$this->referenced[] = $id;
				}
			}
		}

		return $content;
	}

	/**
	 * Post content with the references inside its blocks as tokens.
	 *
	 * The content is parsed and serialised again. Before that is trusted, the
	 * round trip of the untouched content has to give back the stored content
	 * byte for byte, or the page is written as it is and a note is printed.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content Post content.
	 * @param int    $post_id Post ID, for the note.
	 * @return string
	 */
	private function tokenize_content( $content, $post_id ) {
		$blocks = parse_blocks( $content );

		if ( serialize_blocks( $blocks ) !== $content ) {
			$this->notes[] = sprintf( 'Post %d does not survive a parse and serialise round trip; its block references were not tokenized.', $post_id );
			return $content;
		}

		return serialize_blocks( $this->tokenize_blocks( $blocks ) );
	}

	/**
	 * Tokenize the field data of every block, inner blocks included.
	 *
	 * @since 1.2.0
	 *
	 * @param array $blocks Parsed blocks.
	 * @return array
	 */
	private function tokenize_blocks( $blocks ) {
		foreach ( $blocks as $index => $block ) {
			if ( ! empty( $block['attrs']['data'] ) && is_array( $block['attrs']['data'] ) ) {
				$blocks[ $index ]['attrs']['data'] = $this->tokenize_fields( $block['attrs']['data'] );
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->tokenize_blocks( $block['innerBlocks'] );
			}
		}

		return $blocks;
	}

	/**
	 * The Formidable forms the pages use, by ID and key.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	private function forms() {
		$out = array();

		if ( ! class_exists( 'FrmForm' ) ) {
			return $out;
		}

		foreach ( (array) FrmForm::get_published_forms() as $form ) {
			$out[] = array(
				'id'  => (int) $form->id,
				'key' => $form->form_key,
			);
		}

		return $out;
	}

	/**
	 * The forms and their actions in Formidable's own export format.
	 *
	 * Entries and styles are not exported: the demo has no entries, and the
	 * theme switches Formidable's styling off.
	 *
	 * @since 1.2.0
	 *
	 * @param int[] $ids Form IDs.
	 * @return string XML.
	 */
	private function forms_xml( $ids ) {
		if ( ! $ids || ! class_exists( 'FrmXMLController' ) ) {
			return '';
		}

		ob_start();
		FrmXMLController::generate_xml( array( 'forms', 'actions' ), array( 'ids' => $ids ) );

		// The generator comment and the publication date change on every
		// export; without them an export of unchanged forms is unchanged.
		return (string) preg_replace(
			array( '/^<!-- generator=.*-->\n/m', '/^\s*<pubDate>.*<\/pubDate>\n/m' ),
			'',
			(string) ob_get_clean()
		);
	}

	/**
	 * The navigation menus, their items and the locations they fill.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	private function menus() {
		$out       = array();
		$locations = get_nav_menu_locations();

		foreach ( wp_get_nav_menus() as $menu ) {
			$items = array();

			foreach ( (array) wp_get_nav_menu_items( $menu->term_id, array( 'post_status' => 'any' ) ) as $item ) {
				$items[] = array(
					'id'          => $item->ID,
					'title'       => $item->post_title,
					'type'        => $item->type,
					'object'      => $item->object,
					'object_id'   => 'post_type' === $item->type ? $this->post_token( $item->object_id ) : (string) $item->object_id,
					'url'         => 'custom' === $item->type ? $item->url : '',
					'parent'      => (int) $item->menu_item_parent,
					'order'       => $item->menu_order,
					'classes'     => implode( ' ', array_filter( (array) $item->classes ) ),
					'target'      => $item->target,
					'xfn'         => $item->xfn,
					'attr_title'  => $item->attr_title,
					'description' => $item->post_content,
				);
			}

			$out[] = array(
				'id'        => $menu->term_id,
				'name'      => $menu->name,
				'slug'      => $menu->slug,
				'locations' => array_keys( $locations, $menu->term_id, true ),
				'items'     => $items,
			);
		}

		return $out;
	}

	/**
	 * Shipping zones and their methods, the rest of the world included.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	private function shipping() {
		$out = array();

		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return $out;
		}

		$zones   = WC_Shipping_Zones::get_zones( 'admin' );
		$zones[] = array( 'zone_id' => 0 );

		foreach ( $zones as $row ) {
			$zone      = new WC_Shipping_Zone( (int) $row['zone_id'] );
			$locations = array();
			$methods   = array();

			foreach ( $zone->get_zone_locations( 'edit' ) as $location ) {
				$locations[] = array(
					'code' => $location->code,
					'type' => $location->type,
				);
			}

			foreach ( $zone->get_shipping_methods( false, 'admin' ) as $method ) {
				$methods[] = array(
					'type'     => $method->id,
					'enabled'  => 'yes' === $method->enabled,
					'order'    => (int) $method->method_order,
					'settings' => (array) get_option( $method->get_instance_option_key(), array() ),
				);
			}

			$out[] = array(
				'name'      => 0 === (int) $row['zone_id'] ? '' : $zone->get_zone_name( 'edit' ),
				'order'     => 0 === (int) $row['zone_id'] ? 0 : $zone->get_zone_order( 'edit' ),
				'locations' => $locations,
				'methods'   => $methods,
			);
		}

		return $out;
	}

	/**
	 * Every attachment the records reference, copied into the demo folder as
	 * the file that was uploaded, before WordPress scaled or resized it.
	 *
	 * The importer uploads each file again, so WordPress writes the sizes and
	 * the theme writes the AVIF and WebP copies on the new site the same way
	 * it did here. The folder is emptied first, so it holds exactly the
	 * exported files. An attachment no exported record points at stays out of
	 * the folder, which is published with the theme, and a note names it.
	 *
	 * @since 1.2.0
	 * @since 1.2.1 Exports only the attachments the records reference.
	 *
	 * @param string $dir Absolute path of the media folder.
	 * @return array
	 */
	private function media( $dir ) {
		global $wp_filesystem;

		$out = array();
		$all = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
			)
		);
		$ids = array_values( array_intersect( array_map( 'intval', $all ), array_map( 'intval', $this->referenced ) ) );

		$placeholder = (int) get_option( 'woocommerce_placeholder_image', 0 );

		foreach ( array_diff( array_map( 'intval', $all ), $ids, array( $placeholder ) ) as $id ) {
			$this->notes[] = sprintf( 'Attachment %1$d (%2$s) is not used by any exported record and was left out.', $id, wp_basename( (string) get_attached_file( $id ) ) );
		}

		if ( $wp_filesystem->is_dir( $dir ) ) {
			$wp_filesystem->delete( $dir, true );
		}

		foreach ( $ids as $id ) {
			if ( $id === $placeholder ) {
				continue;
			}

			$attached = (string) get_post_meta( $id, '_wp_attached_file', true );
			$original = wp_get_original_image_path( $id );
			$original = $original ? $original : get_attached_file( $id );

			if ( ! $original || ! file_exists( $original ) ) {
				$this->notes[] = sprintf( 'Attachment %d has no file and was skipped.', $id );
				continue;
			}

			$folder = trim( dirname( $attached ), '/.' );
			$file   = ( '' !== $folder ? $folder . '/' : '' ) . wp_basename( $original );

			wp_mkdir_p( dirname( $dir . '/' . $file ) );
			$wp_filesystem->copy( $original, $dir . '/' . $file, true );

			$post  = get_post( $id );
			$out[] = array(
				'id'          => $id,
				'file'        => $file,
				'attached'    => $attached,
				'mime'        => $post->post_mime_type,
				'title'       => $post->post_title,
				'slug'        => $post->post_name,
				'caption'     => $post->post_excerpt,
				'description' => $post->post_content,
				'alt'         => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
				'date'        => $post->post_date,
				'date_gmt'    => $post->post_date_gmt,
			);
		}

		return $out;
	}

	/**
	 * Replace the site address in every string with a token.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	private function tokenize_home( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $one ) {
				$value[ $key ] = $this->tokenize_home( $one );
			}

			return $value;
		}

		if ( ! is_string( $value ) ) {
			return $value;
		}

		return str_replace(
			array( $this->home, str_replace( '/', '\\/', $this->home ), str_replace( '/', '\\\\/', $this->home ) ),
			array( '{{home}}', '{{home_json}}', '{{home_json2}}' ),
			$value
		);
	}

	/**
	 * Replace every email address outside example.com with the same name at
	 * example.com, so the data carries no address anybody could receive mail
	 * at.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	private function scrub_addresses( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $one ) {
				$value[ $key ] = $this->scrub_addresses( $one );
			}

			return $value;
		}

		if ( ! is_string( $value ) ) {
			return $value;
		}

		return (string) preg_replace_callback(
			'/([A-Za-z0-9._%+-]+)@([A-Za-z0-9.-]+\.[A-Za-z]{2,})/',
			function ( $found ) {
				if ( 'example.com' === strtolower( $found[2] ) ) {
					return $found[0];
				}

				$this->notes[] = sprintf( 'An address at %s was written as %s@example.com.', $found[2], $found[1] );

				return $found[1] . '@example.com';
			},
			$value
		);
	}
}

<?php
/**
 * Shop wording: every text a visitor reads on the shop, product, cart,
 * checkout and order pages that is not a product's own.
 *
 * One options page, under WooCommerce, with the design's wording as the
 * placeholder of every field. An empty field prints that wording.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read one wording field, falling back to the design's wording when empty.
 *
 * @since 1.0.0
 *
 * @param string $name Field name from the registry in inc/shop-wording-registry.php.
 * @return string
 */
function prairie_ash_wording( $name ) {
	static $defaults = null;

	if ( null === $defaults ) {
		$defaults = array();
		foreach ( prairie_ash_wording_registry() as $group ) {
			foreach ( $group as $key => $entry ) {
				$defaults[ $key ] = $entry[1];
			}
		}
	}

	$value = function_exists( 'get_field' ) ? get_field( 'wording_' . $name, 'option' ) : '';

	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		$value = isset( $defaults[ $name ] ) ? $defaults[ $name ] : '';
	}

	return strtr(
		$value,
		array(
			'[name]'    => (string) prairie_ash_setting( 'business_name' ),
			'[street]'  => (string) prairie_ash_setting( 'street' ),
			'[address]' => prairie_ash_nbsp_postcode( (string) prairie_ash_setting( 'address_line' ) ),
			'[website]' => (string) prairie_ash_setting( 'website_label' ),
		)
	);
}

/**
 * Read a wording field and put a value into its placeholder.
 *
 * The client writes [count], [total] or [order] in the field where the value
 * belongs, so the word order stays theirs.
 *
 * @since 1.0.0
 *
 * @param string $name   Field name.
 * @param array  $values Values keyed by placeholder name, without brackets.
 * @return string
 */
function prairie_ash_wording_with( $name, $values ) {
	$text = prairie_ash_wording( $name );

	foreach ( $values as $key => $value ) {
		$text = str_replace( '[' . $key . ']', (string) $value, $text );
	}

	return $text;
}

/**
 * Register the Shop wording options page under WooCommerce.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_wording_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Shop wording',
			'menu_title'  => 'Shop wording',
			'menu_slug'   => 'prairie-ash-shop-wording',
			'parent_slug' => 'woocommerce',
			'capability'  => 'manage_woocommerce',
		)
	);
}
add_action( 'acf/init', 'prairie_ash_register_wording_page' );

/**
 * Register the Shop wording fields from the registry.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_wording_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$fields = array();
	foreach ( prairie_ash_wording_registry() as $tab => $group ) {
		$fields[] = array(
			'key'   => 'field_pa_wording_tab_' . sanitize_key( $tab ),
			'label' => $tab,
			'name'  => '',
			'type'  => 'tab',
		);
		foreach ( $group as $name => $entry ) {
			$type = isset( $entry[2] ) ? $entry[2] : 'text';
			if ( 'image' === $type ) {
				$fields[] = array(
					'key'           => 'field_pa_wording_' . $name,
					'label'         => $entry[0],
					'name'          => 'wording_' . $name,
					'type'          => 'image',
					'return_format' => 'id',
					'instructions'  => isset( $entry[3] ) ? $entry[3] : '',
				);
				continue;
			}
			$long     = 'textarea' === $type;
			$fields[] = array(
				'key'          => 'field_pa_wording_' . $name,
				'label'        => $entry[0],
				'name'         => 'wording_' . $name,
				'type'         => $long ? 'textarea' : 'text',
				'rows'         => $long ? 3 : null,
				'new_lines'    => '',
				'placeholder'  => $entry[1],
				'instructions' => isset( $entry[3] ) ? $entry[3] : '',
			);
		}
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_pa_shop_wording',
			'title'    => 'Shop wording',
			'fields'   => $fields,
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'prairie-ash-shop-wording',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'prairie_ash_register_wording_fields' );

require_once get_theme_file_path( 'inc/shop-wording-registry.php' );

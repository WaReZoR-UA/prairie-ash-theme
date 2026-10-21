<?php
/**
 * Field groups, defined as code.
 *
 * Each section block carries its own field list in blocks/<slug>/fields.php,
 * beside the render template that prints it. This file reads those lists,
 * turns each compact entry into a full field array with a permanent key, and
 * registers one group per block on acf/init.
 *
 * Keys and names are PERMANENT once content exists. A key is built from the
 * block slug and the field name, so renaming either orphans stored values.
 * Labels and instructions change freely, and they are written in the client's
 * language, English.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every block definition, read from blocks/<slug>/fields.php.
 *
 * A definition is an array with 'fields' (compact field entries) and
 * 'elements' (text elements for the per-element panels).
 *
 * @since 1.0.0
 *
 * @return array<string, array> Definitions keyed by registered block name.
 */
function prairie_ash_block_definitions() {
	static $definitions = null;

	if ( null !== $definitions ) {
		return $definitions;
	}

	$definitions = array();
	foreach ( (array) glob( get_theme_file_path( 'blocks/*/fields.php' ) ) as $file ) {
		$slug       = basename( dirname( $file ) );
		$definition = require $file;

		if ( is_array( $definition ) ) {
			$definition['slug']                    = $slug;
			$definitions[ 'prairie-ash/' . $slug ] = $definition;
		}
	}

	return $definitions;
}

/**
 * Expand one compact field entry into a field array.
 *
 * An entry is array( type, name, label, extra ). Extra is merged last, so it
 * can set anything the field type documents. Repeater rows carry their own
 * entries under 'sub_fields' and get keys scoped to the repeater.
 *
 * @since 1.0.0
 *
 * @param string $scope Key scope: the block slug, plus the parent repeater name for rows.
 * @param array  $entry Compact entry.
 * @return array Field definition.
 */
function prairie_ash_expand_field( $scope, $entry ) {
	$type  = $entry[0];
	$name  = $entry[1];
	$label = $entry[2];
	$extra = isset( $entry[3] ) ? (array) $entry[3] : array();

	$field = array(
		'key'   => 'field_pa_' . str_replace( '-', '_', $scope ) . '_' . $name,
		'label' => $label,
		'name'  => $name,
		'type'  => $type,
	);

	if ( in_array( $type, array( 'tab', 'message' ), true ) ) {
		$field['name'] = '';
	}
	if ( 'textarea' === $type ) {
		$field['rows']      = 3;
		$field['new_lines'] = '';
	}
	if ( 'image' === $type ) {
		$field['return_format'] = 'id';
		$field['preview_size']  = 'medium';
	}
	if ( 'link' === $type ) {
		$field['return_format'] = 'array';
	}
	if ( 'repeater' === $type ) {
		$field['layout']       = 'block';
		$field['button_label'] = 'Add a row';
	}

	if ( isset( $extra['default'] ) ) {
		// The design's wording: the placeholder shows it, the template prints it when the field is empty.
		$field['placeholder'] = $extra['default'];
		unset( $extra['default'] );
	}

	if ( ! empty( $extra['sub_fields'] ) ) {
		$rows = array();
		foreach ( $extra['sub_fields'] as $sub ) {
			$rows[] = prairie_ash_expand_field( $scope . '_' . $name, $sub );
		}
		$extra['sub_fields'] = $rows;

		if ( ! empty( $extra['collapsed'] ) ) {
			$extra['collapsed'] = 'field_pa_' . str_replace( '-', '_', $scope . '_' . $name ) . '_' . $extra['collapsed'];
		}
	}

	return array_merge( $field, $extra );
}

/**
 * Register one field group per section block.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prairie_ash_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	foreach ( prairie_ash_block_definitions() as $name => $definition ) {
		if ( empty( $definition['fields'] ) ) {
			continue;
		}

		$fields = array();
		foreach ( $definition['fields'] as $entry ) {
			$fields[] = prairie_ash_expand_field( $definition['slug'], $entry );
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_pa_' . str_replace( '-', '_', $definition['slug'] ),
				'title'    => isset( $definition['title'] ) ? $definition['title'] : $definition['slug'],
				'fields'   => $fields,
				'location' => array(
					array(
						array(
							'param'    => 'block',
							'operator' => '==',
							'value'    => $name,
						),
					),
				),
			)
		);
	}
}
add_action( 'acf/init', 'prairie_ash_register_fields' );

/**
 * Read every field of a section block at once, with wording defaults applied.
 *
 * A field whose entry carries a default is wording the design wrote: empty, it
 * prints that wording, so a block saved before the field existed needs no
 * re-saving. A field with no default is content: empty, it is empty, and the
 * template leaves its element out.
 *
 * @since 1.0.0
 *
 * @param string $slug Block directory name.
 * @return array<string, mixed> Values keyed by field name.
 */
function prairie_ash_fields( $slug ) {
	$definitions = prairie_ash_block_definitions();
	$definition  = isset( $definitions[ 'prairie-ash/' . $slug ] ) ? $definitions[ 'prairie-ash/' . $slug ] : array();
	$values      = array();

	foreach ( isset( $definition['fields'] ) ? $definition['fields'] : array() as $entry ) {
		if ( in_array( $entry[0], array( 'tab', 'message' ), true ) ) {
			continue;
		}
		$values[ $entry[1] ] = prairie_ash_resolve_value( $entry, function_exists( 'get_field' ) ? get_field( $entry[1] ) : null );
	}

	return $values;
}

/**
 * Apply a field entry's default to a value, rows included.
 *
 * @since 1.0.0
 *
 * @param array $entry Compact field entry.
 * @param mixed $value Stored value.
 * @return mixed
 */
function prairie_ash_resolve_value( $entry, $value ) {
	$extra = isset( $entry[3] ) ? (array) $entry[3] : array();

	if ( 'repeater' === $entry[0] ) {
		$rows = array();
		foreach ( is_array( $value ) ? $value : array() as $row ) {
			foreach ( isset( $extra['sub_fields'] ) ? $extra['sub_fields'] : array() as $sub ) {
				$row[ $sub[1] ] = prairie_ash_resolve_value( $sub, isset( $row[ $sub[1] ] ) ? $row[ $sub[1] ] : null );
			}
			$rows[] = $row;
		}

		return $rows;
	}

	if ( isset( $extra['default'] ) && ( null === $value || ( is_string( $value ) && '' === trim( $value ) ) ) ) {
		return $extra['default'];
	}

	return $value;
}

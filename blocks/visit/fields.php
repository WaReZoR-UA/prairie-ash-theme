<?php
/**
 * Fields of the home getting-here band: the text, the drawn street map that
 * links out to the map service, and the storefront photo.
 *
 * The map link itself is the one set under Site settings, so every map on the
 * site leads to the same place.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Getting here, home',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array( 'text', 'map_link_label', 'Map link text', array( 'instructions' => 'Leads to the map link set under Site settings.' ) ),
		array( 'text', 'map_label', 'Map, read by screen readers', array( 'instructions' => 'What the map shows, in one sentence. Empty, screen readers read the heading.' ) ),
		array( 'text', 'map_shop', 'Map, label at the cafe' ),
		array( 'text', 'map_bus', 'Map, label at the bus stop' ),
		array( 'text', 'map_bus_letter', 'Map, bus stop sign', array( 'default' => 'B' ) ),
		array( 'text', 'map_avenue', 'Map, name of the cafe street' ),
		array( 'text', 'map_main', 'Map, name of the main street' ),
		array( 'image', 'image', 'Storefront photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);

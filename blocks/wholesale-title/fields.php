<?php
/**
 * Fields of the wholesale page title sign.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Wholesale page title',
	'elements' => array(
		'eyebrow' => 'Small title',
		'heading' => 'Heading',
		'intro'   => 'Text',
	),
	'fields'   => array(
		array( 'text', 'eyebrow', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'intro', 'Text' ),
		array( 'link', 'link', 'Link' ),
		array( 'image', 'image', 'Photo', array( 'instructions' => 'The first picture on the page.' ) ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);

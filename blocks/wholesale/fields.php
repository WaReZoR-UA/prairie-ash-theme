<?php
/**
 * Fields of the home wholesale teaser.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Wholesale teaser',
	'elements' => array(
		'heading' => 'Heading',
		'label'   => 'Small title',
		'body'    => 'Text',
		'terms'   => 'Terms',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'text', 'label', 'Small title' ),
		array( 'textarea', 'body', 'Text' ),
		array( 'textarea', 'terms', 'Terms' ),
		array( 'link', 'link', 'Link to the inquiry form' ),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);

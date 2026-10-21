<?php
/**
 * Fields of the head roaster band.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Who roasts',
	'elements' => array(
		'label'   => 'Small title',
		'heading' => 'Heading',
		'body'    => 'Text',
	),
	'fields'   => array(
		array( 'text', 'label', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array( 'link', 'link', 'Link' ),
		array( 'image', 'image', 'Photo' ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);

<?php
/**
 * Fields of a sent page: the notice after a message or an inquiry is sent.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Sent notice',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text' ),
		array( 'link', 'link', 'Link' ),
		array( 'image', 'image', 'Photo', array( 'instructions' => 'The first picture on the page.' ) ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);

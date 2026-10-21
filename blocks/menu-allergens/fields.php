<?php
/**
 * Fields of the allergens band.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'Allergens and milk alternatives',
	'elements' => array(
		'heading' => 'Heading',
		'body'    => 'Text',
	),
	'fields'   => array(
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text', array( 'instructions' => 'Leave a blank line between paragraphs.' ) ),
	),
);

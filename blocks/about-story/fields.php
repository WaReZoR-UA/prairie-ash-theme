<?php
/**
 * Fields of the about page story sign.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'    => 'About, the story',
	'elements' => array(
		'eyebrow' => 'Small title',
		'heading' => 'Heading',
		'body'    => 'Text',
	),
	'fields'   => array(
		array( 'text', 'eyebrow', 'Small title' ),
		array( 'text', 'heading', 'Heading' ),
		array( 'textarea', 'body', 'Text', array( 'instructions' => 'Leave a blank line between paragraphs.' ) ),
		array( 'link', 'link', 'Link' ),
		array( 'image', 'image', 'Photo', array( 'instructions' => 'The first picture on the page.' ) ),
		array( 'true_false', 'image_decorative', 'The photo is decoration only', array( 'ui' => 1 ) ),
	),
);

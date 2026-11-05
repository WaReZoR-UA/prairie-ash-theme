<?php
/**
 * Render template for the prairie-ash/menu-allergens block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'menu-allergens' );
$title_id = prairie_ash_heading_id( 'menu-allergens-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'menu-allergens', 'band', 'band--paper' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="menu-allergens__container">
		<?php if ( $f['heading'] ) : ?>
			<h2 class="menu-allergens__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
		<?php endif; ?>
		<?php foreach ( prairie_ash_paragraphs( $f['body'] ) as $paragraph ) : ?>
			<p class="menu-allergens__body" data-motion="menu-note"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $paragraph ); ?></p>
		<?php endforeach; ?>
	</div>
</section>

<?php
/**
 * Render template for the prairie-ash/menu-allergens block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'menu-allergens' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'menu-allergens', 'band', 'band--paper' ) ); ?> aria-labelledby="menu-allergens-title">
	<div class="menu-allergens__container">
		<?php if ( $f['heading'] ) : ?>
			<h2 class="menu-allergens__heading" id="menu-allergens-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
		<?php endif; ?>
		<?php foreach ( prairie_ash_paragraphs( $f['body'] ) as $paragraph ) : ?>
			<p class="menu-allergens__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $paragraph ); ?></p>
		<?php endforeach; ?>
	</div>
</section>

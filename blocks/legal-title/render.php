<?php
/**
 * Render template for the prairie-ash/legal-title block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'legal-title' );
$scene    = prairie_ash_motion_scene( 'prairie-ash/legal-title', (string) $f['scene'] );
$raw      = function_exists( 'get_field' ) ? (string) get_field( 'date', false, false ) : '';
$when     = preg_match( '/^\d{8}$/', $raw ) ? DateTimeImmutable::createFromFormat( '!Ymd', $raw, wp_timezone() ) : false;
$title_id = prairie_ash_heading_id( 'legal-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'legal-title', 'band', 'band--wall' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="legal-title__container" data-motion="<?php echo esc_attr( $scene . '-sign' ); ?>">
		<?php if ( $when ) : ?>
			<p class="eyebrow"<?php prairie_ash_el( $block, 'eyebrow' ); ?>><?php echo esc_html( $f['date_label'] ); ?> <time datetime="<?php echo esc_attr( $when->format( 'Y-m-d' ) ); ?>"><?php echo esc_html( wp_date( 'F j, Y', $when->getTimestamp() ) ); ?></time></p>
		<?php endif; ?>
		<?php if ( $f['heading'] ) : ?>
			<h1 class="legal-title__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h1>
		<?php endif; ?>
		<?php if ( $f['intro'] ) : ?>
			<p class="legal-title__intro"<?php prairie_ash_el( $block, 'intro' ); ?>><?php prairie_ash_contact_text( $f['intro'] ); ?></p>
		<?php endif; ?>
	</div>
</section>

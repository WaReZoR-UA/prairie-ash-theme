<?php
/**
 * Render template for the prairie-ash/shipping-part block.
 *
 * Two variants, both drawn by the design. The band colour is a block style
 * (deep, wall, paper) mapped to the design's own band modifier, with the link
 * colour that belongs to that band. The arrangement is a field: with a photo,
 * the text moves into the main column above the facts, as the pickup band is
 * drawn.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f          = prairie_ash_fields( 'shipping-part' );
$band       = prairie_ash_block_style( $block, array( 'deep', 'wall', 'paper' ) );
$photo      = 'photo-beside' === $f['arrangement'] && $f['image'];
$link_class = 'paper' === $band ? 'text-link text-link--on-paper' : 'text-link text-link--on-wall';
$anchor     = sanitize_title( (string) $f['heading'] );
$facts      = $f['facts'];

if ( $f['show_hours'] ) {
	$hours = array();
	foreach ( prairie_ash_hours() as $row ) {
		$hours[] = array(
			'label' => $row['label'],
			'value' => $row['time'],
		);
	}
	array_splice( $facts, min( 1, count( $facts ) ), 0, $hours );
}
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'shipping-part', 'band', 'band--' . $band ) ); ?> aria-labelledby="<?php echo esc_attr( $anchor . '-heading' ); ?>">
	<div class="shipping-part__container">
		<?php if ( $photo ) : ?>
			<div class="shipping-part__media media-ibg">
				<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'shipping-part__media' ), $f['heading'] ); ?>
			</div>
		<?php else : ?>
			<div class="shipping-part__intro">
				<?php if ( $f['heading'] ) : ?>
					<h2 class="shipping-part__heading" id="<?php echo esc_attr( $anchor . '-heading' ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $f['body'] ) : ?>
					<p class="shipping-part__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="shipping-part__main">
			<?php if ( $photo ) : ?>
				<div class="shipping-part__lead">
					<?php if ( $f['heading'] ) : ?>
						<h2 class="shipping-part__heading" id="<?php echo esc_attr( $anchor . '-heading' ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $f['body'] ) : ?>
						<p class="shipping-part__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $facts ) : ?>
				<dl class="lv lv--fact lv--roomy lv--stack">
					<?php foreach ( $facts as $fact ) : ?>
						<div class="lv__row"><dt class="lv__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $fact['label'] ); ?></dt><dd class="lv__value"<?php prairie_ash_el( $block, 'values' ); ?>><?php prairie_ash_contact_text( $fact['value'] ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
			<?php prairie_ash_link_field( $f['link'], $link_class ); ?>
		</div>
	</div>
</section>

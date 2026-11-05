<?php
/**
 * Render template for the prairie-ash/coffees block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f       = prairie_ash_fields( 'coffees' );
$feature = ( $f['feature'] && function_exists( 'wc_get_product' ) ) ? wc_get_product( $f['feature'] ) : null;
// A drafted, private or trashed coffee leaves the sign, exactly as it leaves the board below.
if ( $feature && 'publish' !== $feature->get_status() ) {
	$feature = null;
}
$coffees = array();
foreach ( (array) $f['coffees'] as $coffee_id ) {
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $coffee_id ) : null;
	if ( $product && 'publish' === $product->get_status() ) {
		$coffees[] = prairie_ash_coffee( $product );
	}
}
$rows     = array_chunk( $coffees, 2 );
$title_id = prairie_ash_heading_id( 'coffees-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'coffees' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="coffees__container">
		<div class="coffees__head">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="coffees__title" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['intro'] || ! empty( $f['link']['url'] ) ) : ?>
				<div class="coffees__intro">
					<?php if ( $f['intro'] ) : ?>
						<p class="coffees__intro-text"<?php prairie_ash_el( $block, 'intro' ); ?>><?php echo esc_html( $f['intro'] ); ?></p>
					<?php endif; ?>
					<?php prairie_ash_link_field( $f['link'], 'text-link text-link--on-wall' ); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="coffees__board">
			<?php if ( $f['image'] || $feature ) : ?>
				<div class="coffees__start">
					<?php if ( $f['image'] ) : ?>
						<div class="coffees__media media-ibg">
							<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'coffees__media' ), $f['heading'], $f['image_decorative'] ); ?>
						</div>
					<?php endif; ?>
					<?php
					if ( $feature ) {
						get_template_part(
							'parts/coffee-sign',
							null,
							array(
								'coffee' => prairie_ash_coffee( $feature ),
								'block'  => $block,
							)
						);
					}
					?>
				</div>
			<?php endif; ?>
			<?php if ( $rows ) : ?>
				<div class="coffees__prices">
					<?php foreach ( $rows as $row ) : ?>
						<div class="coffees__row">
							<?php
							foreach ( $row as $coffee ) {
								get_template_part(
									'parts/coffee-sign',
									null,
									array(
										'coffee' => $coffee,
										'block'  => $block,
									)
								);
							}
							?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php prairie_ash_editor_hint( $is_preview && ! $rows, __( 'Choose the coffees for the board.', 'prairie-ash' ) ); ?>
		</div>
		<?php if ( $f['note'] ) : ?>
			<p class="coffees__note"<?php prairie_ash_el( $block, 'note' ); ?>><?php echo esc_html( $f['note'] ); ?></p>
		<?php endif; ?>
	</div>
</section>

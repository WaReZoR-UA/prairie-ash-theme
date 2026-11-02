<?php
/**
 * Render template for the prairie-ash/visit-route block.
 *
 * The map is a static picture that links out to the map service: no embed and
 * no third-party request on load. The motion layer traces the walk from the
 * bus stop over the picture, with coordinates that belong to the two drawings
 * of the design, so both map pictures are expected to be set.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f       = prairie_ash_fields( 'visit-route' );
$map_url = prairie_ash_setting( 'map_url' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'visit-route', 'band', 'band--paper' ), array( 'id' => 'getting-here' ) ); ?> aria-labelledby="visit-route-title">
	<div class="visit-route__container">
		<div class="visit-route__text">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="visit-route__heading" id="visit-route-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="visit-route__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['ways'] ) : ?>
				<dl class="visit-route__table lv lv--feature lv--stack" data-motion="visit-facts">
					<?php foreach ( $f['ways'] as $way ) : ?>
						<div class="lv__row"><dt class="lv__label"<?php prairie_ash_el( $block, 'labels' ); ?>><?php echo esc_html( $way['label'] ); ?></dt><dd class="lv__value"<?php prairie_ash_el( $block, 'values' ); ?>><?php echo esc_html( $way['value'] ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
			<?php prairie_ash_link( $f['map_link_label'], $map_url, 'text-link text-link--on-paper' ); ?>
		</div>
		<?php if ( $map_url && $f['map_image'] ) : ?>
			<a class="visit-route__map" href="<?php echo esc_url( $map_url ); ?>">
				<?php
				prairie_ash_image(
					$f['map_image'],
					prairie_ash_slot_sizes( 'visit-route__map' ),
					$f['heading'],
					false,
					array(),
					$f['map_image_wide'] ? array(
						array(
							'media' => '(min-width: 48em)',
							'image' => $f['map_image_wide'],
							'sizes' => prairie_ash_slot_sizes( 'visit-route__map-wide' ),
						),
					) : array()
				);
				?>
			</a>
		<?php endif; ?>
	</div>
</section>

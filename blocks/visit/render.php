<?php
/**
 * Render template for the prairie-ash/visit block.
 *
 * The map is the design's drawn street grid, a static facade that links out
 * to the map service: no embed, no third-party request on load.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'visit' );
$map_url  = prairie_ash_setting( 'map_url' );
$title_id = prairie_ash_heading_id( 'visit-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'visit' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="visit__container">
		<div class="visit__text">
			<?php if ( $f['heading'] ) : ?>
				<h2 class="visit__title" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="visit__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
			<?php prairie_ash_link( $f['map_link_label'], $map_url, 'text-link text-link--on-paper' ); ?>
			<?php if ( $map_url ) : ?>
				<a class="visit__map map" data-motion="map" href="<?php echo esc_url( $map_url ); ?>">
					<span class="map__art" role="img" aria-label="<?php echo esc_attr( $f['map_label'] ? $f['map_label'] : $f['heading'] ); ?>">
					<span class="map__street map__street--main"></span>
					<span class="map__street map__street--avenue"></span>
					<span class="map__street map__street--cross-1"></span>
					<span class="map__street map__street--cross-2"></span>
					<span class="map__street map__street--cross-3"></span>
					<span class="map__building"></span>
					<span class="map__marker map__marker--shop"><span class="map__pin"></span><span class="map__label"><?php echo esc_html( $f['map_shop'] ); ?></span></span>
					<span class="map__marker map__marker--bus"><span class="map__bus"><?php echo esc_html( $f['map_bus_letter'] ); ?></span><span class="map__label"><?php echo esc_html( $f['map_bus'] ); ?></span></span>
					<span class="map__name map__name--avenue"><?php echo esc_html( $f['map_avenue'] ); ?></span>
					<span class="map__name map__name--main"><?php echo esc_html( $f['map_main'] ); ?></span>
					</span>
				</a>
			<?php endif; ?>
		</div>
		<?php if ( $f['image'] ) : ?>
			<div class="visit__media media-ibg">
				<?php prairie_ash_image( $f['image'], prairie_ash_slot_sizes( 'visit__media' ), $f['heading'], $f['image_decorative'] ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

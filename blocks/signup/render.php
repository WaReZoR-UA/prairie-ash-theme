<?php
/**
 * Render template for the prairie-ash/signup block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f = prairie_ash_fields( 'signup' );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'signup' ), array( 'id' => 'signup' ) ); ?> aria-labelledby="signup-title">
	<div class="signup__container">
		<div class="signup__intro">
			<div class="signup__intro-top">
				<?php if ( $f['heading'] ) : ?>
					<h2 class="signup__title" id="signup-title"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $f['body'] ) : ?>
					<p class="signup__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $f['sample_label'] || $f['sample_text'] ) : ?>
				<div class="signup__sample">
					<?php if ( $f['sample_label'] ) : ?>
						<p class="signup__sample-label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['sample_label'] ); ?></p>
					<?php endif; ?>
					<?php if ( $f['sample_text'] ) : ?>
						<p class="signup__sample-text"<?php prairie_ash_el( $block, 'sample' ); ?>><?php echo esc_html( $f['sample_text'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php prairie_ash_form( $f['form'], $is_preview ); ?>
	</div>
</section>

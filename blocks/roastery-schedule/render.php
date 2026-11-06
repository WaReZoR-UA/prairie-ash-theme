<?php
/**
 * Render template for the prairie-ash/roastery-schedule block.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

$f        = prairie_ash_fields( 'roastery-schedule' );
$title_id = prairie_ash_heading_id( 'roastery-schedule-title', $f['heading'] );
?>
<section <?php echo prairie_ash_section_attrs( $block, $is_preview, array( 'roastery-schedule', 'band', 'band--paper' ) ); ?><?php prairie_ash_labelledby( $title_id ); ?>>
	<div class="roastery-schedule__container">
		<div class="roastery-schedule__text">
			<?php if ( $f['label'] ) : ?>
				<p class="roastery-schedule__label"<?php prairie_ash_el( $block, 'label' ); ?>><?php echo esc_html( $f['label'] ); ?></p>
			<?php endif; ?>
			<?php if ( $f['heading'] ) : ?>
				<h2 class="roastery-schedule__heading" id="<?php echo esc_attr( $title_id ); ?>"<?php prairie_ash_el( $block, 'heading' ); ?>><?php echo esc_html( $f['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $f['body'] ) : ?>
				<p class="roastery-schedule__body"<?php prairie_ash_el( $block, 'body' ); ?>><?php echo esc_html( $f['body'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $f['days'] ) : ?>
			<table class="roastery-schedule__table"<?php prairie_ash_el( $block, 'table' ); ?>>
				<thead>
					<tr>
						<th class="roastery-schedule__th" scope="col"><?php echo esc_html( $f['col_day'] ); ?></th>
						<th class="roastery-schedule__th" scope="col"><?php echo esc_html( $f['col_time'] ); ?></th>
						<th class="roastery-schedule__th" scope="col"><?php echo esc_html( $f['col_coffees'] ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $f['days'] as $row ) : ?>
						<tr>
							<th class="roastery-schedule__day" scope="row"><?php echo esc_html( $row['day'] ); ?></th>
							<td class="roastery-schedule__time"><?php echo esc_html( $row['time'] ); ?></td>
							<td class="roastery-schedule__coffees"><?php echo esc_html( $row['coffees'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</section>

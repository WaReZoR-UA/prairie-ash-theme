<?php
/**
 * Notice notices, read out to screen readers.
 *
 * The design draws no banner for a confirmation: the change itself is on the
 * page, the cart line gone or the cart count raised. The message is still
 * announced, in a status region that takes no space. WooCommerce 10.4.0 gave
 * its own notice role="status", which this region has carried from the start.
 *
 * Overrides WooCommerce's notices/notice.php.
 *
 * @package PrairieAsh
 * @version 10.4.0
 *
 * @var array $notices Notices, each with notice and data.
 */

defined( 'ABSPATH' ) || exit;

if ( ! $notices ) {
	return;
}
?>
<div class="visually-hidden woocommerce-message" role="status">
	<?php foreach ( $notices as $prairie_ash_notice ) : ?>
		<p><?php echo wc_kses_notice( $prairie_ash_notice['notice'] ); ?></p>
	<?php endforeach; ?>
</div>

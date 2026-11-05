<?php
/**
 * The Shop wording registry: every text a visitor reads in the shop templates.
 *
 * Grouped by the page it appears on. Each entry is array( label, the design's
 * wording, field type, instructions ). The name is permanent once a value is
 * saved, the label can change freely.
 *
 * Wording may name the business with [name], [street], [address] and
 * [website], which print the values from Site settings, so no contact detail is
 * written into the theme.
 *
 * @package PrairieAsh
 */

defined( 'ABSPATH' ) || exit;

/**
 * The wording registry, grouped by page.
 *
 * @since 1.0.0
 *
 * @return array<string, array<string, array>>
 */
function prairie_ash_wording_registry() {
	$values = 'Write [name], [street], [address] or [website] where the values from Site settings belong.';

	return array(
		'Coffee cards'   => array(
			'price_for'               => array( 'Word between price and size', 'for' ),
			'card_button'             => array( 'Button', 'Add to cart' ),
			'card_button_aria_before' => array( 'Button, read by screen readers, before the name', 'Add' ),
			'card_button_aria_after'  => array( 'Button, read by screen readers, after the name', 'to cart' ),
			'card_adding_label'       => array( 'Status while adding, read by screen readers', 'Adding to your cart' ),
			'card_added_label'        => array( 'Status after adding, read by screen readers', 'Added to your cart' ),
			'sold_out'                => array( 'Sold out, when the coffee sets none of its own', 'Sold out' ),
			'quantity'                => array( 'Quantity', 'Quantity' ),
		),
		'Product page'   => array(
			'breadcrumb_label' => array( 'Breadcrumb, read by screen readers', 'Breadcrumb' ),
			'breadcrumb_shop'  => array( 'Breadcrumb link back to the shop', 'Shop coffee' ),
			'stock_label'      => array( 'Stock row label', 'Stock' ),
			'in_stock'         => array( 'In stock', 'In stock' ),
			'add_to_cart'      => array( 'Button', 'Add to cart' ),
			'product_delivery' => array( 'Delivery line under the button', 'Ships within 2 business days to Manitoba, Saskatchewan, Alberta and Ontario. Shipping is $12, free over $75. Pickup at the cafe is free.', 'textarea' ),
			'story_title'      => array( 'Heading of the origin band, when the coffee sets none', 'Where this coffee comes from' ),
			'brewing_title'    => array( 'Heading of the recipe band, when the coffee sets none', 'How Mara brews it' ),
			'roasted'          => array( 'Label of the roast date fact', 'Roasted', 'text', 'The cart reads the fact with this label to say when each coffee was roasted.' ),
		),
		'Cart'           => array(
			'cart_heading'        => array( 'Heading', 'Your cart' ),
			'cart_count_one'      => array( 'Count, one coffee', 'One coffee' ),
			'cart_count_many'     => array( 'Count, several coffees', '[count] coffees', 'text', '[count] is the number, written as a word.' ),
			'col_coffee'          => array( 'Column, coffee', 'Coffee' ),
			'col_each'            => array( 'Column, price each', 'Each' ),
			'col_total'           => array( 'Column, total', 'Total' ),
			'each'                => array( 'Word after the price on phones', 'each' ),
			'each_hidden'         => array( 'Before the price each, read by screen readers', 'Each' ),
			'bag'                 => array( 'Word after the bag size', 'bag' ),
			'remove'              => array( 'Remove button, before the name', 'Remove' ),
			'removed_notice'      => array( 'Read out after a line is removed', '[coffee] was removed from your cart.', 'text', '[coffee] is the coffee.' ),
			'update_cart'         => array( 'Update button, shown only without JavaScript', 'Update the cart' ),
			'summary_heading'     => array( 'Summary heading', 'Order summary' ),
			'summary_note'        => array( 'Summary note', 'Pickup at [street] is free: choose it at checkout, and your bags are ready at the bar from the next roast day after your order. We ship to Manitoba, Saskatchewan, Alberta and Ontario.', 'textarea', $values ),
			'subtotal_before_tax' => array( 'Subtotal row', 'Subtotal before tax' ),
			'cart_shipping_label' => array( 'Shipping row', 'Shipping, free over $75' ),
			'shipping_unknown'    => array( 'Shipping before an address is known', 'Chosen at checkout' ),
			'free'                => array( 'Free', 'Free' ),
			'tax'                 => array( 'Tax row', 'Tax' ),
			'total_to_pay'        => array( 'Total row', 'Total to pay' ),
			'go_to_checkout'      => array( 'Checkout button', 'Go to checkout' ),
			'keep_shopping'       => array( 'Link back to the shop', 'Keep shopping' ),
			'discount_row'        => array( 'Coupon discount row, also on the checkout and the receipt', 'Discount, code [code]', 'text', '[code] is the coupon code.' ),
		),
		'Empty cart'     => array(
			'empty_eyebrow' => array( 'Small title', 'Nothing in the cart yet' ),
			'empty_heading' => array( 'Heading', 'Your cart is empty' ),
			'empty_body'    => array( 'Text', 'The coffees we are roasting now are in the shop.' ),
			'empty_link'    => array( 'Link to the shop', 'Shop coffee beans' ),
			'empty_image'   => array( 'Photo', '', 'image', 'Decoration beside the empty cart.' ),
		),
		'Checkout'       => array(
			'errors_label'        => array( 'Error area, read by screen readers', 'Problems with the order details' ),
			'errors_title'        => array( 'Error summary heading', 'There is a problem with your order details' ),
			'notice_error_title'  => array( 'Error heading on other shop pages', 'There is a problem' ),
			'checkout_eyebrow'    => array( 'Small title', 'Guest checkout, [count]', 'text', '[count] is the number of coffees, in words.' ),
			'checkout_heading'    => array( 'Heading', 'Checkout' ),
			'checkout_note'       => array( 'Note under the heading', 'You can order without creating an account.' ),
			'checkout_total_note' => array( 'Note under the total', '[count], [shipping], tax [tax] (coffee beans carry no GST).', 'text', '[count] the coffees, [shipping] the delivery cost, [tax] the tax.' ),
			'shipping_free'       => array( 'Total note, free shipping', 'shipping free' ),
			'pickup_free'         => array( 'Total note, pickup', 'pickup free' ),
			'details_title'       => array( 'Your details, heading', 'Your details' ),
			'details_note'        => array( 'Your details, note', 'We send the receipt to this email address. You do not need an account, and we do not create one for you.', 'textarea' ),
			'label_email'         => array( 'Label, email', 'Email address' ),
			'label_first_name'    => array( 'Label, first name', 'First name' ),
			'label_last_name'     => array( 'Label, last name', 'Last name' ),
			'label_phone'         => array( 'Label, phone', 'Phone number' ),
			'label_street'        => array( 'Label, street', 'Street address' ),
			'label_city'          => array( 'Label, city', 'City' ),
			'label_postcode'      => array( 'Label, postal code', 'Postal code' ),
			'label_province'      => array( 'Label, province', 'Province' ),
			'required'            => array( 'After a required label', '(required)' ),
			'optional'            => array( 'After an optional label', '(optional)' ),
			'hint_postcode'       => array( 'Postal code hint', 'Format [A1A 1A1]', 'text', 'The part in square brackets is set as the format.' ),
			'error_prefix'        => array( 'Before an error beside a field', 'Error:' ),
			'error_email'         => array( 'Error, email', 'Enter an email address in the format name@example.com' ),
			'error_first_name'    => array( 'Error, first name', 'Enter your first name' ),
			'error_last_name'     => array( 'Error, last name', 'Enter your last name' ),
			'error_street'        => array( 'Error, street', 'Enter your street address' ),
			'error_city'          => array( 'Error, city', 'Enter your city' ),
			'error_postcode'      => array( 'Error, postal code', 'Enter a postal code in the format A1A 1A1' ),
			'error_province'      => array( 'Error, province', 'Enter your province' ),
			'delivery_title'      => array( 'Delivery, heading', 'How do you want to get your coffee' ),
			'delivery_note'       => array( 'Delivery, note', 'We ship to Manitoba, Saskatchewan, Alberta and Ontario only. Pickup at the cafe is free on any order, and your bags are ready at the bar from the next roast day after your order.', 'textarea' ),
			'ship_note'           => array( 'Under "ship", the cost', '[cost], tracked parcel', 'text', '[cost] is the shipping cost.' ),
			'free_on_order'       => array( 'Shipping cost when free', 'Free on this order' ),
			'pickup_note'         => array( 'Under "pick up"', '[street], free', 'text', $values ),
			'no_delivery'         => array( 'When no delivery is possible', 'We cannot ship to this address. Pickup at the cafe is always possible.' ),
			'payment_title'       => array( 'Payment, heading', 'Payment' ),
			'payment_note'        => array( 'Payment, note', 'We take credit and debit cards. Your card is charged only when you place the order.', 'textarea' ),
			'no_payment'          => array( 'When no payment method is available', 'No payment method is available right now. Call or write to us and we will take your order.' ),
			'review_title'        => array( 'Review, heading', 'Review your order' ),
			'subtotal'            => array( 'Subtotal row', 'Subtotal' ),
			'shipping'            => array( 'Shipping row', 'Shipping' ),
			'pickup'              => array( 'Pickup row', 'Pickup' ),
			'consent_label'       => array( 'Roast notes consent', 'Yes, send me monthly roast notes from [name] by email. I can unsubscribe at any time.', 'textarea', $values ),
			'consent_sender'      => array( 'Consent, sender line', 'Optional. Sent by [name], [address], [website]. Withdraw your consent at any time with the unsubscribe link in every email.', 'textarea', $values ),
			'privacy_note'        => array( 'Privacy note', 'We use your name, address and email only to fill this order and send the receipt. Our payment provider and the parcel carrier get only what they need to take payment and deliver.', 'textarea' ),
			'privacy_link'        => array( 'Privacy link', 'Read the privacy policy' ),
			'place_order'         => array( 'Place order button', 'Place order and pay [total]', 'text', '[total] is the total to pay.' ),
			'back_to_cart'        => array( 'Link back to the cart', 'Back to cart' ),
		),
		'Order received' => array(
			'received_eyebrow'     => array( 'Small title', 'Order placed [date]', 'text', '[date] is the day of the order.' ),
			'received_title'       => array( 'Heading', 'Order [number] is confirmed', 'text', '[number] is the order number.' ),
			'received_note_ship'   => array( 'Note, shipped order', 'We sent the receipt to [email]. Your coffee ships on the next roast day after your order, [next], with a tracking link by email.', 'textarea', '[email] the customer\'s email, [next] the next roast day.' ),
			'received_note_pickup' => array( 'Note, pickup', 'We sent the receipt to [email]. Your bags are ready at the bar from the next roast day after your order, [next].', 'textarea', '[email] the customer\'s email, [next] the next roast day.' ),
			'next_title'           => array( 'What happens next, heading', 'What happens next' ),
			'next_ship'            => array( 'Next step, shipped order', 'We pack your coffee and send it by tracked parcel on [next]. The tracking link comes by email the day it leaves.', 'textarea' ),
			'next_pickup'          => array( 'Next step, pickup', 'Your bags wait at the bar from [next], during opening hours. Bring your order number.', 'textarea' ),
			'questions_label'      => array( 'Questions, label', 'Questions about the order' ),
			'questions_text'       => array( 'Questions, text', 'Call [phone] or write to [email] and quote [number].', 'text', '[phone] and [email] become links, [number] is the order number.' ),
			'shop_more'            => array( 'Link to the shop', 'Shop more coffee' ),
			'hours_link'           => array( 'Link to the visit page', 'Opening hours and directions to the cafe' ),
			'receipt_title'        => array( 'Receipt heading', 'Your order' ),
			'total_paid'           => array( 'Total row', 'Total paid' ),
			'payment_line'         => array( 'Payment line', 'Paid with [method] on [date].', 'text', '[method] the payment method, [date] the day of the order.' ),
			'payment_line_unpaid'  => array( 'Payment line, order not paid yet', 'To be paid by [method]. The instructions are below.', 'text', 'For a bank transfer, a cheque or cash on delivery. [method] the payment method.' ),
			'failed_title'         => array( 'Heading, payment failed', 'The payment for order [number] did not go through', 'text', '[number] is the order number.' ),
			'failed_note'          => array( 'Note, payment failed', 'Your bank or card issuer declined the payment, so the order has not been placed. Try the payment again, with the same card or another one.', 'textarea' ),
			'failed_pay'           => array( 'Button, pay again', 'Try the payment again' ),
			'failed_account'       => array( 'Link to the account, payment failed', 'Go to your account' ),
		),
	);
}

<?php
/**
 * Plugin Name: WooCommerce QR Code
 * Description: Generate QR codes for WooCommerce orders and include them on the order-received page and email template page.
 * Version: 1.0
 * Author: Dubai Fleamarket
 */

 if (!defined('ABSPATH')) {
    exit;
}

// Include the phpqrcode library
include plugin_dir_path(__FILE__) . 'libs/phpqrcode/qrlib.php';

// Hook to display QR code on the order-received page
add_action('woocommerce_thankyou', 'display_qr_code_on_thankyou', 10, 1);

function display_qr_code_on_thankyou($order_id) {

    $order = wc_get_order($order_id);
    $orderID = $order->get_id();
    $customerEmail = $order->get_billing_email();
    $items = $order->get_items();
    $product_id = "";
    foreach ( $items as $item ) {
        $product_name = $item->get_name();
        $product_id = $item->get_product_id();
        $product_variation_id = $item->get_variation_id();
    }

    $event_title = get_field('event_title', $product_id);
    $event_date = get_field('event_date', $product_id);
    $start_time = get_field('start_time', $product_id);
    $end_time = get_field('end_time', $product_id);

    // Get payment details
    $payment_method = $order->get_payment_method();
    $payment_amount = $order->get_total();
	
	$payment_status = $order->get_status();
	// Check if payment is successful
	 $status = "Failed";
	if (in_array($payment_status, array('processing', 'completed'))) {
		$status = "Valid Event";
	} else {
		  $status = "Failed";
	}

    
    // Create plain text content with additional event-related information
    $plainTextContent = "Status-" .$status."\n";
	$plainTextContent .= "Order Id-" .$orderID."\n";
    $plainTextContent .= "Customer Email-" .$customerEmail."\n";
    $plainTextContent .= "Payment Amount-" .$payment_amount."\n";
    $plainTextContent .= "Payment Method-" .$payment_method."\n";
    $plainTextContent .= "Event Date-" .$event_date."\n";
    $plainTextContent .= "Event Title-" .$event_title."\n";
    $plainTextContent .= "Start Time-" .$start_time."\n";
    $plainTextContent .= "End Time-" .$end_time."\n";

    // Generate QR code with plain text data
    $outputFile = plugin_dir_path(__FILE__) . 'temp/order_' . $order_id . '.png';
    QRcode::png($plainTextContent, $outputFile, QR_ECLEVEL_L, 5);



    // Display the QR code before the footer on the order-received page
    if (is_wc_endpoint_url('order-received')) {
        $outputFile = plugin_dir_url(__FILE__) . 'temp/order_' . $order_id . '.png';

        // Output QR code image with HTML
        echo '<div id="qr-code-section" style="text-align: center; margin-top: 20px;">';
        echo '<img src="' . $outputFile . '" style="width:200px; height:200px;" alt="QR Code for Order ID: ' . $order_id . '"><br>';
        echo '<p> Please Scan Event QR Code </p>';
        echo '</div>';
    }
}

// Add QR code section to WooCommerce email template
add_action('woocommerce_email_order_details', 'add_qr_code_to_email', 10, 4);

function add_qr_code_to_email($order, $sent_to_admin, $plain_text, $email)
{
    // Get order ID
    $order_id = $order->get_id();

    // Get QR code image URL
    $outputFile = plugin_dir_url(__FILE__) . 'temp/order_' . $order_id . '.png';

    // Output QR code HTML
    echo '<div id="qr-code-section" style="text-align: center; margin-top: 20px;">';
    echo '<img src="' . esc_url($outputFile) . '" style="width:200px; height:200px;" alt="QR Code for Order ID: ' . $order_id . '"><br>';
    echo '<p>Please Scan Event QR Code</p>';
    echo '</div>';
}

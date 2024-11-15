<?php
/* 
Send Translate Email Notification Depend on customer langauge. for this tutorial must need to install polylang plugin.
for this tutorial purpose lets say cusomer langauge is English "en"
*/

// First, disable default processing email
add_action('woocommerce_email', 'disable_default_processing_email');
function disable_default_processing_email($email_class) {
    remove_action('woocommerce_order_status_pending_to_processing_notification', array($email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
    remove_action('woocommerce_order_status_failed_to_processing_notification', array($email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
}

// Add custom email for English orders
add_action('woocommerce_order_status_changed', 'send_custom_english_order_email', 10, 4);
function send_custom_english_order_email($order_id, $old_status, $new_status, $order) {
    // Check if new status is processing
    if ($new_status !== 'processing') {
        return;
    }

    // Get order language
    $order_language = $order->get_meta('customer_language');
    
    // Check if order is in English
    if ($order_language === 'en') {
        // Get customer email
        $to = $order->get_billing_email();
        
        // Email subject
        $subject = 'Thank you for your order at ' . get_bloginfo('name');
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        // Email body
        $body = '
        <html>
        <body>
            <h2>Your order has been received and is being processed</h2>
            <p>Dear ' . $order->get_billing_first_name() . ',</p>
            
            <p>Thank you for shopping with us. We\'re excited to let you know that we\'ve received your order #' . $order->get_order_number() . '.</p>
            
            <h3>Order Details:</h3>
            <p>Order Number: #' . $order->get_order_number() . '</p>
            <p>Order Date: ' . $order->get_date_created()->format('F j, Y') . '</p>
            <p>Order Total: ' . $order->get_formatted_order_total() . '</p>
            
            <h3>What happens next?</h3>
            <p>We\'re currently processing your order and will send you another email once your order has been shipped.</p>
            
            <p>If you have any questions about your order, please don\'t hesitate to contact us.</p>
            
            <p>Best regards,<br>
            The team at ' . get_bloginfo('name') . '</p>
        </body>
        </html>';
        
        // Send email
        wp_mail($to, $subject, $body, $headers);
        
        // Add note to order
        $order->add_order_note('Custom English processing email sent to customer.');
    } else {
        // For non-English orders, trigger the default processing email
        WC()->mailer()->get_emails()['WC_Email_Customer_Processing_Order']->trigger($order_id);
    }
}

// Optional: Add custom styling to email
add_action('wp_mail_content_type', 'set_html_mail_content_type');
function set_html_mail_content_type() {
    return 'text/html';
}

// Optional: Log email sending for debugging
function log_custom_email($to, $subject, $message) {
    $log_message = sprintf(
        "Email sent to %s with subject '%s' at %s\n",
        $to,
        $subject,
        date('Y-m-d H:i:s')
    );
    
    // You can modify this to log wherever you prefer
    error_log($log_message);
}

// Optional: Add custom email template
add_filter('woocommerce_email_styles', 'add_custom_email_styles');
function add_custom_email_styles($css) {
    $custom_css = "
        h2 { color: #96588a; }
        h3 { color: #2c2d33; }
        p { color: #636363; }
    ";
    
    return $css . $custom_css;
}

// Optional: Test function
function test_custom_email($order_id) {
    $order = wc_get_order($order_id);
    if ($order) {
        send_custom_english_order_email($order_id, 'pending', 'processing', $order);
        return 'Test email sent for order #' . $order_id;
    }
    return 'Order not found';
}

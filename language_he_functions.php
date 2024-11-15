<?php

/* 
Send Translate Email Notification depends on the customer's language. For this tutorial, you need to install Polylang plugin.
For this tutorial purpose, let's say the customer language is Hebrew "he"
*/

// Disable default processing email
add_action('woocommerce_email', 'disable_default_processing_email');
function disable_default_processing_email($email_class) {
    remove_action('woocommerce_order_status_pending_to_processing_notification', array($email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
    remove_action('woocommerce_order_status_failed_to_processing_notification', array($email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
}

// Add custom email for Hebrew orders
add_action('woocommerce_order_status_changed', 'send_custom_hebrew_order_email', 10, 4);
function send_custom_hebrew_order_email($order_id, $old_status, $new_status, $order) {
    // Check if new status is processing
    if ($new_status !== 'processing') {
        return;
    }

    // Get order language
    $order_language = $order->get_meta('customer_language');
    
    // Check if order is in Hebrew
    if ($order_language === 'he') {
        // Get customer email
        $to = $order->get_billing_email();
        
        // Email subject
        $subject = 'תודה על ההזמנה שלך ב-' . get_bloginfo('name');
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        // Email body
        $body = '
        <html>
        <body style="direction: rtl; text-align: right;">
            <h2>ההזמנה שלך התקבלה ונמצאת בטיפול</h2>
            <p>שלום ' . $order->get_billing_first_name() . ',</p>
            
            <p>תודה שקנית אצלנו. אנחנו שמחים להודיע לך שקיבלנו את הזמנתך מספר #' . $order->get_order_number() . '.</p>
            
            <h3>פרטי ההזמנה:</h3>
            <p>מספר הזמנה: #' . $order->get_order_number() . '</p>
            <p>תאריך ההזמנה: ' . $order->get_date_created()->format('d/m/Y') . '</p>
            <p>סכום כולל: ' . $order->get_formatted_order_total() . '</p>
            
            <h3>מה הלאה?</h3>
            <p>אנחנו כרגע מטפלים בהזמנה שלך ונשלח לך אימייל נוסף ברגע שההזמנה תישלח.</p>
            
            <p>אם יש לך שאלות כלשהן לגבי ההזמנה, אל תהסס/י ליצור איתנו קשר.</p>
            
            <p>בברכה,<br>
            הצוות של ' . get_bloginfo('name') . '</p>
        </body>
        </html>';
        
        // Send email
        wp_mail($to, $subject, $body, $headers);
        
        // Add note to order
        $order->add_order_note('Custom Hebrew processing email sent to customer.');
    } else {
        // For non-Hebrew orders, trigger the default processing email
        WC()->mailer()->get_emails()['WC_Email_Customer_Processing_Order']->trigger($order_id);
    }
}

// Set email content type to HTML
add_action('wp_mail_content_type', 'set_html_mail_content_type');
function set_html_mail_content_type() {
    return 'text/html';
}

// Add custom email styles
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
function test_hebrew_email($order_id) {
    $order = wc_get_order($order_id);
    if ($order) {
        send_custom_hebrew_order_email($order_id, 'pending', 'processing', $order);
        return 'Test email sent for order #' . $order_id;
    }
    return 'Order not found';
}

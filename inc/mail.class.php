<?php
defined( 'ABSPATH' ) || exit;

if (!class_exists('Mailino_EMAIL_Handler')) {
    class Mailino_EMAIL_Handler {
        public function send_email_to_owner($subscriber_email) {
            $to = get_option('admin_email');
            $subject = __('New Subscription', 'mailino');
            $message = sprintf(__('A new subscriber has joined: %s', 'mailino'), $subscriber_email);
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            wp_mail($to, $subject, $message, $headers);
        }

        public function send_email_to_subscriber($subscriber_email) {
            $subject = __('Thank You for Subscribing!', 'mailino');
            $message = __('Thank you for subscribing to our mailing list!', 'mailino');
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            wp_mail($subscriber_email, $subject, $message, $headers);
        }
    }
}

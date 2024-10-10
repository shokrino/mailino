<?php
defined('ABSPATH') || exit;

if (!class_exists('Mailino_EMAIL_Handler')) {
    class Mailino_EMAIL_Handler {
        public function send_email_to_owner($subscriber_email) {
            $to = get_option('admin_email');

            // Translators: %s is the subscriber's email address.
            $subject = __('New Subscription', 'mailino');
            // Translators: %s is the subscriber's email address.
            $message = sprintf(__('A new subscriber has joined: %s', 'mailino'), $subscriber_email);
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            wp_mail($to, $subject, $message, $headers);
        }

        public function send_email_to_subscriber($subscriber_email) {
            // Translators: This message is sent to the subscriber after they subscribe.
            $subject = __('Thank You for Subscribing!', 'mailino');
            // Translators: This message is sent to the subscriber after they subscribe.
            $message = __('Thank you for subscribing to our mailing list!', 'mailino');
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            wp_mail($subscriber_email, $subject, $message, $headers);
        }
    }
}

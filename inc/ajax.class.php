<?php
defined( 'ABSPATH' ) || exit;

if (!class_exists('Mailino_AJAX_Handler')) {
    class Mailino_AJAX_Handler {
        public function __construct() {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_email_subscription_script']);
            add_action('wp_ajax_save_email_mailino', [$this, 'save_email_subscription']);
            add_action('wp_ajax_nopriv_save_email_mailino', [$this, 'save_email_subscription']);
        }

        public function enqueue_email_subscription_script() {
            wp_enqueue_script('ajax-script-mailino', MILIN_ASSETS . '/js/email-form.js', array(), null, true);
            wp_localize_script('ajax-script-mailino', 'mailino_script_data', array(
                'ajax_url' => admin_url('admin-ajax.php'),
            ));
        }

        function save_email_subscription() {
            // Verify nonce
            if (!isset($_POST['email_nonce']) || !wp_verify_nonce($_POST['email_nonce'], 'mailino_subscribe_nonce')) {
                wp_send_json_error(['error' => __('Nonce verification failed.', 'mailino')]);
                wp_die();
            }
        
            // Check if email is set
            if (!isset($_POST['email'])) {
                wp_send_json_error(['error' => __('Email is required.', 'mailino')]);
                wp_die();
            }
        
            $email = sanitize_email($_POST['email']);
            $allowedProviders = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
            $emailDomain = explode('@', $email)[1] ?? '';
        
            // Validate email domain
            if (!in_array($emailDomain, $allowedProviders)) {
                wp_send_json_error(['error' => __('Please use a valid email provider like Gmail, Yahoo, or Outlook.', 'mailino')]);
                wp_die();
            }
        
            // Add your email handling logic here, e.g., saving to the database.
        
            wp_send_json_success(['message' => __('Successfully subscribed!', 'mailino')]);
            wp_die();
        }
    }
}

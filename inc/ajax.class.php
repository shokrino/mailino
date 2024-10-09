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
            wp_enqueue_style('style-mailino', MILIN_ASSETS.'/css/email-form.css', array(), MILIN_VERSION, 'all', false);
            wp_enqueue_script('ajax-script-mailino', MILIN_ASSETS . '/js/email-form.js', array(), null, true);
            wp_localize_script('ajax-script-mailino', 'mailino_script_data', array(
                'ajax_url' => admin_url('admin-ajax.php'),
            ));
        }

        function save_email_subscription() {
            check_ajax_referer('mailino_subscribe_nonce', 'email_nonce');

            if (!isset($_POST['email'])) {
                wp_send_json_error(['error' => __('Email is required.', 'mailino')]);
                wp_die();
            }

            $email = sanitize_email($_POST['email']);
            $allowedProviders = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
            $emailDomain = explode('@', $email)[1] ?? '';

            if (!in_array($emailDomain, $allowedProviders)) {
                wp_send_json_error(['error' => __('Please use a valid email provider like Gmail, Yahoo, or Outlook.', 'mailino')]);
                wp_die();
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'mailino_subscribers';

            $existing_subscriber = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE email = %s", $email));

            if ($existing_subscriber) {
                wp_send_json_error(['message' => __('This email is already subscribed.', 'mailino')]);
                wp_die();
            } else {
                $result = $wpdb->insert(
                    $table_name,
                    [
                        'email' => $email,
                        'time_added' => current_time('mysql'),
                    ],
                    [
                        '%s',
                        '%s',
                    ]
                );

                if ($result) {
                    $email_handler = new Mailino_EMAIL_Handler();
                    $email_handler->send_email_to_owner($email);
                    $email_handler->send_email_to_subscriber($email);
                    wp_send_json_success(['message' => __('Thank you for subscribing!', 'mailino')]);
                } else {
                    wp_send_json_error(['message' => __('There was a problem. Please try again.', 'mailino')]);
                }
            }

            wp_die();
        }


    }
}

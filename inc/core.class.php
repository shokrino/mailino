<?php
defined( 'ABSPATH' ) || exit;

if (!class_exists('Mailino')) {
    class Mailino {
        public function __construct() {
            add_action('admin_menu', [$this, 'register_email_subscribers_menu_page']);
            add_action('after_setup_theme', [$this, 'create_email_subscriber_table']);
            add_action('init', [$this, 'register_shortcodes']);
        }

        public function register_email_subscribers_menu_page() {
            add_menu_page(
                esc_html__('Email Subscribers', 'mailino'),
                esc_html__('Email Subscribers', 'mailino'),
                'manage_options',
                'email-subscribers',
                [$this, 'display_email_subscribers_page'],
                'dashicons-email',
                20
            );
        }

        public function display_email_subscribers_page() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'mailino_subscribers';
            $subscribers = $wpdb->get_results("SELECT * FROM $table_name");

            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Email Subscribers', 'mailino') . '</h1>';
            echo '<table class="widefat fixed" cellspacing="0">';
            echo '<thead><tr><th>ID</th><th>Email</th></tr></thead>';
            echo '<tbody>';

            if ($subscribers) {
                foreach ($subscribers as $subscriber) {
                    echo '<tr><td>' . esc_html($subscriber->id) . '</td><td>' . esc_html($subscriber->email) . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="2">' . esc_html__('No subscribers found.', 'mailino') . '</td></tr>';
            }

            echo '</tbody></table></br>';
            echo '<form method="post" action="">';
            echo '<input type="submit" name="export_csv" class="button-primary" value="' . esc_html__('Export to CSV', 'mailino') . '">';
            echo '</form>';
            echo '</div>';

            if (isset($_POST['export_csv'])) {
                $this->export_subscribers_to_csv();
            }
        }

        public function export_subscribers_to_csv() {
            global $wpdb;
            if (ob_get_length()) {
                ob_clean();
            }

            $table_name = $wpdb->prefix . 'mailino_subscribers';
            $subscribers = $wpdb->get_results("SELECT * FROM $table_name");

            if ($subscribers) {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="mailino_subscribers.csv"');
                header('Pragma: no-cache');
                header('Expires: 0');

                $output = fopen('php://output', 'w');

                foreach ($subscribers as $subscriber) {
                    fputcsv($output, [$subscriber->email]);
                }

                fclose($output);
                exit;
            }
        }

        public function create_email_subscriber_table() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'mailino_subscribers';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                email varchar(255) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY email (email)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        public function register_shortcodes() {
            add_shortcode('mailino_form', [$this, 'render_email_form']);
        }

        public function render_email_form() {
            ob_start();
            ?>
            <form id="mailino-email-form" method="post">
                <label for="email"><?php esc_html_e('Subscribe to our mailing list:', 'mailino'); ?></label>
                <input type="email" name="email" id="email" required placeholder="<?php esc_attr_e('Enter your email', 'mailino'); ?>">
                <input type="hidden" name="email_nonce" value="<?php echo wp_create_nonce('mailino_subscribe_nonce'); ?>">
                <button type="submit"><?php esc_html_e('Subscribe', 'mailino'); ?></button>
                <span class="loading-email-subs" style="display:none;">
                    <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <style>
                            .spinner_nOfF {
                                animation: spinner_qtyZ 2s cubic-bezier(0.36, .6, .31, 1) infinite;
                                fill: #fff;
                            }
                            .spinner_fVhf {
                                animation-delay: -.5s;
                            }
                            .spinner_piVe {
                                animation-delay: -1s;
                            }
                            .spinner_MSNs {
                                animation-delay: -1.5s;
                            }
                            @keyframes spinner_qtyZ {
                                0% { r: 0; }
                                25% { r: 3px; cx: 4px; }
                                50% { r: 3px; cx: 12px; }
                                75% { r: 3px; cx: 20px; }
                                100% { r: 0; cx: 20px; }
                            }
                        </style>
                        <circle class="spinner_nOfF" cx="4" cy="12" r="3"></circle>
                        <circle class="spinner_nOfF spinner_fVhf" cx="4" cy="12" r="3"></circle>
                        <circle class="spinner_nOfF spinner_piVe" cx="4" cy="12" r="3"></circle>
                        <circle class="spinner_nOfF spinner_MSNs" cx="4" cy="12" r="3"></circle>
                    </svg>
                </span>
            </form>
            <div id="mailino-response" class="response" style="display: none;"></div>
            <?php
            return ob_get_clean();
        }

    }

}

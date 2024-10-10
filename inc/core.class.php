<?php
defined( 'ABSPATH' ) || exit;

if (!class_exists('Mailino')) {
    class Mailino {
        public function __construct() {
            add_action('admin_menu', [$this, 'register_email_subscribers_menu_page']);
            add_action('after_setup_theme', [$this, 'create_email_subscriber_table']);
            add_action('init', [$this, 'register_shortcodes']);
            add_action('admin_init', [$this, 'register_mailino_settings']);
            add_action('wp_head', [$this, 'apply_custom_styles']);
            add_action('admin_head', [$this, 'apply_custom_styles']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_email_subscription_script']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_email_subscription_script']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_email_subscription_script']);
        }

        public function register_email_subscribers_menu_page() {
            add_menu_page(
                esc_html__('Mailino Subscribers', 'mailino'),
                esc_html__('Mailino', 'mailino'),
                'manage_options',
                'mailino_email_subscribers',
                [$this, 'display_email_subscribers_page'],
                'dashicons-email',
                20
            );
        }

        public function enqueue_email_subscription_script() {
            if (!is_admin() && has_shortcode(get_post()->post_content, 'mailino_form')) {
                wp_enqueue_style('style-mailino', MILIN_ASSETS . '/css/email-form.css', array(), MILIN_VERSION, 'all');
                wp_enqueue_script('ajax-script-mailino', MILIN_ASSETS . '/js/email-form.js', array(), MILIN_VERSION, true);
                wp_localize_script('ajax-script-mailino', 'mailino_script_data', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                ));
            }

            if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'mailino_email_subscribers') {
                wp_enqueue_style('style-mailino', MILIN_ASSETS . '/css/email-form.css', array(), MILIN_VERSION, 'all');
                wp_enqueue_script('mailino-admin-color-picker', MILIN_ASSETS . '/js/admin-form.js', ['wp-color-picker', 'jquery'], MILIN_VERSION, true);
            }
        }

        public function enqueue_admin_email_subscription_script() {
            wp_enqueue_script('mailino-admin-color-picker', MILIN_ASSETS . '/js/admin-form.js', ['wp-color-picker', 'jquery'], MILIN_VERSION, true);

            add_filter('script_loader_tag', function($tag, $handle) {
                if ('mailino-admin-color-picker' !== $handle) {
                    return $tag;
                }
                return str_replace(' src', ' defer src', $tag);
            }, 10, 2);
        }


        public function display_email_subscribers_page() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'mailino_subscribers';
            $subscribers = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %s", $table_name ) );

            if (isset($_GET['delete_subscriber'])) {
                $this->delete_subscriber(sanitize_text_field( wp_unslash( $_GET['delete_subscriber'] ) ));
                wp_redirect(admin_url('admin.php?page=mailino_email_subscribers'));
                exit;
            }

            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Mailino - Email Subscribers', 'mailino') . '</h1>';
            echo '<h2 class="nav-tab-wrapper">';
            echo '<a href="#dashboard" class="nav-tab nav-tab-active">' . esc_html__('Dashboard', 'mailino') . '</a>';
            echo '<a href="#emails-list" class="nav-tab">' . esc_html__('Emails List', 'mailino') . '</a>';
            echo '</h2>';

            // Dashboard Section
            echo '<div id="dashboard" class="tab-content" style="display: block;">';
            echo '<h2>' . esc_html__('Subscribe Form', 'mailino') . '</h2>';
            echo '<p>' . esc_html__('You can use the following shortcode to display the subscribe form:', 'mailino') . '</p>';
            echo '<span class="shortcode-box-mailino">[mailino_form]</span>';

            echo '<div class="mailino-settings-box" style="margin-top: 20px;">';
            echo '<h2>' . esc_html__('Form Customization Settings', 'mailino') . '</h2>';
            echo do_shortcode('[mailino_form]');
            echo '<form method="post" action="options.php">';
            settings_fields('mailino_settings_group');
            do_settings_sections('mailino_settings');
            submit_button();
            echo '</form>';
            echo '</div>';
            echo '</div>';

            // Emails List Section
            echo '<div id="emails-list" class="tab-content" style="display: none;">';
            echo '<h2>' . esc_html__('Email Subscribers List', 'mailino') . '</h2>';
            echo '<table class="widefat fixed" cellspacing="0">';
            echo '<thead><tr><th>ID</th><th>Email</th><th>Time Added</th><th>Action</th></tr></thead>';
            echo '<tbody>';

            if ($subscribers) {
                foreach ($subscribers as $subscriber) {
                    $time_added = $this->time_elapsed_string($subscriber->time_added);
                    echo '<tr>';
                    echo '<td>' . esc_html($subscriber->id) . '</td>';
                    echo '<td>' . esc_html($subscriber->email) . '</td>';
                    echo '<td>' . esc_html($time_added) . '</td>';
                    echo '<td><a href="' . esc_url(admin_url('admin.php?page=mailino_email_subscribers&delete_subscriber=' . $subscriber->id)) . '" class="button button-secondary" onclick="return confirm(\'Are you sure you want to delete this subscriber?\');">' . esc_html__('Delete', 'mailino') . '</a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4">' . esc_html__('No subscribers found.', 'mailino') . '</td></tr>';
            }

            echo '</tbody></table></br>';
            echo '<form method="post" action="">';
            echo '<input type="submit" name="export_csv" class="button-primary" value="' . esc_html__('Export to CSV', 'mailino') . '">';
            echo '</form>';
            echo '</div>';

            if (isset($_POST['export_csv'])) {
                $this->export_subscribers_to_csv();
            }

            echo '</div>';
        }

        public function register_mailino_settings() {
            register_setting('mailino_settings_group', 'mailino_main_color');
            register_setting('mailino_settings_group', 'mailino_border_radius');
            register_setting('mailino_settings_group', 'mailino_form_size');

            add_settings_section(
                'mailino_settings_section',
                __('Form Style Settings', 'mailino'),
                null,
                'mailino_settings'
            );

            add_settings_field(
                'mailino_main_color',
                __('Main Color', 'mailino'),
                [$this, 'mailino_main_color_callback'],
                'mailino_settings',
                'mailino_settings_section'
            );

            add_settings_field(
                'mailino_border_radius',
                __('Border Radius', 'mailino'),
                [$this, 'mailino_border_radius_callback'],
                'mailino_settings',
                'mailino_settings_section'
            );

            add_settings_field(
                'mailino_form_size',
                __('Form Size', 'mailino'),
                [$this, 'mailino_form_size_callback'],
                'mailino_settings',
                'mailino_settings_section'
            );
        }

        public function mailino_main_color_callback() {
            $value = get_option('mailino_main_color', '#70ce1e');
            echo '<input type="text" name="mailino_main_color" value="' . esc_attr($value) . '" class="mailino-color-picker" data-default-color="#70ce1e">';
        }

        public function mailino_border_radius_callback() {
            $value = get_option('mailino_border_radius', '14');
            echo '<input type="range" name="mailino_border_radius" min="0" max="50" value="' . esc_attr($value) . '" oninput="this.nextElementSibling.value = this.value">';
            echo '<output>' . esc_html($value) . '</output>px';
        }

        public function mailino_form_size_callback() {
            $value = get_option('mailino_form_size', 'medium');
            $options = [
                'small' => __('Small (47px height)', 'mailino'),
                'medium' => __('Medium (54px height)', 'mailino'),
                'large' => __('Large (61px height)', 'mailino')
            ];

            echo '<select name="mailino_form_size">';
            foreach ($options as $key => $label) {
                echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
        }

        public function apply_custom_styles() {
            $main_color = get_option('mailino_main_color', '#70ce1e');
            $border_radius = get_option('mailino_border_radius', '14px');
            $form_size = get_option('mailino_form_size', 'medium');
            $font_size = $form_size === 'small' ? '12px' : ($form_size === 'large' ? '16px' : '14px');

            echo '<style>
                #mailino-email-form {
                    --color-mailino-plugin: ' . esc_attr($main_color) . ';
                    --border-radius-mailino-plugin: ' . esc_attr($border_radius) . 'px;
                    --padding-mailino-plugin: ' . esc_attr($font_size) . ';
                }
            </style>';
        }

        private function delete_subscriber($id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'mailino_subscribers';
            $wpdb->delete($table_name, ['id' => $id]);
        }

        public function export_subscribers_to_csv() {
            global $wpdb;
            if (ob_get_length()) {
                ob_clean();
            }

            $table_name = $wpdb->prefix . 'mailino_subscribers';
            $subscribers = $wpdb->get_results("SELECT * FROM $table_name");

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="subscribers.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Email', 'Time Added']);

            foreach ($subscribers as $subscriber) {
                fputcsv($output, [$subscriber->id, $subscriber->email, $subscriber->time_added]);
            }

            global $wp_filesystem;
            if ( ! $wp_filesystem ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }
            $wp_filesystem->delete($output);

            exit();
        }

        private function time_elapsed_string($datetime, $full = false) {
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = [
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            ];
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }

            if (!$full) {
                $string = array_slice($string, 0, 1);
            }

            return $string ? implode(', ', $string) . ' ago' : 'just now';
        }

        public function create_email_subscriber_table() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $wpdb->prefix . 'mailino_subscribers';

            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                email varchar(100) NOT NULL,
                time_added datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                UNIQUE (email),
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        public function register_shortcodes() {
            add_shortcode('mailino_form', [$this, 'render_email_subscription_form']);
        }

        public function render_email_subscription_form() {
            ?>
<form id="mailino-email-form" method="post">
    <input type="email" name="email" id="email" required placeholder="<?php esc_attr_e('Enter your email', 'mailino'); ?>">
    <input type="hidden" name="email_nonce" value="<?php echo esc_attr(wp_create_nonce('mailino_subscribe_nonce')); ?>">
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
    <span id="mailino-response" class="response-email-subs" style="display: none;"></span>
</form>
<?php
        }
    }
}

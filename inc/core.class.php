<?php
defined( 'ABSPATH' ) || exit;

if (!class_exists('Mailino')) {
class Mailino {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'register_email_subscribers_menu_page']);
        add_action('after_setup_theme', [$this, 'create_email_subscriber_table']);
    }

    public function register_email_subscribers_menu_page() {
        add_menu_page(
            esc_html__('Email Subscribers','mailino'),
            esc_html__('Email Subscribers','mailino'),
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
        echo '<h1>'.esc_html__('Email Subscribers','mailino').'</h1>';
        echo '<table class="widefat fixed" cellspacing="0">';
        echo '<thead><tr><th>ID</th><th>Email</th></tr></thead>';
        echo '<tbody>';

        if ($subscribers) {
            foreach ($subscribers as $subscriber) {
                echo '<tr><td>' . esc_html($subscriber->id) . '</td><td>' . esc_html($subscriber->email) . '</td></tr>';
            }
        } else {
            echo '<tr><td colspan="2">'.esc_html__('No subscribers found.','mailino').'</td></tr>';
        }

        echo '</tbody></table>';
        echo '<form method="post" action="">';
        echo '<input type="submit" name="export_csv" class="button-primary" value="'.esc_html__('Export to CSV','mailino').'">';
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
            $jalali_date = $this->jdf('d-F-Y-H-i');
            header('Content-Disposition: attachment; filename="mailino_subscribers_' . $jalali_date . '.csv"');
                        header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['Email']);

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
    public function jdf($format, $timestamp = '', $timezone = 'Asia/Tehran', $tr_num = 'en') {
        $T_sec = 0;
        if ($timestamp == '') {
            $timestamp = time();
        }
        $ts = ($timestamp + $T_sec);
        $date = explode('_', date('Y_m_d_H_i_s', $ts));
        list($g_y, $g_m, $g_d) = explode('_', date('Y_m_d', $ts));
        
        $jy = $g_y - (($g_y >= 1600) ? 979 : 621);
        $gy = ($g_y >= 1600) ? 1600 : 621;
        $gm = ($g_m > 2) ? ($g_m - 3) : ($g_m + 9);
        $gd = $g_d - (($g_m > 2) ? 79 : (($g_y % 4 == 0) ? 80 : 81));
        
        $date = sprintf("%02d-%s-%04d-%02d-%02d", $gd, 'mehr', $jy, date('H', $ts), date('i', $ts));
        return $date;
    }
    
}

new Mailino();
}
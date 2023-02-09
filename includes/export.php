<?php

/**
 * Load the base class
 */
class Leaky_Paywall_Reporting_Tool_Export
{

    public function __construct()
    {
     //   add_action('admin_init', array( $this, 'process_requests' ), 15);

        add_action('wp_ajax_leaky_paywall_reporting_tool_process', array( $this, 'process_requests') );
    }


    public function process_requests()
    {

        $form_data = isset($_POST['formData']) ? htmlspecialchars_decode(wp_kses_post(wp_unslash($_POST['formData']))) : '';
        parse_str($form_data, $fields);

        if (!isset($fields['leaky_paywall_reporting_tool_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($fields['leaky_paywall_reporting_tool_nonce'], 'submit_leaky_paywall_reporting_tool')) {
            return;
        }

        global $no_lp_subscribers;
        $no_lp_subscribers = false;
        $mode = leaky_paywall_get_current_mode();
        $site = leaky_paywall_get_current_site();
        $step = $_POST['step'];

        if ( $step == 'done' ) {
            die('we are done');
        }

        $users = $this->reporting_tool_query( $fields, $step );

        $meta = array(
            'level_id',
            'hash',
            'subscriber_id',
            'price',
            'description',
            'plan',
            'created',
            'expires',
            'payment_gateway',
            'payment_status',
        );

        $meta = apply_filters('leaky_paywall_reporting_tool_meta', $meta);

        if (is_plugin_active('leaky-paywall-custom-subscriber-fields/issuem-leaky-paywall-subscriber-meta.php')) {
            global $dl_pluginissuem_leaky_paywall_subscriber_meta;
            $custom_meta_fields = $dl_pluginissuem_leaky_paywall_subscriber_meta->get_settings();
        }

        if (!empty($users)) {

            $user_meta = array();
            foreach ($users as $user) {
                $user_meta[$user->ID]['user_id'] = $user->ID;
                $user_meta[$user->ID]['user_login'] = $user->data->user_login;
                $user_meta[$user->ID]['user_email'] = $user->data->user_email;
                $user_meta[$user->ID]['first_name'] = $user->first_name;
                $user_meta[$user->ID]['last_name'] = $user->last_name;
                foreach ($meta as $key) {
                    $user_meta[$user->ID][$key] = get_leaky_user_meta($user->ID, '_leaky_paywall_' . $mode . '_' . $key . $site);
                }
                if (!empty($custom_meta_fields['meta_keys'])) {

                    foreach ($custom_meta_fields['meta_keys'] as $meta_key) {
                        $user_meta[$user->ID][$meta_key['name']] = get_leaky_user_meta($user->ID, '_leaky_paywall_' . $mode . '_subscriber_meta_' . sanitize_title_with_dashes($meta_key['name']) . $site);
                    }
                }

                $user_meta = apply_filters('leaky_paywall_reporting_tool_user_meta', $user_meta, $user->ID);
            }

            if (!empty($user_meta)) {
                $this->export_file( $user_meta, $step );
                die();
            }
        } else {

            $upload_dir       = wp_get_upload_dir();
            $filename = str_replace( 'http://', 'https://', trailingslashit($upload_dir['baseurl']) ) . 'leaky-paywall-report-' . wp_hash(home_url('/')) . '.csv';

            $response = array(
                'step'        => 'done',
                'url'   => $filename
            );
            echo json_encode($response);
            exit;
        }
    }

    public function export_headers() {

        // $now = gmdate("D, d M Y H:i:s");
        $filename = 'leaky-paywall-report-' . time() . '.csv';



        // header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
        // header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate ');
        // header('Last-Modified: ' . $now . ' GMT ');

        // // force download
        // header('Content-Type: application/force-download');
        // header('Content-Type: application/octet-stream');
        // header('Content-Type: application/download');

        // // disposition / encoding on response body
        // header('Content-Disposition: attachment;filename="' . $filename . '"');
        // header('Content-Transfer-Encoding: binary');

    }

    public function export_file( $content_array, $step ) {

        header("Content-type: text/csv");
        $upload_dir       = wp_get_upload_dir();
        $filename = trailingslashit($upload_dir['basedir']) . 'leaky-paywall-report-' . wp_hash(home_url('/')) . '.csv';

        if ($step == 1) {
            $f = fopen($filename, 'w');
        //    $this->export_headers();
        } else {
            $f = fopen($filename, 'a');
        }

        fputcsv($f, array_keys(reset($content_array)));
        foreach ($content_array as $row) {
            fputcsv($f, $row);
        }
        fclose($f);


        $response = array(
            'step'        => $step += 1,
        );

        echo json_encode($response);
        exit;

    }

    public function reporting_tool_query( $fields, $step ) {

        if ( empty( $fields ) ) {
            return false;
        }

        $args = array(
            'role__not_in'    => 'administrator',
            'number' => 1000,
            'offset' => ((int)$step - 1) * 1000,
        );

        $mode = leaky_paywall_get_current_mode();

        if (!empty($fields['expire_start'])) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_expires',
                'value'   => date('Y-m-d 23:59:59', strtotime($fields['expire_start'])),
                'type'    => 'DATE',
                'compare' => '>='
            );
        }
        if (!empty($fields['expire_end'])) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_expires',
                'value'   => date('Y-m-d 23:59:59', strtotime($fields['expire_end'])),
                'type'    => 'DATE',
                'compare' => '<='
            );
        }

        if (!empty($fields['created_start'])) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_created',
                'value'   => date('Y-m-d 23:59:59', strtotime($fields['created_start'])),
                'type'    => 'DATE',
                'compare' => '>='
            );
        }

        if (!empty($fields['created_end'])) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_created',
                'value'   => date('Y-m-d 23:59:59', strtotime($fields['created_end'])),
                'type'    => 'DATE',
                'compare' => '<='
            );
        }

        if (!empty($fields['subscription_level'])) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_level_id',
                'value'   => $fields['subscription_level'],
                'type'    => 'NUMERIC',
                'compare' => 'IN'
            );
        } else {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_level_id',
                'compare' => 'EXISTS'
            );
        }


        if ( !empty($fields['subscriber_status']) ) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_payment_status',
                'value'   => $fields['subscriber_status'],
                'type'    => 'CHAR',
                'compare' => 'IN'
            );
        }

        if (!empty($fields['price'])) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_price',
                'value'   => $fields['price'],
            );
        }
        if (!empty($fields['payment_method'])) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_payment_gateway',
                'value'   => $fields['payment_method'],
                'type'    => 'CHAR',
                'compare' => 'IN'
            );
        }
        if (!empty($fields['subscriber_id'])) {
            $args['meta_query'][] = array(
                'key'     => '_issuem_leaky_paywall_' . $mode . '_subscriber_id',
                'value'   => $fields['subscriber_id'],
                'compare' => 'LIKE',
            );
        }
        if (!empty($fields['custom-meta-key'])) {
            foreach ($fields['custom-meta-key'] as $meta_key => $value) {
                if (
                    !empty($meta_key)
                ) {
                    if (!empty($value)) {
                        $args['meta_query'][] = array(
                            'key'     => '_issuem_leaky_paywall_' . $mode . '_subscriber_meta_' . $meta_key,
                            'value'   => $value,
                            'compare' => 'LIKE',
                        );
                    }
                }
            }
        }

        $args = apply_filters('leaky_paywall_reporting_tool_pre_users', $args, $mode, '_issuem');
        $users = get_users($args);
        return $users;

    }
}

new Leaky_Paywall_Reporting_Tool_Export();
<?php
/**
 * Registers zeen101's Leaky Paywall - Reporting Tool class
 *
 * @package zeen101's Leaky Paywall - Reporting Tool
 * @since 1.0.0
 */

/**
 * This class registers the main issuem functionality
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Leaky_Paywall_Reporting_tool' ) ) {

	class Leaky_Paywall_Reporting_tool {

		/**
		 * Class constructor, puts things in motion
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_wp_enqueue_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'admin_wp_print_styles' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 15 );

			add_action( 'admin_init', array( $this, 'process_requests' ), 15 );

		}

		public function process_requests() {

			if ( is_admin() && current_user_can( apply_filters( 'manage_leaky_paywall_settings', 'manage_options' ) ) ) {

				if ( !empty( $_POST['leaky_paywall_reporting_tool_nonce'] ) ) {

					if ( wp_verify_nonce( $_POST['leaky_paywall_reporting_tool_nonce'], 'submit_leaky_paywall_reporting_tool' ) ) {

						$settings = get_leaky_paywall_settings();
						$mode = 'off' === $settings['test_mode'] ? 'live' : 'test';
						$users = leaky_paywall_reporting_tool_query( $_POST );

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

                        if ( is_plugin_active( 'leaky-paywall-custom-subscriber-fields/issuem-leaky-paywall-subscriber-meta.php' ) ) {
	                        global $dl_pluginissuem_leaky_paywall_subscriber_meta;
	                        $custom_meta_fields = $dl_pluginissuem_leaky_paywall_subscriber_meta->get_settings();
						}

						global $blog_id;
						if ( is_multisite_premium() && !is_main_site( $blog_id ) ){
							$site = '_' . $blog_id;
						} else {
							$site = '';
						}


						if ( !empty( $users ) ) {
							global $is_leaky_paywall, $no_lp_subscribers;
							$no_lp_subscribers = false;

							$user_meta = array();
							foreach( $users as $user ) {
								$user_meta[$user->ID]['user_id'] = $user->ID;
								$user_meta[$user->ID]['user_login'] = $user->data->user_login;
								$user_meta[$user->ID]['user_email'] = $user->data->user_email;
								$user_meta[$user->ID]['first_name'] = $user->first_name;
								$user_meta[$user->ID]['last_name'] = $user->last_name;
								foreach( $meta as $key ) {
									$user_meta[$user->ID][$key] = get_leaky_user_meta( $user->ID, '_leaky_paywall_' . $mode . '_' . $key . $site );
								}
								if ( !empty( $custom_meta_fields['meta_keys'] ) ) {

									foreach( $custom_meta_fields['meta_keys'] as $meta_key ) {
										$user_meta[$user->ID][$meta_key['name']] = get_leaky_user_meta( $user->ID, '_leaky_paywall_' . $mode . '_subscriber_meta_' . sanitize_title_with_dashes( $meta_key['name'] ) . $site );
									}
								}

								$user_meta = apply_filters( 'leaky_paywall_reporting_tool_user_meta', $user_meta, $user->ID );
								
							}

							if ( !empty( $user_meta ) ) {
								leaky_paywall_reporting_tool_csv_export_headers();
								leaky_paywall_reporting_tool_csv_export_file( $user_meta );
								die();
							}
						} else {
							global $no_lp_subscribers;
							$no_lp_subscribers = true;
						}

					} else {

						wp_die( 'Unable to verify Leaky Paywall Reporting Tool security token. Please try again.' );

					}

				}

			}

		}

		public function admin_wp_enqueue_scripts( $hook_suffix ) {
			if ( 'leaky-paywall_page_reporting-tool' === $hook_suffix )
				wp_enqueue_script( 'lp_reporting_tool_admin_js', LP_RT_URL . 'js/admin.js', array( 'jquery', 'jquery-ui-datepicker' ), LP_RT_VERSION );
		}

		public function admin_wp_print_styles() {
			global $hook_suffix;
			if ( 'leaky-paywall_page_reporting-tool' === $hook_suffix ) {
				wp_enqueue_style( 'lp_reporting_tool_admin_css', LP_RT_URL . 'css/admin.css', '', LP_RT_VERSION );
				wp_enqueue_style( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' );
			}
		}

		public function admin_menu() {
			add_submenu_page( 'issuem-leaky-paywall', __( 'Reporting Tool', 'lp-reporting-tool' ), __( 'Reporting Tool', 'lp-reporting-tool' ), apply_filters( 'manage_leaky_paywall_settings', 'manage_options' ), 'reporting-tool', array( $this, 'reporting_page' ) );
		}

		/**
		 * Create and Display Leaky Paywall Reporting Tool page
		 *
		 * @since 1.0.0
		 */
		public function reporting_page() {

			$settings = get_leaky_paywall_settings();
			?>
			<div class=wrap>
            <div style="width:70%;" class="postbox-container">
            <div class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">

                <form id="issuem" method="post" action="">

                    <h2 style='margin-bottom: 10px;' ><?php _e( "Leaky Paywall - Reporting Tool", 'lp-reporting-tool' ); ?></h2>

                    <?php
                    	global $no_lp_subscribers;
						if ( $no_lp_subscribers == true ) {
							echo '<div class="updated">';
							echo '<p>No subscribers matched your search.</p>';
							echo '</div>';
						}
                    ?>

                    <p>1. If a subscriber was created while in test mode, Leaky Paywall must be in test mode to export the subscriber. If a subscriber was created while in live mode, Leaky Paywall must be in live mode to the export the subscriber.</p>

					<p>2. To export all subscribers, leave all fields blank.</p>

                    <div id="modules" class="postbox">

                        <div class="inside">

                        <table id="reporting_tool_table" class="reporting-tool-table form-table">

                        	<tr>
                                <th><?php _e( 'Created Date Range', 'lp-reporting-tool' ); ?></th>
                                <td>
                                	<input type="text" id="created-start" name="created-start" value="" />
                                	&nbsp; &mdash; &nbsp;
                                	<input type="text" id="created-end" name="created-end" value="" />	
                                </td>
                            </tr>
                        	<tr>
                                <th><?php _e( 'Expiration Range', 'lp-reporting-tool' ); ?></th>
                                <td>
                                	<input type="text" id="expire-start" name="expire-start" value="" />
                                	&nbsp; &mdash; &nbsp;
                                	<input type="text" id="expire-end" name="expire-end" value="" />
                                	<?php
                    				$date_format = get_option( 'date_format' );
									$jquery_date_format = leaky_paywall_jquery_datepicker_format( $date_format );
									?>
                                	<input type="hidden" name="date_format" value="<?php echo $jquery_date_format; ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Price', 'lp-reporting-tool' ); ?></th>
                                <td>
                                	<input type="text" id="price" name="price" value="" />
                                </td>
                            </tr>
                        	<tr>
                                <th><?php _e( 'Subscription Level', 'lp-reporting-tool' ); ?></th>
                                <td>
			                        <select name="subscription-level[]" multiple="multiple" size="5">
			                        <?php
			                        foreach( $settings['levels'] as $key => $level ) {
				                        echo '<option value="' . $key .'">' . stripslashes( $level['label'] ) . '</option>';
			                        }
			                        ?>
			                        </select>
	                        	</td>
                            </tr>
                        	<tr>
                                <th><?php _e( 'Subscription Status', 'lp-reporting-tool' ); ?></th>
                                <td>
			                        <select name="subscriber-status[]" multiple="multiple" size="4">
			                            <option value="active"><?php _e( 'Active', 'lp-reporting-tool' ); ?></option>
			                            <option value="canceled"><?php _e( 'Canceled', 'lp-reporting-tool' ); ?></option>
			                            <option value="deactivated"><?php _e( 'Deactivated', 'lp-reporting-tool' ); ?></option>
			                            <option value="expired"><?php _e( 'Expired', 'lp-reporting-tool' ); ?></option>
			                        </select>
                                </td>
                            </tr>
                        	<tr>
                                <th><?php _e( 'Payment Method', 'lp-reporting-tool' ); ?></th>
                                <td>
			                        <select name="payment-method[]" multiple="multiple" size="4">
										<?php
										$payment_gateways = leaky_paywall_payment_gateways();
										foreach( $payment_gateways as $key => $gateway ) {
											echo '<option value="' . $key . '">' . $gateway . '</option>';
										}
										?>
			                        </select>
                                </td>
                            </tr>
                        	<tr>
                                <th><?php _e( 'Subscriber ID', 'lp-reporting-tool' ); ?></th>
                                <td>
                                	<input type="text" id="subscriber-id" name="subscriber-id" value="" />
                                </td>
                            </tr>

                            <?php
	                        if ( is_plugin_active( 'leaky-paywall-custom-subscriber-fields/issuem-leaky-paywall-subscriber-meta.php' ) ) {
		                        global $dl_pluginissuem_leaky_paywall_subscriber_meta;
		                        $custom_meta_fields = $dl_pluginissuem_leaky_paywall_subscriber_meta->get_settings();

		                        if ( !empty( $custom_meta_fields['meta_keys'] ) ) {
			                        foreach ( $custom_meta_fields['meta_keys'] as $meta_key ) {
				                		$label = $meta_key['name'];
					                	$meta_key = sanitize_title_with_dashes( $meta_key['name'] );
				                        ?>
			                        	<tr>
			                                <th><?php echo $label; ?></th>
			                                <td>
						                        <input class="subscriber-meta-key subscriber-<?php echo $meta_key; ?>-meta-key" type="text" value="" name="custom-meta-key[<?php echo $meta_key; ?>]"  />
			                                </td>
			                            </tr>
				                        <?php
			                        }
		                        }
		                    }
                            ?>

                        </table>

                        <p class="submit">
                            <input class="button-primary" type="submit" name="generate_leaky_paywall_report" value="<?php _e( 'Generate Report', 'lp-reporting-tool' ) ?>" />
                        </p>

                        </div>

                    </div>

                    <?php wp_nonce_field( 'submit_leaky_paywall_reporting_tool', 'leaky_paywall_reporting_tool_nonce' ); ?>

                </form>

            </div>
            </div>
            </div>
			</div>
			<?php
		}

	}

}

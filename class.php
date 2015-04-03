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
		function __construct() {
			
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_wp_enqueue_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'admin_wp_print_styles' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
									
			add_action( 'admin_init', array( $this, 'process_requests' ), 15 );

		}
		
		function process_requests() {
			
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
						
                        if ( is_plugin_active( 'leaky-paywall-custom-subscriber-fields/issuem-leaky-paywall-subscriber-meta.php' ) ) { 
	                        global $dl_pluginissuem_leaky_paywall_subscriber_meta;
	                        $custom_meta_fields = $dl_pluginissuem_leaky_paywall_subscriber_meta->get_settings();
						}
						
						if ( !empty( $users ) ) {
							$user_meta = array();
							foreach( $users as $user ) {
								$user_meta[$user->ID]['user_id'] = $user->ID;
								$user_meta[$user->ID]['user_login'] = $user->data->user_login;
								$user_meta[$user->ID]['user_email'] = $user->data->user_email;
								foreach( $meta as $key ) {
									$user_meta[$user->ID][$key] = get_user_meta( $user->ID, '_issuem_leaky_paywall_' . $mode . '_' . $key, true );
								}
								if ( !empty( $custom_meta_fields['meta_keys'] ) ) {
									foreach( $custom_meta_fields['meta_keys'] as $meta_key ) {
										$user_meta[$user->ID][$meta_key['name']] = get_user_meta( $user->ID, '_issuem_leaky_paywall_' . $mode . '_subscriber_meta_' . sanitize_title_with_dashes( $meta_key['name'] ), true );
									}
								}
							}
							
							if ( !empty( $user_meta ) ) {
								leaky_paywall_reporting_tool_csv_export_headers();
								leaky_paywall_reporting_tool_csv_export_file( $user_meta );
								die();
							}
						}
											
					} else {
						
						wp_die( 'Unable to verify Leaky Paywall Reporting Tool security token. Please try again.' );
						
					}
					
				}
				
			}
			
		}
		
		function admin_wp_enqueue_scripts( $hook_suffix ) {
			if ( 'leaky-paywall_page_reporting-tool' === $hook_suffix )
				wp_enqueue_script( 'lp_reporting_tool_admin_js', LP_RT_URL . 'js/admin.js', array( 'jquery', 'jquery-ui-datepicker' ), ISSUEM_LP_UPAPI_VERSION );
		}
		
		function admin_wp_print_styles() {
			global $hook_suffix;
			if ( 'leaky-paywall_page_reporting-tool' === $hook_suffix ) {
				wp_enqueue_style( 'lp_reporting_tool_admin_css', LP_RT_URL . 'css/admin.css', '', ISSUEM_LP_UPAPI_VERSION );
				wp_enqueue_style( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' );
			}
		}
		
		function admin_menu() {
			add_submenu_page( 'issuem-leaky-paywall', __( 'Reporting Tool', 'lp-reporting-tool' ), __( 'Reporting Tool', 'lp-reporting-tool' ), apply_filters( 'manage_leaky_paywall_settings', 'manage_options' ), 'reporting-tool', array( $this, 'reporting_page' ) );
		}
				
		/**
		 * Create and Display Leaky Paywall Reporting Tool page
		 *
		 * @since 1.0.0
		 */
		function reporting_page() {
			
			$settings = get_leaky_paywall_settings();
			?>
			<div class=wrap>
            <div style="width:70%;" class="postbox-container">
            <div class="metabox-holder">	
            <div class="meta-box-sortables ui-sortable">
            
                <form id="issuem" method="post" action="">

                    <h2 style='margin-bottom: 10px;' ><?php _e( "Leaky Paywall - Reporting Tool", 'lp-reporting-tool' ); ?></h2>
                    
                    <div id="modules" class="postbox">
                                            
                        <div class="inside">
                        
                        <table id="reporting_tool_table" class="reporting-tool-table">

                        	<tr>
                                <th><?php _e( 'Price', 'lp-reporting-tool' ); ?></th>
                                <td>
                                	<input type="text" id="price" name="price" value="" />
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
                                <th><?php _e( 'Subscription Level', 'lp-reporting-tool' ); ?></th>
                                <td>
			                        <select name="subscription-level[]" multiple="multiple" size="5">
			                        <?php
			                        foreach( $settings['levels'] as $key => $level ) {
				                        echo '<option value="' . $key .'" ' . selected( $key, $subscriber_level_id, true ) . '>' . stripslashes( $level['label'] ) . '</option>';
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
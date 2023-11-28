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
if (!class_exists('Leaky_Paywall_Reporting_tool')) {

	class Leaky_Paywall_Reporting_tool
	{

		/**
		 * Class constructor, puts things in motion
		 *
		 * @since 1.0.0
		 */
		public function __construct()
		{

			add_action('admin_enqueue_scripts', array($this, 'admin_wp_enqueue_scripts'));
			add_action('admin_print_styles', array($this, 'admin_wp_print_styles'));
			add_action('admin_menu', array($this, 'admin_menu'), 15);
		}

		public function admin_wp_enqueue_scripts($hook_suffix)
		{
			if ('leaky-paywall_page_reporting-tool' === $hook_suffix)
				wp_enqueue_script('lp_reporting_tool_admin_js', LP_RT_URL . 'js/admin.js', array('jquery', 'jquery-ui-datepicker'), LP_RT_VERSION);
		}

		public function admin_wp_print_styles()
		{
			global $hook_suffix;
			if ('leaky-paywall_page_reporting-tool' === $hook_suffix) {
				wp_enqueue_style('lp_reporting_tool_admin_css', LP_RT_URL . 'css/admin.css', '', LP_RT_VERSION);
				wp_enqueue_style('jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
			}
		}

		public function admin_menu()
		{
			add_submenu_page('issuem-leaky-paywall', __('Reporting Tool', 'lp-reporting-tool'), __('Reporting Tool', 'lp-reporting-tool'), apply_filters('manage_leaky_paywall_settings', 'manage_options'), 'reporting-tool', array($this, 'reporting_page'));
		}

		/**
		 * Create and Display Leaky Paywall Reporting Tool page
		 *
		 * @since 1.0.0
		 */
		public function reporting_page()
		{

			$settings = get_leaky_paywall_settings();
?>
			<div class=wrap>
				<div style="width:70%;" class="postbox-container">
					<div class="metabox-holder">
						<div class="meta-box-sortables ui-sortable">

							<form id="leaky-paywall-reporting-tool-form" method="post" action="">

								<h2 style='margin-bottom: 10px;'><?php _e("Leaky Paywall - Reporting Tool", 'lp-reporting-tool'); ?></h2>

								<?php
								global $no_lp_subscribers;
								if ($no_lp_subscribers == true) {
									echo '<div class="updated">';
									echo '<p>No subscribers matched your search.</p>';
									echo '</div>';
								}
								?>

								<p>1. If a subscriber was created while in test mode, Leaky Paywall must be in test mode to export the subscriber. If a subscriber was created while in live mode, Leaky Paywall must be in live mode to the export the subscriber.</p>

								<p>2. To export all subscribers, leave all fields blank.</p>

								<div id="modules">

									<table id="reporting_tool_table" class="reporting-tool-table form-table">

										<tr>
											<th><?php _e('Created Date Range', 'lp-reporting-tool'); ?></th>
											<td>
												<input type="text" id="created-start" name="created_start" value="" />
												&nbsp; &mdash; &nbsp;
												<input type="text" id="created-end" name="created_end" value="" />
											</td>
										</tr>
										<tr>
											<th><?php _e('Expiration Range', 'lp-reporting-tool'); ?></th>
											<td>
												<input type="text" id="expire-start" name="expire_start" value="" />
												&nbsp; &mdash; &nbsp;
												<input type="text" id="expire-end" name="expire_end" value="" />
												<?php
												$date_format = get_option('date_format');
												$jquery_date_format = leaky_paywall_jquery_datepicker_format($date_format);
												?>
												<input type="hidden" name="date_format" value="<?php echo $jquery_date_format; ?>" />
											</td>
										</tr>
										<tr>
											<th><?php _e('Price', 'lp-reporting-tool'); ?></th>
											<td>
												<input type="text" id="price" name="price" value="" />
											</td>
										</tr>
										<tr>
											<th><?php _e('Subscription Level', 'lp-reporting-tool'); ?></th>
											<td>
												<select name="subscription_level[]" multiple="multiple" size="5">
													<?php
													foreach ($settings['levels'] as $key => $level) {
														echo '<option value="' . $key . '">' . stripslashes($level['label']) . '</option>';
													}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<th><?php _e('Payment Status', 'lp-reporting-tool'); ?></th>
											<td>
												<select name="subscriber_status[]" multiple="multiple" size="4">
													<option value="active"><?php _e('Active', 'lp-reporting-tool'); ?></option>
													<option value="canceled"><?php _e('Canceled', 'lp-reporting-tool'); ?></option>
													<option value="deactivated"><?php _e('Deactivated', 'lp-reporting-tool'); ?></option>
													<option value="trial"><?php _e('Trial', 'lp-reporting-tool'); ?></option>
													<option value="expired"><?php _e('Expired', 'lp-reporting-tool'); ?></option>
												</select>

												<p class="description">For more details on what Leaky Paywall payment statuses mean, <a target="_blank" href="https://docs.leakypaywall.com/article/70-i-have-an-expired-subscriber-that-has-an-active-status-can-they-get-access">please read our documentation</a>.
											</td>
										</tr>
										<tr>
											<th><?php _e('Payment Method', 'lp-reporting-tool'); ?></th>
											<td>
												<select name="payment_method[]" multiple="multiple" size="4">
													<?php
													$payment_gateways = leaky_paywall_payment_gateways();
													foreach ($payment_gateways as $key => $gateway) {
														echo '<option value="' . $key . '">' . $gateway . '</option>';
													}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<th><?php _e('Subscriber ID', 'lp-reporting-tool'); ?></th>
											<td>
												<input type="text" id="subscriber-id" name="subscriber_id" value="" />
											</td>
										</tr>

										<?php

										if (function_exists('leaky_paywall_gift_subscription_generate_unique_code')) {
										?>
											<tr>
												<th><?php _e('Only Export Gift Subscriptions', 'lp-reporting-tool'); ?></th>
												<td>
													<select name="gift_subscriptions" id="gift_subscriptions">
														<option value="0">No</option>
														<option value="1">Yes</option>
													</select>

												</td>
											</tr>
										<?php
										} ?>

										<?php
										if (is_plugin_active('leaky-paywall-custom-subscriber-fields/issuem-leaky-paywall-subscriber-meta.php')) {
											global $dl_pluginissuem_leaky_paywall_subscriber_meta;
											$custom_meta_fields = $dl_pluginissuem_leaky_paywall_subscriber_meta->get_settings();

											if (!empty($custom_meta_fields['meta_keys'])) {
												foreach ($custom_meta_fields['meta_keys'] as $meta_key) {
													$label = $meta_key['name'];
													$meta_key = sanitize_title_with_dashes($meta_key['name']);
										?>
													<tr>
														<th><?php echo $label; ?></th>
														<td>
															<input class="subscriber-meta-key subscriber-<?php echo $meta_key; ?>-meta-key" type="text" value="" name="custom-meta-key[<?php echo $meta_key; ?>]" />
														</td>
													</tr>
										<?php
												}
											}
										}
										?>

									</table>

									<p class="submit">
										<input class="button-primary" type="submit" id="leaky-paywall-reporting-tool-submit" name="generate_leaky_paywall_report" value="<?php _e('Generate Report', 'lp-reporting-tool') ?>" />
									</p>

									<p id="leaky-paywall-reporting-tool-message"></p>



								</div>

								<?php wp_nonce_field('submit_leaky_paywall_reporting_tool', 'leaky_paywall_reporting_tool_nonce'); ?>

							</form>

						</div>
					</div>
				</div>
			</div>
<?php
		}
	}
}

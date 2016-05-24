<?php

if ( !function_exists( 'leaky_paywall_reporting_tool_query' ) ){

	/**
	 * Gets leaky paywall subscribers
	 *
	 * @since 1.1.0
	 *
	 * @param array $post POST data from reporting form
	 * @return mixed $wpdb var or false if invalid hash
	 */
	function leaky_paywall_reporting_tool_query( $post ) {
		global $is_leaky_paywall, $which_leaky_paywall;

		if ( !empty( $post ) ) {

			$args = array(
				'role'	=> 'subscriber'
			);

			$settings = get_leaky_paywall_settings();
			$mode = 'off' === $settings['test_mode'] ? 'live' : 'test';

			if ( !empty( $post['expire-start' ] ) ) {
				$args['meta_query'][] = array(
					'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_expires',
					'value'   => date( 'Y-m-d 23:59:59', strtotime( $post['expire-start' ] ) ),
					'type'    => 'DATE',
					'compare' => '>='
				);
			}
			if ( !empty( $post['expire-end' ] ) ) {
				$args['meta_query'][] = array(
					'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_expires',
					'value'   => date( 'Y-m-d 23:59:59', strtotime( $post['expire-end' ] ) ),
					'type'    => 'DATE',
					'compare' => '<='
				);
			}

			if ( !empty( $post['subscription-level'] ) ) {
				$args['meta_query'][] = array(
					'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_level_id',
					'value'   => $post['subscription-level'],
					'type'    => 'NUMERIC',
					'compare' => 'IN'
				);
			}
			if ( !empty( $post['subscriber-status'] ) ) {
				$args['meta_query'][] = array(
					'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_payment_status',
					'value'   => $post['subscriber-status'],
					'type'    => 'CHAR',
					'compare' => 'IN'
				);
			}

			if ( !empty( $post['price'] ) ) {
				$args['meta_query'][] = array(
					'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_price',
					'value'   => $post['price'],
				);
			}
			if ( !empty( $post['payment-method'] ) ) {
				$args['meta_query'][] = array(
					'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_payment_gateway',
					'value'   => $post['payment-method'],
					'type'    => 'CHAR',
					'compare' => 'IN'
				);
			}
			if ( !empty( $post['subscriber-id'] ) ) {
				$args['meta_query'][] = array(
					'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_subscriber_id',
					'value'   => $post['subscriber-id'],
					'compare' => 'LIKE',
				);
			}
			if ( !empty( $post['custom-meta-key'] ) ) {
				foreach( $post['custom-meta-key'] as $meta_key => $value ) {
					if ( !empty( $meta_key ) ) {
						if ( !empty( $value ) ) {
							$args['meta_query'][] = array(
								'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_subscriber_meta_' . $meta_key,
								'value'   => $value,
								'compare' => 'LIKE',
							);
						}
					}
				}
			}

			$users = get_users( $args );
			return $users;

		}

		return false;

	}

}

if ( !function_exists( 'leaky_paywall_reporting_tool_csv_export_headers' ) ) {

	function leaky_paywall_reporting_tool_csv_export_headers() {

	    $now = gmdate("D, d M Y H:i:s");
	    $filename = 'leaky-paywall-report-' . time() . '.csv';
	    header( 'Expires: Tue, 03 Jul 2001 06:00:00 GMT' );
	    header( 'Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate ');
	    header( 'Last-Modified: ' . $now . ' GMT ');

	    // force download
	    header( 'Content-Type: application/force-download' );
	    header( 'Content-Type: application/octet-stream' );
	    header( 'Content-Type: application/download' );

	    // disposition / encoding on response body
	    header( 'Content-Disposition: attachment;filename="' . $filename . '"' );
	    header( 'Content-Transfer-Encoding: binary' );

	}
}

if ( !function_exists( 'leaky_paywall_reporting_tool_csv_export_file' ) ) {

	function leaky_paywall_reporting_tool_csv_export_file( $content_array ) {

		if ( 0 == count( $content_array ) ) {
			return null;
		}

		ob_start();
		$f = fopen( 'php://output', 'w' );
		fputcsv( $f, array_keys( reset( $content_array ) ) );
		foreach ( $content_array as $row ) {
			fputcsv( $f, $row );
		}
		fclose( $f );

		echo ob_get_clean();

	}
}

/**
 * Get user meta, attempting with and without the `_issuem` prefix.
 *
 * There was a period where several plugins were setting user meta information
 * without the _issuem prefix due to a bad check. This function compensates
 * for this inconsistency by first attempting to grab user meta values with the
 * $which_leaky_paywall prefix and if none exists, trying for an unprefixed version.
 *
 * @see get_user_meta
 * @global string $which_leaky_paywall user meta prefix
 *
 * @param int $user_id User ID.
 * @param string $key Desired main key string without prefix.
 * @return string Meta value.
 */
if ( !function_exists( 'get_leaky_user_meta' ) ) {
	function get_leaky_user_meta( $user_id, $key ){
		global $which_leaky_paywall;

		// Try for the new meta string first
		$meta = get_user_meta( $user_id, $which_leaky_paywall . $key, true );

		// If that returned nothing, try for an un-prefixed meta string
		if ( empty( $meta ) ){
			$meta = get_user_meta( $user_id, $key, true );
		}

		// Return whichever result returned, if any
		return $meta;

	}
}

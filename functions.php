<?php
	
if ( !function_exists( 'leaky_paywall_reporting_tool_query' ) ){

	/**
	 * Gets leaky paywall subscribers
	 *
	 * @since 1.1.0
	 *
	 * @param array $args Leaky Paywall Subscribers
	 * @return mixed $wpdb var or false if invalid hash
	 */
	function leaky_paywall_reporting_tool_query( $post ) {
		global $is_leaky_paywall, $which_leaky_paywall;
	
		if ( !empty( $post ) ) {
			
			$settings = get_leaky_paywall_settings();
			$mode = 'off' === $settings['test_mode'] ? 'live' : 'test';
			$args['meta_query'] = array( 
				'relation' => 'AND',
				array(
					'key'     => $which_leaky_paywall . '_leaky_paywall_' . $mode . '_hash',
					'compare' => 'EXISTS',
				),
			);
			
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

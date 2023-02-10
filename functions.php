<?php

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

		$which_leaky_paywall = '_issuem';
		$mode = leaky_paywall_get_current_mode();

		// Try for the new meta string first
		$meta = get_user_meta( $user_id, $which_leaky_paywall . $key, true );

		// If that returned nothing, try for an un-prefixed meta string
		if ( empty( $meta ) ){
				$meta = get_user_meta( $user_id, $which_leaky_paywall . $key . '_all', true);

			if( empty( $meta ) ){
			$meta = get_user_meta( $user_id, $key, true );
			}
		}

		if ( $key == '_leaky_paywall_' . $mode . '_created' ) {

			if ( is_numeric($meta) && (int)$meta == $meta ) { // is timestamp
				$date = date( 'Y-M-d H:i:s', $meta );
				$meta = $date;
			}

			if ( !$meta ) {
				// no created date found, so use their WP registration date
				$user = get_user_by('id', $user_id );
				$meta = $user->user_registered;
			}
		}

		return $meta;

	}
}

<?php
/**
 * Deprecated hooks, filters and functions
 *
 * @since  2.0
 */

/**
 * Check for deprecated filters.
 */
function pmpro_init_check_for_deprecated_filters() {
	global $wp_filter;
	
	$pmpro_map_deprecated_filters = array(
		'pmpro_getfile_extension_blocklist'    => 'pmpro_getfile_extension_blacklist',
	);
	
	foreach ( $pmpro_map_deprecated_filters as $new => $old ) {
		if ( has_filter( $old ) ) {
			/* translators: 1: the old hook name, 2: the new or replacement hook name */
			trigger_error( sprintf( esc_html__( 'The %1$s hook has been deprecated in Paid Memberships Pro. Please use the %2$s hook instead.', 'paid-memberships-pro' ), $old, $new ) );
			
			// Add filters back using the new tag.
			foreach( $wp_filter[$old]->callbacks as $priority => $callbacks ) {
				foreach( $callbacks as $callback ) {
					add_filter( $new, $callback['function'], $priority, $callback['accepted_args'] ); 
				}
			}
		}
	}
}
add_action( 'init', 'pmpro_init_check_for_deprecated_filters', 99 );

/**
 * Previously used function for class definitions for input fields to see if there was an error.
 *
 * To filter field values, we now recommend using the `pmpro_element_class` filter.
 *
 */
function pmpro_getClassForField( $field ) {
	return pmpro_get_element_class( '', $field );
}

/**
 * Redirect some old menu items to their new location
 */
function pmpro_admin_init_redirect_old_menu_items() {	
	if ( is_admin()
		&& ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pmpro_license_settings'
		&& basename( $_SERVER['SCRIPT_NAME'] ) == 'options-general.php' ) {
		wp_safe_redirect( admin_url( 'admin.php?page=pmpro-license' ) );
		exit;
	}
}
add_action( 'init', 'pmpro_admin_init_redirect_old_menu_items' );

// Check if installed, deactivate it and show a notice now.
function pmpro_check_for_deprecated_add_ons() {

	$deprecated = array(
		'pmpro-member-history' => array(
			'file' => 'pmpro-member-history.php',
			'label' => 'Member History Add On'
		)
	);
	
	$deprecated = apply_filters( 'pmpro_deprecated_add_ons_list', $deprecated );
	
	// If the list is empty or not an array, just bail.
	if ( empty( $deprecated ) || ! is_array( $deprecated ) ) {
		return;
	}
	
	$deprecated_active = array();
	foreach( $deprecated as $key => $values ) {
		$path = '/' . $key . '/' . $values['file'];
		if ( file_exists( WP_PLUGIN_DIR . $path ) ) {
			$deprecated_active[] = $values['label'];

			// Try to deactivate it if it's enabled.
			if ( is_plugin_active( plugin_basename( $path ) ) ) {
				deactivate_plugins( $path );
			}
		}
	}

	// If any deprecated add ons are active, show warning.
	if ( is_array( $deprecated_active ) && ! empty( $deprecated_active ) ) {
		// Only show on certain pages.
		if ( ! isset( $_REQUEST['page'] ) || strpos( $_REQUEST['page'], 'pmpro' ) === false  ) {
			return;
		}
		?>
		<div class="notice notice-warning">
        <p><?php _e( sprintf( 'Some of the PMPro Add Ons on your site have been merged into the core PMPro plugin or are otherwise not needed. <br />The following plugins <strong>should be deleted</strong> from your site: <strong>%s</strong>.', implode( ', ', $deprecated_active ) ), 'paid-memberships-pro' ); ?></p>
    	</div>
		<?php
	}
}
add_action( 'admin_notices', 'pmpro_check_for_deprecated_add_ons' );

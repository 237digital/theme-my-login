<?php
/*
Plugin Name: Themed Profiles
Description: Enabling this module will initialize and enable themed profiles. There are no other settings for this module.
*/

add_action('tml_init', 'wdbj_tml_themed_profiles_init');
function wdbj_tml_themed_profiles_init() {
	global $current_user, $action, $redirect, $profile, $user_id, $wp_http_referer;
	
	require_once( TML_MODULE_DIR . '/themed-profiles/includes/template-functions.php' );
	
	require_once( ABSPATH . 'wp-admin/includes/misc.php' );
	require_once( ABSPATH . 'wp-admin/includes/template.php' );
	require_once( ABSPATH . 'wp-admin/includes/user.php' );
	require_once( ABSPATH . WPINC . '/registration.php' );
	
	// Needed to make admin scripts available
	define('WP_ADMIN', true);
	define('IS_PROFILE_PAGE', true);
	
	wp_enqueue_style('themed-profiles', plugins_url('theme-my-login/modules/themed-profiles/themed-profiles.css'));
	
	wp_enqueue_script('user-profile');
	wp_enqueue_script('password-strength-meter');
	
	wp_reset_vars(array('action', 'redirect', 'profile', 'user_id', 'wp_http_referer'));

	$wp_http_referer = remove_query_arg(array('update', 'delete_count'), stripslashes($wp_http_referer));
	
	if ( 'update' == $action ) {
		check_admin_referer('update-user_' . $current_user->ID);

		if ( !current_user_can('edit_user', $current_user->ID) )
			wp_die(__('You do not have permission to edit this user.', 'theme-my-login'));

		do_action('personal_options_update', $current_user->ID);

		$errors = edit_user($current_user->ID);

		if ( !is_wp_error( $errors ) ) {
			$redirect = add_query_arg(array('updated' => 'true', 'wp_http_referer' => urlencode($wp_http_referer)));
			wp_redirect($redirect);
			exit;
		} else wdbj_tml_set_error($errors);
	}
}

add_filter('the_content', 'wdbj_tml_themed_profiles_content');
function wdbj_tml_themed_profiles_content($content) {
	if ( is_page( wdbj_tml_get_option('page_id') ) && is_user_logged_in() && 'profile' == wdbj_tml_get_var('request_action') )
		return wdbj_tml_themed_profiles_display();
	return $content;
}

add_action('init', 'wdbj_tml_themed_profiles_template_redirect');
function wdbj_tml_themed_profiles_template_redirect() {
	global $pagenow;
	if ( 'profile.php' == $pagenow ) {
		$redirect_to = add_query_arg( 'action', 'profile', get_page_link(wdbj_tml_get_option('page_id')) );
		$redirect_to = add_query_arg( $_GET, $redirect_to );
		wp_redirect($redirect_to);
		exit;
	}
}

add_filter('site_url', 'wdbj_tml_themed_profiles_site_url', 10, 3);
add_filter('admin_url', 'wdbj_tml_themed_profiles_site_url', 10, 2);
function wdbj_tml_themed_profiles_site_url($url, $path, $orig_scheme = '') {
	global $wp_rewrite, $current_user;
	
	if ( is_user_logged_in() ) {
		if ( strpos($url, 'profile.php') !== false ) {
			$orig_url = $url;
			$url = add_query_arg('action', 'profile', get_permalink(wdbj_tml_get_option('page_id')));
			if ( strpos($orig_url, '?') ) {
				$query = substr($orig_url, strpos($orig_url, '?') + 1);
				parse_str($query, $r);
				$url = add_query_arg($r, $url);
			}
		}
	}
	return $url;
}

add_filter('tml_title', 'wdbj_tml_themed_profiles_title', 10, 2);
function wdbj_tml_themed_profiles_title($title, $action) {
	if ( 'profile' == $action && is_user_logged_in() && '' == wdbj_tml_get_var('current_instance', 'instance_id') )
		$title = __('Your Profile', 'theme-my-login');
	return $title;
}

?>
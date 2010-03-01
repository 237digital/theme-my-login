<?php

function jkf_tml_custom_email_headers() {	
	add_filter('wp_mail_from', 'jkf_tml_custom_email_from');
	add_filter('wp_mail_from_name', 'jkf_tml_custom_email_from_name');
	add_filter('wp_mail_content_type', 'jkf_tml_custom_email_content_type');
}

function jkf_tml_custom_email_retrieve_pass_filters($user_login) {
	jkf_tml_custom_email_headers();
	add_filter('retrieve_password_title', 'jkf_tml_custom_email_retrieve_pass_title', 10, 2);
	add_filter('retrieve_password_message', 'jkf_tml_custom_email_retrieve_pass_message', 10, 3);
}

function jkf_tml_custom_email_reset_pass_filters($user, $new_pass) {
	jkf_tml_custom_email_headers();
	add_filter('password_reset_title', 'jkf_tml_custom_email_reset_pass_title', 10, 2);
	add_filter('password_reset_message', 'jkf_tml_custom_email_reset_pass_message', 10, 3);
	add_filter('password_change_notification', 'jkf_tml_custom_email_reset_pass_disable');
}

function jkf_tml_custom_email_new_user_filters($user_id, $user_pass) {
	jkf_tml_custom_email_headers();
	add_filter('new_user_notification_title', 'jkf_tml_custom_email_new_user_title', 10, 2);
	add_filter('new_user_notification_message', 'jkf_tml_custom_email_new_user_message', 10, 3);
	add_filter('new_user_admin_notification', 'jkf_tml_custom_email_new_user_admin_disable');
}

function jkf_tml_custom_email_from($from_email) {
    $_from_email = jkf_tml_get_option('email', 'mail_from');
    return empty($_from_email) ? $from_email : $_from_email;
}
    
function jkf_tml_custom_email_from_name($from_name) {
    $_from_name = jkf_tml_get_option('email', 'mail_from_name');
    return empty($_from_name) ? $from_name : $_from_name;
}

function jkf_tml_custom_email_content_type($content_type) {
    $_content_type = jkf_tml_get_option('email', 'mail_content_type');
    return empty($_content_type) ? $content_type : 'text/' . $_content_type;
}

function jkf_tml_custom_email_retrieve_pass_title($title, $user_id) {
	$_title = jkf_tml_get_option('email', 'retrieve_pass', 'title');
	return empty($_title) ? $title : jkf_tml_custom_email_replace_vars($_title, $user_id);
}

function jkf_tml_custom_email_retrieve_pass_message($message, $key, $user_id) {
	$_message = jkf_tml_get_option('email', 'retrieve_pass', 'message');
	$user = get_userdata($user_id);
	$replacements = array(
		'%loginurl%' => site_url('wp-login.php', 'login'),
		'%reseturl%' => site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login')
		);
	return empty($_message) ? $message : jkf_tml_custom_email_replace_vars($_message, $user_id, $replacements);
}

function jkf_tml_custom_email_reset_pass_title($title, $user_id) {
	$_title = jkf_tml_get_option('email', 'reset_pass', 'title');
	return empty($_title) ? $title : jkf_tml_custom_email_replace_vars($_title, $user_id);
}

function jkf_tml_custom_email_reset_pass_message($message, $new_pass, $user_id) {
	$_message = jkf_tml_get_option('email', 'reset_pass', 'message');
	$replacements = array(
		'%loginurl%' => site_url('wp-login.php', 'login'),
		'%user_pass%' => $new_pass
		);	
	return empty($_message) ? $message : jkf_tml_custom_email_replace_vars($_message, $user_id, $replacements);
}

function jkf_tml_custom_email_reset_pass_disable($enable) {
	return ( jkf_tml_get_option('email', 'reset_pass', 'admin_disable') ) ? 0 : 1;
}

function jkf_tml_custom_email_new_user_title($title, $user_id) {
	$_title = jkf_tml_get_option('email', 'new_user', 'title');
	return empty($_title) ? $title : jkf_tml_custom_email_replace_vars($_title, $user_id);
}

function jkf_tml_custom_email_new_user_message($message, $new_pass, $user_id) {
	$_message = jkf_tml_get_option('email', 'new_user', 'message');
	$replacements = array(
		'%loginurl%' => site_url('wp-login.php', 'login'),
		'%user_pass%' => $new_pass
		);	
	return empty($_message) ? $message : jkf_tml_custom_email_replace_vars($_message, $user_id, $replacements);
}

function jkf_tml_custom_email_new_user_admin_disable($enable) {
	return ( jkf_tml_get_option('email', 'new_user', 'admin_disable') ) ? 0 : 1;
}

function jkf_tml_custom_email_replace_vars($text, $user_id = '', $replacements = array()) {
	// Get user data
	if ( $user_id )
		$user = get_userdata($user_id);
		
	// Get all matches ($matches[0] will be '%value%'; $matches[1] will be 'value')
	preg_match_all('/%([^%]*)%/', $text, $matches);
		
	// Iterate through matches
	foreach ( $matches[0] as $key => $match ) {
		if ( isset($replacements[$match]) )
			continue;		
		if ( isset($user) && isset($user->{$matches[1][$key]}) )
			$replacements[$match] = $user->{$matches[1][$key]};
		else
			$replacements[$match] = get_bloginfo($matches[1][$key]);
	}
	return str_replace(array_keys($replacements), array_values($replacements), $text);
}

?>
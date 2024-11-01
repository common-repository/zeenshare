<?php
/*
Plugin Name: ZeenShare
Plugin URI: https://zeenshare.com
Description: ZeenShare integration with wordpress. Access ZeenShare workspace directly from your website.
Version: 1.0.1
Author: Stanislas Giraudet
Author URI:  https://zeenshare.com
*/

include('zeenshare_api_curl.php');
include('zeenshare_widget.php');



// Now we set that function up to execute when the admin_notices action is called
add_action('admin_menu', 'zs_insert_menu');
add_action('user_register', 'zs_user_register');
add_action('wp_logout', 'zs_wp_logout');


add_action('get_header', 'zs_get_header');


// register Foo_Widget widget


add_action("widgets_init", array('Zeenshare_Widget', 'register'));


function get_zs_user() {
	$current_user = wp_get_current_user();
	
	$zs_user = new zs_user();
	$zs_user->email = $current_user->user_email;
	
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	
	$zs_map_role = get_option('zs_map_'.$user_role);
	if(!$zs_map_role) {
		$zs_map_role = 'TEAM_CLIENT';
	}
	$zs_user->role = $zs_map_role;
	
	return $zs_user;
	
}


function zs_get_home_url() {
	$zs_domain_name =  get_option('zs_domain_name');
	if(isset($_REQUEST["zs_sid"]) && $_REQUEST["zs_sid"] != null) {
		$zs_sid = $_REQUEST["zs_sid"];
		return 'https://'.$zs_domain_name.'.zeenshare.com/home?sid='.$zs_sid;
	}else if(isset($_COOKIE["zs_sid"]) && $_COOKIE["zs_sid"] != null) {
		$zs_sid = $_COOKIE["zs_sid"];
		return 'https://'.$zs_domain_name.'.zeenshare.com/home?sid='.$zs_sid;
	} else {
		return 'https://'.$zs_domain_name.'.zeenshare.com';
	}
}


function zs_insert_menu() {
	add_options_page('ZeenShare options', 'ZeenShare', 'manage_options', 'zeenshare-options', 'zeenshare_options');
}

function zeenshare_options() {
	include('zeenshare_admin.php');
}


function zs_user_register($user_id) {

	$zs_enable = get_option('zs_enable');
	if(!$zs_enable) {
		return;
	}

	$wp_user = get_userdata($user_id);
	$zs_user = new zs_user();
	$zs_user->email = $wp_user->user_email;
	
	$user = zs_create_user($zs_user);
}


//check before the construction of the header, that we have opened a ZeenShare session for the user, if needed.
//when created, the session id is stored on a cookie.
function zs_get_header() {

	$zs_enable = get_option('zs_enable');
	if(!$zs_enable) {
		return;
	}
	$current_user = wp_get_current_user();

	//first check cookies
	if ( 0 == $current_user->ID ) {
		setcookie("zs_sid", NULL, -1, COOKIEPATH, COOKIE_DOMAIN);
		setcookie("zs_uid", NULL, -1, COOKIEPATH, COOKIE_DOMAIN);
		
		$_REQUEST["zs_sid"] = null;

	} else {
		$create_new_session = false;
		if(!isset($_COOKIE['zs_uid']) || !isset($_COOKIE['zs_sid'])) {
			$create_new_session = true;
		} else {
			$zs_uid = $_COOKIE['zs_uid'];
			if($zs_uid != $current_user->ID) {
				$create_new_session = true;
			}
		}
		$sid = NULL;
		if($create_new_session) {

			$zs_user = get_zs_user();;

			$session = zs_open_user_session($zs_user);
			$sid = $session->sessionId;
			setcookie("zs_sid", $sid, (time() + (3600*24*7)), COOKIEPATH, COOKIE_DOMAIN);
			setcookie("zs_uid", $current_user->ID, (time() + (3600*24*7)), COOKIEPATH, COOKIE_DOMAIN);
				
			//$current_user->ID
		} else {
			$sid = $_COOKIE['zs_sid'];
		}
		$_REQUEST["zs_sid"] = $sid;
		
		$zs_user = new zs_user();
		$zs_user->email = $current_user->user_email;
	}
}



function zs_wp_logout() {
	$zs_enable = get_option('zs_enable');
	if(!$zs_enable) {
		return;
	}
	if(isset($_COOKIE['zs_sid'])) {
		zs_close_user_session($_COOKIE['zs_sid']);
	}
	setcookie("zs_sid", "", (time() - (3600*24*7)), COOKIEPATH, COOKIE_DOMAIN);
	setcookie("zs_uid", "", (time() - (3600*24*7)), COOKIEPATH, COOKIE_DOMAIN);
}


add_action('wp', 'zs_init');


function zs_init() {
	if(isset($_GET['zs_login']) &&$_GET['zs_login'] == '1' ) {
		zs_login_page();
	} else if(isset($_GET['zs_logout']) &&$_GET['zs_logout'] == '1' ) {
		zs_logout_page();
	} 
	
}

function zs_logout_page() {
	wp_logout();
	wp_redirect(home_url()); 
	exit;
}

function zs_login_page() {
	
	$zs_enable = get_option('zs_enable');
	if(!$zs_enable) {
		return;
	}
	
	$current_user = wp_get_current_user();
	if ( 0 == $current_user->ID ) {
		setcookie("zs_sid", NULL, -1, COOKIEPATH, COOKIE_DOMAIN);
		setcookie("zs_uid", NULL, -1, COOKIEPATH, COOKIE_DOMAIN);
	
		$_REQUEST["zs_sid"] = null;
		include 'login_redirect.php'; 
	
	} else {
		$create_new_session = false;
		
		if(!isset($_COOKIE['zs_uid']) || !isset($_COOKIE['zs_sid'])) {
			$create_new_session = true;
		} else {
			$zs_uid = $_COOKIE['zs_uid'];
			if($zs_uid != $current_user->ID) {
				$create_new_session = true;
			}
		}
		if( !isset($_GET['zs_hasNewSession'])) {
			$create_new_session = true;
		}
		
		$sid = NULL;
		$zs_user = get_zs_user();;
		if($create_new_session) {
			$session = zs_open_user_session($zs_user);
			$sid = $session->sessionId;
			setcookie("zs_sid", $sid, (time() + (3600*24*7)), COOKIEPATH, COOKIE_DOMAIN);
			setcookie("zs_uid", $current_user->ID, (time() + (3600*24*7)), COOKIEPATH, COOKIE_DOMAIN);
	
			//$current_user->ID
		} else {
			$sid = $_COOKIE['zs_sid'];
		}
		$_REQUEST["zs_sid"] = $sid;
		include 'redirect_to_zeenshare.php';
	}
	exit;
}

?>

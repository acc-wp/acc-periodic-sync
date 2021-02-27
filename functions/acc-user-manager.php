<?php


// Move Pages above Media
add_action( 'init', 'kfg_adapt_acc_adminpage', 100 );

function kfg_adapt_acc_adminpage(){
	remove_action( 'admin_menu', 'accUM_add_menu_page' );
	add_action( 'admin_menu', 'kfg_accUM_add_menu_page', 11 );
	
	add_action( 'kfg_acc_expiry_check', 'kfg_acc_expiry_check' );
	add_action( 'kfg_acc_update_users', 'kfg_acc_update_users' );

	add_action('wp_authenticate_user', 'check_validation_status', 10, 2);

}


add_filter( 'cron_schedules', 'cron_add_weekly' );

function cron_add_weekly( $schedules ) {
	// Adds once weekly to the existing schedules.
	$schedules['toute_minute'] = array(
		'interval' => 30, // 60 secondes
		'display' => __( 'Toutes les minutes' )
	);
	return $schedules;
}

add_action( 'user_register', 'kfg_welcome_email', 10, 1);

function kfg_accUM_add_menu_page () {
	add_users_page( 
		'ACC Administration',		//Title
		'ACC Admin',				//Menu Title
		'edit_users',				//Capability
		'acc_admin_page',			//Slug
		'kfg_add_html_in_acc'		//Callback
	);
	add_options_page( 
		'ACC Cron Jobs',			//Title
		'Cron Manager',				//Menu Title
		'edit_users',				//Capability
		'acc_cron_list',			//Slug
		'kfg_cron_settings_lite'	//Callback
	);
	add_options_page( 
		'ACC Email Templates',		//Title
		'Email Templates',			//Menu Title
		'edit_users',				//Capability
		'email_templates',			//Slug
		'kfg_email_settings'		//Callback
	);
}


function kfg_add_html_in_acc() {
	accUM_render_options_pages();
	require_once (KFG_BASE_DIR . '/template/acc_logs.php');
	// do_action("kfg_acc_expiry_check");
}



function kfg_cron_settings_lite() {
	require_once (KFG_BASE_DIR . '/template/cron_settings.php');
}



function kfg_email_settings() {
	require_once (KFG_BASE_DIR . '/template/email_settings.php');
}


 
function kfg_cron_activate() {
    wp_schedule_event( time(), "toute_minute", 'kfg_acc_expiry_check' );
    wp_schedule_event( time(), "toute_minute", 'kfg_acc_update_users' );
}


function kfg_cron_deactivate() {
    $timestamp = wp_next_scheduled( 'kfg_acc_expiry_check' );
    wp_unschedule_event( $timestamp, 'kfg_acc_expiry_check' );

    $timestamp = wp_next_scheduled( 'kfg_acc_update_users' );
    wp_unschedule_event( $timestamp, 'kfg_acc_update_users' );

        wp_unschedule_hook("kfg_acc_update_users");
        wp_unschedule_hook("kfg_acc_expiry_check");
        wp_clear_scheduled_hook("kfg_acc_update_users");
        wp_clear_scheduled_hook("kfg_acc_expiry_check");
}



//CRON FUNCTIONS

	//code du dev de François
	//adds check if member expiry date is in the past
	function check_validation_status($user, $password) {
	    $userID = $user->ID;

	    $wp_caps = get_user_meta( $userID, 'wp_capabilities', 'true' );
	    $role = array_keys((array)$wp_caps);

	    if($role[0] == "administrator") {
	    	return $user;
	    }

	    $expiry= get_user_meta( $userID, 'expiry', 'true' );

	    if(($expiry=='')){
	      
	   		 return $user;
	      }

	    elseif($expiry < date("Y-m-d")){
	      $error = new WP_Error();
	      $error->add( 403, 'Oops. Your membership has expired, please renew your membership at <a href="https://www.alpineclubofcanada.ca">www.alpineclubofcanada.ca</a>.' );
	      return $error;
	    }
	    
	    return $user;
	}


function kfg_check_validation_status($user) {
    $userID = $user->ID;

    $wp_caps = get_user_meta( $userID, 'wp_capabilities', 'true' );
    $role = array_keys((array)$wp_caps);

    if($role[0] == "administrator") {
    	return;
    }

    $expiry = get_user_meta( $userID, 'expiry', 'true' );
    $expiry_formatted = date("Y-m-d", strtotime($expiry));

    $this_month = date("Y-m-01");
    if(empty($expiry)){
    	//champ vide
    	return;

    } elseif($expiry < $this_month){ 
    	//Expiré si le mois de fin est avant ce mois présent

    	// Mettre à jour le rôle des membres expirés
    	$u = new WP_User( $userID );
    	$send_email = false;	
    	$update_role = true;

    	// I need two checks: less than a month and more than a month
    	$previous_month = date("Y-m-01", strtotime("-1 months"));

		//Si l'expiration est plus vieille du mois précédent	
		// Pick up role from the Options : acc_expiry_lvl_1 et acc_expiry_lvl_2
    	if($expiry > $previous_month){
			$role_expiry = get_option("acc_expiry_lvl_1");

    	} else { 
			$role_expiry = get_option("acc_expiry_lvl_2");
			$send_email = true;
    	}
		
		if(empty($role_expiry) ){
			//Roles not set
			return;
		}

		// Remove roles
    	foreach($role as $r){
    		$sanitize_role = strtolower($r);
    		if($r != $role_expiry) {
				$u->remove_role( $r );
    		} else {
    			// the role already exists
    			$send_email = false; 
    			$update_role = false;	

    		}
    	}
		if($send_email && !empty($u->user_email)) {
			//Send email to the role that is the most expired
			$email = kfg_send_email( $u->user_email, 1 );
		}
		
		if($update_role) {
			$u->add_role( $role_expiry );
		}
    }

    return $user;
}


function kfg_send_email($user_email, $email_ID) {
	// Picks up from the ACC Email contents/titles options
	// 0 = Welcome Email 
	// 1 = Expired Email 
	// 2 = ...

	$email_contents = get_option("acc_email_contents");

	if(!empty($email_contents)){
		add_option("acc_email_contents", array());

		$email_contents = get_option("acc_email_contents");
		$chosen_email = stripslashes(html_entity_decode( $email_contents[$email_ID] ) );

		$email_titles = get_option("acc_email_titles");
		$chosen_title = stripslashes( $email_titles[$email_ID] );

		$email_active = get_option("acc_email_activation");
		$chosen_active = stripslashes( $email_active[$email_ID] );

		if(empty($chosen_active)){
			// return wp_mail( "flareduststudio@gmail.com", "email not sent:".$chosen_title, $chosen_email, 'Content-Type: text/html; charset=UTF-8' );
			return false;
		}

		//Send email
		return wp_mail( $user_email, $chosen_title, $chosen_email, 'Content-Type: text/html; charset=UTF-8' );
	}
}

function kfg_welcome_email($user_id) {
	$user = get_userdata($user_id);
	$user_email = $user->user_email;
	$test = kfg_send_email( $user_email, 0 );
}

function kfg_acc_expiry_check() {
	// $date = date('d');
	// if ($date == '01') {
		// Ne peut que s'executer le premier du mois

		//Loop dans tous les usagers sauf les admins
		$all_users = get_users( array(
			"role__not_in" => "administrator",

			) );

		foreach ($all_users as $user) {
			kfg_check_validation_status($user);
		}

	// }

}

function kfg_acc_update_users() {

	do_action("wpb_sync_acc_users");
	//Certains changments ont été faits dans le plugin original afin de permettre la génération de Logs (et corriger les bugs de cette action)
	//Ainsi que pour permettre au gens importés (nouveaux) d'avoir un rôle spécial (choisi dans la page ACC)
}

// function get_editable_roles() {
// 	//https://wordpress.stackexchange.com/questions/1665/getting-a-list-of-currently-available-roles-on-a-wordpress-site
//     global $wp_roles;

//     $all_roles = $wp_roles->roles;
//     $editable_roles = apply_filters('editable_roles', $all_roles);

//     return $editable_roles;
// }

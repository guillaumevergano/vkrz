<?php

    // Custom login CSS
    function custom_login_css(){
        echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('template_directory') . '/assets/css/lecentre.css" />';
    }
    add_action('login_head', 'custom_login_css');

    // Custom login title
    function custom_title_login($message) {
        return get_bloginfo('description');
    }
    add_filter('login_headertitle', 'custom_title_login');

    // Custom login URL
    function custom_url_login() {
        return get_bloginfo( 'url' );
    }
    add_filter('login_headerurl', 'custom_url_login');
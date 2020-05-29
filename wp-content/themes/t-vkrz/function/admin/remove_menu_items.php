<?php
function remove_menu_items() {
    global $menu;
    $restricted = array(__('Links'), __('Comments'), __('Posts'));
    end ($menu);
    while (prev($menu)){
        $value = explode(' ',$menu[key($menu)][0]);
        if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){
            unset($menu[key($menu)]);}
    }
}

add_action('admin_menu', 'remove_menu_items');
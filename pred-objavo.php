<?php
/*
Plugin Name: Pred Objavo
Description: Ustvari začasen CPT za prispevke "Pred objavo" in skrije navadne prispevke ter določene strani, razen za administratorja.
Version: 1.1
Author: Miha Omejc
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prepreči direkten dostop
}

// Registracija CPT
function pred_objavo_register_cpt() {
    $labels = array(
        'name'               => 'Pred objavo',
        'singular_name'      => 'Pred objavo',
        'menu_name'          => 'Pred objavo',
        'name_admin_bar'     => 'Pred objavo',
        'add_new'            => 'Dodaj nov',
        'add_new_item'       => 'Dodaj nov prispevek',
        'edit_item'          => 'Uredi prispevek',
        'new_item'           => 'Nov prispevek',
        'view_item'          => 'Ogled prispevka',
        'all_items'          => 'Vsi prispevki',
        'search_items'       => 'Išči prispevke',
        'not_found'          => 'Ni najdenih prispevkov',
        'not_found_in_trash' => 'Ni prispevkov v smeteh',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'menu_icon'          => 'dashicons-edit',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'show_in_rest'       => true, // Podpora za Gutenberg
    );

    register_post_type( 'pred_objavo', $args );
}
add_action( 'init', 'pred_objavo_register_cpt' );

// Brisanje CPT in vseh prispevkov ob deaktivaciji
function pred_objavo_deactivate() {
    // Pridobi vse prispevke CPT
    $posts = get_posts( array(
        'post_type' => 'pred_objavo',
        'numberposts' => -1
    ));

    // Izbriši vsakega posebej
    foreach ( $posts as $post ) {
        wp_delete_post( $post->ID, true );
    }

    // Odstrani CPT
    unregister_post_type( 'pred_objavo' );
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pred_objavo_deactivate' );

// Odstrani CPT in prispevke ob brisanju vtičnika
function pred_objavo_uninstall() {
    pred_objavo_deactivate();
}
register_uninstall_hook( __FILE__, 'pred_objavo_uninstall' );

// Preusmeri stran posameznega prispevka in arhivov za ne-administratorje
function pred_objavo_redirect_single_post_and_archives() {
    if ( ! current_user_can( 'administrator' ) ) {
        
        if ( is_singular( 'post' ) ) {
            wp_redirect( home_url() );
            exit;
        }

        if ( is_post_type_archive( 'post' ) || is_category() || is_tag() || is_author() ) {
            wp_redirect( home_url() );
            exit;
        }
    }
}
add_action( 'template_redirect', 'pred_objavo_redirect_single_post_and_archives' );

// Skrij strani z ID-ji 47
function pred_objavo_hide_pages() {
    if ( is_page( array( 47 ) ) && ! current_user_can( 'administrator' ) ) {
        wp_redirect( home_url() );
        exit;
    }
}
add_action( 'template_redirect', 'pred_objavo_hide_pages' );

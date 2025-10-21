<?php
/**
 * Register booking custom post type.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPK_Booking_Post_Type {
    /**
     * Post type slug.
     */
    const POST_TYPE = 'cpk_booking';

    /**
     * Register custom post type.
     */
    public static function register() {
        $labels = [
            'name'               => _x( 'Rezervácie', 'post type general name', 'cpk-booking' ),
            'singular_name'      => _x( 'Rezervácia', 'post type singular name', 'cpk-booking' ),
            'menu_name'          => _x( 'Rezervácie', 'admin menu', 'cpk-booking' ),
            'name_admin_bar'     => _x( 'Rezervácia', 'add new on admin bar', 'cpk-booking' ),
            'add_new'            => _x( 'Pridať rezerváciu', 'booking', 'cpk-booking' ),
            'add_new_item'       => __( 'Pridať novú rezerváciu', 'cpk-booking' ),
            'new_item'           => __( 'Nová rezervácia', 'cpk-booking' ),
            'edit_item'          => __( 'Upraviť rezerváciu', 'cpk-booking' ),
            'view_item'          => __( 'Zobraziť rezerváciu', 'cpk-booking' ),
            'all_items'          => __( 'Všetky rezervácie', 'cpk-booking' ),
            'search_items'       => __( 'Hľadať rezervácie', 'cpk-booking' ),
            'parent_item_colon'  => __( 'Nadradená rezervácia:', 'cpk-booking' ),
            'not_found'          => __( 'Žiadne rezervácie sa nenašli.', 'cpk-booking' ),
            'not_found_in_trash' => __( 'V koši sa nenašli žiadne rezervácie.', 'cpk-booking' ),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => 26,
            'menu_icon'           => 'dashicons-calendar-alt',
            'supports'            => [ 'title', 'editor', 'author' ],
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Flush rewrite rules.
     */
    public static function flush_rewrite_rules() {
        flush_rewrite_rules();
    }
}

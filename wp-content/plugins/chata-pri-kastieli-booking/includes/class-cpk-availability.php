<?php
/**
 * Handle availability logic.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPK_Availability {
    /**
     * Register REST routes.
     */
    public static function register_routes() {
        register_rest_route(
            'cpk-booking/v1',
            '/check-availability',
            [
                'methods'             => 'POST',
                'callback'            => [ self::class, 'check_availability' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'check_in'  => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                    'check_out' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                ],
            ]
        );
    }

    /**
     * Check availability.
     *
     * @param WP_REST_Request $request Request.
     *
     * @return WP_REST_Response
     */
    public static function check_availability( WP_REST_Request $request ) {
        $check_in  = sanitize_text_field( $request['check_in'] );
        $check_out = sanitize_text_field( $request['check_out'] );

        if ( empty( $check_in ) || empty( $check_out ) ) {
            return new WP_REST_Response(
                [ 'available' => false, 'message' => __( 'Dátumy sú povinné.', 'cpk-booking' ) ],
                400
            );
        }

        $check_in_date  = DateTime::createFromFormat( 'Y-m-d', $check_in );
        $check_out_date = DateTime::createFromFormat( 'Y-m-d', $check_out );

        if ( ! $check_in_date || ! $check_out_date ) {
            return new WP_REST_Response(
                [ 'available' => false, 'message' => __( 'Neplatný formát dátumu.', 'cpk-booking' ) ],
                400
            );
        }

        if ( $check_in_date >= $check_out_date ) {
            return new WP_REST_Response(
                [ 'available' => false, 'message' => __( 'Dátum odchodu musí byť neskôr ako dátum príchodu.', 'cpk-booking' ) ],
                400
            );
        }

        $settings     = get_option( CPK_Settings::OPTION_NAME, [] );
        $minimum_stay = $settings['minimum_stay'] ?? 2;
        $interval     = $check_in_date->diff( $check_out_date )->days;

        if ( $interval < $minimum_stay ) {
            return new WP_REST_Response(
                [
                    'available' => false,
                    'message'   => sprintf(
                        /* translators: %d: minimum stay nights */
                        __( 'Minimálna dĺžka pobytu je %d nocí.', 'cpk-booking' ),
                        $minimum_stay
                    ),
                ],
                400
            );
        }

        $available = self::is_range_available( $check_in, $check_out );

        return new WP_REST_Response(
            [
                'available' => $available,
                'message'   => $available
                    ? __( 'Termín je dostupný.', 'cpk-booking' )
                    : __( 'Termín je už obsadený.', 'cpk-booking' ),
            ]
        );
    }

    /**
     * Determine whether date range is available.
     *
     * @param string $check_in  Check-in date (Y-m-d).
     * @param string $check_out Check-out date (Y-m-d).
     *
     * @return bool
     */
    public static function is_range_available( $check_in, $check_out ) {
        $args = [
            'post_type'      => CPK_Booking_Post_Type::POST_TYPE,
            'post_status'    => [ 'publish', 'pending' ],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => '_cpk_check_in',
                    'value'   => $check_out,
                    'compare' => '<',
                    'type'    => 'DATE',
                ],
                [
                    'key'     => '_cpk_check_out',
                    'value'   => $check_in,
                    'compare' => '>',
                    'type'    => 'DATE',
                ],
            ],
        ];

        $query = new WP_Query( $args );

        return 0 === $query->found_posts;
    }
}

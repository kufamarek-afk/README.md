<?php
/**
 * Shortcodes registration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPK_Shortcodes {
    /**
     * Register shortcodes.
     */
    public static function register_shortcodes() {
        add_shortcode( 'chata_booking_form', [ self::class, 'render_booking_form' ] );
        add_shortcode( 'chata_availability_message', [ self::class, 'render_availability_message' ] );
    }

    /**
     * Render booking form shortcode.
     *
     * @return string
     */
    public static function render_booking_form() {
        $output = '';

        if ( isset( $_GET['cpk_booking_success'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $output .= sprintf( '<div class="cpk-alert cpk-alert--success">%s</div>', esc_html__( 'Ďakujeme, rezervácia bola odoslaná. Overíme dostupnosť a budeme vás kontaktovať.', 'cpk-booking' ) );
        }

        if ( isset( $_GET['cpk_booking_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $message = self::get_error_message( sanitize_text_field( wp_unslash( $_GET['cpk_booking_error'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $output .= sprintf( '<div class="cpk-alert cpk-alert--error">%s</div>', esc_html( $message ) );
        }

        $output .= CPK_Booking_Form::render();

        return $output;
    }

    /**
     * Render availability message container.
     *
     * @return string
     */
    public static function render_availability_message() {
        return '<div class="cpk-availability-message" aria-live="polite"></div>';
    }

    /**
     * Resolve error messages for URL flags.
     *
     * @param string $code Error code.
     *
     * @return string
     */
    private static function get_error_message( $code ) {
        switch ( $code ) {
            case 'missing_fields':
                return __( 'Prosím vyplňte všetky povinné polia.', 'cpk-booking' );
            case 'unavailable':
                return __( 'Žiaľ, termín, ktorý ste vybrali, už nie je dostupný. Zvoľte, prosím, iný termín.', 'cpk-booking' );
            case 'save_failed':
                return __( 'Pri ukladaní rezervácie nastala chyba. Skúste to, prosím, neskôr.', 'cpk-booking' );
            default:
                return __( 'Nastala neznáma chyba.', 'cpk-booking' );
        }
    }
}

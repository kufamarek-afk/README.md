<?php
/**
 * Handle booking form rendering and submissions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPK_Booking_Form {
    /**
     * Nonce action.
     */
    const NONCE_ACTION = 'cpk_submit_booking';

    /**
     * Render booking form HTML.
     *
     * @return string
     */
    public static function render() {
        ob_start();
        ?>
        <form class="cpk-booking-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="cpk_submit_booking" />
            <?php wp_nonce_field( self::NONCE_ACTION, '_cpk_nonce' ); ?>
            <div class="cpk-grid">
                <div class="cpk-field">
                    <label for="cpk-name"><?php esc_html_e( 'Meno a priezvisko', 'cpk-booking' ); ?></label>
                    <input type="text" id="cpk-name" name="cpk_name" required />
                </div>
                <div class="cpk-field">
                    <label for="cpk-email"><?php esc_html_e( 'E-mail', 'cpk-booking' ); ?></label>
                    <input type="email" id="cpk-email" name="cpk_email" required />
                </div>
                <div class="cpk-field">
                    <label for="cpk-phone"><?php esc_html_e( 'Telefón', 'cpk-booking' ); ?></label>
                    <input type="tel" id="cpk-phone" name="cpk_phone" />
                </div>
                <div class="cpk-field">
                    <label for="cpk-check-in"><?php esc_html_e( 'Príchod', 'cpk-booking' ); ?></label>
                    <input type="date" id="cpk-check-in" name="cpk_check_in" required />
                </div>
                <div class="cpk-field">
                    <label for="cpk-check-out"><?php esc_html_e( 'Odchod', 'cpk-booking' ); ?></label>
                    <input type="date" id="cpk-check-out" name="cpk_check_out" required />
                </div>
                <div class="cpk-field">
                    <label for="cpk-guests"><?php esc_html_e( 'Počet hostí', 'cpk-booking' ); ?></label>
                    <input type="number" min="1" id="cpk-guests" name="cpk_guests" required />
                </div>
                <div class="cpk-field cpk-field--wide">
                    <label for="cpk-message"><?php esc_html_e( 'Poznámka', 'cpk-booking' ); ?></label>
                    <textarea id="cpk-message" name="cpk_message" rows="4"></textarea>
                </div>
            </div>
            <button type="submit" class="cpk-button"><?php esc_html_e( 'Odoslať rezerváciu', 'cpk-booking' ); ?></button>
            <div class="cpk-alert cpk-alert--success" hidden></div>
            <div class="cpk-alert cpk-alert--error" hidden></div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle form submission.
     */
    public static function handle_submission() {
        if ( ! isset( $_POST['_cpk_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_cpk_nonce'] ) ), self::NONCE_ACTION ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            wp_die( __( 'Neplatná požiadavka.', 'cpk-booking' ), 403 );
        }

        $name      = sanitize_text_field( wp_unslash( $_POST['cpk_name'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $email     = sanitize_email( wp_unslash( $_POST['cpk_email'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $phone     = sanitize_text_field( wp_unslash( $_POST['cpk_phone'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $check_in  = sanitize_text_field( wp_unslash( $_POST['cpk_check_in'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $check_out = sanitize_text_field( wp_unslash( $_POST['cpk_check_out'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $guests    = absint( wp_unslash( $_POST['cpk_guests'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $message   = sanitize_textarea_field( wp_unslash( $_POST['cpk_message'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ( empty( $name ) || empty( $email ) || empty( $check_in ) || empty( $check_out ) ) {
            wp_safe_redirect( add_query_arg( 'cpk_booking_error', 'missing_fields', wp_get_referer() ) );
            exit;
        }

        if ( ! CPK_Availability::is_range_available( $check_in, $check_out ) ) {
            wp_safe_redirect( add_query_arg( 'cpk_booking_error', 'unavailable', wp_get_referer() ) );
            exit;
        }

        $booking_post = [
            'post_type'    => CPK_Booking_Post_Type::POST_TYPE,
            'post_status'  => 'pending',
            'post_title'   => sprintf( __( 'Rezervácia: %1$s (%2$s – %3$s)', 'cpk-booking' ), $name, $check_in, $check_out ),
            'post_content' => $message,
            'meta_input'   => [
                '_cpk_email'     => $email,
                '_cpk_phone'     => $phone,
                '_cpk_check_in'  => $check_in,
                '_cpk_check_out' => $check_out,
                '_cpk_guests'    => $guests,
            ],
        ];

        $booking_id = wp_insert_post( $booking_post, true );

        if ( is_wp_error( $booking_id ) ) {
            wp_safe_redirect( add_query_arg( 'cpk_booking_error', 'save_failed', wp_get_referer() ) );
            exit;
        }

        self::send_notification_email( $booking_id, $name, $email, $phone, $check_in, $check_out, $guests, $message );

        wp_safe_redirect( add_query_arg( 'cpk_booking_success', '1', wp_get_referer() ) );
        exit;
    }

    /**
     * Send notification email.
     *
     * @param int    $booking_id Booking post ID.
     * @param string $name       Guest name.
     * @param string $email      Guest email.
     * @param string $phone      Guest phone.
     * @param string $check_in   Check-in date.
     * @param string $check_out  Check-out date.
     * @param int    $guests     Number of guests.
     * @param string $message    Guest message.
     */
    private static function send_notification_email( $booking_id, $name, $email, $phone, $check_in, $check_out, $guests, $message ) {
        $settings   = get_option( CPK_Settings::OPTION_NAME, [] );
        $recipient  = $settings['email_recipient'] ?? get_option( 'admin_email' );
        $subject    = sprintf( __( 'Nová rezervácia #%d', 'cpk-booking' ), $booking_id );
        $headers    = [ 'Content-Type: text/html; charset=UTF-8' ];
        $body       = sprintf(
            '<p>%1$s</p><ul>%2$s</ul><p>%3$s</p>',
            esc_html__( 'Prišla nová rezervácia:', 'cpk-booking' ),
            wp_kses_post(
                sprintf(
                    '<li><strong>%1$s</strong>: %2$s</li>' .
                    '<li><strong>%3$s</strong>: %4$s</li>' .
                    '<li><strong>%5$s</strong>: %6$s</li>' .
                    '<li><strong>%7$s</strong>: %8$s</li>' .
                    '<li><strong>%9$s</strong>: %10$s</li>' .
                    '<li><strong>%11$s</strong>: %12$s</li>',
                    esc_html__( 'Meno', 'cpk-booking' ),
                    esc_html( $name ),
                    esc_html__( 'E-mail', 'cpk-booking' ),
                    esc_html( $email ),
                    esc_html__( 'Telefón', 'cpk-booking' ),
                    esc_html( $phone ),
                    esc_html__( 'Príchod', 'cpk-booking' ),
                    esc_html( $check_in ),
                    esc_html__( 'Odchod', 'cpk-booking' ),
                    esc_html( $check_out ),
                    esc_html__( 'Počet hostí', 'cpk-booking' ),
                    esc_html( $guests )
                )
            ),
            wp_kses_post( nl2br( esc_html( $message ) ) )
        );

        if ( ! empty( $message ) ) {
            $body .= sprintf( '<p><strong>%1$s</strong>: %2$s</p>', esc_html__( 'Poznámka', 'cpk-booking' ), wp_kses_post( nl2br( esc_html( $message ) ) ) );
        }

        wp_mail( $recipient, $subject, $body, $headers );

        wp_mail( $email, __( 'Potvrdenie rezervácie', 'cpk-booking' ), __( 'Ďakujeme za vašu rezerváciu. Čoskoro sa vám ozveme s potvrdením.', 'cpk-booking' ), $headers );
    }
}

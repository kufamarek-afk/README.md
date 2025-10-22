<?php
/**
 * Settings page for booking plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPK_Settings {
    /**
     * Option group.
     */
    const OPTION_GROUP = 'cpk_booking_options';

    /**
     * Option name.
     */
    const OPTION_NAME = 'cpk_booking_settings';

    /**
     * Register settings page.
     */
    public static function register_settings_page() {
        add_options_page(
            __( 'Chata pri Kaštieli – Rezervácie', 'cpk-booking' ),
            __( 'Chata pri Kaštieli', 'cpk-booking' ),
            'manage_options',
            'cpk-booking-settings',
            [ self::class, 'render_settings_page' ]
        );
    }

    /**
     * Register settings and fields.
     */
    public static function register_settings() {
        register_setting( self::OPTION_GROUP, self::OPTION_NAME, [ self::class, 'sanitize_settings' ] );

        add_settings_section(
            'cpk_booking_section_general',
            __( 'Základné nastavenia', 'cpk-booking' ),
            '__return_false',
            'cpk-booking-settings'
        );

        add_settings_field(
            'email_recipient',
            __( 'E-mail príjemcu', 'cpk-booking' ),
            [ self::class, 'render_email_field' ],
            'cpk-booking-settings',
            'cpk_booking_section_general'
        );

        add_settings_field(
            'minimum_stay',
            __( 'Minimálny počet nocí', 'cpk-booking' ),
            [ self::class, 'render_minimum_stay_field' ],
            'cpk-booking-settings',
            'cpk_booking_section_general'
        );
    }

    /**
     * Sanitize settings.
     *
     * @param array $settings Settings array.
     *
     * @return array
     */
    public static function sanitize_settings( $settings ) {
        $defaults = [
            'email_recipient' => get_option( 'admin_email' ),
            'minimum_stay'    => 2,
        ];

        $settings = wp_parse_args( $settings, $defaults );
        $settings['email_recipient'] = sanitize_email( $settings['email_recipient'] );
        $settings['minimum_stay']    = absint( $settings['minimum_stay'] );

        return $settings;
    }

    /**
     * Render settings page.
     */
    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = get_option( self::OPTION_NAME, [] );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Nastavenia rezervačného systému', 'cpk-booking' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( 'cpk-booking-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render email recipient field.
     */
    public static function render_email_field() {
        $settings        = get_option( self::OPTION_NAME, [] );
        $email_recipient = $settings['email_recipient'] ?? get_option( 'admin_email' );
        ?>
        <input type="email" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[email_recipient]" value="<?php echo esc_attr( $email_recipient ); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e( 'E-mailová adresa, na ktorú budú odchádzať notifikácie o novej rezervácii.', 'cpk-booking' ); ?></p>
        <?php
    }

    /**
     * Render minimum stay field.
     */
    public static function render_minimum_stay_field() {
        $settings     = get_option( self::OPTION_NAME, [] );
        $minimum_stay = $settings['minimum_stay'] ?? 2;
        ?>
        <input type="number" min="1" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[minimum_stay]" value="<?php echo esc_attr( $minimum_stay ); ?>" class="small-text" />
        <p class="description"><?php esc_html_e( 'Minimálny počet nocí potrebných pre rezerváciu.', 'cpk-booking' ); ?></p>
        <?php
    }
}

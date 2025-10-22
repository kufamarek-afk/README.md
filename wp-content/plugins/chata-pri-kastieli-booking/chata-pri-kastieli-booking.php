<?php
/**
 * Plugin Name:       Chata pri Kaštieli Booking
 * Plugin URI:        https://example.com/chata-pri-kastieli-booking
 * Description:       Kompletný rezervačný systém pre chatu pri kaštieli s kalendárom dostupnosti a e-mailovými notifikáciami.
 * Version:           1.0.0
 * Author:            OpenAI Assistant
 * License:           GPL-2.0-or-later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cpk-booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Chata_Pri_Kastieli_Booking' ) ) {
    final class Chata_Pri_Kastieli_Booking {
        /**
         * Singleton instance.
         *
         * @var Chata_Pri_Kastieli_Booking
         */
        private static $instance;

        /**
         * Plugin version.
         */
        const VERSION = '1.0.0';

        /**
         * Plugin slug.
         */
        const SLUG = 'chata-pri-kastieli-booking';

        /**
         * Absolute plugin path.
         *
         * @var string
         */
        private $plugin_path;

        /**
         * Absolute plugin URL.
         *
         * @var string
         */
        private $plugin_url;

        /**
         * Plugin basename.
         *
         * @var string
         */
        private $plugin_basename;

        /**
         * Get singleton instance.
         *
         * @return Chata_Pri_Kastieli_Booking
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor.
         */
        private function __construct() {
            $this->plugin_path     = plugin_dir_path( __FILE__ );
            $this->plugin_url      = plugin_dir_url( __FILE__ );
            $this->plugin_basename = plugin_basename( __FILE__ );

            $this->load_dependencies();
            $this->register_hooks();
        }

        /**
         * Load plugin dependencies.
         */
        private function load_dependencies() {
            require_once $this->plugin_path . 'includes/class-cpk-booking-post-type.php';
            require_once $this->plugin_path . 'includes/class-cpk-settings.php';
            require_once $this->plugin_path . 'includes/class-cpk-availability.php';
            require_once $this->plugin_path . 'includes/class-cpk-booking-form.php';
            require_once $this->plugin_path . 'includes/class-cpk-shortcodes.php';
        }

        /**
         * Register plugin hooks.
         */
        private function register_hooks() {
            register_activation_hook( __FILE__, [ $this, 'activate' ] );
            register_deactivation_hook( __FILE__, [ 'CPK_Booking_Post_Type', 'flush_rewrite_rules' ] );

            add_action( 'init', [ 'CPK_Booking_Post_Type', 'register' ] );
            add_action( 'init', [ 'CPK_Shortcodes', 'register_shortcodes' ] );
            add_action( 'admin_menu', [ 'CPK_Settings', 'register_settings_page' ] );
            add_action( 'admin_init', [ 'CPK_Settings', 'register_settings' ] );

            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

            add_action( 'admin_post_cpk_submit_booking', [ 'CPK_Booking_Form', 'handle_submission' ] );
            add_action( 'admin_post_nopriv_cpk_submit_booking', [ 'CPK_Booking_Form', 'handle_submission' ] );

            add_action( 'rest_api_init', [ 'CPK_Availability', 'register_routes' ] );
        }

        /**
         * Enqueue public assets.
         */
        public function enqueue_assets() {
            $post = get_post();

            if ( ! is_singular() ) {
                if ( ! $post instanceof \WP_Post ) {
                    return;
                }

                if ( ! has_shortcode( $post->post_content, 'chata_booking_form' ) ) {
                    return;
                }
            } elseif ( ! $post instanceof \WP_Post ) {
                return;
            }

            wp_enqueue_style(
                'cpk-booking-form',
                $this->plugin_url . 'assets/css/booking-form.css',
                [],
                self::VERSION
            );

            wp_enqueue_script(
                'cpk-booking-form',
                $this->plugin_url . 'assets/js/booking-form.js',
                [ 'jquery' ],
                self::VERSION,
                true
            );

            wp_localize_script(
                'cpk-booking-form',
                'cpkBooking',
                [
                    'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
                    'restUrl'     => esc_url_raw( rest_url( 'cpk-booking/v1/check-availability' ) ),
                    'nonce'       => wp_create_nonce( 'wp_rest' ),
                    'successText' => __( 'Ďakujeme, rezervácia bola úspešne odoslaná. Ozveme sa vám čoskoro!', 'cpk-booking' ),
                    'errorText'   => __( 'Ospravedlňujeme sa, ale daný termín už je obsadený. Prosím zvoľte iný termín.', 'cpk-booking' ),
                ]
            );
        }

        /**
         * Activation callback.
         */
        public function activate() {
            CPK_Booking_Post_Type::register();
            CPK_Booking_Post_Type::flush_rewrite_rules();
        }

        /**
         * Run plugin.
         */
        public static function run() {
            self::instance();
        }
    }
}

Chata_Pri_Kastieli_Booking::run();

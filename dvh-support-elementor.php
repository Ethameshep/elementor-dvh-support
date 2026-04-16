<?php
/**
 * Plugin Name: DVH Support for Elementor
 * Description: Automatically adds dvh unit support to Elementor 'vh' controls with a standard 'vh' fallback.
 * Version: 1.1
 * Author: Ethan Sheppard
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dvh-support-for-elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class DVH_Support_For_Elementor {

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
            return;
        }

        add_filter( 'elementor/element/parse_css', [ $this, 'add_dvh_fallback' ], 10, 2 );
    }

    public function add_dvh_fallback( $post_css, $element ) {
        $settings = $element->get_settings();

        if ( 
            ! empty( $settings['min_height']['unit'] ) && 
            $settings['min_height']['unit'] === 'vh' &&
            ! empty( $settings['min_height']['size'] )
        ) {
            $value = $settings['min_height']['size'];
            $selector = $post_css->get_element_unique_selector( $element );

            $custom_css = "
                {$selector} { 
                    min-height: {$value}vh; 
                    min-height: {$value}dvh; 
                }
            ";

            $post_css->add_custom_css( $custom_css );
        }

        return $post_css;
    }

    public function admin_notice_missing_elementor() {
        $message = sprintf(
            esc_html__( '%1$sDVH Support for Elementor%2$s requires %1$sElementor%2$s to be installed and activated.', 'dvh-support-for-elementor' ),
            '<strong>',
            '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
    }
}

new DVH_Support_For_Elementor();

register_activation_hook( __FILE__, 'dvh_support_for_elementor_clear_cache' );
register_deactivation_hook( __FILE__, 'dvh_support_for_elementor_clear_cache' );

function dvh_support_for_elementor_clear_cache() {
    if ( did_action( 'elementor/loaded' ) ) {
        \Elementor\Plugin::instance()->files_manager->clear_cache();
    }
}

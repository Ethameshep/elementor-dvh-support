<?php
/**
 * Plugin Name: DVH Min-Height Support for Elementor Containers
 * Description: Automatically adds dynamic viewport height (dvh) to the 'Min Height' controls of Elementor Containers for perfect mobile responsiveness.
 * Version: 1.0
 * Author: Ethan Sheppard
 * Text Domain: dvh-support-elementor
 * Requires Plugins: elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DVH_MH_SUPPORT' ) ) {

    class DVH_MH_SUPPORT {

        const MINIMUM_ELEMENTOR_VERSION = '3.16.0';

        public function __construct() {
            add_action( 'plugins_loaded', [ $this, 'init' ] );
        }

        public function init() {
            if ( ! did_action( 'elementor/loaded' ) ) {
                return;
            }

            if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
                return;
            }

            add_filter( 'elementor/element/parse_css', [ $this, 'dvh_mh_support_add' ], 20, 2 );
        }

        public function dvh_mh_support_add( $post_css, $element ) {
            if ( ! isset( \Elementor\Plugin::$instance->experiments ) ) {
                return $post_css;
            }

            $experiments = \Elementor\Plugin::$instance->experiments;
            if ( ! $experiments->is_feature_active( 'container' ) ) {
                return $post_css;
            }

            $supported_elements = [ 'container', 'grid' ];
            if ( ! in_array( $element->get_name(), $supported_elements, true ) ) {
                return $post_css;
            }

            $settings = $element->get_settings();

            if ( 
                ! empty( $settings['min_height']['unit'] ) && 
                $settings['min_height']['unit'] === 'vh' &&
                ! empty( $settings['min_height']['size'] )
            ) {
                $value = $settings['min_height']['size'];
                $selector = $post_css->get_element_unique_selector( $element );

                $post_css->get_stylesheet()->add_raw_css( "
                    {$selector} { 
                        min-height: {$value}dvh;
                    }
                " );
            }

            return $post_css;
        }
    }

    new DVH_MH_SUPPORT();
}

register_activation_hook( __FILE__, 'dvh_mh_support_clear_cache' );
register_deactivation_hook( __FILE__, 'dvh_mh_support_clear_cache' );

function dvh_mh_support_clear_cache() {
    if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
        \Elementor\Plugin::instance()->files_manager->clear_cache();
    }
}

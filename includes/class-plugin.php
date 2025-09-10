<?php
namespace FakeStoreSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Plugin {
    const OPTION_KEY = 'fakestore_sync_options';
    const STATUS_OPTION_KEY = 'fakestore_sync_status';
    const META_KEY   = '_fakestore_id';
    const NAME       = 'FakeStore Sync';

    private static $instance = null;
    private $options = array();

    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->setup();
        }
        return self::$instance;
    }

    private function setup() {
        $this->options = get_option( self::OPTION_KEY, array(
            'api_base'    => 'https://fakestoreapi.com',
            'last_sync'   => '',
            'last_counts' => array( 'imported' => 0, 'updated' => 0 ),
            'batch_size'  => 10,
        ) );

        register_activation_hook( FAKESTORE_SYNC_PATH . 'fakestore-sync.php', array( $this, 'on_activation' ) );
        register_deactivation_hook( FAKESTORE_SYNC_PATH . 'fakestore-sync.php', array( $this, 'on_deactivation' ) );

        require_once FAKESTORE_SYNC_PATH . 'includes/class-admin.php';
        require_once FAKESTORE_SYNC_PATH . 'includes/class-sync.php';

        Admin::init();
        Sync::init();
    }

    public function on_activation() {
        $defaults = array(
            'api_base'    => 'https://fakestoreapi.com',
            'last_sync'   => '',
            'last_counts' => array( 'imported' => 0, 'updated' => 0 ),
            'batch_size'  => 10,
        );
        add_option( self::OPTION_KEY, wp_parse_args( $this->options, $defaults ) );

        $defaults = array(
            'last_sync'   => '',
            'last_counts' => array( 'imported' => 0, 'updated' => 0 ),
        );
        add_option( self::STATUS_OPTION_KEY, wp_parse_args( $this->options, $defaults ) );
    }

    public function on_deactivation() {
       
    }

    public function get_options() {
        return $this->options;
    }

    
    public function update_options( $new_opts ) {
        $this->options = wp_parse_args( $new_opts, $this->options );
        update_option( self::OPTION_KEY, $this->options );
    }

    public function update_status( $data = [] ) {
        $status = get_option( self::STATUS_OPTION_KEY, [] );
        $status = array_merge( $status, $data );
        update_option( self::STATUS_OPTION_KEY, $status );
        return $status;
    }

    public function get_status() {
        return get_option( self::STATUS_OPTION_KEY, [] );
    }
}

<?php
/**
 * Plugin Name:  FakeStore Sync
 * Description: Sync products from FakeStoreAPI
 * Version:     1.0
 * Author:      kasun kalya
 */

namespace FakeStoreSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FAKESTORE_SYNC_PATH', plugin_dir_path( __FILE__ ) );
define( 'FAKESTORE_SYNC_URL',  plugin_dir_url( __FILE__ ) );
define( 'FAKESTORE_SYNC_VER',  '1.0' );

require_once FAKESTORE_SYNC_PATH . 'includes/class-plugin.php';

add_action( 'plugins_loaded', function() {
    \FakeStoreSync\Plugin::init();
});
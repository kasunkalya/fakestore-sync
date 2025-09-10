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
define( 'FAKESTORE_SYNC_INC', FAKESTORE_SYNC_PATH . 'includes/' );

require_once FAKESTORE_SYNC_INC . 'class-plugin.php';

use FakeStoreSync\Plugin;

add_action( 'plugins_loaded', function() {
    Plugin::init();
} );
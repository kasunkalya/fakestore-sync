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

define( 'FAKESTORE_SYNC_PATH', plugin_dir_path( __FILE__ ) );
define( 'FAKESTORE_SYNC_INC', FAKESTORE_SYNC_PATH . 'includes/' );

require_once FAKESTORE_SYNC_INC . 'class-plugin.php';
require_once FAKESTORE_SYNC_INC . 'class-admin.php';
require_once FAKESTORE_SYNC_INC . 'class-sync.php';
require_once FAKESTORE_SYNC_INC . 'class-product.php';

use FakeStoreSync\Plugin;
use FakeStoreSync\Admin;
use FakeStoreSync\Sync;

add_action( 'plugins_loaded', function() {
    Plugin::init();
    Admin::init();    
    Sync::init();
} );
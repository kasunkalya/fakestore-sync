<?php
namespace FakeStoreSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sync {

    public static function init() {
        add_action( 'admin_post_fakestore_sync_now', [ __CLASS__, 'handle_sync_now' ] );
    }

    public static function handle_sync_now() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Insufficient permissions', 'fakestore-sync' ), 403 );
        }

        check_admin_referer( 'fakestore_sync_now_action', 'fakestore_sync_nonce' );

        $results = self::run_sync();
        wp_safe_redirect(
            add_query_arg(
                [ 'page' => 'fakestore-sync', 'synced' => '1', 'imported' => $results['imported'], 'updated' => $results['updated'] ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }
    
    public static function run_sync() {
        $plugin   = Plugin::init();
        $opts     = $plugin->get_options();
        $api_base = rtrim( $opts['api_base'], '/' );
        $batch    = max( 1, intval( $opts['batch_size'] ) );

        $url  = $api_base . '/products';
       
        $args = [];
      

        $response = wp_remote_get( $url, $args );
   
        if ( is_wp_error( $response ) ) {
            error_log( 'error: ' . $response->get_error_message() );
            return [ 'imported' => 0, 'updated' => 0 ];
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! is_array( $data ) ) {
            error_log( 'FakeStore Sync error' );
            return [ 'imported' => 0, 'updated' => 0 ];
        }

        $imported = 0;
        $updated  = 0;
        $count    = 0;

        foreach ( $data as $item ) {
            if ( $count >= $batch ) break;

            $res = Product::import_or_update( $item );
            if ( $res === 'imported' ) {
                $imported++;
            } elseif ( $res === 'updated' ) {
                $updated++;
            }

            $count++;
        }

        $plugin = Plugin::init();
        $plugin->update_status([
            'last_sync'   => current_time('mysql'),
            'last_counts' => [
                'imported' => $imported,
                'updated'  => $updated,
            ],
        ]);
        $status = $plugin->get_status();
        error_log( 'Sync Status: ' . print_r( $status, true ) );    
        return [ 'imported' => $imported, 'updated' => $updated ];
    }
}

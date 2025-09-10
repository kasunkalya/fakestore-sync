<?php
namespace FakeStoreSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );
        add_action( 'admin_notices', [ __CLASS__, 'maybe_show_missing_wc_notice' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function admin_menu() {
        $cap = 'manage_woocommerce';
        add_submenu_page(
            'woocommerce',
            __( 'FakeStore Sync', 'fakestore-sync' ),
            __( 'FakeStore Sync', 'fakestore-sync' ),
            $cap,
            'fakestore-sync',
            [ __CLASS__, 'settings_page' ]
        );
    }

    public static function maybe_show_missing_wc_notice() {
        if ( ! is_admin() ) return;

        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<div class="error"><p><strong>FakeStore Sync:</strong> Please activate WooCommerce to use this plugin.</p></div>';
        }
    }

    public static function register_settings() {
        $opt_key = Plugin::OPTION_KEY;

        register_setting( 'fakestore_sync_group', $opt_key, function( $input ) use ( $opt_key ) {
            $existing = get_option( $opt_key, [] );

            $new = [];
            $new['api_base']   = isset( $input['api_base'] ) ? esc_url_raw( trim( $input['api_base'] ) ) : ( $existing['api_base'] ?? 'https://fakestoreapi.com' );
            $new['api_key']    = isset( $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : ( $existing['api_key'] ?? '' );
            $new['batch_size'] = isset( $input['batch_size'] ) ? intval( $input['batch_size'] ) : ( $existing['batch_size'] ?? 10 );

            $new['last_sync']   = $existing['last_sync'] ?? '';
            $new['last_counts'] = $existing['last_counts'] ?? [ 'imported' => 0, 'updated' => 0 ];

            return $new;
        } );

        add_settings_section( 'fakestore_sync_main_section', __( 'FakeStore API', 'fakestore-sync' ), function() {
            echo '<p>' . esc_html__( 'Configure the FakeStore API URL.', 'fakestore-sync' ) . '</p>';
        }, 'fakestore_sync' );

        add_settings_field( 'fakestore_sync_api_base', __( 'API Base URL', 'fakestore-sync' ), function() use ( $opt_key ) {
            $opts = get_option( $opt_key );
            $val  = isset( $opts['api_base'] ) ? esc_attr( $opts['api_base'] ) : 'https://fakestoreapi.com';
            echo '<input type="url" class="regular-text" name="' . esc_attr( $opt_key ) . '[api_base]" value="' . $val . '" required />';
        }, 'fakestore_sync', 'fakestore_sync_main_section' );

        add_settings_field( 'fakestore_sync_batch_size', __( 'Batch size', 'fakestore-sync' ), function() use ( $opt_key ) {
            $opts = get_option( $opt_key );
            $val  = isset( $opts['batch_size'] ) ? intval( $opts['batch_size'] ) : 10;
            echo '<input type="number" min="1" max="50" class="small-text" name="' . esc_attr( $opt_key ) . '[batch_size]" value="' . esc_attr( $val ) . '" />';
            echo '<p class="description">' . esc_html__( 'Smaller batch size', 'fakestore-sync' ) . '</p>';
        }, 'fakestore_sync', 'fakestore_sync_main_section' );
    }
   
    public static function settings_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Insufficient permissions', 'fakestore-sync' ), 403 );
        }

  
        $status     = get_option( Plugin::STATUS_OPTION_KEY, [] );
        $last_sync = ! empty( $status['last_sync'] ) ? $status['last_sync'] : 'Never';
        $counts    = isset( $status['last_counts'] ) ? $status['last_counts'] : [ 'imported' => 0, 'updated' => 0 ];
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'FakeStore Sync (WooCommerce)', 'fakestore-sync' ); ?></h1>

            <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
                <?php
                settings_fields( 'fakestore_sync_group' );
                do_settings_sections( 'fakestore_sync' );
                submit_button();
                ?>
            </form>

             <hr>

            <h2><?php esc_html_e( 'Sync Info', 'fakestore-sync' ); ?></h2>
            <table class="widefat striped">
                <tbody>
                    <tr>
                        <th><?php esc_html_e( 'Last sync', 'fakestore-sync' ); ?></th>
                        <td><?php echo esc_html( $last_sync ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Last imported', 'fakestore-sync' ); ?></th>
                        <td><?php echo intval( $counts['imported'] ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Last updated', 'fakestore-sync' ); ?></th>
                        <td><?php echo intval( $counts['updated'] ); ?></td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <h2><?php esc_html_e( 'Manual Sync', 'fakestore-sync' ); ?></h2>
            <p><?php esc_html_e( 'Click to fetch products from FakeStore API.', 'fakestore-sync' ); ?></p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1em;">
                <?php wp_nonce_field( 'fakestore_sync_now_action', 'fakestore_sync_nonce' ); ?>
                <input type="hidden" name="action" value="fakestore_sync_now" />
                <?php submit_button( __( 'Sync Now', 'fakestore-sync' ), 'primary', 'submit', true ); ?>
            </form>
        </div>
        <?php
    }
}

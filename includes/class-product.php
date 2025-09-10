<?php
namespace FakeStoreSync;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Product {

    public static function import_or_update( $item ) {
        if ( empty( $item['id'] ) || empty( $item['title'] ) ) {
            return null;
        }

        $fakestore_id = intval( $item['id'] );
        $existing_id = self::get_wc_product_id_by_fakestore_id( $fakestore_id );

        if ( $existing_id ) {
            self::update_product( $existing_id, $item );
            return 'updated';
        } else {
            self::create_product( $item );
            return 'imported';
        }
    }

    private static function get_wc_product_id_by_fakestore_id( $fakestore_id ) {
        global $wpdb;
        $meta_key = Plugin::META_KEY;

        $product_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %d LIMIT 1",
            $meta_key,
            $fakestore_id
        ) );

        return $product_id ? intval( $product_id ) : 0;
    }

    private static function create_product( $item ) {
        $post_id = wp_insert_post( [
            'post_title'   => sanitize_text_field( $item['title'] ),
            'post_content' => sanitize_textarea_field( $item['description'] ?? '' ),
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ] );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return;
        }

        update_post_meta( $post_id, '_price', floatval( $item['price'] ) );
        update_post_meta( $post_id, '_regular_price', floatval( $item['price'] ) );
        update_post_meta( $post_id, Plugin::META_KEY, intval( $item['id'] ) );

        if ( ! empty( $item['category'] ) ) {
            wp_set_object_terms( $post_id, sanitize_text_field( $item['category'] ), 'product_cat', true );
        }

        if ( ! empty( $item['image'] ) ) {
            self::set_product_image( $post_id, esc_url_raw( $item['image'] ) );
        }
    }

    private static function update_product( $post_id, $item ) {
        wp_update_post( [
            'ID'           => $post_id,
            'post_title'   => sanitize_text_field( $item['title'] ),
            'post_content' => sanitize_textarea_field( $item['description'] ?? '' ),
        ] );

        update_post_meta( $post_id, '_price', floatval( $item['price'] ) );
        update_post_meta( $post_id, '_regular_price', floatval( $item['price'] ) );

        if ( ! empty( $item['category'] ) ) {
            wp_set_object_terms( $post_id, sanitize_text_field( $item['category'] ), 'product_cat', true );
        }

        if ( ! empty( $item['image'] ) ) {
            self::set_product_image( $post_id, esc_url_raw( $item['image'] ) );
        }
    }

    private static function set_product_image( $post_id, $image_url ) {
        if ( ! function_exists( 'media_sideload_image' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        if ( has_post_thumbnail( $post_id ) ) {
            return;
        }

        $attach_id = media_sideload_image( $image_url, $post_id, null, 'id' );

        if ( ! is_wp_error( $attach_id ) ) {
            set_post_thumbnail( $post_id, $attach_id );
        }
    }
}

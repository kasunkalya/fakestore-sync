## FakeStore Sync for WooCommerce

A WordPress plugin that connects WooCommerce with the FakeStore API
Contributors: Kasun Kalya
Tags: woocommerce, fakestoreapi

## Features

1. Pull products from FakeStore API and create WooCommerce products.
2. Maps key fields: product name, description, price, category and image.
3. Keeps track of the original FakeStore product ID in _fakestore_id to prevent duplicates.
4. Imports in small batches to avoid timeout errors.
5. Records the last sync date and how many products were imported/updated.

## Requirements

1. WordPress 5.8 or newer
2. WooCommerce installed and active
3. PHP 7.4 or higher (PHP 8 recommended)

### Installation

1. Place the plugin folder fakestore-sync inside wp-content/plugins/.
2. Activate FakeStore Sync from the Plugins screen in WordPress.
3. A new menu item appears under WooCommerce → FakeStore Sync.
4. Open the settings page to set the API Base URL (default: https://fakestoreapi.com) and choose batch size.
5. Click Sync Now to import products.

## How to Test

1. Make sure you can add products in WooCommerce (check your user role permissions).
2. Open WooCommerce → FakeStore Sync.
3. Press Sync Now.
4. Go to Products in the WordPress admin. New products will show up with the _fakestore_id meta saved.

## Security Notes

1. Only users with manage_woocommerce (shop managers/admins) or manage_options capability can access the plugin.
2. All settings and sync actions are protected with nonces.
3. Input values are sanitized before saving.
4. Product images are downloaded into the Media Library and attached to the product.



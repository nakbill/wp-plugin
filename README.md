# WP REST API Simpler

## Description
WP REST API Simpler is a WordPress plugin that adds a REST route for retrieving specific order details securely via Basic Authentication.

## Features
- Adds a custom REST route (`/wp-json/wp-order-api/v1/order/{order_id}`) to retrieve order details.
- Implements Basic Authentication for secure access to the REST route.
- Allows configuration of Basic Authentication credentials via WordPress admin settings.

## Installation
1. Download the plugin zip file.
2. Upload the plugin to your WordPress site's plugin directory (`wp-content/plugins/`).
3. Activate the plugin through the WordPress admin interface.

## Usage
1. After activation, navigate to `Settings > Simpler Settings` in the WordPress admin dashboard.
2. Enter your Basic Authentication username and password in the provided fields.
3. Save the settings.
4. You can now access the custom REST route to retrieve order details securely using Basic Authentication:
   - Route: `/wp-json/wp-order-api/v1/order/{order_id}`
   - Replace `{order_id}` with the ID of the order you want to retrieve.

## Configuration
- The plugin adds a settings page under `Settings > Simpler Settings` where you can configure Basic Authentication credentials.

<?php
/**
 * Plugin Name: Dafap SEO
 * Description: Évalue le score SEO des articles WordPress et optimise leur visibilité sociale avec des métadonnées enrichies.
 * Plugin URI: https://github.com/dafap/dafap-seo
 * Update URI: https://github.com/dafap/dafap-seo
 * Version: 1.0.0
 * Author: Alain Pomirol
 * Author URI: https://dafap.fr
 * Text Domain: dafap-seo
 * Domain Path: /languages
 * Requires PHP: 8.2
 */

defined('ABSPATH') || exit;
if ( ! defined( 'PLUGIN_BASENAME' ) ) {
    define( 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if (! defined('PLUGIN_DATA')) {
    define ('PLUGIN_DATA', get_plugin_data(__FILE__, true, false));
}
/**
 * Structure de la constante PLUGIN_DATA
 * Array
 * (
 *    [Name] => Dafap SEO
 *    [PluginURI] => https://github.com/dafap/dafap-seo
 *    [Version] => 1.0.0
 *    [Description] => Évalue le score SEO des articles WordPress et optimise leur visibilité sociale avec des métadonnées enrichies. By Alain Pomirol.
 *    [Author] => Alain Pomirol
 *    [AuthorURI] => https://dafap.fr
 *    [TextDomain] => dafap-seo
 *    [DomainPath] => /languages
 *    [Network] => 
 *    [RequiresWP] => 
 *    [RequiresPHP] => 8.1
 *    [UpdateURI] => https://github.com/dafap/dafap-seo
 *    [RequiresPlugins] => 
 *    [Title] => Dafap SEO
 *    [AuthorName] => Alain Pomirol
 * )
 */
if (version_compare(PHP_VERSION, PLUGIN_DATA['RequiresPHP'], '<')) {
    deactivate_plugins(plugin_basename(__FILE__));
}

require_once __DIR__ . '/vendor/autoload.php';
use DafapSEO\Core\Plugin;

add_action('plugins_loaded', [Plugin::class, 'init']);

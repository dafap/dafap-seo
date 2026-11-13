<?php
/**
 * Plugin Name: Dafap SEO
 * Description: Évalue le score SEO des articles WordPress et optimise leur visibilité sociale avec des métadonnées enrichies.
 * Plugin URI: https://github.com/dafap/dafap-seo
 * Update URI: https://github.com/dafap/dafap-seo
 * Version: 1.1.0
 * Tested up to: 7.1
 * Author: Alain Pomirol
 * Author URI: https://dafap.fr
 * Text Domain: dafap-seo
 * Domain Path: /languages
 * Requires PHP: 8.2
 */

// Si ce fichier est accédé directement, bloquer.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Récupération dynamique des métadonnées depuis l'en-tête
if ( ! function_exists( 'get_file_data' ) ) {
    require_once ABSPATH . 'wp-includes/functions.php';
}
$plugin_data    = get_file_data( __FILE__, [ 
    'version'     => 'Version', 
    'requires_php' => 'Requires PHP',
    'text_domain' => 'Text Domain'
] );

$plugin_version = ! empty( $plugin_data['version'] ) ? $plugin_data['version'] : '1.0.0';
$required_php   = ! empty( $plugin_data['requires_php'] ) ? $plugin_data['requires_php'] : '8.2';
$text_domain    = ! empty( $plugin_data['text_domain'] ) ? $plugin_data['text_domain'] : 'dafap-seo';

// Vérification de compatibilité PHP
if ( version_compare( PHP_VERSION, $required_php, '<' ) ) {
    deactivate_plugins( plugin_basename( __FILE__ ) );
    return;
}

// Définition des constantes propres au plugin
if ( ! defined( 'DAFAPSEO_VERSION' ) ) {
    define( 'DAFAPSEO_VERSION', $plugin_version );
}
if ( ! defined( 'DAFAPSEO_PATH' ) ) {
    define( 'DAFAPSEO_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'DAFAPSEO_TEXTDOMAIN' ) ) {
    define( 'DAFAPSEO_TEXTDOMAIN', $text_domain );
}
if ( ! defined( 'DAFAPSEO_BASENAME' ) ) {
    define( 'DAFAPSEO_BASENAME', plugin_basename( __FILE__ ) );
}

// 1. Chargement de l'Autoloader de Composer
require_once DAFAPSEO_PATH . 'vendor/autoload.php';

// 1. bis : Initialisation de Plugin Update Checker (v6)
if ( class_exists( 'YahnisElsts\PluginUpdateChecker\v6\PucFactory' ) ) {
    $updateChecker = YahnisElsts\PluginUpdateChecker\v6\PucFactory::buildUpdateChecker(
        'https://github.com/dafap/dafap-seo',
        __FILE__,
        'dafap-seo'
    );
    $updateChecker->setBranch( 'master' );
} else {
    error_log( 'ERREUR : PucFactory introuvable pour dafap-seo !' );
}

// 2. Initialisation du plugin
use DafapSEO\Core\Plugin;

add_action( 'plugins_loaded', [ Plugin::class, 'init' ] );
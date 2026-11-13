<?php
namespace DafapSEO\Core;

use DafapSEO\Core\Hooks;

class Plugin {
    public static function init(): void {
        load_plugin_textdomain(
            DAFAPSEO_TEXTDOMAIN, 
            false, 
            dirname( DAFAPSEO_BASENAME ) . '/languages'
        );
        Hooks::register();
        Hooks::register_meta();
    }
}
<?php
namespace DafapSEO\Core;

use DafapSEO\Core\Hooks;

class Plugin {
    public static function init(): void {
        load_plugin_textdomain(PLUGIN_DATA['TextDomain'], false, dirname(PLUGIN_BASENAME) . PLUGIN_DATA['DomainPath']);
        Hooks::register();
        Hooks::register_meta();
    }
}

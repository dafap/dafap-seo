<?php

namespace DafapSEO\Admin;

use DafapSEO\Core\Config;
use DafapSEO\Helpers\Sanitizer;
use DafapSEO\Helpers\Validator;

class SettingsPage
{
    private const PAGE_TITLE = 'SEO Settings';
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
        add_action('admin_init', [self::class, 'registerSettings']);
        add_filter(
            'plugin_action_links_' . DAFAPSEO_BASENAME,
            [self::class, 'add_settings_link']
        );
    }

    public static function addMenu(): void
    {
        add_options_page(
            __(self::PAGE_TITLE, DAFAPSEO_TEXTDOMAIN),
            'Dafap SEO',
            'manage_options',
            Config::PLUGIN_SLUG,
            [self::class, 'render']
        );
    }

    public static function add_settings_link(array $links): array
    {
        $path = admin_url('options-general.php?page=' . Config::PLUGIN_SLUG);
        $settings_link = '<a href="' . $path . '">' . __('Settings', DAFAPSEO_TEXTDOMAIN) . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public static function registerSettings(): void
    {
        register_setting(Config::PLUGIN_SLUG, Config::SETTINGS_FIELDS['facebook']['page_fb'], [
            'type' => 'string',
            'sanitize_callback' => [Sanitizer::class, 'url'],
        ]);
        register_setting(Config::PLUGIN_SLUG, Config::SETTINGS_FIELDS['facebook']['app_id'], [
            'type' => 'string',
            'sanitize_callback' => [Sanitizer::class, 'fbAppId'],
        ]);

        register_setting(Config::PLUGIN_SLUG, Config::SETTINGS_FIELDS['x']['profil_x'], [
            'type' => 'string',
            'sanitize_callback' => [Sanitizer::class, 'url'],
        ]);

        // 📦 Déclaration de la section
        add_settings_section(
            'social_profiles', // ID interne
            __('Profils sociaux'), // Titre affiché
            null,              // Callback de description (facultatif)
            Config::PLUGIN_SLUG // Page cible
        );

        // 📄 Déclaration des champs
        add_settings_field(
            'facebook_url',
            __('Page Facebook'),
            [self::class, 'renderFacebookField'],
            Config::PLUGIN_SLUG,
            'social_profiles'
        );

        add_settings_field(
            'facebook_ap_id',
            __('Id de l\'application Facebook'),
            [self::class, 'renderFacebookAppId'],
            Config::PLUGIN_SLUG,
            'social_profiles'
        );

        add_settings_field(
            'x_url',
            __('Profil X (Twitter)'),
            [self::class, 'renderXField'],
            Config::PLUGIN_SLUG,
            'social_profiles'
        );
    }

    // 🖼️ Rendu du champ Facebook
    public static function renderFacebookField(): void
    {
        $value = esc_attr(get_option(Config::SETTINGS_FIELDS['facebook']['page_fb']));
        echo '<input type="url" name="' . Config::SETTINGS_FIELDS['facebook']['page_fb'] . '" value="' . $value . '" class="regular-text">';
    }

    // 🖼️ Rendu du champ Application Id de Facebook
    public static function renderFacebookAppId(): void
    {
        $value = esc_attr(get_option(Config::SETTINGS_FIELDS['facebook']['app_id']));
        echo '<input type="text" name="' . Config::SETTINGS_FIELDS['facebook']['app_id'] . '" value="' . $value . '" class="regular-text">';
    }

    // 🖼️ Rendu du champ X
    public static function renderXField(): void
    {
        $value = esc_attr(get_option(Config::SETTINGS_FIELDS['x']['profil_x']));
        echo '<input type="url" name="' . Config::SETTINGS_FIELDS['x']['profil_x'] . '" value="' . $value . '" class="regular-text">';
    }

    public static function render(): void
    {
        echo sprintf('<div class="wrap"><h1>%s</h1><form method="post" action="options.php">', __(self::PAGE_TITLE));
        settings_fields(Config::PLUGIN_SLUG);
        do_settings_sections(Config::PLUGIN_SLUG);
        submit_button();
        echo '</form></div>';
    }
}

<?php

namespace DafapSEO\Core;

use DafapSEO\Admin\Metabox;
use DafapSEO\Admin\Column;
use DafapSEO\Admin\SettingsPage;
use DafapSEO\Core\Config;
use DafapSEO\SEO\Score;
use DafapSEO\Front\MetaBuilder;
use DafapSEO\Helpers\Sanitizer;
use SimplePie\Sanitize;

class Hooks
{
    public static function register(): void
    {
        // Admin CSS
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('dafap-seo-admin', plugin_dir_url(__FILE__) . '../../assets/css/admin.css');
        });

        // Métaboxes et colonnes
        add_action('add_meta_boxes', [Metabox::class, 'register']);
        add_action('admin_enqueue_scripts', [Metabox::class, 'enqueueAssetsJS']);
        add_filter('manage_post_posts_columns', [Column::class, 'add']); // entête de la colonne
        add_action('manage_post_posts_custom_column', [Column::class, 'render'], 10, 2); // contenu de la colonne

        // CPT et sauvegarde
        self::register_meta();
        SettingsPage::init();
        Column::register();
        add_action('save_post', [Score::class, 'save_seo_score'], 10, 2);
        add_action('save_post', [self::class, 'save_seo_fields'], 20, 2);

        // Front
        add_filter('document_title_parts', [MetaBuilder::class, 'replace_document_title']);
        add_action('wp_head', function () {
            if (is_singular('post') && $post = get_post()) {
                (new MetaBuilder($post))->output();
            }
        });
    }

    public static function register_meta(): void
    {
        foreach (Config::FIELDS['plugin_fields'] as $field) {
            register_post_meta('post', $field, [
                'show_in_rest' => true,
                'type' => 'string',
                'single' => true,
                'auth_callback' => fn() => current_user_can('edit_posts'),
            ]);
        }
        foreach (Config::FIELDS['social']['plugin_fields'] as $field) {
            register_post_meta('post', $field, [
                'show_in_rest' => true,
                'type' => 'string',
                'single' => true,
                'auth_callback' => fn() => current_user_can('edit_posts'),
            ]);
        }
    }

    public static function save_seo_fields(int $post_id, \WP_Post $post): void
    {
        $nonce_field = $_POST[Config::NONCE['metabox']['field_name']] ?? null;
        if (!isset($nonce_field) || !wp_verify_nonce($nonce_field, Config::NONCE['metabox']['action'])) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if ($post->post_type !== 'post') {
            return;
        }

        $fields = [
            'focus_kw' => Sanitizer::text($_POST[Config::FIELDS['form_fields']['focus_kw']] ?? ''),
            'seo_title' => Sanitizer::text($_POST[Config::FIELDS['form_fields']['seo_title']] ?? ''),
            'seo_meta_desc' => Sanitizer::textarea($_POST[Config::FIELDS['form_fields']['seo_meta_desc']] ?? ''),
            'secondary_kws' => implode(', ', Sanitizer::keywords($_POST[Config::FIELDS['form_fields']['secondary_kws']] ?? '')),
        ];

        foreach ($fields as $key => $value) {
            update_post_meta($post_id, Config::FIELDS['plugin_fields'][$key], $value);
        }

        // Champs sociaux (si présents)
        $social = Sanitizer::social([
            'og_title' => $_POST[Config::FIELDS['social']['form_fields']['og_title']] ?? '',
            'og_desc' => $_POST[Config::FIELDS['social']['form_fields']['og_desc']] ?? '',
            'og_image' => $_POST[Config::FIELDS['social']['form_fields']['og_image']] ?? '',
            'og_image_alt' => $_POST[Config::FIELDS['social']['form_fields']['og_image_alt']] ?? '',
        ]);
        foreach ($social as $key => $value) {
            update_post_meta($post_id, Config::FIELDS['social']['plugin_fields'][$key], $value);
        }

        // Champs spécifiques à X (si présents)
        $social = Sanitizer::x([
            'x_title' => $_POST[Config::FIELDS['social']['form_fields']['x_title']] ?? '',
            'x_desc' => $_POST[Config::FIELDS['social']['form_fields']['x_desc']] ?? '',
            'x_image' => $_POST[Config::FIELDS['social']['form_fields']['x_image']] ?? '',
            'x_image_alt' => $_POST[Config::FIELDS['social']['form_fields']['x_image_alt']] ?? '',
        ]);
        foreach ($social as $key => $value) {
            update_post_meta($post_id, Config::FIELDS['social']['plugin_fields'][$key], $value);
        }
    }
}

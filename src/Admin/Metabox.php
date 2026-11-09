<?php

namespace DafapSEO\Admin;

use DafapSEO\SEO\Analyzer;
use DafapSEO\SEO\Score;
use DafapSEO\Core\Config;

class Metabox
{
    public static function register(): void
    {
        add_meta_box(
            'dafap_seo_metabox',
            __('Analyse SEO complète', PLUGIN_DATA['TextDomain']),
            [self::class, 'render'],
            ['post'], // Tu peux ajouter des CPT ici
            'normal',
            'default'
        );
    }

    public static function enqueueAssetsJS(): void
    {
        $screen = get_current_screen();
        if ($screen && in_array($screen->post_type, ['post'])) { // Ajoute ici tes CPT si besoin
            wp_enqueue_script(
                'dafap-seo-metabox-tabs',
                plugin_dir_url(__FILE__) . '../../assets/js/metabox-tabs.js',
                ['jquery'],
                PLUGIN_DATA['Version'],
                true
            );
            wp_enqueue_media(); // nécessaire pour wp.media
            wp_enqueue_script(
                'dafap-seo-metabox-media',
                plugin_dir_url(__FILE__) . '../../assets/js/metabox-media.js',
                ['jquery'],
                PLUGIN_DATA['Version'],
                true
            );
            wp_localize_script('dafap-seo-metabox-media', 'DafapSEOMediaL10n', [
                'chooseImage' => __('Choisir une image', 'dafap'),
                'useImage'    => __('Utiliser cette image', 'dafap'),
            ]);
        }
    }

    public static function render(\WP_Post $post): void
    {
        self::renderSnippets($post);
?>
        <div id="dafap-seo-tabs">
            <ul class="dafap-tab-nav">
                <li class="active" data-tab="seo"><?php _e('Paramètres SEO', PLUGIN_DATA['TextDomain']); ?></li>
                <li data-tab="social"><?php _e('Réseaux sociaux', PLUGIN_DATA['TextDomain']); ?></li>
            </ul>

            <div class="dafap-tab-content active" id="tab-seo">
                <?php self::renderSeoFields($post); ?>
            </div>

            <div class="dafap-tab-content" id="tab-social">
                <?php self::renderSocialFields($post); ?>
            </div>
        </div>
    <?php
    }

    public static function renderSeoFields(\WP_Post $post): void
    {
        $focus_kw = get_post_meta($post->ID, Config::FIELDS['plugin_fields']['focus_kw'], true);
        $title_seo = get_post_meta($post->ID, Config::FIELDS['plugin_fields']['seo_title'], true);
        $meta_desc = get_post_meta($post->ID, Config::FIELDS['plugin_fields']['seo_meta_desc'], true);
        $secondary_kws = get_post_meta($post->ID, Config::FIELDS['plugin_fields']['secondary_kws'], true);
        $score = get_post_meta($post->ID, Score::FEILD_NAME, true);
        if ($score === '') {
            $score = Score::compute_score($post->ID);
        }
        $diagnostic = \DafapSEO\SEO\Analyzer::diagnose($post->ID);
        wp_nonce_field(Config::NONCE['metabox']['action'], Config::NONCE['metabox']['field_name']);

        echo '<p><label for="' . Config::FIELDS['form_fields']['focus_kw'] . '">' . __('Mot-clé principal', PLUGIN_DATA['TextDomain']) . '</label><br>';
        echo '<input type="text" name="' . Config::FIELDS['form_fields']['focus_kw'] . '" id="' . Config::FIELDS['form_fields']['focus_kw'] . '" value="' . esc_attr($focus_kw) . '" class="widefat" /></p>';

        echo '<p><label for="' . Config::FIELDS['form_fields']['seo_title'] . '">' . __('Titre SEO', PLUGIN_DATA['TextDomain']) . '</label><br>';
        echo '<input type="text" name="' . Config::FIELDS['form_fields']['seo_title'] . '" id="' . Config::FIELDS['form_fields']['seo_title'] . '" value="' . esc_attr($title_seo) . '" class="widefat" /></p>';

        echo '<p><label for="' . Config::FIELDS['form_fields']['seo_meta_desc'] . '">' . __('Meta description', PLUGIN_DATA['TextDomain']) . '</label><br>';
        echo '<textarea name="' . Config::FIELDS['form_fields']['seo_meta_desc'] . '" id="' . Config::FIELDS['form_fields']['seo_meta_desc'] . '" rows="3" class="widefat">' . esc_textarea($meta_desc) . '</textarea></p>';

        echo '<p><label for="' . Config::FIELDS['form_fields']['secondary_kws'] . '">' . __('Mots-clés secondaires (séparés par des virgules)', PLUGIN_DATA['TextDomain']) . '</label><br>';
        echo '<input type="text" name="' . Config::FIELDS['form_fields']['secondary_kws'] . '" id="' . Config::FIELDS['form_fields']['secondary_kws'] . '" value="' . esc_attr($secondary_kws) . '" class="widefat" /></p>';

        echo '<hr>';

        echo '<div class="dafap-seo-score">';
        echo '<p><strong>' . __('Score SEO :', PLUGIN_DATA['TextDomain']) . '</strong> ' . esc_html($score) . '/100</p>';
        echo '<ul class="dafap-seo-diagnostic">';

        foreach ($diagnostic as $item) {
            $status_icon = $item['status'] === 'ok' ? '✅' : '⚠️';
            echo '<li>';
            echo $status_icon . ' <strong>' . esc_html($item['label']) . '</strong> (' . $item['score'] . '/100)';
            if ($item['suggestion']) {
                echo '<br><em>' . esc_html($item['suggestion']) . '</em>';
            }
            echo '</li>';
        }
        echo '</ul>';
        echo '<p>' . __('Analyse effectuée automatiquement à la sauvegarde.', PLUGIN_DATA['TextDomain']) . '</p>';
        echo '</div>';
    }

    private static function renderSocialFields(\WP_Post $post): void
    {
        $meta = get_post_meta($post->ID);

        $attr = Config::FIELDS['social']['form_fields']['og_title'];
        echo '<p><strong>Open Graph</strong></p>';
        echo '<p><label for="' . $attr . '">' . __('Titre OG') . '</label><br />';
        echo '<input type="text" name="' . $attr . '" id="' . $attr . '" value="' .
            esc_attr($meta[Config::FIELDS['social']['plugin_fields']['og_title']][0] ?? '') . '" class="widefat" /><br />';
        echo '<span style="color: blue; font-size: 80%">' . __('(60 caractères max idéalement)') . '</span></p>';

        $attr = Config::FIELDS['social']['form_fields']['og_desc'];
        echo '<p><label for="' . $attr . '">Description OG</label><br />';
        echo '<textarea name="' . $attr . '" id="' . $attr . '" class="widefat">' .
            esc_textarea($meta[Config::FIELDS['social']['plugin_fields']['og_desc']][0] ?? '') . '</textarea><br />';
        echo '<span style="color: blue; font-size: 80%">' . __('(150-200 caractères idéalement)') . '</span></p>';

        $attr = Config::FIELDS['social']['form_fields']['og_image'];
        $attr_alt = Config::FIELDS['social']['form_fields']['og_image_alt'];
        echo '<p><label for="' . $attr . '">Image OG (URL)</label><br />';
        echo '<input type="text" name="' . $attr . '" id="' . $attr . '" value="' .
            esc_attr($meta[Config::FIELDS['social']['plugin_fields']['og_image']][0] ?? '') . '" class="widefat" />' .
            '<div class="image-preview" data-preview="' . $attr . '"></div>' .
            '<button type="button" class="button select-media" data-target="' . $attr . '" data-alt="' . $attr_alt . '">' .
            __('Choisir dans la médiathèque') . '</button><br />';
        echo '<span style="color: blue; font-size: 80%">' . __('Taille recommandée : 1200×630 px') . '<br />';
        echo __('Format : JPG ou PNG') . '<br />' . __('Poids < 300 Ko') . '</span><br />';
        echo '<label for="' . $attr_alt . '">Texte alternatif</label><br />';
        echo '<input type="text" name="' . $attr_alt . '" id="' . $attr_alt . '" value="' .
            esc_attr($meta[Config::FIELDS['social']['plugin_fields']['og_image_alt']][0] ?? '') . '" class="widefat" /><br />';
        echo '<span style="color: blue; font-size: 80%">' . __('Brève description de l\'image. (50-100 caractères idéalement') . '</span></p>';

        $attr = Config::FIELDS['social']['form_fields']['x_title'];
        echo '<p><strong>Twitter / X</strong></p>';
        echo '<p><label for="' . $attr . '">Titre X</label><br />';
        echo '<input type="text" name="' . $attr . '" id="' . $attr . '" value="' .
            esc_attr($meta[Config::FIELDS['social']['plugin_fields']['x_title']][0] ?? '') . '" class="widefat" />';

        $attr = Config::FIELDS['social']['form_fields']['x_desc'];
        echo '<p><label for="' . $attr . '">Description X</label><br />';
        echo '<textarea name="' . $attr . '" id="' . $attr . '" class="widefat">' .
            esc_textarea($meta[Config::FIELDS['social']['plugin_fields']['x_desc']][0] ?? '') . '</textarea></p>';

        $attr = Config::FIELDS['social']['form_fields']['x_image'];
        $attr_alt = Config::FIELDS['social']['form_fields']['x_image_alt'];
        echo '<p><label for="' . $attr . '">Image X (URL)</label><br />';
        echo '<input type="text" name="' . $attr . '" id="' . $attr . '" value="' .
            esc_attr($meta[Config::FIELDS['social']['plugin_fields']['x_image']][0] ?? '') . '" class="widefat" />' .
            '<div class="image-preview" data-preview="' . $attr . '"></div>' .
            '<button type="button" class="button select-media" data-target="' . $attr . '" data-alt="' . $attr_alt . '">' .
            __('Choisir dans la médiathèque') . '</button><br />';
        echo '<label for="' . $attr_alt . '">Texte alternatif</label><br />';
        echo '<input type="text" name="' . $attr_alt . '" id="' . $attr_alt . '" value="' .
            esc_attr($meta[Config::FIELDS['social']['plugin_fields']['x_image_alt']][0] ?? '') . '" class="widefat" /><br />';
        echo '<span style="color: blue; font-size: 80%">' . __('Brève description de l\'image. (50-100 caractères idéalement') . '</span></p>';

        echo '<p style="font-size:12px;color:#666;">' . __('Les champs OG et X permettent d’optimiser l’affichage de votre contenu sur les réseaux sociaux.', PLUGIN_DATA['TextDomain']) . '</p>';
    }

    public static function renderSnippets(\WP_Post $post): void
    {
        $meta = get_post_meta($post->ID);
    ?>
        <div class="dafap-snippets-preview">
            <h3>Aperçus de publication</h3>

            <div class="snippet-preview google-snippet">
                <h4>🔍 Google (résultat de recherche)</h4>
                <div class="snippet-box google">
                    <div class="snippet-title"><?php echo esc_html($meta[Config::FIELDS['plugin_fields']['seo_title']][0]); ?></div>
                    <div class="snippet-url"><?php echo esc_url(get_permalink($post)); ?></div>
                    <div class="snippet-description"><?php echo esc_html($meta[Config::FIELDS['plugin_fields']['seo_meta_desc']][0]); ?></div>
                </div>
            </div>

            <div class="snippet-preview facebook-snippet">
                <h4>📘 Facebook</h4>
                <div class="snippet-box facebook">
                    <div class="snippet-image">
                        <img src="<?php echo esc_url($meta[Config::FIELDS['social']['plugin_fields']['og_image']][0]); ?>" 
                        alt="<?php echo esc_attr($meta[Config::FIELDS['social']['plugin_fields']['og_image_alt']][0]); ?>" />
                    </div>
                    <div class="snippet-meta">
                        <div class="snippet-title"><?php echo esc_html($meta[Config::FIELDS['social']['plugin_fields']['og_title']][0]); ?></div>
                        <div class="snippet-description"><?php echo esc_html($meta[Config::FIELDS['social']['plugin_fields']['og_desc']][0]); ?></div>
                        <div class="snippet-url"><?php echo esc_url(get_permalink($post)); ?></div>
                    </div>
                </div>
            </div>

            <div class="snippet-preview twitter-snippet">
                <h4>🐦 X (anciennement Twitter)</h4>
                <div class="snippet-box twitter">
                    <div class="snippet-image">
                        <img src="<?php echo esc_url($meta[Config::FIELDS['social']['plugin_fields']['x_image']][0]); ?>" 
                        alt="<?php echo esc_attr($meta[Config::FIELDS['social']['plugin_fields']['x_image_alt']][0]); ?>" />
                    </div>
                    <div class="snippet-meta">
                        <div class="snippet-title"><?php echo esc_html($meta[Config::FIELDS['social']['plugin_fields']['x_title']][0]); ?></div>
                        <div class="snippet-description"><?php echo esc_html($meta[Config::FIELDS['social']['plugin_fields']['x_desc']][0]); ?></div>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}

<?php

namespace DafapSEO\Front;

use DafapSEO\Core\Config;
use DafapSEO\Helpers\Sanitizer;
use DafapSEO\Helpers\SocialImageBuilder;
use DafapSEO\JsonLd\GraphBuilder;

class MetaBuilder
{
    protected \WP_Post $post;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    public static function replace_document_title(array $title): array
    {
        if (is_singular('post')) {
            $post_id = get_the_ID();
            $seo_title = Sanitizer::getPostMetaOrDefault($post_id, Config::FIELDS['plugin_fields']['seo_title']);
            if (! empty($seo_title)) {
                $title['title'] = $seo_title;
            }
        }
        return $title;
    }

    public function output(): void
    {
        if (!is_singular('post') || ! $this->post instanceof \WP_Post) {
            return;
        }
        // Pour Google ...
        $meta = $this->getMetaValues();
        // Ne pas insérer le title ici car il y aurait 2 balises <title>
        echo $this->buildMeta('description', $meta['desc']);
        echo $this->buildMeta('keywords', $meta['keywords']);
        // Pour les réseaux sociaux
        echo $this->buildMeta('og:title', $meta['title'], 'property');
        echo $this->buildMeta('og:description', $meta['desc'], 'property');
        echo $this->buildMeta('og:type', 'article', 'property');
        echo $this->buildMeta('og:url', get_permalink($this->post), 'property');
        echo $this->buildMeta('og:locale', get_locale(), 'property');
        echo $this->buildMeta('og:site_name', get_bloginfo('name'), 'property');
        echo $this->buildMeta('og:see_also', $meta['page_fb']);
        echo $this->buildMeta('fb:app_id', $meta['app_id'], 'property');
        echo $this->buildMeta('og:image', $meta['og_image'], 'property');
        echo $this->buildMeta('og:image:alt', $meta['og_image_alt'], 'property');
        // Spécial X
        echo $this->buildMeta('twitter:card', 'summary_large_image');
        echo $this->buildMeta('twitter:title', $meta['x_title']);
        echo $this->buildMeta('twitter:description', $meta['x_desc']);
        echo $this->buildMeta('twitter:creator', $meta['profil_x']);
        echo $this->buildMeta('twitter:image', $meta['x_image']);
        echo $this->buildMeta('twitter:image:alt', $meta['x_image_alt']);
        // bloc <script type="application/ld+json">
        echo (new \DafapSEO\JsonLd\GraphBuilder($this->post))->render();
    }

    /**
     * Pour faciliter les tests unitaires ou l'utilisation dans d'autres contextes
     */
    public function buildAllMeta(): string
    {
        ob_start();
        $this->output();
        return ob_get_clean();
    }

    /**
     * Récupère les valeurs enregistrées dans la logique de ce plugin
     * La clé 'keyword' est la liste des mots-clés séparés par une virgule en commençant par le mot-clé principal
     */
    protected function getMetaValues(): array
    {
        $title = Sanitizer::getPostMetaOrDefault($this->post->ID, Config::FIELDS['plugin_fields']['seo_title'], '');
        $title = $title ?: get_the_title($this->post);
        $og_title = Sanitizer::getPostMetaOrDefault($this->post->ID, Config::FIELDS['social']['plugin_fields']['og_title'], $title);
        $x_title = Sanitizer::getPostMetaOrDefault($this->post->ID, Config::FIELDS['social']['plugin_fields']['x_title'], $og_title);
        $desc = Sanitizer::getPostMetaOrDefault($this->post->ID, Config::FIELDS['plugin_fields']['seo_meta_desc'], '');
        $desc = $desc ?: get_the_excerpt($this->post);
        $focus = Sanitizer::getPostMetaOrDefault($this->post->ID, Config::FIELDS['plugin_fields']['focus_kw']);
        $secondary = Sanitizer::getPostMetaOrDefault($this->post->ID, Config::FIELDS['plugin_fields']['secondary_kws']);
        $images = SocialImageBuilder::getSocialImages($this->post->ID);
        $og_image = $images['og']['url'] ?? '';
        $og_image_alt = $images['og']['alt'] ?? '';
        $x_image = $images['x']['url'] ?? '';
        $x_image_alt = $images['x']['alt'] ?? '';
        

        return [
            'title' => is_string($title) ? $title : '',
            'desc' => is_string($desc) ? $desc : '',
            'keywords' => $this->buildKeywordsString($focus, $secondary),
            'og_tite' => $og_title,
            'og_desc' => Sanitizer::getPostMetaOrDefault($this->post->ID, Config::FIELDS['social']['plugin_fields']['og_desc'], ''),
            'og_image' => $og_image,
            'og_image_alt' => $og_image_alt,
            'x_title' => $x_title,
            'x_desc' => Sanitizer::getPostMetaOrDefault($this->post->ID, Config::FIELDS['social']['plugin_fields']['x_desc'], ''),
            'x_image' => $x_image,
            'x_image_alt' => $x_image_alt,
            'page_fb' => get_option(Config::SETTINGS_FIELDS['facebook']['page_fb'], ''),
            'app_id' => get_option(Config::SETTINGS_FIELDS['facebook']['app_id'], ''),
            'profil_x' => get_option(Config::SETTINGS_FIELDS['x']['profil_x'], ''),
        ];
    }

    /**
     * Construction des balises meta
     * Un filtre dont le nom est 'dafap_seo_meta_content' permet depersonnaliser les balises avant affichage
     */
    protected function buildMeta(string $name, string $content, string $attr = 'name'): string
    {
        if (trim($content) === '') {
            return '';
        }
        $content = apply_filters('dafap_seo_meta_content', $content, $name, $this->post);

        return sprintf(
            '<meta %s="%s" content="%s">' . "\n",
            esc_attr($attr),
            esc_attr($name),
            esc_attr($content)
        );
    }

    /**
     * Construction de balises link
     */
    protected function buildLink(string $rel, string $href): string
    {
        return sprintf(
            '<link rel="%s" href="%s">' . "\n",
            esc_attr($rel),
            esc_url($href)
        );
    }

    /**
     * Construit une chaîne de mots-clés nettoyée à partir d’un mot-clé principal et d’une liste secondaire.
     *
     * @param string $focus Mot-clé principal
     * @param string $secondary Liste secondaire séparée par des virgules
     * @return string Chaîne de mots-clés séparés par virgule
     */
    public function buildKeywordsString(?string $focus = '', ?string $secondary = ''): string
    {
        // Sécurisation contre les passages explicites de null
        $secondary = $secondary ?? '';
        $focus     = $focus ?? '';

        $keywords = array_map('trim', explode(',', $secondary));

        if ($focus !== '') {
            array_unshift($keywords, $focus);
        }

        // array_filter supprime les chaînes vides ou nulles résultantes
        $keywords = array_filter($keywords);

        return implode(', ', $keywords);
    }
}

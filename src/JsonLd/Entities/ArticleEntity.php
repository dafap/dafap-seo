<?php
namespace DafapSEO\JsonLd\Entities;

class ArticleEntity
{
    protected \WP_Post $post;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    /**
     * @id                      Identifiant unique de l'article qui permet de le relier aux autres entités
     * headline                 Titre SEO ou titre WordPress
     * datePublished            Date de publication
     * dateModified             Date de la dernière modification
     * author @id               Lien vers l'entité PersonEntity
     * publisher @id            Lien vers l'entité OrganizationEntity
     * image @id                Lien vers l'entité ImageEntity
     * keywords                 Liste des mots-clés
     * articleSection           Catégorie principale attribuée à l'article
     * inLanguage               Langue du contenu
     * mainEntityOfPage @id     Lien vers l'entité WebPageEntity
     */
    public function build(): array
    {
        return [
            '@type' => 'Article',
            '@id' => get_permalink($this->post) . '#article',
            'headline' => get_post_meta($this->post->ID, '_dafap_seo_title', true) ?: get_the_title($this->post),
            'datePublished' => get_the_date('c', $this->post),
            'dateModified' => get_the_modified_date('c', $this->post),
            'author' => ['@id' => get_author_posts_url($this->post->post_author) . '#author'],
            'publisher' => ['@id' => home_url('/') . '#organization'],
            'image' => ['@id' => get_permalink($this->post) . '#primaryimage'],
            'keywords' => explode(',', get_post_meta($this->post->ID, '_dafap_seo_secondary_kws', true) ?? ''),
            'articleSection' => [get_the_category($this->post)[0]->name ?? ''],
            'inLanguage' => get_locale(),
            'mainEntityOfPage' => ['@id' => get_permalink($this->post)],
        ];
    }
}

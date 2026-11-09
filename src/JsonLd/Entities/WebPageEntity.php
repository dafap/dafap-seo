<?php
namespace DafapSEO\JsonLd\Entities;

class WebPageEntity
{
    protected \WP_Post $post;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    /**
     * @id                           URL canonique de la page → utilisé comme identifiant principal
     * name                          titre SEO ou titre WordPress
     * description                   meta description ou extrait
     * primaryImageOfPage et image   liés à l’image principale via ImageEntity
     * breadcrumb                    lié à BreadcrumbEntity
     * isPartOf                      relie la page au site (WebSiteEntity)
     * potentialAction               indique que la page peut être lue (utile pour les assistants vocaux et moteurs)
     */
    public function build(): array
    {
        $permalink = get_permalink($this->post);
        $title = get_post_meta($this->post->ID, '_dafap_seo_title', true) ?: get_the_title($this->post);
        $desc = get_post_meta($this->post->ID, '_dafap_seo_meta_desc', true) ?: get_the_excerpt($this->post);
        $imageId = $permalink . '#primaryimage';
        $breadcrumbId = $permalink . '#breadcrumb';

        return [
            '@type' => 'WebPage',
            '@id' => $permalink,
            'url' => $permalink,
            'name' => $title,
            'description' => $desc,
            'datePublished' => get_the_date('c', $this->post),
            'dateModified' => get_the_modified_date('c', $this->post),
            'primaryImageOfPage' => ['@id' => $imageId],
            'image' => ['@id' => $imageId],
            'breadcrumb' => ['@id' => $breadcrumbId],
            'isPartOf' => ['@id' => home_url('/') . '#website'],
            'potentialAction' => [[
                '@type' => 'ReadAction',
                'target' => [$permalink]
            ]],
            'inLanguage' => get_locale(),
        ];
    }
}

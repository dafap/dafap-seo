<?php
namespace DafapSEO\JsonLd;

use DafapSEO\JsonLd\Entities\{ArticleEntity, WebPageEntity, ImageEntity, BreadcrumbEntity, WebSiteEntity, OrganizationEntity, PersonEntity};

class GraphBuilder
{
    protected \WP_Post $post;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    public function render(): string
    {
        $graph = [
            (new ArticleEntity($this->post))->build(),
            (new WebPageEntity($this->post))->build(),
            (new ImageEntity($this->post))->build(),
            (new BreadcrumbEntity($this->post))->build(),
            (new WebSiteEntity())->build(),
            (new OrganizationEntity())->build(),
            (new PersonEntity($this->post))->build(),
        ];

        $json = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        return '<script type="application/ld+json">' . wp_json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
}

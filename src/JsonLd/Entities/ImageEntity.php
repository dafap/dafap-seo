<?php
namespace DafapSEO\JsonLd\Entities;

class ImageEntity
{
    protected \WP_Post $post;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    /**
     * @type: ImageObject	Déclare le type d’entité
     * @id	                Identifiant unique lié à l’article
     * url / contentUrl	    URL de l’image à la une
     * caption	            Titre de l’article comme légende
     * inLanguage	        Langue du contenu (ex. fr-FR)
     */
    public function build(): array
    {
        $imageUrl = get_the_post_thumbnail_url($this->post);
        if (! $imageUrl) {
            return [];
        }

        $imageId = get_permalink($this->post) . '#primaryimage';

        return [
            '@type' => 'ImageObject',
            '@id' => $imageId,
            'inLanguage' => get_locale(),
            'url' => $imageUrl,
            'contentUrl' => $imageUrl,
            'caption' => get_the_title($this->post),
        ];
    }
}

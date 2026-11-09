<?php
namespace DafapSEO\JsonLd\Entities;

class PersonEntity
{
    protected \WP_Post $post;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    /**
     * @type: Person	Déclare l’auteur comme une personne
     * @id	            Identifiant unique basé sur l’URL de l’auteur
     * name	            Nom affiché de l’auteur
     * url	            Page auteur WordPress
     * image	        Avatar de l’auteur (via get_avatar_url)
     * sameAs	        Site personnel ou réseaux sociaux (si renseignés)
     * inLanguage	    Langue principale du site
     */
    public function build(): array
    {
        $authorId = $this->post->post_author;
        $authorUrl = get_author_posts_url($authorId);
        $authorName = get_the_author_meta('display_name', $authorId);
        $authorImage = get_avatar_url($authorId, ['size' => 96]);
        $locale = get_locale();
        $personId = $authorUrl . '#author';

        $entity = [
            '@type' => 'Person',
            '@id' => $personId,
            'name' => $authorName,
            'url' => $authorUrl,
            'inLanguage' => $locale,
        ];

        if ($authorImage) {
            $entity['image'] = [
                '@type' => 'ImageObject',
                '@id' => $personId . '#image',
                'url' => $authorImage,
                'contentUrl' => $authorImage,
                'caption' => $authorName,
                'inLanguage' => $locale
            ];
        }

        // Réseaux sociaux ou site personnel (optionnel)
        $website = get_the_author_meta('user_url', $authorId);
        $sameAs = array_filter([$website]);

        if (!empty($sameAs)) {
            $entity['sameAs'] = $sameAs;
        }

        return $entity;
    }
}

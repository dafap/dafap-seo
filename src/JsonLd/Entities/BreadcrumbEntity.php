<?php
namespace DafapSEO\JsonLd\Entities;

class BreadcrumbEntity
{
    protected \WP_Post $post;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
    }

    /**
     * @type: BreadcrumbList	Déclare le type d’entité
     * @id	                    Identifiant unique lié à la page
     * itemListElement	        Liste ordonnée des niveaux de navigation
     * position	                Ordre dans le fil d’Ariane
     * name	                    Nom affiché
     * item	                    URL de la page correspondante
     */
    public function build(): array
    {
        $permalink = get_permalink($this->post);
        $breadcrumbId = $permalink . '#breadcrumb';

        $items = [];

        // Accueil
        $items[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Accueil',
            'item' => home_url('/')
        ];

        // Catégorie principale (si disponible)
        $categories = get_the_category($this->post);
        if (!empty($categories)) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $categories[0]->name,
                'item' => get_category_link($categories[0]->term_id)
            ];
        }

        // Article courant
        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => get_the_title($this->post),
            'item' => $permalink
        ];

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $breadcrumbId,
            'itemListElement' => $items
        ];
    }
}

<?php

namespace DafapSEO\JsonLd\Entities;

class OrganizationEntity
{
    /**
     * @type: Organization	Déclare l’entité comme organisation éditrice
     * @id	                Identifiant unique (#organization)
     * name	                Nom du site ou de l’organisation
     * url	                URL du site
     * logo / image	        Image de l’organisation (favicon ou logo personnalisé)
     * sameAs	            Réseaux sociaux (si disponibles)
     * inLanguage	        Langue principale du site
     */
    public function build(): array
    {
        $siteUrl = home_url('/');
        $orgId = $siteUrl . '#organization';
        $orgName = get_bloginfo('name');
        $logoUrl = get_site_icon_url() ?: (
            get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : ''
        );
        $locale = get_locale();

        $entity = [
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => $orgName,
            'url' => $siteUrl,
            'inLanguage' => $locale,
        ];

        if ($logoUrl) {
            $entity['logo'] = [
                '@type' => 'ImageObject',
                '@id' => $orgId . '#logo',
                'url' => $logoUrl,
                'contentUrl' => $logoUrl,
                'caption' => $orgName
            ];
            $entity['image'] = ['@id' => $orgId . '#logo'];
        }

        // Réseaux sociaux (optionnel)
        $facebook = get_option('facebook_url');
        $twitter = get_option('twitter_url');
        $sameAs = array_filter([$facebook, $twitter]);

        if (!empty($sameAs)) {
            $entity['sameAs'] = $sameAs;
        }

        return $entity;
    }
}

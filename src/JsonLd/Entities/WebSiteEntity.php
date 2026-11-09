<?php
namespace DafapSEO\JsonLd\Entities;

class WebSiteEntity
{
    /**
     * @type: WebSite	Déclare l’entité comme un site web
     * @id	            Identifiant unique du site (suffixé par #website)
     * name	            Nom du site (get_bloginfo('name'))
     * description	    Description du site (get_bloginfo('description'))
     * publisher	    Lien vers l’entité Organization
     * potentialAction	Décrit la recherche interne du site
     * inLanguage	    Langue principale du site (get_locale())
     */
    public function build(): array
    {
        $siteUrl = home_url('/');
        $siteName = get_bloginfo('name');
        $siteDescription = get_bloginfo('description');
        $locale = get_locale();

        return [
            '@type' => 'WebSite',
            '@id' => $siteUrl . '#website',
            'url' => $siteUrl,
            'name' => $siteName,
            'description' => $siteDescription,
            'publisher' => [
                '@id' => $siteUrl . '#organization'
            ],
            'potentialAction' => [[
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $siteUrl . '?s={search_term_string}'
                ],
                'query-input' => [
                    '@type' => 'PropertyValueSpecification',
                    'valueRequired' => true,
                    'valueName' => 'search_term_string'
                ]
            ]],
            'inLanguage' => $locale
        ];
    }
}

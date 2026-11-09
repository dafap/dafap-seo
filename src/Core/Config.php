<?php
namespace DafapSEO\Core;

class Config
{
    public const PLUGIN_SLUG = "dafap-seo";
    public const SETTINGS_FIELDS = [
        'facebook' => [
            'page_fb' => '_dafap_seo_owner_facebook',
            'app_id' => '_dafap_seo_fb_app_id'
        ],
        'x' => [
            'profil_x' => '_dafap_seo_owner_x'
        ]
    ];
    //public const OPTION_FACEBOOK_URL = '_dafap_seo_owner_facebook';
    //public const OPTION_X_URL = '_dafap_seo_owner_x';

    public const FIELDS = [
        'plugin_fields' => [
            'focus_kw' => '_dafap_seo_focus_kw',
            'seo_title' => '_dafap_seo_title',
            'seo_meta_desc' => '_dafap_seo_meta_desc',
            'secondary_kws' => '_dafap_seo_secondary_kws',
        ],
        'form_fields' => [
            'focus_kw' => 'dafap_seo_focus_kw',
            'seo_title' => 'dafap_seo_title',
            'seo_meta_desc' => 'dafap_seo_meta_desc',
            'secondary_kws' => 'dafap_seo_secondary_kws'
        ],
        'social' => [
            'plugin_fields' => [
                'og_title' => '_dafap_seo_social_title',
                'og_desc' => '_dafap_seo_social_desc',
                'og_image' =>'_dafap_seo_social_image',
                'og_image_alt' => '_dafap_seo_social_image_alt',
                'x_title' => '_dafap_seo_x_title',
                'x_desc' => '_dafap_seo_x_desc',
                'x_image' => '_dafap_seo_x_image',
                'x_image_alt' => '_dafap_seo_x_image_alt',
            ],
            'form_fields' => [
                'og_title' => 'dafap_seo_social_title',
                'og_desc' => 'dafap_seo_social_desc',
                'og_image' =>'dafap_seo_social_image',
                'og_image_alt' => 'dafap_seo_social_image_alt',
                'x_title' => 'dafap_seo_x_title',
                'x_desc' => 'dafap_seo_x_desc',
                'x_image' => 'dafap_seo_x_image',
                'x_image_alt' => 'dafap_seo_x_image_alt',
            ]
        ]
    ];

    public const NONCE = [
        'metabox' => [
            'action' => 'dafap_seo_metabox_nonce',
            'field_name' => 'dafap_seo_nonce'
        ]
    ];

    public const CRITERES = [
        'presence_mot_cle_titre' => [
            'poids' => 10,
            'optimum' => true,
            'warn' => false,
        ],
        'presence_mot_cle_h1' => [
            'poids' => 8,
            'too_far' => 20,
            'optimum' => true,
            'warn' => false,
        ],
        'mot_cle_dans_intro' => [
            'poids' => 7,
            'max_words' => 100,
            'optimum' => true,
            'warn' => false,
        ],
        'presence_mot_cle_meta_desc' => [
            'poids' => 5,
            'optimum' => true,
            'warn' => false,
        ],
        'presence_mot_cle_url' => [
            'poids' => 5,
            'optimum' => true,
            'warn' => false,
        ],
        'longueur_contenu' => [
            'poids' => 15,
            'min' => 1000,
            'warn' => 300,
        ],
        'densite_mot_cle' => [
            'poids' => 10,
            'min' => 0.5,
            'max' => 2.5,
            'warn_low' => 0.5,
            'warn_high' => 3.0,
        ],
        'presence_mots_cles_secondaires' => [
            'poids' => 5,
            'min' => 2,
        ],
        'lisibilite_headings' => [
            'poids' => 8,
            'structure' => true,
        ],
        'longueur_titre_seo' => [
            'poids' => 5,
            'min' => 45,
            'max' => 60,
            'warn_low' => 45,
            'warn_high' => 70,
        ],
        'longueur_meta_desc' => [
            'poids' => 4,
            'min' => 120,
            'max' => 150,
            'warn_low' => 100,
            'warn_high' => 160,
        ],
        'longueur_paragraphes' => [
            'poids' => 2,
            'max_mots' => 60,
            'warn_lignes' => 7,
        ],
        'usage_voix_passive' => [
            'poids' => 1,
            'max_percent' => 10,
            'warn_percent' => 15,
        ],
        'maillage_interne' => [
            'poids' => 7,
            'min_liens' => 3,
            'warn_liens' => 1,
        ],
        'optimisation_images' => [
            'poids' => 5,
            'min_images' => 1,
        ],
        'liens_externes' => [
            'poids' => 3,
            'min_liens' => 1,
        ],
    ];
}

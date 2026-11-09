# 🧩 Fonctionnalités proposées pour le plugin `Dafap SEO`
## 🔍 Analyse SEO de l’article
- **Score global SEO** affiché dans l’interface d’édition (metabox ou colonne personnalisée)
- **Analyse des mots-clés principaux** : fréquence, position, densité
- **Longueur du titre et de la meta description** avec indicateurs de conformité
- **Structure des balises Hn** : hiérarchie, présence, cohérence
- **Analyse de lisibilité** : phrases trop longues, voix passive, complexité syntaxique
- **Vérification de la présence d’image avec attribut `alt`**

## 🧠 Suggestions d’amélioration
- Recommandations dynamiques pour améliorer le score SEO
- Indicateurs de sur-optimisation ou de contenu trop pauvre
- Avertissements sur les liens cassés ou absents

## 🗂 Intégration WordPress
- Affichage du score SEO dans la **liste des articles** (colonne personnalisée)
- **Metabox personnalisée** dans l’éditeur classique ou Gutenberg
- Compatibilité avec les **Custom Post Types** (via filtre ou paramètre)

## 🛠 Architecture technique
- Plugin modulaire avec séparation stricte :
  - `src/Utils/` pour les helpers 
  -  `src/SEO/` pour les analyseurs
  - `src/Admin/` pour les metaboxes et colonnes
  - `assets/` pour les styles et scripts
  - `templates/` pour les rendus HTML
- Utilisation de **hooks** (`save_post`, `admin_init`, `enqueue_scripts`) pour une intégration native
- **Namespace** : `DafapSEO\` pour éviter les collisions
- Implémentation au standard PSR-4
- Fichier principal `dafap-seo.php` avec chargement conditionnel

## 📊 Export et diagnostic
- Export du score SEO en JSON pour usage externe ou archivage
- Mode debug pour afficher les critères analysés et leur pondération

## 🌐 Internationalisation
- Fichiers .pot pour traduction
- Préparation pour `fr_FR` et `en_US` via `load_plugin_textdomain`

# Arborescence du plugin
## La structure de fichiers
```
dafap-seo/
├── dafap-seo.php
├── composer.json
├── README.md
├── assets/
│   ├── css/
│   └── js/
├── templates/
├── languages/
│   └── dafap-seo.pot
├── src/
│   ├── Admin/
│   │   ├── Metabox.php
│   │   └── Column.php
│   ├── Core/
│   │   ├── Plugin.php
│   │   └── Hooks.php
│   ├── SEO/
│   │   ├── Analyzer.php
│   │   ├── Score.php
│   │   └── Suggestions.php
│   ├── Utils/
│   │   └── HtmlHelper.php
└── vendor/
```
## Autoloader des classes
### Le fichier `composer.json` minimal (PSR-4)
```
{
  "name": "dafap/dafap-seo",
  "description": "Plugin WordPress pour évaluer le score SEO des articles",
  "type": "wordpress-plugin",
  "require": {},
  "autoload": {
    "psr-4": {
      "DafapSEO\\": "src/"
    }
  }
}
```
### Chargement de l'autoloader dans le fichier principal du plugin
Dans `dafap-seo.php` :
```
<?php
/**
 * Plugin Name: Dafap SEO
 * Description: Évalue le score SEO des articles WordPress.
 * Version: 0.1.0
 * Author: Alain
 * Text Domain: dafap-seo
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

use DafapSEO\Core\Plugin;

add_action('plugins_loaded', [Plugin::class, 'init']);
```
Tout se passe ensuite dans les classes.
### Chargement de la traduction
Dans la classe `Plugin` du fichier `Plugin.php`
```
namespace DafapSEO\Core;

class Plugin {
    public static function init(): void {
        load_plugin_textdomain('dafap-seo', false, dirname(plugin_basename(__FILE__)) . '/languages');
        Hooks::register();
    }
}
```
Tout se passe ensuite dans la classe `Hooks` et les classes référencées.
# La partie Administration du plugin
## Principe de base
La partie Administration se compose de trois parties
- Dans la liste des articles, affichage du score SEO de chaque article.
- Pour chaque article :
  - une partie bilan détaillée basée sur une meta box qui expose l'état de le surveillance SEO et propose des améliorations. Cette surveillance évalue le score SEO de l'article à chaque sauvegarde.
  - une partie dynamique développée en JSX et intégrée à Gutenberg (développement REACT), permettant de visualiser le snippet de Google concernant cet article.
## 🧩 Architecture du diagnostic visuel
### 🔍 Objectif
#### Afficher dans la metabox :
- Le score global
- Les critères analysés (ex. titre, contenu, images, balises Hn…)
- Pour chaque critère :

  - ✅ Statut (OK / à améliorer)

  - 📊 Pondération

  - 💡 Suggestion
- Enregistrer le score global et les critères analysés lors de l'enregistrement de l'article (création ou modification)

#### Afficher dans une box dynamique l'aspect du snippet de Google lors de la création ou de la modification de l'article.
Possibilité de :
- modifier le mot clé principal
- modifier le titre SEO
- modifier la méta description SEP

#### Afficher dans la liste des articles
- Pour chaque article, son score global sur 100 qui a été calculé et enregistré lors de sa création ou de sa dernière modification.

### Classe `Analyzer.php`
- Evalue le score SEO de l'article par une méthode publique `compute_score(int $post_id): int`
- La méthode publique `diagnose(int $post_id): array` renvoie un tableau détaillé donnant, pour chaque item analysé et noté, une structure de tableau avec les clés 'label', 'score', 'status' (ok ou warn) et 'suggestion' (si warn).

### Critères d'analyse SEO pour un article de WordPress

| Label (Clé pour mon tableau)    | Poids attribué | Critère analysé      | Optimum (ok)      | Limite warn (seuil minimal recommandé) |
|---------------------------------|----------------|----------------------|-------------------|--------------------|
| **Optimisation du Mot-Clé Principal (35 pts)** | | | | **Facteur de classement fondamental.** |
| presence_mot_cle_titre | 10 | Le mot-clé principal est-il dans la Balise Titre (`<title>`) ? | Présent et de préférence au début. | Absent. (Facteur crucial) |
| presence_mot_cle_h1 | 8 | Le mot-clé principal est-il dans le Titre principal de l'article (H1) ? | Présent (idéalement c'est le même que le titre SEO). | Absent. |
| mot_cle_dans_intro | 7 | Le mot-clé principal est-il présent dans les 100 premiers mots du contenu ? | Présent. | Absent. |
| presence_mot_cle_meta_desc | 5 | Le mot-clé principal est-il dans la Méta Description ? | Présent, au début si possible. | Absent. |
| presence_mot_cle_url | 5 | Le mot-clé principal est-il dans l'URL (slug) ? | Présent et l'URL est courte et lisible. | Absent ou URL trop longue/confuse. |
| **Qualité et Longueur du Contenu (30 pts)** | | | | **Essentiel pour la qualité et la profondeur.** |
| longueur_contenu | 15 | Nombre total de mots dans l'article. | Au moins 1000 mots. | < 300 mots. (Le contenu court a plus de mal à se classer) |
| densite_mot_cle | 10 | Densité du mot-clé principal dans le corps de l'article (en pourcentage). | 0.5% - 2.5%. (Insertion naturelle) | < 0.5% (Sous-optimisé) ou > 3% (Bourrage de mots-clés) |
| presence_mots_cles_secondaires | 5 | Y a-t-il des mots-clés secondaires ou des synonymes pertinents ? | Présents et bien répartis. | Absent ou très peu (moins de 2 occurrences). |
| **Structure et Lisibilité (20 pts)** | | | | **Impacte l'expérience utilisateur (UX) et l'exploration des bots.** |
| lisibilite_headings | 8 | Utilisation correcte des balises de titres (H2, H3, H4) pour structurer le contenu. | Hiérarchie logique (H1 > H2 > H3...) avec H2/H3 contenant des mots-clés. | H2/H3 absents ou désordonnés (ex: H1 direct à H4). |
| longueur_titre_seo | 5 | Longueur de la Balise Titre (`<title>`). | 45 - 60 caractères. | < 45 ou > 70 caractères. |
| longueur_meta_desc | 4 | Longueur de la Méta Description. | 120 - 150 caractères. | < 100 ou > 160 caractères. |
| longueur_paragraphes | 2 | Longueur moyenne des paragraphes. | Maximum 4 à 5 lignes de texte (ou 60 mots). | Paragraphes très longs (plus de 7 lignes en moyenne). (Impacte la lisibilité) |
| usage_voix_passive | 1 | Pourcentage de phrases écrites à la voix passive. | Moins de 10% (préférer la voix active). | Plus de 15%. (Impacte la lisibilité) |
| **Optimisation Technique (15 pts)** | | | | **Optimisation des éléments d'accompagnement du contenu.** |
| maillage_interne | 7 | Nombre de liens internes (vers d'autres articles de votre site). | Minimum 3 liens. | < 1 lien. |
| optimisation_images | 5 | Présence d'au moins une image avec l'attribut Alt Text contenant le mot-clé. | Au moins 1 image avec un Alt Text descriptif et mot-clé inclus. | Aucune image avec Alt Text, ou Alt Text vide/générique. |
| liens_externes | 3 | Nombre de liens externes (vers des sites d'autorité). | Minimum 1 lien. | < 1 lien (sauf si non pertinent). |

### La classe `Metabox`
Elle propose de saisir les données suivantes :
- le mot-clé principal
- les mots-clés secondaires
- le titre SEO
- la méta description (ou description SEO)
Elle affiche le rapport détaillé de la dernière analyse.
### Le module `gutenberg.js`
Ce script `gutenberg.js` repose sur `React`, `JSX` et une architecture modulaire avec des stores WordPress (`@wordpress/data`), des composants (`@wordpress/components`), et des hooks. Il doit être compilé car les navigateurs ne comprennent pas `JSX`.

#### Les principes du module `gutenberg.js`
Les clés de métadonnées et les paramètres de validation sont centralisées dans une classe PHP (`Config.php`). Il faut les injecter dans le script Gutenberg (gutenberg.js) pour garantir :
- Cohérence entre PHP et JS
- Facilité de maintenance
- Internationalisation (i18n)
- Architecture modulaire et évolutive

L'injection des données dans `gutenberg.js` se fait depuis la classe `Hooks` via `wp_localize_script`.

La récupérations des paramètres dans `gutenberg.js`est centralisée :
```
const CONFIG = window.DafapSEOConfig;

const META_KEYS = {
  title: CONFIG.metaKeyTitle,
  desc: CONFIG.metaKeyDesc,
  focus: CONFIG.metaKeyFocus
};
```
La lecture des métadonnées sécurisée est assurée par :
```
const getMetaValue = (meta, key) => (meta[key]?.trim() || '');
```
Le composant réutilisable `SeoField` assure la mise en page, tenant compte des obsolescences annoncées et de la validation.


#### La compilation du module `gutenberg.js`
D'abord il faut installer les packages utilisés :
```
Bash
npm install @wordpress/i18n @wordpress/plugins @wordpress/editor @wordpress/components @wordpress/data @wordpress/element
```
Ensuite, il faut configurer Webpack en créant un fichier `webpack.config.js`.
```
const path = require('path');

module.exports = {
    entry: './assets/js/gutenberg.js',
    output: {
        path: path.resolve(__dirname, 'assets/js'),
        filename: 'gutenberg.build.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: 'babel-loader'
            }
        ]
    },
    externals: {
        '@wordpress/i18n': ['wp', 'i18n'],
        '@wordpress/plugins': ['wp', 'plugins'],
        '@wordpress/editor': ['wp', 'editor'],
        '@wordpress/components': ['wp', 'components'],
        '@wordpress/data': ['wp', 'data'],
        '@wordpress/element': ['wp', 'element']
    },
    resolve: {
        extensions: ['.js']
    },
    mode: 'development'
};
```
Le mode sera :
- `production` pour obtenir un fichier exécutable compressé
- `development` pour obtenir un javascript de débuggage dans le navigateur

On doit installer Babel :
```
npm install --save-dev babel-loader @babel/core @babel/preset-env
```
Puis créer le fichier `.babelrc` au format `json` :
```
{
  "presets": ["@babel/preset-env"]
}
```
Ensuite on compile :
```
npx webpack
```
----- 
**Bonus**

On peut aussi utiliser Webpack / Balel / Vite directement depuis VS Code. Pour cela, ajouter dans `package.json` :
```
"scripts": {
  "build": "webpack",
  "dev": "webpack --watch"
}
```

### La colonne `Score SEO` dans la liste des articles
C'est la classe `Column` qui met en place la colonne `Score SEO` dans la liste des articles. Cette classe ne présente que 2 méthodes publiques :
- la méthode `add` met en place une colonne et son titre ;
- la méthode `render` peuple cette colonne. 

# La partie Front : publication des articles avec leurs balises meta SEO
## Objectif
Injecter dans le `<head>` des pages de type `single` :
- `<title>` personnalisé
- `<meta name="description">`
- `<meta name="keywords">` (mot-clé pricipal + secondaires)
- (optionnel) `<meta property="og:title">`, `<meta property="og:description">`, etc.

S'assurer que la balise `<title>` est unique.
## Implémentation dans le plugin
### Hook : `wp_head`
Soit dans le fichier principal, soit dans une classe dédiée :
```
// Front
        add_filter('document_title_parts', [MetaBuilder::class, 'replace_document_title']);
        add_action('wp_head', function () {
            if (is_singular('post') && $post = get_post()) {
                (new MetaBuilder($post))->output();
            }
        });
```
Le filtre `document_title_parts` permet d'intercepter le `title` composé par WordPress avant l'affichage pour le modifier et le renvoyer dans le flux du traitement. La méthode statique `replace_document_title` de la classe `MetaBuilder` se charge de ce travail.
### Classe `MetaBuilder`
```
namespace DafapSEO\Front;

use DafapSEO\Core\Config;

class MetaBuilder
{
    public static function replace_document_title(array $title): array
    {
        if (is_singular('post')) {
            $post_id = get_the_ID();
            $seo_title = get_post_meta($post_id, Config::FIELDS['plugin_fields']['seo_title'], true);
            if (! empty($seo_title)) {
                $title['title'] = $seo_title;
            }
        }
        return $title;
    }
}
```
Cette méthode remplace le `title` proposé par WordPress par le `seo_title` indiqué par l'auteur de l'article.

Ensuite, la méthode publique `output` met en place les balises `<link ...>` et `<meta ...>`.
## Balises SEO recommandées
| Balise | Rôle | Recommandation |
|--------|------|----------------|
| `<title>` | Titre affiché dans l'onglet et les SERP. | Doit être unique et descriptif. |
| `<meta name="description">` | Résumé affiché dans les résultats Google | De 120 à 160 caractères. |
| `<meta name="keywords">` | Liste des mots-clés (moins utilisée aujourd'hui) | Peu aider certains moteurs. |
| `<meta property="og:title">` | Titre pour le partage sur les réseaux sociaux | Reprend ou surcharge le titre SEO. | 
| `<meta property="og:description">` | Description pour les réseaux | Reprend ou surcharge la description. |
| `<meta property="og:url">` | URL canonique | Utilise `get_permalink()` |
| `<meta property="og:type">`| Type de contenu | `"article"` pour les posts |
| `<meta property="og:image">` | Spécifie l’image à afficher lors du partage de la page. | Utiliser une image pertinente et attractive. Taille recommandée : 1200x630 px. Format: `JPEG` ou `PNG`. Poids inférieur à 300 Ko. On peut utiliser l'image à la une (`get_the_post_thumbnail_url`)|
| `<meta property="og:locale">`| Indique la langue et la région du contenu. | Utiliser le format langue_région (ex. : `fr_FR`, `en_US`, `es_ES`). On peut utiliser `get_locale()` |
| `<meta property="og:site_name">` | Le nom du site web tel qu'il doit apparaître dans le partage. | Utiliser le nom réel du site ou de l’organisation. On peut utiliser `get_bloginfo('name')` |

## Métadonnées sémantiques
### Linked Data en JSON `ld+json`
Pour les moteurs de recherche :
- Comprendre le type de contenu : article, produit, événement, recette, etc.
- Identifier les auteurs, dates, images, catégories, prix, lieux, etc.
- Afficher des rich snippets dans les résultats (extraits enrichis).

En général, cela améliore le référencement.

_Voir la fiche spécifique sur un graphe JSON-LD_
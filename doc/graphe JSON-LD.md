# 📘 Fiche technique : Structure d’un graphe JSON-LD complet pour WordPress

## 🎯 Objectif
Permettre aux moteurs de recherche (Google, Bing, etc.) de comprendre sémantiquement le contenu d’une page WordPress en injectant un bloc JSON-LD conforme à [schema.org](https://schema.org/).

---

## 🧠 Qu’est-ce qu’un graphe JSON-LD ?
- **JSON-LD** : format de données structurées en JSON (Linked Data).
- **Graphe** : ensemble d’entités liées entre elles via des identifiants (`@id`).
- **Usage** : injecté dans le `<head>` via `<script type="application/ld+json">`.

---

## 🧱 Structure typique du graphe

```json
{
  "@context": "https://schema.org",
  "@graph": [
    { "@type": "Article", ... },
    { "@type": "WebPage", ... },
    { "@type": "ImageObject", ... },
    { "@type": "BreadcrumbList", ... },
    { "@type": "WebSite", ... },
    { "@type": "Organization", ... },
    { "@type": "Person", ... }
  ]
}
```

---

## 🔍 Détail des entités

### 1. `Article`
Représente le contenu principal (post, page, produit…).

```json
{
  "@type": "Article",
  "headline": "Titre H1 de l'article dans la page",
  "datePublished": "2025-10-28T12:00:00+00:00",
  "dateModified": "2025-10-28T14:00:00+00:00",
  "author": { "@id": "#author", "name": "Nom de l'auteur" },
  "publisher": { "@id": "#organization" },
  "image": { "@id": "#primaryimage" },
  "thumbnailUrl": "url de la première image de l'article",
  "keywords": ["mot-clé principal", "secondaires", "étiquettes WP"],
  "articleSection": ["Catégorie", ...],
  "inLanguage": "fr-FR",
  "mainEntityOfPage": { "@id": "#webpage" },
  "wordCount": nombre de mots,
  "@id": "#article"
}
```

---

### 2. `WebPage`
Décrit la page contenant l’article.

```json
{
  "@type": "WebPage",
  "url": "https://example.com/article",
  "name": "Titre SEO",
  "description": "Description SEO",
  "isPartOf": "#website",
  "primaryImageOfPage": { "@id": "#primaryimage" },
  "image": { "@id": #primaryimage},
  "thumbnailUrl": "url de la première image de l'article",
  "datePublished": "2025-10-28T12:00:00+00:00",
  "dateModified": "2025-10-28T14:00:00+00:00",
  "breadcrumb": { "@id": "#breadcrumb" },
  "inLanguage": "fr-FR",
  "@id": "#webpage",
	"potentialAction":[
	{
		"@type":"ReadAction",
		"target":["url de l'article"]
	}]
}
```

---

### 3. `ImageObject`
Décrit l’image principale.

```json
{
  "@type": "ImageObject",
  "inLanguage": "fr-FR",
  "url": "https://example.com/image.jpg",
  "contentUrl": "https://example.com/image.jpg",
  "caption": "Légende",
  "width": largeur en pixel,
  "height": hauteur en pixel,
  "@id": "#primaryimage"
}
```

---

### 4. `BreadcrumbList`
Permet l’affichage du fil d’Ariane dans les résultats Google.

```json
{
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "https://example.com" },
    { "@type": "ListItem", "position": 2, "name": "H1 de l'article" }
  ],
  "@id": "#breadcrumb"
}
```

---

### 5. `WebSite`
Décrit le site dans son ensemble.

```json
{
  "@type": "WebSite",
  "name": "Nom du site",
  "url": "https://example.com",
  "publisher": { "@id": "#organization" },
  "potentialAction": [{
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "https://example.com/?s={search_term_string}"
    },
    "query-input": {
      "@type": "PropertyValueSpecification",
      "valueRequired": true,
      "valueName": "search_term_string"
    }
  }],
  "@id": "#website"
}
```

---

### 6. `Organization`
Identifie l’éditeur du site.

```json
{
  "@type": "Organization",
  "@id": "#organization",
  "name": "Biennale Culturelle Maçonnique de Bordeaux",
  "url": "https://example.com",
  "logo": {
    "@type": "ImageObject",
		"inLanguage":"fr-FR",
		"@id":"https://example.com/#/schema/logo/image/",
    "url": "https://example.com/logo.jpg",
		"contentUrl": "https://example.com/logo.jpg",
		"width": largeur en px,
		"height": hauteur en px,
		"caption": "texte descriptif"
  },
  "sameAs": ["url de la page Facebook"]
}
```

---

### 7. `Person`
Identifie l’auteur.

```json
{
  "@type": "Person",,
  "@id": "#author",
  "name": "Alain",
  "url": "https://example.com/auteur/alain",
  "image": {
    "@type": "ImageObject",
		"inLanguage":"fr-FR",
		"@id":"https://example.com/#/schema/person/image/",
    "url": "https://example.com/avatar.jpg",
		"contentUrl": "https://example.com/avatar.jpg",
		"caption": "texte descriptif"
  },
  "sameAs": ["url du site de la personne"],
  "url": "https://example.com/author/" + nom de la personne + "/"]
}
```

---

## ✅ Bonnes pratiques

- Utiliser des `@id` cohérents pour relier les entités.
- Injecter le bloc dans le `<head>` ou juste avant `</body>`.
- Valider avec [Google Rich Results Test](https://search.google.com/test/rich-results).
- Ne pas dupliquer les types (`Article`, `WebPage`) sur une même page.
- Utiliser `wp_json_encode()` avec les options `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.

---

## 🧩 Intégration dans WordPress

Dans `wp_head` :

```php
add_action('wp_head', function () {
    if (is_singular('post') && $post = get_post()) {
        echo (new \DafapSEO\Front\MetaBuilder($post))->buildJsonLd();
    }
});
```

---

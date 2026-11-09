# Fiche pratique : Optimiser les métadonnées sociales pour le partage sur les réseaux

## 🎯 Objectif
Améliorer la visibilité et l’engagement des contenus WordPress lorsqu’ils sont partagés sur les réseaux sociaux (Facebook, X/Twitter, LinkedIn, etc.) en configurant efficacement les balises Open Graph (`og:*`) et Twitter Card (`x:*`).

---

## 🧠 Pourquoi c’est important
- Les réseaux sociaux utilisent ces balises pour générer l’aperçu du lien partagé.
- Un bon aperçu = plus de clics, plus de partages, meilleure image de marque.
- Une mauvaise configuration = aperçu vide, générique ou peu engageant.

---

## 🧩 Balises principales à renseigner

| Balise        | Description | Recommandation |
|---------------|-------------|----------------|
| `og:title`    | Titre affiché sur Facebook/LinkedIn | 60 caractères max, percutant, clair |
| `og:description` | Résumé du contenu | 150–200 caractères, incitatif |
| `og:image`    | Image d’aperçu | 1200×630 px, JPG/PNG, < 300 Ko |
| `x:title`     | Titre pour X/Twitter | Même logique que `og:title`, mais plus court |
| `x:description` | Résumé pour X/Twitter | 120–160 caractères, direct |
| `x:image`     | Image pour X/Twitter | Format carré recommandé (800×800 px) |

---

## ✅ Bonnes pratiques

### 📝 Titres (`og:title`, `x:title`)
- Reprendre le titre SEO ou le reformuler pour plus d’impact.
- Éviter les titres génériques : “Nouvel article”, “Page d’accueil”.
- Utiliser des mots-clés émotionnels ou orientés bénéfice : “Découvrez”, “Apprenez”, “Évitez”.

### 📄 Descriptions (`og:description`, `x:description`)
- Compléter le titre sans le répéter.
- Mettre en avant la valeur du contenu : ce que le lecteur va apprendre ou gagner.
- Bannir les phrases vagues : “Un article intéressant sur le SEO…”

### 🖼️ Images (`og:image`, `x:image`)
- Image claire, bien cadrée, sans texte illisible.
- Éviter les logos seuls ou les images trop abstraites.
- Prévoir une image différente pour chaque réseau si nécessaire.

---

## 🧪 Outils de test

- [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)

---

## 🧰 Intégration dans WordPress

- Utiliser `update_post_meta()` pour enregistrer les champs personnalisés.
- Afficher les champs dans une MetaBox dédiée ou un panneau Gutenberg.
- Générer automatiquement les balises dans `<head>` via `wp_head` :

```php
add_action('wp_head', function () {
    if (is_singular('post')) {
        $og_title = get_post_meta(get_the_ID(), 'og_title', true);
        echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />';
        // Répéter pour les autres balises
    }
});
```

---

## 🧾 Résumé

| Élément | Conseil |
|--------|---------|
| Titre | Court, clair, incitatif |
| Description | Résumé engageant, complémentaire |
| Image | Illustrative, optimisée |
| Tests | Utiliser les validateurs Facebook/X |
| Cohérence | Aligné avec le contenu réel |

---
## La balise `fb:app_id` : guide pas à pas pour créer une application Facebook via Meta for Developers 

---

### 🛠️ Étape 1 : Créer une application Facebook

1. **Accédez à la plateforme développeur**  
   👉 [https://developers.facebook.com](https://developers.facebook.com)

2. **Connectez-vous** avec votre compte Facebook personnel (nécessaire pour créer une app).

3. **Cliquez sur “Mes applications”** (en haut à droite), puis sur **“Créer une application”**.

4. **Choisissez un type d’application**  
   Pour un site web, sélectionnez **“Aucun”** ou **“Entreprise”** selon vos besoins.

5. **Remplissez les informations de base** :
   - Nom de l’application (ex. : *Imaq.fr App*)
   - Adresse e-mail de contact
   - Domaine du site (ex. : `https://www.imaq.fr`)
   - Ajoutez une **politique de confidentialité** si vous en avez une

6. **Validez le CAPTCHA** et cliquez sur **“Créer une application”**.

---

### 🔑 Étape 2 : Récupérer votre App ID

Une fois l’application créée :
- Vous serez redirigé vers le tableau de bord de l’application.
- L’**App ID** est affiché en haut : un numéro comme `123456789012345`.

Copiez-le : c’est ce que vous allez intégrer dans votre site.

---

### 🧩 Étape 3 : Intégrer `fb:app_id` dans votre site

Dans le **code HTML** de vos pages (idéalement dans le `<head>`), ajoutez :

```html
<meta property="fb:app_id" content="123456789012345" />
```

Remplacez `123456789012345` par votre vrai App ID.

💡 Si vous utilisez un CMS comme WordPress, vous pouvez :
- Ajouter cette balise via un plugin SEO (ex. : Yoast SEO → section “Social”)
- Ou insérer manuellement dans le fichier `header.php` de votre thème

---

### ✅ Étape 4 : Vérifier avec l’outil de débogage

1. Rendez-vous sur : [https://developers.facebook.com/tools/debug](https://developers.facebook.com/tools/debug)
2. Collez l’URL d’une page de votre site
3. Cliquez sur **“Déboguer”**
4. Vérifiez que la propriété `fb:app_id` est bien détectée

---


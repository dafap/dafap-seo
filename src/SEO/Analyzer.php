<?php

namespace DafapSEO\SEO;

use DafapSEO\Core\Config;

class Analyzer
{
    /**
     * @var string Le mot-clé principal pour l'analyse.
     */
    private static $focusKeyword = '';

    /**
     * @var array La liste des mots-clés secondaires et des synonymes
     * Dans un plugin réel, ceci serait défini par l'utilisateur ou automatiquement pour les synonymes
     */
    private static $secondaryKeywords = [];

    /**
     * @var string Contenu complet (HTML ou nettoyé) de l'article pour analyse.
     * Dans un plugin réel, ceci serait extrait de $post->post_content.
     */
    private static $postContent = '';

    /**
     * @var array Données analysées du post (titre, URL, meta, etc.).
     * Pourrait inclure des données pré-nettoyées.
     */
    private static $postData = [];

    /**
     * Génère un tableau d'analyse détaillé pour l'article.
     *
     * @param int $post_id ID de l'article WordPress.
     * @return array Tableau d'analyse détaillé.
     */
    public static function diagnose(int $post_id): array
    {
        // 1. Initialisation des données (à implémenter en fonction de WordPress)
        self::loadPostData($post_id);

        $results = [];

        // 2. Exécution de chaque critère d'analyse
        $results['presence_mot_cle_titre']         = self::check_presence_mot_cle_titre();
        $results['longueur_titre_seo']             = self::check_longueur_titre_seo();
        $results['presence_mot_cle_meta_desc']     = self::check_presence_mot_cle_meta_desc();
        $results['longueur_meta_desc']             = self::check_longueur_meta_desc();
        $results['presence_mot_cle_h1']            = self::check_presence_mot_cle_h1();
        $results['mot_cle_dans_intro']             = self::check_mot_cle_dans_intro();
        $results['longueur_contenu']               = self::check_longueur_contenu();
        $results['densite_mot_cle']                = self::check_densite_mot_cle();
        $results['presence_mots_cles_secondaires'] = self::check_presence_mots_cles_secondaires();
        $results['presence_mot_cle_url']           = self::check_presence_mot_cle_url();
        $results['maillage_interne']               = self::check_maillage_interne();
        $results['liens_externes']                 = self::check_liens_externes();
        $results['optimisation_images']            = self::check_optimisation_images();
        $results['lisibilite_headings']            = self::check_lisibilite_headings();
        $results['longueur_paragraphes']           = self::check_longueur_paragraphes();
        $results['usage_voix_passive']             = self::check_usage_voix_passive();

        return $results;
    }

    // -----------------------------------------------------------------------
    // Méthodes Privées d'Aide (Préparation des données)
    // -----------------------------------------------------------------------

    /**
     * Charge et initialise toutes les données nécessaires (titre, contenu, mot-clé focus, etc.)
     * Cette méthode interagirait avec les fonctions natives de WordPress (get_post, get_post_meta).
     * @param int $post_id
     */
    private static function loadPostData(int $post_id): void
    {
        // Logique pour récupérer :
        // 1. L'objet Post de WordPress ($post)
        // 2. Le mot-clé focus (généralement stocké en meta data)
        // 3. Le titre SEO et la meta description (via WP ou un autre plugin SEO comme Yoast/AIOSEO)
        // 4. Le contenu post-nettoyage (self::$postContent = strip_tags($post->post_content);)

        // Exemple simplifié :
        // $post = get_post($post_id); 
        // self::$focusKeyword = get_post_meta($post_id, 'dafap_seo_focus_kw', true);
        // self::$postContent = $post->post_content; // Contenu brut
        // self::$postData['title'] = get_the_title($post_id);
        // ...
        // --- SIMULATION DES FONCTIONS WORDPRESS ---
        // Dans un plugin réel, vous utiliseriez les fonctions natives.

        // 1. Récupération de l'objet post principal
        $post = get_post($post_id);
        if (!$post) {
            // Gérer le cas où l'article n'existe pas
            self::$postContent = '';
            self::$focusKeyword = '';
            self::$postData = ['site_url' => home_url()];
            return;
        }

        // 2. Récupération des données structurées et standard
        self::$postData['title'] = $post->post_title;
        self::$postData['post_slug'] = $post->post_name;
        self::$postData['site_url'] = home_url();

        // La balise H1 est souvent le titre de l'article dans la structure WP
        self::$postData['h1_title'] = $post->post_title;

        // Le contenu brut, y compris le HTML (requis pour l'analyse des liens, images, headings)
        self::$postContent = $post->post_content;

        // 3. Récupération des données SEO (simulées via meta data)

        // Récupération du mot-clé focus (stocké par le plugin)
        self::$focusKeyword = get_post_meta($post_id, Config::FIELDS['plugin_fields']['focus_kw'], true);

        // Récupération du Titre SEO et de la Méta Description (souvent gérés par un plugin tiers ou le vôtre)
        // Si ces champs sont vides, on utilise les valeurs par défaut (titre de l'article pour le titre SEO).
        self::$postData['seo_title'] = get_post_meta($post_id, Config::FIELDS['plugin_fields']['seo_title'], true)
            ?: $post->post_title;

        // Récupération de la meta description
        self::$postData['meta_description'] = get_post_meta($post_id, Config::FIELDS['plugin_fields']['seo_meta_desc'], true)
            ?: self::generate_excerpt_from_content($post->post_content); // Fallback générique

        // Récupération des mots-clés secondaires (chaîne séparée par des virgules)
        $secondaryKwString = get_post_meta($post_id, Config::FIELDS['plugin_fields']['secondary_kws'], true);
        // Transformer la chaîne en tableau, nettoyer les espaces et les entrées vides
        self::$secondaryKeywords = array_filter(
            array_map('trim', explode(',', $secondaryKwString))
        );

        // Assurez-vous que le mot-clé est propre
        self::$focusKeyword = trim(self::$focusKeyword);
    }

    // --- Méthode d'aide pour simuler un extrait ---
    /**
     * Génère un extrait simple (comme fallback pour la méta description).
     * @param string $content Contenu brut.
     * @return string Extrait de 155 caractères max.
     */
    private static function generate_excerpt_from_content(string $content): string
    {
        $cleanText = strip_tags($content);
        $excerpt = mb_substr($cleanText, 0, 155);
        if (mb_strlen($cleanText) > 155) {
            $excerpt .= '...';
        }
        return trim($excerpt);
    }

    /**
     * Extrait la clé de poids du nom de la méthode et retourne le poids correspondant.
     *
     * @param string $methodName Nom de la méthode d'analyse (ex: 'check_presence_mot_cle_titre').
     * @return int Poids attribué à ce critère.
     */
    private static function get_weight(string $methodName): int
    {
        $key = str_replace('check_', '', $methodName);
        return Config::CRITERES[$key]['poids'] ?? 0;
    }

    // -----------------------------------------------------------------------
    // Méthodes Privées d'Analyse (Un par Critère)
    // -----------------------------------------------------------------------

    /**
     * Vérifie la présence du mot-clé dans la balise titre SEO.
     * @return array Résultat d'analyse.
     */
    private static function check_presence_mot_cle_titre(): array
    {
        $label = __("Présence du mot-clé dans le Titre SEO", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $title = self::$postData['seo_title'] ?? self::$postData['title']; // Récupération du titre

        $status = 'warn';
        $suggestion = __("Le mot-clé principal n'est pas présent dans le titre SEO. Ajoutez-le au début du titre pour un meilleur classement.", PLUGIN_DATA['TextDomain']);
        $score = 0;

        if (stripos($title, self::$focusKeyword) !== false) {
            $status = 'ok';
            $suggestion = __("Parfait ! Le mot-clé principal est présent dans le titre SEO.", PLUGIN_DATA['TextDomain']);
            $score = $weight;

            // Logique de 'warn' si présent mais pas au début (pour 5/10 points)
            if (stripos(trim($title), self::$focusKeyword) > 10) {
                $status = 'warn';
                $suggestion = __("Le mot-clé principal est présent, mais il n'est pas au début du titre. Essayez de le placer le plus tôt possible.", PLUGIN_DATA['TextDomain']);
                $score = $weight / 2;
            }
        }

        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score // Clé nécessaire pour compute_score
        ];
    }

    /**
     * Vérifie la longueur du Titre SEO (entre 45 et 60 caractères).
     * @return array Résultat d'analyse.
     */
    private static function check_longueur_titre_seo(): array
    {
        $label = __("Longueur du Titre SEO", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $title = self::$postData['seo_title'] ?? self::$postData['title'];
        $length = mb_strlen($title);
        $score = $weight;
        $status = 'ok';
        $suggestion = __("La longueur de votre titre SEO est optimale.", PLUGIN_DATA['TextDomain']);

        if ($length < Config::CRITERES['longueur_titre_seo']['min']) {
            $status = 'warn';
            $suggestion = sprintf(
                __("Votre titre SEO est trop court (actuel : %d caractères). Visez au moins %d caractères pour être plus descriptif.", PLUGIN_DATA['TextDomain']),
                $length,
                Config::CRITERES['longueur_titre_seo']['warn_low']
            );
            $score = $weight / 2;
        } elseif ($length > Config::CRITERES['longueur_titre_seo']['warn_high']) {
            $status = 'warn';
            $suggestion = sprintf(
                __("Votre titre SEO est trop long (actuel : %d caractères). Il risque d'être tronqué dans les résultats de recherche. Visez moins de %d-%d caractères.", PLUGIN_DATA['TextDomain']),
                $length,
                Config::CRITERES['longueur_titre_seo']['max'],
                Config::CRITERES['longueur_titre_seo']['warn_high']
            );
            $score = 0;
        }

        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la présence du mot-clé principal dans la méta-description.
     * Le mot-clé dans la méta-description est essentiel pour le Taux de Clic (CTR).
     *
     * @return array Résultat d'analyse.
     */
    private static function check_presence_mot_cle_meta_desc(): array
    {
        $label = __("Présence du mot-clé dans la Méta Description", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        // Récupération de la méta-description et du mot-clé.
        // Dans un environnement réel, cela utiliserait self::$postData et self::$focusKeyword.
        $metaDescription = self::$postData['meta_description'] ?? '';
        $keyword = self::$focusKeyword;
        $status = 'warn';
        $suggestion = __("La Méta Description n'est pas optimale.", PLUGIN_DATA['TextDomain']);
        $score = 0;
        if (empty($keyword)) {
            $suggestion = __("Le mot-clé principal n'a pas été défini pour cet article. Impossible d'analyser.", PLUGIN_DATA['TextDomain']);
        } elseif (stripos($metaDescription, $keyword) !== false) {
            $status = 'ok';
            $suggestion = __("Parfait ! Le mot-clé principal est présent dans la Méta Description.", PLUGIN_DATA['TextDomain']);
            $score = $weight;
            // Logique de 'warn' si présent mais pas au début (pour un meilleur CTR)
            // Si le mot-clé commence après le 50ème caractère, c'est moins visible.
            if (stripos($metaDescription, $keyword) > 50) {
                $status = 'warn';
                $suggestion = _("Le mot-clé principal est présent, mais il est placé trop loin dans la Méta Description. Pour maximiser le Taux de Clic (CTR), essayez de le placer dans les premières phrases.");
                $score = round($weight * 0.75, 2); // Attribue 75% du poids (ex: 3.75 sur 5)
            }
        } else {
            $suggestion = sprintf(
                __("Le mot-clé principal ('%s') est absent de la Méta Description. Ajoutez-le pour inciter les utilisateurs à cliquer.", PLUGIN_DATA['TextDomain']),
                $keyword
            );
            $score = 0;
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la longueur de la méta-description.
     * Optimum : 120 - 150 caractères. Warn : < 100 ou > 160.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_longueur_meta_desc(): array
    {
        $label = __("Longueur de la Méta Description", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        // Récupération de la méta-description (doit être chargé dans loadPostData)
        $metaDescription = self::$postData['meta_description'] ?? '';
        // Utilisation de mb_strlen pour compter correctement les caractères multi-octets (accents, etc.)
        $length = mb_strlen($metaDescription);
        $status = 'ok';
        $suggestion = sprintf(__("La longueur de votre Méta Description est optimale (%d caractères).", PLUGIN_DATA['TextDomain']), $length);
        $score = $weight;
        // Seuil d'avertissement haut (troncation par les moteurs de recherche)
        if ($length > Config::CRITERES['longueur_meta_desc']['warn_high']) {
            $status = 'warn';
            $suggestion = sprintf(
                __("Votre Méta Description est trop longue (actuel : %d caractères). Elle sera très probablement tronquée par Google, ce qui pourrait masquer l'appel à l'action. Réduisez-la à **%d-%d caractères** maximum.", PLUGIN_DATA['TextDomain']),
                $length,
                Config::CRITERES['longueur_meta_desc']['max'],
                Config::CRITERES['longueur_meta_desc']['warn_high']
            );
            $score = round($weight * 0.25, 2); // Score très faible (ex: 1 sur 4)
        }
        // Seuil d'avertissement bas (potentiel de CTR non exploité)
        elseif ($length < Config::CRITERES['longueur_meta_desc']['min']) {
            $status = 'warn';
            $suggestion = sprintf(
                __("Votre Méta Description est trop courte (actuel : %d caractères). Vous n'utilisez pas suffisamment d'espace pour inciter au clic. Visez **au moins %d caractères** pour être plus convaincant.", PLUGIN_DATA['TextDomain']),
                $length,
                Config::CRITERES['longueur_meta_desc']['min']
            );
            $score = round($weight * 0.5, 2); // Score moyen (ex: 2 sur 4)
        }
        // Acceptable mais non optimal (zone de tolérance)
        elseif ($length > Config::CRITERES['longueur_meta_desc']['max'] && $length <= Config::CRITERES['longueur_meta_desc']['warn_high']) {
            $status = 'warn';
            $suggestion = sprintf(
                __("Votre Méta Description est un peu longue (actuel : %d caractères) mais reste dans une zone tolérable. Essayez de la maintenir sous les %d caractères.", PLUGIN_DATA['TextDomain']),
                $length,
                Config::CRITERES['longueur_meta_desc']['max']
            );
            $score = round($weight * 0.75, 2); // Bon score (ex: 3 sur 4)
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la présence du mot-clé principal dans le titre H1 de l'article.
     * Le H1 doit confirmer le sujet principal et contenir le mot-clé.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_presence_mot_cle_h1(): array
    {
        $label = __("Présence du mot-clé dans le Titre H1", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        // Récupération du H1. 
        // Dans WordPress, le H1 est souvent le titre de l'article (get_the_title($post_id)).
        $h1Title = self::$postData['h1_title'] ?? self::$postData['title'] ?? '';
        $keyword = self::$focusKeyword;
        $status = 'warn';
        $suggestion = __("Le Titre H1 n'est pas optimal.", PLUGIN_DATA['TextDomain']);
        $score = 0;
        if (empty($keyword)) {
            $suggestion = __("Le mot-clé principal n'a pas été défini. Impossible d'analyser la présence dans le H1.", PLUGIN_DATA['TextDomain']);
        } elseif (stripos($h1Title, $keyword) !== false) {
            $status = 'ok';
            $suggestion = __("Excellent ! Le mot-clé principal est présent dans le Titre H1, confirmant la pertinence du sujet.", PLUGIN_DATA['TextDomain']);
            $score = $weight;
            // Si le H1 est très long ou si le KW est trop loin, une petite pénalité peut être appliquée.
            if (stripos(trim($h1Title), $keyword) > Config::CRITERES['presence_mot_cle_h1']['too_far']) {
                $status = 'warn';
                $suggestion = __("Le mot-clé est présent, mais il est placé loin du début du H1. Pour un impact maximal, essayez de le placer dans les premiers mots.", PLUGIN_DATA['TextDomain']);
                $score = round($weight * 0.75, 2); // Attribue 75% du poids (ex: 6 sur 8)
            }
        } else {
            $suggestion = sprintf(
                __("Le mot-clé principal ('%s') est absent du Titre H1. Ceci est une erreur majeure. Assurez-vous que le **H1 contient votre mot-clé** pour signaler le sujet de l'article aux moteurs de recherche.", PLUGIN_DATA['TextDomain']),
                $keyword
            );
            $score = 0;
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie si le mot-clé principal est présent dans les 100 premiers mots du contenu.
     * C'est crucial pour la pertinence immédiate du sujet.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_mot_cle_dans_intro(): array
    {
        $label = sprintf(
            __("Mot-clé principal dans l'introduction (%d premiers mots)", PLUGIN_DATA['TextDomain']),
            Config::CRITERES['mot_cle_dans_intro']['max_words']
        );
        $weight = self::get_weight(__FUNCTION__);
        $keyword = self::$focusKeyword;
        $content = self::$postContent; // Contenu brut de l'article
        $status = 'warn';
        $suggestion = __("L'analyse est incomplète ou le mot-clé est absent de l'introduction.", PLUGIN_DATA['TextDomain']);
        $score = 0;
        if (empty($keyword)) {
            $suggestion = __("Le mot-clé principal n'est pas défini. Impossible d'analyser l'introduction.", PLUGIN_DATA['TextDomain']);
        } elseif (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide ou n'a pas été chargé. Impossible d'analyser.", PLUGIN_DATA['TextDomain']);
        } else {
            // 1. Nettoyer le contenu : supprimer le HTML pour ne compter que les mots du texte.
            $cleanContent = strip_tags($content);

            // 2. Extraire les 100 premiers mots.
            // On split le texte par espaces et on prend les 100 premiers éléments.
            $words = preg_split('/\s+/', $cleanContent, -1, PREG_SPLIT_NO_EMPTY);
            $introWords = array_slice($words, 0, Config::CRITERES['mot_cle_dans_intro']['max_words']);
            $introText = implode(' ', $introWords);

            // 3. Vérifier la présence du mot-clé dans ce texte d'introduction
            if (stripos($introText, $keyword) !== false) {
                $status = 'ok';
                $suggestion = sprintf(
                    __("Excellent ! Le mot-clé principal est présent dans les %d premiers mots, ce qui assure une bonne pertinence thématique dès le début.", PLUGIN_DATA['TextDomain']),
                    Config::CRITERES['mot_cle_dans_intro']['max_words']
                );
                $score = $weight;
            } else {
                $suggestion = sprintf(
                    __("Le mot-clé principal ('%s') est absent des %d premiers mots de l'article. C'est une omission importante pour le SEO : intégrez-le naturellement et rapidement.", PLUGIN_DATA['TextDomain']),
                    $keyword,
                    Config::CRITERES['mot_cle_dans_intro']['max_words']
                );
                $score = 0;
            }
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la longueur du contenu de l'article en mots.
     * Optimum : >= 1000 mots. Limite 'warn' : < 300 mots.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_longueur_contenu(): array
    {
        $label = __("Longueur du contenu de l'article", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $content = self::$postContent; // Contenu brut de l'article
        $status = 'warn';
        // 1. Nettoyer le contenu : supprimer le HTML pour ne compter que les mots du texte.
        $cleanContent = strip_tags($content);

        // 2. Compter les mots (en utilisant une méthode robuste)
        $wordCount = str_word_count($cleanContent);
        // ------------------------------------------------
        // Évaluation des seuils
        // ------------------------------------------------
        if ($wordCount >= Config::CRITERES['longueur_contenu']['min']) {
            // OPTIMAL : > 1000 mots
            $status = 'ok';
            $suggestion = sprintf(
                __("Excellent ! Votre article est long (%d mots), ce qui est idéal pour la profondeur thématique et le classement. (Objectif > %d mots)", PLUGIN_DATA['TextDomain']),
                $wordCount,
                Config::CRITERES['longueur_contenu']['min']
            );
            $score = $weight;
        } elseif ($wordCount >= (Config::CRITERES['longueur_contenu']['warn'] + Config::CRITERES['longueur_contenu']['min']) * 0.384) {
            // ACCEPTABLE : 500 à 999 mots
            $suggestion = sprintf(
                __("La longueur de votre article est correcte (%d mots), mais il pourrait bénéficier de plus de détails et d'une plus grande profondeur. Visez un minimum de **%d mots**.", PLUGIN_DATA['TextDomain']),
                $wordCount,
                Config::CRITERES['longueur_contenu']['min']
            );
            $score = round($weight * 0.7, 2); // 70% du poids (ex: 10.5 sur 15)
        } elseif ($wordCount >= Config::CRITERES['longueur_contenu']['warn']) {
            // FAIBLE : 300 à 499 mots
            $suggestion = sprintf(
                __("Votre contenu est relativement court (%d mots). Il manque probablement de profondeur. Le contenu mince est difficile à classer. **Ajoutez du développement pour atteindre au moins %d mots**.", PLUGIN_DATA['TextDomain']),
                $wordCount,
                Config::CRITERES['longueur_contenu']['warn']
            );
            $score = round($weight * 0.4, 2); // 40% du poids (ex: 6 sur 15)
        } else {
            // TRÈS FAIBLE/PÉNALISANT : < 300 mots
            $suggestion = sprintf(
                __("Attention ! Votre article est beaucoup trop court (%d mots) et sera considéré comme du **contenu mince** par les moteurs de recherche. Il est impératif de le développer (Objectif > %d mots).", PLUGIN_DATA['TextDomain']),
                $wordCount,
                Config::CRITERES['longueur_contenu']['min']
            );
            $score = 0;
        }

        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la densité du mot-clé principal dans le contenu de l'article.
     * Optimum : 0.5% à 2.5%.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_densite_mot_cle(): array
    {
        $label = __("Densité du mot-clé principal", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $keyword = self::$focusKeyword;
        $content = self::$postContent;
        $status = 'warn';
        $suggestion = __("La densité du mot-clé n'est pas optimale.", PLUGIN_DATA['TextDomain']);
        $score = 0;
        if (empty($keyword)) {
            $suggestion = __("Le mot-clé principal n'est pas défini. Impossible de calculer la densité.", PLUGIN_DATA['TextDomain']);
        } elseif (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide ou n'a pas été chargé. Impossible de calculer la densité.", PLUGIN_DATA['TextDomain']);
        } else {
            // 1. Nettoyer et compter les mots.
            $cleanContent = strip_tags($content);
            $totalWordCount = str_word_count($cleanContent);

            // Éviter la division par zéro
            if ($totalWordCount === 0) {
                return [
                    'label' => $label,
                    'score' => $weight,
                    'status' => 'warn',
                    'suggestion' => __("Le contenu est vide. La densité ne peut pas être calculée.", PLUGIN_DATA['TextDomain']),
                    'points_obtenus' => 0
                ];
            }

            // 2. Compter les occurrences du mot-clé.
            // On utilise une expression régulière pour compter les occurrences du mot-clé comme un mot entier, 
            // pour éviter de compter des parties de mots plus longs.
            $keywordCount = preg_match_all('/\b' . preg_quote($keyword, '/') . '\b/iu', $cleanContent, $matches);

            // 3. Calculer la densité.
            $density = ($keywordCount / $totalWordCount) * 100;
            $density = round($density, 2); // Arrondi à deux décimales

            $suggestion = sprintf(__("Densité actuelle : %.2f%%", PLUGIN_DATA['TextDomain']), $density);

            // ------------------------------------------------
            // Évaluation des seuils (Optimal: 0.5% à 2.5%)
            // ------------------------------------------------

            if ($density >= Config::CRITERES['densite_mot_cle']['min'] && $density <= Config::CRITERES['densite_mot_cle']['max']) {
                // OPTIMAL
                $status = 'ok';
                $suggestion .= __(" La densité est excellente ! Elle est suffisante sans tomber dans le bourrage de mots-clés (keyword stuffing).", PLUGIN_DATA['TextDomain']);
                $score = $weight;
            } elseif ($density < Config::CRITERES['densite_mot_cle']['min'] && $density >= Config::CRITERES['densite_mot_cle']['min'] / 5) {
                // SOUS-OPTIMISÉ (Faible)
                $status = 'warn';
                $suggestion .= sprintf(
                    __(" La densité est faible. Le mot-clé principal ('%s') apparaît trop peu souvent. Ajoutez-le quelques fois de plus dans le texte pour renforcer la pertinence.", PLUGIN_DATA['TextDomain']),
                    $keyword
                );
                $score = round($weight * 0.5, 2); // 50% du poids (ex: 5 sur 10)
            } elseif ($density > Config::CRITERES['densite_mot_cle']['max']) {
                // SUR-OPTIMISÉ (Bourrage de mots-clés)
                $status = 'warn';
                $suggestion .= __(" Attention ! La densité est trop élevée. Cela peut être perçu comme du **bourrage de mots-clés** (keyword stuffing) et nuire à la lisibilité. Réduisez les occurrences en utilisant des synonymes ou des termes secondaires.", PLUGIN_DATA['TextDomain']);
                $score = 0;
            } else {
                // TRÈS SOUS-OPTIMISÉ (Quasi-absent)
                $status = 'warn';
                $suggestion .= __(" La densité est critique. Le mot-clé est presque absent du corps de l'article. C'est une erreur majeure de pertinence.", PLUGIN_DATA['TextDomain']);
                $score = 0;
            }
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la présence de mots-clés secondaires ou de synonymes pour valider la richesse sémantique.
     * Optimum : Au moins 3 mots-clés secondaires/synonymes présents.
     *
     * NOTE: Dans un plugin réel, les mots-clés secondaires devraient être définis par l'utilisateur
     * ou automatiquement générés (par exemple, des synonymes du mot-clé principal).
     *
     * @return array Résultat d'analyse.
     */
    private static function check_presence_mots_cles_secondaires(): array
    {
        $label = __("Richesse sémantique (Mots-clés secondaires/Synonymes)", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $content = self::$postContent;
        $status = 'warn';
        //$suggestion = __("Le contenu manque de richesse sémantique.",PLUGIN_DATA['TextDomain']);
        $score = 0;
        $secondaryKeywords = self::$secondaryKeywords;
        $minRequired = Config::CRITERES['presence_mots_cles_secondaires']['min'];
        $foundCount = 0;
        if (empty($secondaryKeywords)) {
            $suggestion = __("Aucun mot-clé secondaire n'a été défini. C'est essentiel pour la richesse sémantique.", PLUGIN_DATA['TextDomain']);
            // On peut attribuer un petit score pour ne pas bloquer l'analyse :
            $score = 2;
        } elseif (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide. Impossible d'analyser la richesse sémantique.", PLUGIN_DATA['TextDomain']);
        } else {
            $cleanContent = strip_tags($content);

            // Compter les occurrences de chaque mot-clé secondaire
            foreach (self::$secondaryKeywords as $secondaryKw) {
                // Utilisation de \b pour s'assurer que c'est un mot complet, case-insensitive
                if (preg_match('/\b' . preg_quote($secondaryKw, '/') . '\b/iu', $cleanContent)) {
                    $foundCount++;
                }
            }

            // ------------------------------------------------
            // Évaluation des seuils (Basée sur le nombre de KW définis)
            // ------------------------------------------------
            $targetCount = min($minRequired, count($secondaryKeywords)); // Ne pas exiger plus que ce qui est fourni

            if ($foundCount >= $targetCount) {
                $status = 'ok';
                $suggestion = sprintf(
                    __("Excellent ! **%d** termes secondaires pertinents (synonymes et concepts connexes) ont été détectés, enrichissant la couverture sémantique du sujet.", PLUGIN_DATA['TextDomain']),
                    $foundCount
                );
                $score = $weight;
            } elseif ($foundCount > 0) {
                $status = 'warn';
                $remaining = $targetCount - $foundCount;
                $suggestion = sprintf(
                    __("Seulement **%d** termes secondaires ont été trouvés. Il en manque au moins %d. Essayez d'intégrer plus de synonymes et de concepts connexes pour approfondir la thématique de l'article.", PLUGIN_DATA['TextDomain']),
                    $foundCount,
                    $remaining
                );
                $score = round($weight * 0.5, 2); // 50% du poids (ex: 2.5 sur 5)
            } else {
                $suggestion = __("Aucun terme secondaire pertinent n'a été trouvé. Votre contenu est trop centré sur un seul mot-clé. Intégrez des **synonymes** et des mots du **champ lexical** pour améliorer la pertinence et le SEO sémantique.", PLUGIN_DATA['TextDomain']);
                $score = 0;
            }
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la présence du mot-clé principal dans l'URL (slug) de l'article.
     * Le mot-clé doit être présent et l'URL doit être courte et lisible.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_presence_mot_cle_url(): array
    {
        $label = __("Présence du mot-clé dans l'URL (slug)", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $keyword = self::$focusKeyword;
        // Récupération de l'URL ou du slug de l'article.
        // Dans WordPress, c'est le champ 'post_name' ou le slug.
        $postSlug = self::$postData['post_slug'] ?? '';
        $status = 'warn';
        $suggestion = __("L'URL de l'article n'est pas optimale.", PLUGIN_DATA['TextDomain']);
        $score = 0;
        if (empty($keyword)) {
            $suggestion = __("Le mot-clé principal n'est pas défini. Impossible d'analyser l'URL.", PLUGIN_DATA['TextDomain']);
            return [
                'label' => $label,
                'score' => $weight,
                'status' => 'warn',
                'suggestion' => $suggestion,
                'points_obtenus' => 0
            ];
        }
        // Nettoyage du mot-clé pour la comparaison avec le slug (suppression des espaces, accents, conversion en minuscule)
        // C'est une vérification simplifiée. Le slug est généralement déjà nettoyé par WordPress.
        $cleanedSlug = strtolower(urldecode($postSlug));
        $cleanedKeyword = strtolower(str_replace([' ', '_'], ['-', '-'], $keyword));

        // ------------------------------------------------
        // 1. Vérification de la présence du mot-clé dans le slug
        // ------------------------------------------------
        if (strpos($cleanedSlug, $cleanedKeyword) !== false) {
            $score = $weight; // 5 points

            // ------------------------------------------------
            // 2. Vérification de la longueur de l'URL (bonus/malus)
            // L'objectif est d'avoir une URL courte et concise (moins de 5 mots-clés distincts est un bon indicateur).
            // ------------------------------------------------
            $slugParts = explode('-', $cleanedSlug);
            // On retire les mots vides communs pour ne garder que les mots-clés importants pour la longueur (ex: 'le', 'la', 'des', 'et')
            $stopWords = ['a', 'de', 'du', 'la', 'le', 'les', 'un', 'une', 'des', 'et', 'ou', 'par', 'pour', 'que', 'qui', 'ce', 'cet', 'cette', 'ces', 'est', 'sont', 'il', 'elle', 'on', 'nous', 'vous', 'ils', 'elles'];
            $relevantParts = array_diff($slugParts, $stopWords);
            $slugLength = count($relevantParts);

            if ($slugLength <= 5) {
                // Optimal : Mot-clé présent et URL courte.
                $status = 'ok';
                $suggestion = __("Parfait ! Le mot-clé principal est présent dans l'URL, qui est de plus courte et facile à lire.", PLUGIN_DATA['TextDomain']);
            } else {
                // Avertissement : Mot-clé présent mais URL trop longue.
                $status = 'warn';
                $suggestion = sprintf(
                    __("Le mot-clé est présent, mais l'URL semble trop longue (plus de %d mots importants). Essayez de la raccourcir pour une meilleure expérience utilisateur et SEO.", PLUGIN_DATA['TextDomain']),
                    $slugLength
                );
                $score = round($weight * 0.75, 2); // Petite pénalité (ex: 3.75 sur 5)
            }
        } else {
            // Critique : Mot-clé absent de l'URL.
            $status = 'warn';
            $suggestion = sprintf(
                __("Le mot-clé principal ('%s') est absent de l'URL. Modifiez le slug pour y inclure votre mot-clé et rendre l'URL plus descriptive.", PLUGIN_DATA['TextDomain']),
                $keyword
            );
            $score = 0;
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie le nombre de liens internes (vers d'autres articles du même site) dans le contenu.
     * Optimum : Minimum 3 liens.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_maillage_interne(): array
    {
        $label = __("Maillage interne (Liens vers votre site)", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $content = self::$postContent; // Contenu brut (HTML) de l'article
        $status = 'warn';
        $suggestion = __("Le maillage interne n'est pas optimal.", PLUGIN_DATA['TextDomain']);
        $score = 0;
        $siteUrlBase = self::$postData['site_url'] ?? home_url();
        $minRequired = Config::CRITERES['maillage_interne']['min_liens'];

        if (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide. Impossible d'analyser le maillage interne.", PLUGIN_DATA['TextDomain']);
            return [
                'label' => $label,
                'score' => $weight,
                'status' => 'warn',
                'suggestion' => $suggestion,
                'points_obtenus' => 0
            ];
        }

        // 1. Trouver tous les liens <a> dans le contenu
        // Utilisation d'une expression régulière pour trouver les balises <a> avec un attribut href
        $pattern = '/<a\s+(?:[^>]*?\s+)?href=["\'](.*?)["\'][^>]*>(.*?)<\/a>/i';
        preg_match_all($pattern, $content, $matches);

        $linkUrls = $matches[1];
        $internalLinkCount = 0;

        // 2. Compter les liens internes
        foreach ($linkUrls as $url) {
            // Un lien est considéré comme interne s'il contient l'URL de base du site.
            // On utilise une vérification simple 'contient' pour les URL relatives/absolues.
            // Une vérification plus stricte pourrait être nécessaire dans un environnement de production.
            if (strpos($url, $siteUrlBase) !== false || (strpos($url, '/') === 0 && strpos($url, '//') !== 0)) {
                $internalLinkCount++;
            }
        }

        // 3. Évaluation des seuils
        if ($internalLinkCount >= $minRequired) {
            // OPTIMAL
            $status = 'ok';
            $suggestion = sprintf(
                __("Excellent ! Votre article contient **%d** liens internes, renforçant le maillage de votre site et la distribution du PageRank.", PLUGIN_DATA['TextDomain']),
                $internalLinkCount
            );
            $score = $weight;
        } elseif ($internalLinkCount >= Config::CRITERES['maillage_interne']['warn_liens']) {
            // ACCEPTABLE MAIS À AMÉLIORER
            $status = 'warn';
            $suggestion = sprintf(
                __("Seulement **%d** liens internes trouvés. Il est fortement recommandé d'insérer au moins **%d liens internes** pour améliorer l'exploration et la pertinence thématique (SEO).", PLUGIN_DATA['TextDomain']),
                $internalLinkCount,
                $minRequired
            );
            $score = round($weight * 0.5, 2); // 50% du poids (ex: 3.5 sur 7)
        } else {
            // ABSENT
            $status = 'warn';
            $suggestion = __("Pas de lien interne trouvé. Le maillage interne est un pilier du SEO ! Ajoutez immédiatement des liens vers d'autres articles pertinents de votre site.", PLUGIN_DATA['TextDomain']);
            $score = 0;
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la présence et le nombre de liens externes (vers des sites d'autorité).
     * Optimum : Minimum 1 lien.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_liens_externes(): array
    {
        $label = __("Liens externes (vers des sources d'autorité)", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $content = self::$postContent; // Contenu brut (HTML) de l'article
        $status = 'warn';
        $suggestion = __("Les liens externes ne sont pas optimaux.", PLUGIN_DATA['TextDomain']);
        $score = 0;
        // Simuler la base de l'URL du site (pour exclure les liens internes)
        $siteUrlBase = self::$postData['site_url'] ?? home_url();

        $minRequired = Config::CRITERES['liens_externes']['min_liens'];

        if (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide. Impossible d'analyser les liens externes.", PLUGIN_DATA['TextDomain']);
            return [
                'label' => $label,
                'score' => $weight,
                'status' => 'warn',
                'suggestion' => $suggestion,
                'points_obtenus' => 0
            ];
        }

        // 1. Trouver tous les liens <a> dans le contenu
        // Utilisation d'une expression régulière pour trouver les balises <a> avec un attribut href
        $pattern = '/<a\s+(?:[^>]*?\s+)?href=["\'](.*?)["\'][^>]*>(.*?)<\/a>/i';
        preg_match_all($pattern, $content, $matches);

        $linkUrls = $matches[1];
        $externalLinkCount = 0;

        // 2. Compter les liens externes
        foreach ($linkUrls as $url) {
            // Un lien est considéré comme externe s'il ne contient PAS l'URL de base du site,
            // et s'il est une URL absolue (commence par http/https, ou //)
            $isAbsoluteUrl = (strpos($url, 'http') === 0 || strpos($url, '//') === 0);

            if ($isAbsoluteUrl && strpos($url, $siteUrlBase) === false) {
                $externalLinkCount++;
            }
        }

        // 3. Évaluation des seuils
        if ($externalLinkCount >= $minRequired) {
            // OPTIMAL
            $status = 'ok';
            $suggestion = sprintf(
                __("Très bien ! Votre article contient **%d** lien(s) externe(s) vers des sources de qualité, renforçant la crédibilité de votre contenu.", PLUGIN_DATA['TextDomain']),
                $externalLinkCount
            );
            $score = $weight;
        } elseif ($externalLinkCount > 0) {
            // FAIBLE
            $status = 'warn';
            $suggestion = sprintf(
                __('Peu de liens externes (%d). Il en faudrait au moins %d.', PLUGIN_DATA['TextDomain']),
                $externalLinkCount,
                $minRequired
            );
        } else {
            // ABSENT
            $status = 'warn';
            $suggestion = __("Pas de lien externe trouvé. Ajoutez **au moins un lien sortant** vers une source d'autorité et pertinente pour valider vos informations (E-E-A-T).", PLUGIN_DATA['TextDomain']);
            $score = 0;
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie l'optimisation des images : présence d'au moins une image
     * avec un attribut 'alt' contenant le mot-clé principal.
     * Optimum : Au moins 1 image avec Alt Text descriptif et mot-clé inclus.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_optimisation_images(): array
    {
        $label = __("Optimisation des images (Texte Alternatif)", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $content = self::$postContent;
        $keyword = self::$focusKeyword;
        $status = 'warn';
        $score = 0;
        $imageCount = 0;
        $altCount = 0;
        $kwInAltCount = 0;

        if (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide. Impossible d'analyser les images.", PLUGIN_DATA['TextDomain']);
        } elseif (empty($keyword)) {
            $suggestion = __("Le mot-clé principal n'est pas défini. Impossible de vérifier l'inclusion du mot-clé dans le texte alternatif.", PLUGIN_DATA['TextDomain']);
        } else {
            // 1. Trouver toutes les balises <img> dans le contenu
            // Utilisation d'une expression régulière simple pour capturer les balises <img>
            $pattern = '/<img\s+[^>]*>/i';
            preg_match_all($pattern, $content, $matches);

            $images = $matches[0];
            $imageCount = count($images);

            if ($imageCount === 0) {
                $suggestion = __("Aucune image n'a été trouvée dans le contenu. Il est fortement recommandé d'ajouter des images pertinentes pour améliorer l'expérience utilisateur et l'accessibilité.", PLUGIN_DATA['TextDomain']);
                // On attribue un score de 50% du poids si aucune image n'est trouvée pour ne pas trop pénaliser
                // si le contenu n'en requiert pas absolument, mais on met un avertissement.
                $score = round($weight * 0.5, 2);
            } else {
                // 2. Analyser chaque image pour les attributs alt
                foreach ($images as $imgTag) {
                    // Chercher l'attribut alt="[contenu]"
                    if (preg_match('/alt=["\'](.*?)["\']/i', $imgTag, $altMatch)) {
                        $altText = trim($altMatch[1]);

                        if (!empty($altText)) {
                            $altCount++;

                            // Vérifier si le mot-clé est dans l'attribut alt
                            if (stripos($altText, $keyword) !== false) {
                                $kwInAltCount++;
                            }
                        }
                    }
                }

                // 3. Évaluation des seuils
                if ($kwInAltCount >= Config::CRITERES['optimisation_images']['min_images']) {
                    // OPTIMAL : Au moins une image a un alt text avec le mot-clé
                    $status = 'ok';
                    $suggestion = sprintf(
                        __("Parfait ! **%d** image(s) possède(nt) un attribut `alt` contenant le mot-clé principal. Continuez d'optimiser toutes vos images.", PLUGIN_DATA['TextDomain']),
                        $kwInAltCount
                    );
                    $score = $weight;
                } elseif ($altCount >= Config::CRITERES['optimisation_images']['min_images']) {
                    // ACCEPTABLE : Au moins une image a un alt text, mais sans le mot-clé
                    $status = 'warn';
                    $suggestion = sprintf(
                        __("Toutes les images n'ont pas un texte alternatif optimisé. **%d** image(s) ont un `alt` text, mais aucune ne contient le mot-clé principal. Intégrez le mot-clé dans au moins une description d'image.", PLUGIN_DATA['TextDomain']),
                        $altCount
                    );
                    $score = round($weight * 0.7, 2); // 70% du poids (ex: 3.5 sur 5)
                } else {
                    // CRITIQUE : Aucune image avec un alt text (ou alt text vide)
                    $status = 'warn';
                    $suggestion = sprintf(
                        __("Attention ! Aucune des **%d** image(s) n'a d'attribut `alt` renseigné ou il est vide. L'attribut `alt` est essentiel pour l'accessibilité et le référencement des images. Corrigez immédiatement.", PLUGIN_DATA['TextDomain']),
                        $imageCount
                    );
                    $score = 0;
                }
            }
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la lisibilité et la structure via les balises de titres (H2, H3, H4) :
     * 1. Hiérarchie logique (pas de saut de niveau).
     * 2. Présence du mot-clé principal dans au moins un H2 ou H3.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_lisibilite_headings(): array
    {
        $label = __("Structure des titres (Hn) et inclusion du mot-clé", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $content = self::$postContent;
        $keyword = self::$focusKeyword;
        $status = 'warn';
        $suggestion = __("La structure des titres n'est pas optimisée.", PLUGIN_DATA['TextDomain']);
        $score = 0;

        $kwInSubheading = false;
        $hasStructuralIssue = false;
        $currentLevel = 0;

        // Initialisation correcte: On suppose qu'aucun H1 n'a été trouvé DANS LE CONTENU au départ.
        $h1Found = false;

        if (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide. Impossible d'analyser la structure des titres.", PLUGIN_DATA['TextDomain']);
        } else {
            // 1. Trouver tous les titres H1 à H6.
            // Le /s permet d'inclure les sauts de ligne dans la correspondance de .
            $pattern = '/<h([1-6])(?:\s+[^>]*)?>(.*?)<\/h\1>/is';
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
            // 2. Analyser la hiérarchie et les mots-clés.
            foreach ($matches as $match) {
                $level = (int)$match[1]; // Niveau du titre (1, 2, 3...)
                $text = strip_tags($match[2]); // Contenu du titre           
                if ($level === 1) {
                    // A. Vérification de l'unicité du H1 (si présent dans le contenu)
                    if ($h1Found) {
                        $hasStructuralIssue = true;
                        $suggestion = __("CRITIQUE: Plusieurs balises H1 détectées dans le contenu. Un article ne doit avoir qu'**un seul H1**.", PLUGIN_DATA['TextDomain']);
                        $score = 0; // Pénalité maximale pour une erreur critique.
                        break;
                    }
                    $h1Found = true;
                } elseif ($currentLevel > 0 && $level > $currentLevel + 1) {

                    // B. Vérification du saut de niveau (ex: H2 après H1, puis H3 après H2 ...)
                    $hasStructuralIssue = true;
                    $suggestion = sprintf(
                        __("Erreur de hiérarchie: Saut de niveau détecté (ex: H%d est suivi de H%d). Respectez la séquence: H2 après H1, H3 après H2, etc. pour une meilleure lisibilité.", PLUGIN_DATA['TextDomain']),
                        $currentLevel,
                        $level
                    );
                    $score = 0; // Pénalité maximale pour une erreur de structure.
                    break;
                }
                $currentLevel = $level;

                // Vérification du mot-clé dans les sous-titres (H2 et H3)
                if (($level === 2 || $level === 3) && !empty($keyword)) {
                    if (stripos($text, $keyword) !== false) {
                        $kwInSubheading = true;
                    }
                }
            }

            // 3. Évaluation finale
            if (!$hasStructuralIssue) {
                // Structure validée (pas de H1 multiple, pas de saut de niveau).
                $status = 'ok';
                $suggestion = __("La structure des titres est logique et bien organisée.", PLUGIN_DATA['TextDomain']);
                $score = $weight; // 8 points initialement

                // Malus si le mot-clé est absent des sous-titres
                if (!empty($keyword) && !$kwInSubheading) {
                    $status = 'warn';
                    $suggestion .= sprintf(
                        __(" Cependant, le mot-clé principal ('%s') n'est pas inclus dans un sous-titre (H2 ou H3). Intégrez-le naturellement dans un sous-titre pour renforcer la pertinence.", PLUGIN_DATA['TextDomain']),
                        $keyword
                    );
                    $score = round($weight * 0.75, 2); // Malus de 25%
                }
            }
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie la longueur moyenne des blocs de texte pour la lisibilité.
     * Les blocs de texte incluent : <p>, <td>, <th>, <li> et <div>.
     * Optimum : Maximum 60 mots par bloc.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_longueur_paragraphes(): array
    {
        $label = __("Longueur des blocs de texte (P, TD, TH, LI, DIV)", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $content = self::$postContent;
        $status = 'warn';
        $suggestion = __("La lisibilité est impactée par la longueur des blocs de texte.", PLUGIN_DATA['TextDomain']);
        $score = 0;

        $maxWordsPerBlock = Config::CRITERES['longueur_paragraphes']['max_mots'];
        $longBlocksCount = 0;
        $totalBlocks = 0;

        if (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide. Impossible d'analyser la longueur des paragraphes.", PLUGIN_DATA['TextDomain']);
        } else {
            // 1. Définir les balises considérées comme des "blocs de texte"
            // P, TD, TH, LI sont des blocs de contenu spécifiques.
            // DIV est inclus ici pour traiter les blocs de texte non structurés ou les regroupements.
            $blockTags = ['p', 'td', 'th', 'li', 'div'];
            $pattern = '/<(' . implode('|', $blockTags) . ')[^>]*>(.*?)<\/\1>/is';
            // 2. Trouver tous les blocs de texte.
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
            // On ne compte que les blocs qui contiennent réellement du texte (non vide).
            $blocks = array_filter($matches, function ($match) {
                // S'assurer que le contenu du bloc (match[2]) n'est pas juste du vide ou des espaces
                return !empty(trim(strip_tags($match[2])));
            });

            $totalBlocks = count($blocks);

            if ($totalBlocks === 0) {
                $suggestion = __("Aucun bloc de texte significatif (<p>, <td>, etc.) n'a été trouvé. Assurez-vous que l'article contient du contenu structuré.", PLUGIN_DATA['TextDomain']);
                $score = 0;
            } else {
                // 3. Compter les mots dans chaque bloc et identifier les trop longs.
                foreach ($blocks as $blockMatch) {
                    $blockContent = $blockMatch[2];
                    $cleanBlock = strip_tags($blockContent);
                    $wordCount = str_word_count($cleanBlock);

                    if ($wordCount > $maxWordsPerBlock) {
                        $longBlocksCount++;
                    }
                }

                $ratioLong = ($longBlocksCount / $totalBlocks) * 100;

                // 4. Évaluation des seuils (Optimal: Moins de 10% des blocs de texte sont trop longs)
                if ($ratioLong <= 10 || $longBlocksCount === 0) {
                    $status = 'ok';
                    $suggestion = sprintf(
                        __("Excellent ! Seuls %d blocs de texte(s) sur %d dépassent la limite de %d mots. La lisibilité est bonne.", PLUGIN_DATA['TextDomain']),
                        $longBlocksCount,
                        $totalBlocks,
                        $maxWordsPerBlock
                    );
                    $score = $weight;
                } elseif ($ratioLong <= 30) {
                    $status = 'warn';
                    $suggestion = sprintf(
                        __("Attention ! %d blocs sur %d (soit %.1f%%) sont trop longs. Ceci inclut des paragraphes, des cellules de tableau, ou des éléments de liste. Essayez de les diviser en blocs de texte plus courts (max **%d mots**) pour améliorer la lisibilité sur mobile.", PLUGIN_DATA['TextDomain']),
                        $longBlocksCount,
                        $totalBlocks,
                        $ratioLong,
                        $maxWordsPerBlock
                    );
                    $score = round($weight * 0.5, 2); // 50% du poids
                } else {
                    $status = 'warn';
                    $suggestion = sprintf(
                        __("CRITIQUE : Une majorité de vos blocs de texte (%.1f%%) sont trop longs. Cela nuit sévèrement à la lecture rapide et à l'expérience utilisateur. **Divisez-les**.", PLUGIN_DATA['TextDomain']),
                        $ratioLong
                    );
                    $score = 0;
                }
            }
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    /**
     * Vérifie le pourcentage de phrases à la voix passive.
     * Optimum : Moins de 10%. Limite 'warn' : Plus de 15%.
     *
     * @return array Résultat d'analyse.
     */
    private static function check_usage_voix_passive(): array
    {
        $label = __("Usage de la voix passive", PLUGIN_DATA['TextDomain']);
        $weight = self::get_weight(__FUNCTION__);
        $content = self::$postContent;
        $status = 'warn';
        $suggestion = __("L'usage de la voix passive est trop élevé.", PLUGIN_DATA['TextDomain']);
        $score = 0;
        if (empty($content)) {
            $suggestion = __("Le contenu de l'article est vide. Impossible d'analyser l'usage de la voix passive.", PLUGIN_DATA['TextDomain']);
        } else {
            $cleanContent = strip_tags($content);

            // 1. Compter le nombre total de phrases.
            // On utilise une expression régulière simple qui compte les phrases terminées par '.', '!', ou '?'
            // suivie d'un espace ou de la fin de chaîne.
            $sentenceCount = preg_match_all('/[.?!]+(?:\s|$)/', $cleanContent, $matches);

            if ($sentenceCount === 0) {
                $suggestion = __("Moins d'une phrase détectée. Impossible d'analyser la voix passive.", PLUGIN_DATA['TextDomain']);
                return [
                    'label' => $label,
                    'score' => $weight,
                    'status' => 'warn',
                    'suggestion' => $suggestion,
                    'points_obtenus' => 0
                ];
            }

            // 2. Détecter la voix passive (méthode simplifiée).
            // On cherche l'auxiliaire 'être' conjugué suivi d'un participe passé (un mot après).
            // Auxiliaires: est, sont, fut, furent, sera, seront, était, étaient, a été, ont été, etc.
            // La détection des formes composées est complexe, on se concentre sur les formes simples courantes.
            $passivePattern =
                '/\b(est|sont|fut|furent|sera|seront|était|étaient|soit|soient|serait|seraient)\s+[\w\']{2,}[éèeéëaaoît]{1}\b/iu';

            $passiveCount = preg_match_all($passivePattern, $cleanContent, $matches);

            // La méthode ci-dessus n'est pas parfaite car elle ne compte que les 'blocs passifs' et non les phrases entières.
            // Pour une meilleure précision, on doit d'abord séparer les phrases, puis analyser chacune :

            $sentences = preg_split('/(?<=[.?!])\s+/', $cleanContent, -1, PREG_SPLIT_NO_EMPTY);
            $passiveSentenceCount = 0;

            foreach ($sentences as $sentence) {
                // Si la phrase contient une construction 'être + participe passé' (simplifié)
                if (preg_match($passivePattern, $sentence)) {
                    $passiveSentenceCount++;
                }
            }

            $passiveRatio = ($passiveSentenceCount / $sentenceCount) * 100;
            $passiveRatio = round($passiveRatio, 2);

            $suggestion = sprintf(
                __("Ratio de phrases à la voix passive : **%.1f%%** sur %d phrases.", PLUGIN_DATA['TextDomain']),
                $passiveRatio,
                $sentenceCount
            );

            // 3. Évaluation des seuils (Poids 1)
            // Optimal: <= 10%
            if ($passiveRatio <= Config::CRITERES['usage_voix_passive']['max_percent']) {
                $status = 'ok';
                $suggestion .= __(" Excellent ! L'usage de la voix passive est très faible. Le contenu est clair et dynamique.", PLUGIN_DATA['TextDomain']);
                $score = $weight;
            } elseif ($passiveRatio <= Config::CRITERES['usage_voix_passive']['warn_percent']) {
                // Tolérable mais à surveiller: 10% < ratio <= 15%
                $status = 'warn';
                $suggestion .= __(" Le ratio est tolérable mais pourrait être amélioré. Essayez de reformuler certaines phrases pour utiliser la voix active.", PLUGIN_DATA['TextDomain']);
                $score = round($weight * 0.5, 2); // 0.5 point
            } else {
                // Critère non rempli: > 15%
                $status = 'warn';
                $suggestion .= __(" L'usage de la voix passive est trop élevé. Cela rend votre texte lourd et moins direct. **Reformulez les phrases concernées** pour passer à la voix active.", PLUGIN_DATA['TextDomain']);
                $score = 0;
            }
        }
        return [
            'label' => $label,
            'score' => $weight,
            'status' => $status,
            'suggestion' => $suggestion,
            'points_obtenus' => $score
        ];
    }

    // --- SIMULATION DE LA FONCTION DE LOGGING ---
    // Vous devez définir cette fonction dans votre classe Analyser ou utiliser la fonction de logging de votre CMS/Framework.
    private static function write_log(string $message): void
    {
        // Chemin absolu vers votre fichier de log. 
        // Adaptez '/votre-chemin/log-seo.txt' à un emplacement sécurisé dans votre plugin.
        $logFile = ABSPATH . '/wp-content/debug.log'; // Exemple WP standard
        $timestamp = date("Y-m-d H:i:s");

        // La fonction error_log est la plus simple en WP
        error_log("[{$timestamp}] [ANALYSER] " . $message . "\n");
    }
}

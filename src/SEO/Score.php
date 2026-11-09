<?php

namespace DafapSEO\SEO;

use DafapSEO\SEO\Analyzer;

class Score
{
    /**
     * Nom du champ enregistrant le score SEO (sur 100)
     */
    public const FEILD_NAME = '_dafap_seo_score';

    /**
     * Nom de la comonne affichant le score SEO (sur 100)
     */
    public const COLUMN_NAME = 'dafap_seo_score';

    /**
     * Calcule le score SEO global de l'article (sur 100).
     *
     * @param int $post_id ID de l'article WordPress.
     * @return int Le score SEO calculé (0 à 100).
     */
    public static function compute_score(int $post_id): int
    {
        $analysis = Analyzer::diagnose($post_id);
        $totalScore = 0;

        foreach ($analysis as $item) {
            // Assurez-vous d'ajouter une clé 'points_obtenus' lors de l'appel à la méthode privée.
            $totalScore += $item['points_obtenus'] ?? 0;
        }

        // Le score est déjà sur 100 avec la pondération proposée.
        return (int) round($totalScore);
    }

    /**
     * Enregistre le score de l'article
     */
    public static function save_seo_score(int $post_id, \WP_Post $post): void
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        if ($post->post_type !== 'post') {
            return;
        }

        $score = self::compute_score($post_id);
        update_post_meta($post_id, self::FEILD_NAME, $score);
    }
}
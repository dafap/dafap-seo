<?php

namespace DafapSEO\Admin;

use DafapSEO\SEO\Score;

class Column
{
    /**
     * Définit la colonne par son entête
     */
    public static function add(array $columns): array
    {
        $columns[Score::COLUMN_NAME] = __('Score SEO', DAFAPSEO_TEXTDOMAIN);
        return $columns;
    }

    /**
     * Peuple la colonne
     */
    public static function render(string $column, int $post_id): void
    {
        if ($column === Score::COLUMN_NAME) {
            $score = get_post_meta($post_id, Score::FEILD_NAME, true);
            if ($score === '') {
                $score = Score::compute_score($post_id);
            }
            echo esc_html($score) . '/100';
        }
    }

    /**
     * callback pour Hooks
     */
    public static function register(): void
    {
        $post_types = get_post_types(['public' => true], 'names');
        foreach ($post_types as $type) {
            add_filter("manage_{$type}_posts_columns", [self::class, 'add']);
            add_action("manage_{$type}_posts_custom_column", [self::class, 'render'], 10, 2);
        }
    }
}

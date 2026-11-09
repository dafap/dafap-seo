<?php
namespace DafapSEO\Helpers;

class Validator
{
    /**
     * Vérifie qu’un champ texte est non vide et raisonnablement long
     */
    public static function requiredText(string $value, int $minLength = 3): bool
    {
        return strlen(trim($value)) >= $minLength;
    }

    /**
     * Vérifie qu’une URL est valide
     */
    public static function validUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Vérifie qu’un identifiant d’image est valide
     */
    public static function validImageId(int $id): bool
    {
        return $id > 0 && wp_attachment_is_image($id);
    }

    /**
     * Vérifie qu’un tableau de mots-clés contient au moins un mot
     */
    public static function hasKeywords(array $keywords): bool
    {
        return count(array_filter($keywords)) > 0;
    }

    /**
     * Vérifie la cohérence d’un bloc social (titre sans image, etc.)
     */
    public static function validSocialBlock(array $social): bool
    {
        $hasTitle = self::requiredText($social['title'] ?? '');
        $hasImage = self::validUrl($social['image'] ?? '');

        // Si titre présent, image obligatoire
        if ($hasTitle && ! $hasImage) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie la cohérence d’un bloc X (Twitter)
     */
    public static function validXBlock(array $x): bool
    {
        $hasTitle = self::requiredText($x['title'] ?? '');
        $hasImage = self::validUrl($x['image'] ?? '');

        // Même logique que pour social
        if ($hasTitle && ! $hasImage) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie les profils sociaux du propriétaire
     */
    public static function validOwnerProfiles(array $profiles): array
    {
        $valid = [];

        foreach ($profiles as $key => $url) {
            if (self::validUrl($url)) {
                $valid[$key] = $url;
            }
        }

        return $valid;
    }
}

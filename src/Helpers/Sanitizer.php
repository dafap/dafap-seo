<?php
namespace DafapSEO\Helpers;

class Sanitizer
{
    /**
     * Nettoie une chaîne de texte courte (titre, mot-clé, champ simple)
     */
    public static function text(string|int|float|bool|null $value): string
    {
        return is_array($value)
            ? implode(' ', array_map('sanitize_text_field', $value))
            : sanitize_text_field($value);
    }

    /**
     * Nettoie une zone de texte longue (description, extrait)
     */
    public static function textarea(string|int|float|bool|null $value): string
    {
        return is_array($value)
            ? implode("\n", array_map('sanitize_textarea_field', $value))
            : sanitize_textarea_field($value);
    }

    /**
     * Nettoie une URL (image, profil social, lien externe)
     */
    public static function url(string|int|float|bool|null $value): string
    {
        $url = esc_url_raw((string) $value);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }

    /**
     * Nettoie une liste de mots-clés séparés par des virgules
     * @var string|array $value
     */
    public static function keywords(string|int|float|bool|null $value): array
    {
        $raw = is_array($value) ? $value : explode(',', (string) $value);
        return array_filter(array_map([self::class, 'text'], $raw));
    }

    /**
     * Nettoie un identifiant d’image WordPress
     */
    public static function imageId(int|string|null $value): int
    {
        return is_numeric($value) && $value > 0 ? (int) $value : 0;
    }

    /**
     * Nettoie une valeur booléenne (checkbox, toggle)
     */
    public static function bool(bool|int|string|null $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Nettoie un bloc de données sociales (titre, description, image)
     */
    public static function social(array $data): array
    {
        return [
            'og_title' => self::text($data['og_title'] ?? ''),
            'og_desc'  => self::textarea($data['og_desc'] ?? ''),
            'og_image' => self::url($data['og_image'] ?? ''),
            'og_image_alt' => self::text($data['og_image_alt']),
        ];
    }

    /**
     * Nettoie un bloc spécifique à X (Twitter)
     */
    public static function x(array $data): array
    {
        return [
            'x_title' => self::text($data['x_title'] ?? ''),
            'x_desc'  => self::textarea($data['x_desc'] ?? ''),
            'x_image' => self::url($data['x_image'] ?? ''),
            'x_image_alt' => self::text($data['x_image_alt'] ?? ''),
        ];
    }

    /**
     * Nettoie les profils sociaux du propriétaire du site
     */
    public static function ownerProfiles(array $data): array
    {
        return array_filter([
            'facebook'  => self::url($data['facebook'] ?? ''),
            'x'         => self::url($data['x'] ?? ''),
            'linkedin'  => self::url($data['linkedin'] ?? ''),
            'instagram' => self::url($data['instagram'] ?? ''),
        ]);
    }

    /**
     * Nettoie et valide un fb:app_id.
     * - Supprime les caractères non numériques.
     * - Vérifie que l'ID contient exactement 16 chiffres.
     * - Retourne l'ID nettoyé ou null si invalide.
     */
    public static function fbAppId(string $appId): ?string
    {
        // Supprimer tous les caractères non numériques
        $cleaned = preg_replace('/\D/', '', $appId);

        // Vérifier que l'ID contient exactement 16 chiffres
        if (strlen($cleaned) === 16) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Récupère une meta d'article avec une valeur par défaut si elle est absente ou vide.
     *
     * @param int $postId ID de l'article
     * @param string $key Clé meta à récupérer
     * @param mixed $default Valeur par défaut si la meta est absente ou vide
     * @return mixed
     */
    public static function getPostMetaOrDefault(int $postId, string $key, mixed $default = null): mixed
    {
        $value = get_post_meta($postId, $key, true);

        if ($value === '' || $value === null) {
            return $default;
        }

        return $value;
    }
}

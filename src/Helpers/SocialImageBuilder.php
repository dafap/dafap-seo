<?php
namespace DafapSEO\Helpers;

use DafapSEO\Core\Config;

class SocialImageBuilder
{
    /**
     * Retourne les balises og:image et twitter:image avec alt, selon les priorités définies.
     *
     * @param int $postId ID du post
     * @return array|null Tableau associatif avec 'og' et 'twitter' ou null si aucune image
     */
    public static function getSocialImages(int $postId): ?array
    {
        // 1. Images et alt définis dans la MetaBox
        $ogImage = get_post_meta($postId, Config::FIELDS['social']['plugin_fields']['og_image'], true);
        $ogAlt   = get_post_meta($postId, Config::FIELDS['social']['plugin_fields']['og_image_alt'], true);

        $twImage = get_post_meta($postId, Config::FIELDS['social']['plugin_fields']['x_image'], true);
        $twAlt   = get_post_meta($postId, Config::FIELDS['social']['plugin_fields']['x_image_alt'], true);

        // 2. Compléter les alt depuis la médiathèque si manquants
        if ($ogImage && empty($ogAlt)) {
            $ogAlt = self::getMediaAlt($ogImage);
        }
        if ($twImage && empty($twAlt)) {
            $twAlt = self::getMediaAlt($twImage);
        }

        // 3. Fallback Facebook → image à la une
        if (empty($ogImage)) {
            $ogImage = get_the_post_thumbnail_url($postId);
            $ogAlt   = get_post_meta(get_post_thumbnail_id($postId), '_wp_attachment_image_alt', true);
        }

        // 4. Fallback → première image dans le contenu
        if (empty($ogImage)) {
            if ($fallback = self::extractFirstImageWithAlt($postId)) {};
            $ogImage = $fallback['url'] ?? null;
            $ogAlt   = $fallback['alt'] ?? '';
        }

        // 5. Fallback Twitter → Facebook
        if (empty($twImage)) {
            $twImage = $ogImage;
            $twAlt   = $ogAlt;
        }

        // 6. Si aucune image trouvée
        if (empty($ogImage)) {
            return null;
        }

        // 7. Twitter hérite de Facebook si toujours vide
        if (empty($twImage)) {
            $twImage = $ogImage;
            $twAlt   = $ogAlt;
        }

        return [
            'og' => ['url' => $ogImage, 'alt' => $ogAlt],
            'x' => ['url' => $twImage, 'alt' => $twAlt],
        ];
    }

    /**
     * Récupère le alt d’une image de la médiathèque.
     */
    private static function getMediaAlt(string $url): ?string
    {
        $attachmentId = attachment_url_to_postid($url);
        if ($attachmentId) {
            return get_post_meta($attachmentId, '_wp_attachment_image_alt', true);
        }
        return null;
    }

    /**
     * Extrait la première image du contenu HTML d’un post avec son alt si présent.
     *
     * @param int $postId ID du post
     * @return array|null ['url' => ..., 'alt' => ...] ou null si aucune image
     */
    public static function extractFirstImageWithAlt(int $postId): ?array
    {
        $post = get_post($postId);
        if (! $post || empty($post->post_content)) {
            return null;
        }

        // Expression régulière pour capturer src et alt
        $pattern = '/<img[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\'][^>]*>/i';

        if (preg_match($pattern, $post->post_content, $matches)) {
            return [
                'url' => $matches[1],
                'alt' => $matches[2],
            ];
        }

        // Si alt n’est pas présent, capturer seulement src
        $fallbackPattern = '/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i';
        if (preg_match($fallbackPattern, $post->post_content, $matches)) {
            return [
                'url' => $matches[1],
                'alt' => '',
            ];
        }

        return null;
    }
}

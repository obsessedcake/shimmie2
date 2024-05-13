<?php

declare(strict_types=1);

namespace Shimmie2;

class DanbooruTransloader
{
    public static function accept(string $url): bool
    {
        return str_contains($url, 'https://danbooru.donmai.us/');
    }

    public static function enrich_metadata(string $url, int $slot, array &$metadata): void
    {
        $json_url = self::clean_url($url).'.json';
        $raw_json = self::fetch($json_url);

        $tags = [];
        foreach (json_decode($raw_json, true) as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $tag_category = str_replace('tag_string_', '', $key, $is_category);
            if (!$is_category) {
                continue;
            }

            if ($tag_category == 'general') {
                $tag_category = NULL;
            }

            foreach (explode(' ', $value) as $tag) {
                $fixed_tag = preg_replace(
                    pattern: '/^(.*)_\((place|series)\)$/',
                    replacement: '\2:\1',
                    subject: $tag,
                    limit: 1,
                    count: $is_replaced
                );

                if (!$is_replaced && $tag_category) {
                    $fixed_tag = "$tag_category:$tag";
                } 

                $tags[] = $fixed_tag;
            }
        }

        $metadata['rating'.$slot] = self::convert_rating($json['rating']);
        $metadata['source'.$slot] = self::clean_url($json['source']);
        $metadata['tags'.$slot] = implode(' ', $tags);
        $metadata['url'.$slot] = $json['file_url'];
    }

    /**
     * Clean URL from fragment and query options.
     */
    private static function clean_url(string $url): string
    {
        $url_parts = parse_url($url);
        return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
    }

    private static function fetch(string $url): array
    {
        $ch = curl_init($url);
        assert($ch !== false);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "Shimmie-".VERSION);
        # curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new UploadException("cURL failed: ".curl_error($ch));
        }

        curl_close($ch);
        return $response;
    }

    private static function convert_rating(string $rating): string
    {
        switch ($rating) {
            case 'g':           // general -> safe
                return 's';
            case 's':           // sensitive -> sensitive
                return 'c';
            case 'q':           // questionable -> questionable
                return 'q';
            case 'e':           // explicit -> questionable
                return 'e';
            default:
                return '?';
        }
    }
}

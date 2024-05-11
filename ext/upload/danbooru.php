<?php

declare(strict_types=1);

namespace Shimmie2;

class DanbooruTransloader
{
    private static string $TAG_CATEGORY_PREFIX = 'tag_string_';
    private static string $TAG_SERIES_PREFIX = '_(series)';

    public static function accept(string $url): bool
    {
        return str_contains($url, 'https://danbooru.donmai.us/');
    }

    public static function enrich_metadata(string $url, int $slot, array &$metadata): void
    {
        $json_url = self::clean_url($url).'.json';
        $json = self::fetch_json($json_url);

        $final_tags = [];
        foreach ($json as $key => $value) {
            if (!str_starts_with($key, self::$TAG_CATEGORY_PREFIX)) {
                continue;
            }

            if (empty($value)) {
                continue;
            }

            $tag_category = substr($key, strlen(self::$TAG_CATEGORY_PREFIX)) . ':';
            if ($tag_category === 'general:') {
                $final_tags[] = $value;
                continue;
            }

            $tags = explode(' ', $value);

            if ($tag_category === 'copyright:') {
                foreach ($tags as $tag) {
                    if (str_ends_with($tag, self::TAG_SERIES_PREFIX)) {
                        $fixed_tag = 'series:'.substr($tag, 0, -strlen(self::TAG_SERIES_PREFIX));
                    } else {
                        $fixed_tag = $tag_category.$tag;
                    }

                    $final_tags[] = $fixed_tag;
                }
                continue;
            }

            $final_tags = array_merge($final_tags, substr_replace($tags, $tag_category, 0, 0));
        }

        $metadata['rating'.$slot] = self::convert_rating($json['rating']);
        $metadata['source'.$slot] = self::clean_url($json['source']);
        $metadata['tags'.$slot] = implode(' ', $final_tags);
        $metadata['url'.$slot] = $json['file_url'];
    }

    private static function clean_url(string $url): string
    {
        $url_parts = parse_url($url);
        return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
    }

    private static function fetch_json(string $url): array
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
        return json_decode($response, true);
    }

    private static function convert_rating(string $rating): string
    {
        switch ($rating) {
            case 'g':           // general -> safe
                return 's';
            case 's':           // sensitive -> questionable
            case 'q':           // questionable -> questionable
                return 'q';
            case 'e':           // explicit -> questionable
                return 'e';
            default:
                return '?';
        }
    }
}

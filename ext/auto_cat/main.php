<?php

declare(strict_types=1);

namespace Shimmie2;

class AutoCat extends Extension
{
    public function get_priority(): int
    {
        return 40;
    } // before ImageUploadEvent

    private static function log($data): void
    {
        $formatted = print_r($data, true);
        echo "<pre>" . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8', true) . "</pre>";
    }

    public function onTagSet(TagSetEvent $event): void
    {
        global $database;

        $sql_cte_placeholders = [];
        $sql_cte_args = [];

        foreach ($event->new_tags as $idx => $tag) {
            if (strrpos($tag, ':') === false) {
                $sql_cte_placeholders[] = "($idx, :tag$idx)";
                $sql_cte_args["tag$idx"] = $event->new_tags[$idx];
            }
        }

        if (empty($sql_cte_placeholders)) {
            return;
        }

        $new_marked_tags = $database->get_pairs(
            "
            WITH cte(idx, tag) AS (VALUES
                ".implode(', ', $sql_cte_placeholders)."
            )
            SELECT cte.idx, tags.tag
            FROM tags
                INNER JOIN cte ON LOWER(tags.tag) LIKE '%:' || LOWER(cte.tag)
            ",
            $sql_cte_args
        );

        $event->new_tags = array_replace($event->new_tags, $new_marked_tags);
    }
}

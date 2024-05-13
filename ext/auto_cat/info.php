<?php

declare(strict_types=1);

namespace Shimmie2;

class AutoCatInfo extends ExtensionInfo
{
    public const KEY = "auto_cat";

    public string $key = self::KEY;
    public string $name = "Category Auto-Mapper";
    public string $license = self::LICENSE_WTFPL;
    public ExtensionCategory $category = ExtensionCategory::METADATA;
    public string $description = "Provides an automatic category mapping";
}

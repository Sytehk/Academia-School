<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait SlugAbleTrait
{
    /**
     * @throws Exception
     */
    public function createUniqueSlug(string $title, string $tableName, string $columnName, string $separator = '-'): string
    {
        $id = 0;
        $slug = preg_replace('/\s+/', $separator, (trim(strtolower($title))));
        $slug = preg_replace('/\?+/', $separator, (trim(strtolower($slug))));
        $slug = preg_replace('/\#+/', $separator, (trim(strtolower($slug))));
        $slug = preg_replace('/\/+/', $separator, (trim(strtolower($slug))));

        // // Replace all separator characters and whitespace by a single separator
        $slug = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $slug);

        $allSlugs = $this->getRelatedSlugs($slug, $tableName, $columnName, $id);
        // If we haven't used it before then we are all good.
        if (! $allSlugs->contains("$columnName", $slug)) {
            return $slug;
        }
        // Just append numbers like a savage until we find not used.
        for ($i = 1; $i <= 1000000000; $i++) {
            $newSlug = $slug.$separator.$i;
            if (! $allSlugs->contains("$columnName", $newSlug)) {
                return $newSlug;
            }
        }

        throw new Exception('Can not create a unique slug');
    }

    private function getRelatedSlugs(string $slug, string $tableName, string $columnName, int $id = 0): Collection
    {
        return DB::table($tableName)
            ->select("$columnName")->where("$columnName", 'like', $slug.'%')
            ->where('id', '<>', $id)
            ->get();
    }
}

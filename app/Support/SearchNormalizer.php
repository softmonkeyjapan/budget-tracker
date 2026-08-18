<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Normalizes text for case- and accent-insensitive search, portably across SQL engines.
 *
 * Column values are normalized once, at write time, into a plain dedicated column (see
 * `Expense::$search_text`), and search terms are normalized the same way before filtering with a
 * simple `LIKE`. This avoids relying on engine-specific collations, `ILIKE`, extensions like
 * PostgreSQL's `unaccent`, or deeply nested `REPLACE()` SQL expressions — which SQLite's parser
 * rejects past a certain nesting depth ("parser stack overflow").
 */
final class SearchNormalizer
{
    /**
     * Maps accented characters (both cases) to their plain lowercase ASCII equivalent.
     *
     * @var array<string, string>
     */
    private const ACCENT_MAP = [
        'à' => 'a', 'À' => 'a', 'á' => 'a', 'Á' => 'a', 'â' => 'a', 'Â' => 'a',
        'ã' => 'a', 'Ã' => 'a', 'ä' => 'a', 'Ä' => 'a', 'å' => 'a', 'Å' => 'a',
        'æ' => 'ae', 'Æ' => 'ae',
        'ç' => 'c', 'Ç' => 'c',
        'è' => 'e', 'È' => 'e', 'é' => 'e', 'É' => 'e', 'ê' => 'e', 'Ê' => 'e', 'ë' => 'e', 'Ë' => 'e',
        'ì' => 'i', 'Ì' => 'i', 'í' => 'i', 'Í' => 'i', 'î' => 'i', 'Î' => 'i', 'ï' => 'i', 'Ï' => 'i',
        'ñ' => 'n', 'Ñ' => 'n',
        'ò' => 'o', 'Ò' => 'o', 'ó' => 'o', 'Ó' => 'o', 'ô' => 'o', 'Ô' => 'o',
        'õ' => 'o', 'Õ' => 'o', 'ö' => 'o', 'Ö' => 'o',
        'œ' => 'oe', 'Œ' => 'oe',
        'ù' => 'u', 'Ù' => 'u', 'ú' => 'u', 'Ú' => 'u', 'û' => 'u', 'Û' => 'u', 'ü' => 'u', 'Ü' => 'u',
        'ý' => 'y', 'Ý' => 'y', 'ÿ' => 'y',
    ];

    public static function normalize(string $value): string
    {
        return mb_strtolower(strtr($value, self::ACCENT_MAP));
    }
}

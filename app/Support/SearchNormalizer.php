<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Normalizes text for case- and accent-insensitive search, portably across SQL engines
 * (no reliance on engine-specific collations, ILIKE, or extensions like PostgreSQL's unaccent).
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

    /**
     * Normalizes a PHP string the same way {@see sqlExpression()} normalizes a column,
     * so both sides of a `LIKE` comparison agree regardless of the underlying SQL engine.
     */
    public static function normalize(string $value): string
    {
        return mb_strtolower(strtr($value, self::ACCENT_MAP));
    }

    /**
     * Builds a SQL expression that strips accents and lower-cases the given column, using only
     * `REPLACE`/`LOWER`, which behave identically on SQLite and PostgreSQL. `REPLACE` runs before
     * `LOWER` so the result doesn't depend on the database's locale/collation for folding
     * non-ASCII letters (only the final ASCII fold is delegated to `LOWER`).
     */
    public static function sqlExpression(string $column): string
    {
        $expression = $column;

        foreach (self::ACCENT_MAP as $accented => $plain) {
            $expression = "REPLACE({$expression}, '{$accented}', '{$plain}')";
        }

        return "LOWER({$expression})";
    }
}

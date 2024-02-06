<?php

namespace Jalno\AAA\Contracts;

enum Comparison: string
{
    case STARTSWITH = 'STARTSWITH';
    case EQUALS = 'EQUALS';
    case ENDSWITH = 'ENDSWITH';
    case CONTAINS = 'CONTAINS';

    /**
     * @return \Closure(string $operator, string|null $value)
     */
    public static function forQueryBuilder(\Closure $fn, string $value = null, self|string $comparison = null): void
    {
        if (!$comparison or !$value) {
            $fn(null, $value);
        } else {
            if (is_string($comparison)) {
                $comparison = self::from($comparison);
            }

            match ($comparison) {
                self::EQUALS => $fn('=', $value),
                self::ENDSWITH => $fn('LIKE', '%'.$value),
                self::STARTSWITH => $fn('LIKE', $value.'%'),
                self::CONTAINS => $fn('LIKE', '%'.$value.'%'),
            };
        }
    }
}

<?php

namespace Jalno\AAA\Eloquent;

enum Comparison: string
{
    case CONTAINS = 'contains';
    case EQUALS = 'equals';
    case STARTSWITH = 'startswith';
    case ENDSWITH = 'endswith';

    public static function filter(string $value, $comparison = null) {
        if (! $comparison instanceof self) {
            $comparison = self::tryFrom($comparison);
        }
        return match ($comparison) {
            Comparison::STARTSWITH => ['LIKE', $value.'%'],
            Comparison::ENDSWITH => ['LIKE', '%'.$value],
            Comparison::CONTAINS => ['LIKE', '%'.$value.'%'],
            Comparison::EQUALS => ['=', $value],
            null => [null, $value]
        };
    }
}

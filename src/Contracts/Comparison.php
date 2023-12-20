<?php

namespace Jalno\AAA\Contracts;

enum Comparison: string
{
    case CONTAINS = 'contains';
    case EQUALS = 'equals';
    case STARTSWITH = 'startswith';
    case ENDSWITH = 'endswith';
}

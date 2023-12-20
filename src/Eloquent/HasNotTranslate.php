<?php

namespace Jalno\AAA\Eloquent;

use dnj\Localization\Contracts\ITranslateModel;

trait HasNotTranslate
{
    public function getTranslate(string $locale): ?ITranslateModel
    {
        return null;
    }

    /**
     * @return iterable<ITranslateModel>
     */
    public function getTranslates(): iterable
    {
        return [];
    }
}

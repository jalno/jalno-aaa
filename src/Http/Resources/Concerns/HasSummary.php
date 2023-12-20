<?php

namespace Jalno\AAA\Http\Resources\Concerns;

trait HasSummary
{
    protected bool $summary = false;

    public function summarize(bool $summary = true): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function full(bool $full = true): static
    {
        $this->summary = !$full;

        return $this;
    }
}

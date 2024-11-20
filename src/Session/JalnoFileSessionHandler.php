<?php

namespace Jalno\AAA\Session;

use Illuminate\Session\FileSessionHandler;
use Illuminate\Support\Carbon;

class JalnoFileSessionHandler extends FileSessionHandler
{
    public static function create(): self
    {
        return new self(
            app(\Illuminate\Filesystem\Filesystem::class),
            config('jalno-aaa.session.options.php.save_path'),
            config('jalno-aaa.session.lifetime'),
        );
    }

    public function read($sessionId): string|false
    {
        if (
            $this->files->isFile($path = $this->path.'/'.$sessionId)
            && $this->files->lastModified($path) >= Carbon::now()->subMinutes($this->minutes)->getTimestamp()
        ) {
            return $this->files->get($path);
        }

        return '';
    }
}

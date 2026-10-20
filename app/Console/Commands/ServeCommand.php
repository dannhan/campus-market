<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

class ServeCommand extends BaseServeCommand
{
    /**
     * Raise PHP's upload limits for the local development server.
     *
     * Listings accept up to five photos of 5 MB each, while PHP's defaults are
     * commonly 2 MB per file and 8 MB per request. Those defaults produce a
     * 413 response that Turbo cannot render, so uploads appear to be ignored.
     *
     * @return array<int, string>
     */
    protected function serverCommand(): array
    {
        $command = parent::serverCommand();

        array_splice($command, 1, 0, [
            '-d', 'upload_max_filesize=8M',
            '-d', 'post_max_size=40M',
            '-d', 'max_file_uploads=20',
        ]);

        return $command;
    }
}

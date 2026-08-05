<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

#[Signature('production|prod')]
#[Description('Sort translation files alphabetically and format all PHP files')]
class Production extends Command
{
    public function handle(): int
    {
        $languages = array_column(
            config('i18nlocale.supportedLocales', []),
            'code'
        );

        foreach ($languages as $lang) {
            $path = lang_path("$lang/translate.php");

            if (! File::exists($path)) {
                $this->warn("File not found: {$path}");

                continue;
            }

            $translations = require $path;

            if (! is_array($translations)) {
                $this->warn("Invalid translation file: {$path}");

                continue;
            }

            ksort($translations);

            $content = sprintf(
                "<?php\n\nreturn %s;\n",
                var_export($translations, true)
            );

            File::put($path, $content);

            $this->info("Sorted: {$path}");
        }

        $this->formatPhpFiles();

        return self::SUCCESS;
    }

    protected function formatPhpFiles(): void
    {
        $process = new Process([
            base_path('vendor/bin/pint'),
            base_path(),
        ]);

        $process->setWorkingDirectory(base_path());
        $process->setTimeout(null);
        $process->run();

        if ($process->isSuccessful()) {
            $this->info('All PHP files formatted.');

            return;
        }

        $this->error('Failed to format PHP files.');
        $this->line($process->getErrorOutput());
    }
}

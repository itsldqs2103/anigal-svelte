<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

#[Signature('lang:sort')]
#[Description('Sort translation files alphabetically')]
class SortTranslations extends Command
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

        $this->formatTranslations();

        return self::SUCCESS;
    }

    protected function formatTranslations(): void
    {
        $process = new Process([
            base_path('vendor/bin/pint'),
            'lang',
        ]);

        $process->run();

        if ($process->isSuccessful()) {
            $this->info('Translation files formatted.');
            return;
        }

        $this->error('Failed to format translation files.');
        $this->line($process->getErrorOutput());
    }
}

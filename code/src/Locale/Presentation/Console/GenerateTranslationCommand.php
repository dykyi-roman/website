<?php

declare(strict_types=1);

namespace App\Locale\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:generate:js-translations',
    description: 'Generate JavaScript translation files'
)]
final class GenerateTranslationCommand extends Command
{
    private const string TRANSLATIONS_SOURCE_DIR = '/code/translations';
    private const string JS_TRANSLATIONS_OUTPUT_DIR = '/code/public/translations';

    public function __construct(
        private readonly Filesystem $filesystem
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->generateJsTranslationFiles($io);
            $io->success('JavaScript translation files generated');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error generating JS translation files: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function generateJsTranslationFiles(SymfonyStyle $io): void
    {
        $projectDir = dirname(__DIR__, 5);
        $sourcePath = $projectDir . self::TRANSLATIONS_SOURCE_DIR;
        $outputPath = $projectDir . self::JS_TRANSLATIONS_OUTPUT_DIR;

        // Ensure output directory exists
        $this->filesystem->mkdir($outputPath);

        // Find translation files
        $finder = new Finder();
        $finder->files()->in($sourcePath)->name('*.json');

        foreach ($finder as $file) {
            $filename = $file->getFilename();
            $jsonContent = json_decode($file->getContents(), true);

            // Extract only 'js' translations
            $jsTranslations = $jsonContent['js'] ?? [];

            // Skip if no JS translations
            if (empty($jsTranslations)) {
                $io->note(sprintf('No JS translations found in %s', $filename));
                continue;
            }

            // Generate JS file
            $outputFilename = $filename;
            $outputFile = $outputPath . '/' . $outputFilename;

            // Convert to JSON, preserving only JS translations
            $jsContent = json_encode($jsTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // Write JS file
            $this->filesystem->dumpFile($outputFile, $jsContent);

            $io->note(sprintf('Generated: %s', $outputFilename));
        }
    }
}

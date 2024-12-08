<?php

declare(strict_types=1);

namespace App\Locale\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:parse:translations',
    description: 'Parse and consolidate translation files'
)]
class ParseTranslationCommand extends Command
{
    private const TRANSLATIONS_SOURCE_DIR = '/code/translations';

    public function __construct(
        private readonly Filesystem $filesystem
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('locale', InputArgument::OPTIONAL, 'Specific locale to parse', 'en')
            ->setHelp('This command parses and consolidates translation files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locale = $input->getArgument('locale');

        try {
            $this->parseTranslations($locale, $io);
            $io->success(sprintf('Translations parsed for locale: %s', $locale));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error parsing translations: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function parseTranslations(string $locale, SymfonyStyle $io): void
    {
        $projectDir = dirname(__DIR__, 5);
        $sourcePath = $projectDir . self::TRANSLATIONS_SOURCE_DIR;

        // Find translation files
        $finder = new Finder();
        $finder->files()->in($sourcePath)->name("*.$locale.json");

        $consolidatedTranslations = [];

        foreach ($finder as $file) {
            $jsonContent = json_decode($file->getContents(), true);
            
            if (is_array($jsonContent)) {
                $consolidatedTranslations = array_merge_recursive(
                    $consolidatedTranslations, 
                    $jsonContent
                );
                
                $io->note(sprintf('Parsed: %s', $file->getFilename()));
            }
        }

        // Output consolidated translations
        $outputFile = sprintf('%s/consolidated.%s.json', $sourcePath, $locale);
        $this->filesystem->dumpFile(
            $outputFile, 
            json_encode($consolidatedTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $io->success(sprintf('Consolidated translations saved to: %s', $outputFile));
    }
}

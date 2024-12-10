<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Console;

use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

class TestDataCommand extends Command
{
    protected static $defaultName = 'app:test-data';

    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly PartnerRepositoryInterface $partnerRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Populate test data for Client and Partner');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create and save a test client
        $client = new Client();
        $client->setId(Uuid::v4());
        $client->setName('Test Client');
        $client->setEmail('test@client.com');
        $client->setPhone('+1234567890');
        $client->setCountry('USA');
        $client->setCity('New York');
        $client->setStatus(1);
        
        $this->clientRepository->save($client);
        $io->success('Test client created with ID: ' . $client->getId());

        // Create and save a test partner
        $partner = new Partner();
        $partner->setId(Uuid::v4());
        $partner->setName('Test Partner');
        $partner->setEmail('test@partner.com');
        $partner->setPhone('+0987654321');
        $partner->setCountry('UK');
        $partner->setCity('London');
        $partner->setStatus(1);
        
        $this->partnerRepository->save($partner);
        $io->success('Test partner created with ID: ' . $partner->getId());

        // Test retrieving the data
        $foundClient = $this->clientRepository->findById($client->getId());
        $foundPartner = $this->partnerRepository->findById($partner->getId());

        if ($foundClient) {
            $io->info('Found client: ' . $foundClient->getName());
        }

        if ($foundPartner) {
            $io->info('Found partner: ' . $foundPartner->getName());
        }

        return Command::SUCCESS;
    }
}

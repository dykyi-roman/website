<?php

declare(strict_types=1);

namespace App\Client\Presentation\Console;

use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:create-test-client',
    description: 'Creates a test client in the database',
)]
class CreateTestClientCommand extends Command
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $client = new Client();
            $client->setId(Uuid::v4());
            $client->setName('Test Client');
            $client->setEmail('test@example.com');
            $client->setPhone('+1234567890');
            $client->setCountry('Test Country');
            $client->setCity('Test City');
            $client->setPhoneVerifiedAt(null);
            $client->setEmailVerifiedAt(null);
            $client->setActivatedAt(null);
            $client->setDeactivatedAt(null);

            $this->clientRepository->save($client);

            $io->success(sprintf('Test client created with ID: %s', $client->getId()));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Failed to create test client: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Partner\Presentation\Console;

use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:create-test-partner',
    description: 'Creates a test partner in the database',
)]
class CreateTestPartnerCommand extends Command
{
    public function __construct(
        private readonly PartnerRepositoryInterface $partnerRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $partner = new Partner();
            $partner->setId(Uuid::v4());
            $partner->setName('Test Partner');
            $partner->setEmail('partner@example.com');
            $partner->setPhone('+0987654321');
            $partner->setCountry('Test Partner Country');
            $partner->setCity('Test Partner City');
            $partner->setPhoneVerifiedAt(null);
            $partner->setEmailVerifiedAt(null);
            $partner->setActivatedAt(null);
            $partner->setDeactivatedAt(null);

            $this->partnerRepository->save($partner);

            $io->success(sprintf('Test partner created with ID: %s', $partner->getId()));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Failed to create test partner: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}

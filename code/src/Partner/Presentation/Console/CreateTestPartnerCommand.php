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
            
            // Ensure all required fields are set before saving
            $partnerId = Uuid::v4();
            $partner->setId($partnerId);
            $partner->setName('Test Partner');
            $partner->setEmail('partner_' . uniqid() . '@example.com');
            $partner->setPhone('+0987654321');
            $partner->setCountry('Test Partner Country');
            $partner->setCity('Test Partner City');
            $partner->setStatus(1);

            // Explicitly set nullable fields
            $partner->setPhoneVerifiedAt(null);
            $partner->setEmailVerifiedAt(null);
            $partner->setActivatedAt(null);
            $partner->setDeactivatedAt(null);

            // Validate the partner object before saving
            $this->validatePartner($partner);

            $this->partnerRepository->save($partner);

            $io->success(sprintf('Test partner created with ID: %s', $partnerId));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Failed to create test partner: %s', $e->getMessage()));
            $io->error('Trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    private function validatePartner(Partner $partner): void
    {
        // Perform additional validation
        if (empty($partner->getName())) {
            throw new \InvalidArgumentException('Partner name cannot be empty');
        }

        if (empty($partner->getEmail())) {
            throw new \InvalidArgumentException('Partner email cannot be empty');
        }

        // Validate UserInterface methods
        $roles = $partner->getRoles();
        if (!is_array($roles)) {
            throw new \InvalidArgumentException('getRoles() must return an array');
        }

        $identifier = $partner->getUserIdentifier();
        if (empty($identifier)) {
            throw new \InvalidArgumentException('getUserIdentifier() cannot return an empty string');
        }
    }
}

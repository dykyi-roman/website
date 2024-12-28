<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Web;

use Psr\Log\LoggerInterface;
use Site\Profile\Presentation\Web\Request\ActivateAccountRequestDTO;
use Site\Profile\Presentation\Web\Response\PrivacyJsonResponder;
use Site\User\DomainModel\Repository\UserRepositoryInterface;
use Site\User\DomainModel\Service\UserFetcher;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final readonly class PrivacyAction
{
    public function __construct(
        private UserFetcher $userFetcher,
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/settings/privacy/account-activate', name: 'settings-privacy-account-activate', methods: ['GET'])]
    public function activateAccount(
        #[MapQueryString] ActivateAccountRequestDTO $request,
        PrivacyJsonResponder $responder,
    ): PrivacyJsonResponder {
        try {
            $user = $this->userFetcher->fetch();
            $request->status ? $user->activate() : $user->deactivate();
            $this->userRepository->save($user);

            return $responder->success('Ok')->respond();
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $responder->error('Ok')->respond();
        }
    }

    #[Route('/settings/privacy/account-delete', name: 'settings-privacy-account-delete', methods: ['GET'])]
    public function __invoke(
        PrivacyJsonResponder $responder,
    ): PrivacyJsonResponder {
        try {
            $user = $this->userFetcher->fetch();
            $user->delete();
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $responder->error('Ok')->respond();
        }

        return $responder->success('Ok')->respond();
    }
}

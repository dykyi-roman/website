<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Web;

use Profile\Setting\Application\SettingsPrivacy\Command\ActivateUserAccountCommand;
use Profile\Setting\Application\SettingsPrivacy\Command\DeleteUserAccountCommand;
use Profile\Setting\Presentation\Web\Request\ActivateAccountRequestDTO;
use Profile\Setting\Presentation\Web\Response\PrivacyJsonResponder;
use Profile\User\DomainModel\Enum\UserStatus;
use Profile\User\DomainModel\Service\UserFetcherInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class PrivacyAction
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/settings/privacy/user-activate', name: 'settings-privacy-user-activate', methods: ['GET'])]
    public function activateUserAccount(
        #[MapQueryString] ActivateAccountRequestDTO $request,
        UserFetcherInterface $userFetcher,
        PrivacyJsonResponder $responder,
    ): PrivacyJsonResponder {
        try {
            $this->messageBus->dispatch(
                new ActivateUserAccountCommand(
                    $userFetcher->fetch()->id(),
                    UserStatus::from($request->status),
                ),
            );

            return $responder->success('Ok')->respond();
        } catch (\Throwable) {
            return $responder->error($this->translator->trans('unexpected_registration_save_settings'))->respond();
        }
    }

    #[Route('/settings/privacy/user-delete', name: 'settings-privacy-user-delete', methods: ['GET'])]
    public function deleteUserAccount(
        UserFetcherInterface $userFetcher,
        PrivacyJsonResponder $responder,
    ): PrivacyJsonResponder {
        try {
            $this->messageBus->dispatch(new DeleteUserAccountCommand($userFetcher->fetch()->id()));

            return $responder->success('Account deleted')->respond();
        } catch (\Throwable) {
            return $responder->error($this->translator->trans('unexpected_registration_save_settings'))->respond();
        }
    }
}

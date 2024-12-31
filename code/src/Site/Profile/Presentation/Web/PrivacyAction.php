<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Web;

use Shared\DomainModel\Services\MessageBusInterface;
use Site\Profile\Application\SettingsPrivacy\Command\ActivateUserAccountCommand;
use Site\Profile\Application\SettingsPrivacy\Command\DeleteUserAccountCommand;
use Site\Profile\Presentation\Web\Request\ActivateAccountRequestDTO;
use Site\Profile\Presentation\Web\Response\PrivacyJsonResponder;
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
        PrivacyJsonResponder $responder,
    ): PrivacyJsonResponder {
        try {
            $this->messageBus->dispatch(new ActivateUserAccountCommand($request->status));

            return $responder->success('Ok')->respond();
        } catch (\Throwable) {
            return $responder->error($this->translator->trans('unexpected_registration_save_settings'))->respond();
        }
    }

    #[Route('/settings/privacy/user-delete', name: 'settings-privacy-user-delete', methods: ['GET'])]
    public function deleteUserAccount(
        PrivacyJsonResponder $responder,
    ): PrivacyJsonResponder {
        try {
            $this->messageBus->dispatch(new DeleteUserAccountCommand());

            return $responder->success('Account deleted')->respond();
        } catch (\Throwable) {
            return $responder->error($this->translator->trans('unexpected_registration_save_settings'))->respond();
        }
    }
}

<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Web;

use Site\Profile\Presentation\Web\Response\SettingsHtmlResponder;
use Site\User\DomainModel\Model\UserInterface;
use Site\User\DomainModel\Service\UserFetcher;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SettingsAction
{
    #[Route('/settings', name: 'settings')]
    public function __invoke(
        SettingsHtmlResponder $responder,
        TranslatorInterface $translator,
        UserFetcher $userFetcher,
    ): SettingsHtmlResponder {
        $user = $userFetcher->fetch();

        return $responder->context([
            'page_title' => $translator->trans('settings.page_title'),
            'content' => '',
            'settings' => [
                'privacy' => [
                    'user_status' => $user->getStatus()->isActive(),
                ],
            ],
        ])->respond();
    }
}

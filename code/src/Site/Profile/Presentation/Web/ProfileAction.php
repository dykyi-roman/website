<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Web;

use Site\Profile\Presentation\Web\Response\ProfileHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ProfileAction
{
    #[Route('/profile', name: 'profile')]
    public function __invoke(
        ProfileHtmlResponder $responder,
        TranslatorInterface $translator,
    ): ProfileHtmlResponder {
        return $responder->context([
            'page_title' => $translator->trans('profile.page_title'),
            'content' => '',
        ])->respond();
    }
}

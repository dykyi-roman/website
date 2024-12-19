<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Web\Response\ContactHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ContactAction
{
    #[Route('/contact', name: 'contact')]
    public function contact(
        ContactHtmlResponder $responder,
        TranslatorInterface $translator,
    ): ContactHtmlResponder {
        return $responder
            ->context([
                'page_title' => $translator->trans('contact.page_title'),
                'content' => $translator->trans('contact.content'),
            ])
            ->contacts($translator->trans('contact.business_hours'))
            ->respond();
    }
}

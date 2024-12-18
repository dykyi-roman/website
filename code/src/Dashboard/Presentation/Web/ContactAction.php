<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Web\Response\ContactHtmlResponder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ContactAction
{
    #[Route('/contact', name: 'contact')]
    public function contact(
        ContactHtmlResponder $responder,
        TranslatorInterface $translator,
    ): ContactHtmlResponder {
        throw new NotFoundHttpException('Oh nowwwww', 404);

        return $responder->respond([
            'page_title' => $translator->trans('contact.page_title'),
            'content' => $translator->trans('contact.content'),
            'contact' => $responder->contacts($translator->trans('contact.business_hours')),
        ]);
    }
}

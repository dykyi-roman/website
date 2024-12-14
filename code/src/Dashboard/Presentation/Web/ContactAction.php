<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Responder\ContactHtmlResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ContactAction
{
    #[Route('/contact', name: 'contact')]
    public function contact(
        ContactHtmlResponder $responder,
        TranslatorInterface $translator,
    ): Response {
        return $responder
            ->withContactData([
                'page_title' => $translator->trans('contact.page_title'),
                'page_description' => $translator->trans('contact.page_description'),
                'page_keywords' => $translator->trans('contact.page_keywords'),
            ])
            ->respond();
    }
}

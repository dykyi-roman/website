<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TermsAction extends AbstractController
{
    #[Route('/terms', name: 'terms')]
    public function __invoke(TranslatorInterface $translator): Response
    {
        $termsContent = $translator->trans('terms.content');
        $termsContent = str_replace('%last_updated_date%', date('Y-m-d'), $termsContent);

        return $this->render('@Dashboard/terms.html.twig', [
            'page_title' => $translator->trans('terms.page_title'),
            'content' => $termsContent,
        ]);
    }
}

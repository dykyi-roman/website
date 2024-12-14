<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Web\Responder\DashboardHtmlResponder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DashboardAction
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(
        TranslatorInterface $translator,
        DashboardHtmlResponder $responder
    ): DashboardHtmlResponder {
        return $responder->respond([
            'page_title' => $translator->trans('Welcome!'),
        ]);
    }
}

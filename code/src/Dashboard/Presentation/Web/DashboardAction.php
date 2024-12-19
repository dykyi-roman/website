<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Web\Response\DashboardHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DashboardAction
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(
        TranslatorInterface $translator,
        DashboardHtmlResponder $responder,
    ): DashboardHtmlResponder {
        return $responder
            ->context(['page_title' => $translator->trans('dashboard.dashboard_page_title')])
            ->respond();
    }
}

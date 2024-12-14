<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Responder\DashboardHtmlResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final readonly class DashboardAction
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(DashboardHtmlResponder $responder): Response
    {
        return $responder
            ->withDashboardData([])
            ->respond();
    }
}

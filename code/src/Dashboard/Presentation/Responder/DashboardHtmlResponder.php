<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Responder;

use App\Shared\Presentation\Responder\HtmlResponder;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class DashboardHtmlResponder extends HtmlResponder
{
    private const string TEMPLATE = '@dashboard/dashboard.html.twig';

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
        $this->template = self::TEMPLATE;
    }

    public function withDashboardData(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function respond(): Response
    {
        return parent::respond();
    }
}

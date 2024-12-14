<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Responder;

use App\Shared\Presentation\Responder\HtmlResponder;
use Twig\Environment;

final class PrivacyHtmlResponder extends HtmlResponder
{
    private const string TEMPLATE = '@dashboard/privacy.html.twig';

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);

        $this->template = self::TEMPLATE;
    }

    public function withPrivacyData(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }
}

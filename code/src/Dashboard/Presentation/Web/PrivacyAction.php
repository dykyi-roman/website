<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Web\Responder\PrivacyHtmlResponder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class PrivacyAction
{
    public function __construct(
        private string $appName,
        private string $supportEmail,
        private string $supportAddress,
    ) {
    }

    #[Route('/privacy', name: 'privacy')]
    public function privacy(
        PrivacyHtmlResponder $responder,
        TranslatorInterface $translator,
    ): PrivacyHtmlResponder {
        return $responder->respond([
            'page_title' => $translator->trans('privacy.page_title'),
            'content' => $translator->trans('privacy.content', [
                '%app_name%' => $this->appName,
                '%support_email%' => $this->supportEmail,
                '%support_address%' => $this->supportAddress,
                '%last_updated_date%' => date('Y-m-d'),
            ]),
        ]);
    }
}

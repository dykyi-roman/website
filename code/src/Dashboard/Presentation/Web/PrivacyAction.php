<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PrivacyAction extends AbstractController
{
    public function __construct(
        private readonly string $appName,
        private readonly string $supportEmail,
        private readonly string $supportAddress,
    ) {
    }

    #[Route('/privacy', name: 'privacy')]
    public function __invoke(
        TranslatorInterface $translator,
    ): Response {
        $content = $translator->trans('privacy.content', [
            '%app_name%' => $this->appName,
            '%support_email%' => $this->supportEmail,
            '%support_address%' => $this->supportAddress,
            '%last_updated_date%' => date('Y-m-d'),
        ]);

        return $this->render('@Dashboard/privacy.html.twig', [
            'page_title' => $translator->trans('Privacy Policy'),
            'content' => $content,
        ]);
    }
}

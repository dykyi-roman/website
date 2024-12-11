<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class PrivacyAction
{
    public function __construct(
        private Environment $twig,
        private TranslatorInterface $translator,
        private string $appName,
        private string $supportEmail,
        private string $supportAddress,
    ) {
    }

    #[Route('/privacy', name: 'privacy')]
    public function __invoke(Request $request): Response
    {
        $content = $this->translator->trans('privacy.content', [
            '%app_name%' => $this->appName,
            '%support_email%' => $this->supportEmail,
            '%support_address%' => $this->supportAddress,
            '%last_updated_date%' => date('Y-m-d'),
        ]);

        return new Response(
            $this->twig->render('@Dashboard/page/privacy.html.twig', [
                'page_title' => $this->translator->trans('privacy.page_title'),
                'content' => $content,
            ])
        );
    }
}

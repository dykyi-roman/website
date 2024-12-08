<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        UrlGeneratorInterface $generator,
        TranslatorInterface $translator,
    ): Response {
        $url = $generator->generate('privacy', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $content = $translator->trans('privacy.content');

        return $this->render('@Dashboard/privacy.html.twig', [
            'content' => $content,
        ]);
    }
}

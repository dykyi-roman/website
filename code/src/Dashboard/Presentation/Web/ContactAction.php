<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class ContactAction
{
    public function __construct(
        private Environment $twig,
        private TranslatorInterface $translator,
        private string $supportEmail,
        private string $supportPhone,
        private string $supportAddress,
        private string $supportMap,
        private string $supportBusinessTimeFrom,
        private string $supportBusinessTimeTo,
    ) {
    }

    #[Route('/contact', name: 'contact')]
    public function __invoke(Request $request): Response
    {
        $contactInfo = [
            'email' => $this->supportEmail,
            'phone' => $this->supportPhone,
            'address' => $this->supportAddress,
            'hours' => sprintf(
                '%s: %s - %s', 
                $this->translator->trans('contact.business_hours'), 
                $this->supportBusinessTimeFrom,
                $this->supportBusinessTimeTo
            ),
            'map' => '<iframe src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=' . $this->supportMap . '&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
        ];

        return new Response(
            $this->twig->render('@Dashboard/contact.html.twig', [
                'page_title' => $this->translator->trans('contact.page_title'),
                'content' => $this->translator->trans('contact.content'),
                'contact' => $contactInfo,
            ])
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactAction extends AbstractController
{
    public function __construct(
        private readonly string $supportEmail,
        private readonly string $supportPhone,
        private readonly string $supportAddress,
        private readonly string $supportMap,
        private readonly string $supportBusinessTimeFrom,
        private readonly string $supportBusinessTimeTo,
    ) {
    }

    #[Route('/contact', name: 'contact')]
    public function __invoke(TranslatorInterface $translator): Response
    {
        $contactInfo = [
            'email' => $this->supportEmail,
            'phone' => $this->supportPhone,
            'address' => $this->supportAddress,
            'hours' => sprintf(
                '%s: %s - %s', 
                $translator->trans('contact.business_hours'), 
                $this->supportBusinessTimeFrom,
                $this->supportBusinessTimeTo
            ),
            'map' => '<iframe src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=' . $this->supportMap . '&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
        ];

        return $this->render('@Dashboard/contact.html.twig', [
            'page_title' => $translator->trans('contact.page_title'),
            'content' => $translator->trans('contact.content'),
            'contact' => $contactInfo,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactAction extends AbstractController
{
    public function __construct(
        private readonly string $supportEmail,
        private readonly string $supportPhone,
        private readonly string $supportAddress,
    ) {
    }

    #[Route('/contact', name: 'contact')]
    public function __invoke(): Response
    {
        $contactInfo = [
            'email' => $this->supportEmail,
            'phone' => $this->supportPhone,
            'address' => $this->supportAddress,
            'hours' => 'Monday - Friday: 9:00 AM - 6:00 PM',
            'map' => '<iframe src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=Pushkina%20St,%2011+(My%20Business%20Name)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
        ];

        return $this->render('@Dashboard/contact.html.twig', [
            'page_title' => 'Contact Us',
            'content' => '<h2>Get in Touch</h2><p>We\'d love to hear from you! Please use the contact information provided or visit us during business hours.</p>',
            'contact' => $contactInfo,
        ]);
    }
}

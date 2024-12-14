<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function login(TranslatorInterface $translator): JsonResponse
    {
        //

        return new JsonResponse([
            'success' => true,
            'message' => $translator->trans('Letter sent. Check your email.'),
        ], Response::HTTP_OK);
    }
}

<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Application\ResetPassword\Query\ResetPasswordQuery;
use App\Registration\DomainModel\Service\TokenGeneratorInterface;
use App\Registration\Presentation\Web\Request\ResetPasswordFormRequestDTO;
use App\Registration\Presentation\Web\Request\ResetPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ResetPasswordHtmlResponder;
use App\Registration\Presentation\Web\Response\ResetPasswordJsonResponder;
use App\Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ResetPasswordAction
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private MessageBusInterface $queryBus,
        private TranslatorInterface $translator,
        private Security $security,
        private RouterInterface $router
    ) {
    }

    #[Route('/reset-password', name: 'reset-password-page', methods: ['GET'])]
    public function showResetPasswordPage(
        #[MapQueryString] ResetPasswordFormRequestDTO $request,
        ResetPasswordHtmlResponder $responder,
    ): ResetPasswordHtmlResponder|RedirectResponse {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse(
                $this->router->generate('dashboard')
            );
        }

        return $responder->respond([
            'page_title' => $this->translator->trans('reset_password_page_title'),
            'token' => $request->token,
            'isValid' => $this->tokenGenerator->isValid($request->token),
        ]);
    }

    #[Route('/reset-password', name: 'reset-password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] ResetPasswordRequestDTO $request,
        ResetPasswordJsonResponder $responder
    ): ResetPasswordJsonResponder {
        $response = $this->queryBus->dispatch(
            new ResetPasswordQuery(
                $request->password,
                $request->confirmPassword,
                $request->token,
            ),
        );

        if ($response->success) {
            return $responder->success($response->message)->respond();
        }

        return $responder->validationError($response->message)->respond();
    }
}

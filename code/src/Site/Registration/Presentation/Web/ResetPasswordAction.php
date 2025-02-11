<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use Shared\DomainModel\Services\MessageBusInterface;
use Site\Registration\Application\ResetPassword\Query\ResetPasswordQuery;
use Site\Registration\Application\ResetPassword\ValueObject\ResetPasswordResponse;
use Site\Registration\DomainModel\Service\TokenGeneratorInterface;
use Site\Registration\Presentation\Web\Request\ResetPasswordFormRequestDto;
use Site\Registration\Presentation\Web\Request\ResetPasswordRequestDto;
use Site\Registration\Presentation\Web\Response\ResetPasswordHtmlResponder;
use Site\Registration\Presentation\Web\Response\ResetPasswordJsonResponder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ResetPasswordAction
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private MessageBusInterface $queryBus,
        private TranslatorInterface $translator,
        private Security $security,
        private RouterInterface $router,
    ) {
    }

    #[Route('/reset-password', name: 'reset-password-page', methods: ['GET'])]
    public function showResetPasswordPage(
        #[MapQueryString] ResetPasswordFormRequestDto $request,
        ResetPasswordHtmlResponder $responder,
    ): ResetPasswordHtmlResponder|RedirectResponse {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse(
                $this->router->generate('dashboard')
            );
        }

        return $responder->context([
            'page_title' => $this->translator->trans('reset_password_page_title'),
            'token' => $request->token,
            'isValid' => $this->tokenGenerator->isValid($request->token),
        ])->respond();
    }

    #[Route('/reset-password', name: 'reset-password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] ResetPasswordRequestDto $request,
        ResetPasswordJsonResponder $responder,
    ): ResetPasswordJsonResponder {
        /** @var ResetPasswordResponse $response */
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

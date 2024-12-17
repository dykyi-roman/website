<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Application\Command\PasswordResetCommand;
use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use App\Shared\DomainModel\Services\MessageBusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private MessageBusInterface $messageBus
    ) {
    }

    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] ForgotPasswordRequestDTO $request,
        ForgotPasswordJsonResponder $responder,
        CacheInterface $cache,
        TranslatorInterface $translator,
        LoggerInterface $logger,
    ): ForgotPasswordJsonResponder {
        try {
            $cacheKey = 'forgot_password_' . $request->email()->hash();
            $cache->get($cacheKey, function (ItemInterface $item) use ($request) {
                $this->messageBus->dispatch(
                    new PasswordResetCommand(
                        $request->email()->value,
                        $this->tokenGenerator->generateToken(),
                    ),
                );

                $item->expiresAfter(3600);
            });

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            $logger->error('Password reset failed', [
                'email' => $request->email(),
                'error' => $exception->getMessage(),
            ]);

            return $responder->error($exception)->respond();
        }
    }
}

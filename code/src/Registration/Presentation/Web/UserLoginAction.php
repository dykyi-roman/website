<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Application\Command\LoginUserCommand;
use App\Registration\DomainModel\Exception\InvalidCredentialsException;
use App\Registration\Presentation\Web\Request\UserLoginRequestDTO;
use App\Shared\Domain\ValueObject\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class UserLoginAction
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/login', name: 'login_process', methods: ['GET', 'POST'])]
    public function login(
        #[MapRequestPayload] UserLoginRequestDTO $request
    ): JsonResponse {
        try {
            $command = new LoginUserCommand(
                Email::fromString($request->email),
                $request->password,
            );

            try {
                $this->commandBus->dispatch($command);
            } catch (\Throwable $busException) {
                $originalException = $busException->getPrevious() ?? $busException;

                if ($originalException instanceof \DomainException) {
                    throw $originalException;
                }

                throw $busException;
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $this->urlGenerator->generate('dashboard')
            ]);
        } catch (\DomainException $exception) {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'message' => $exception->getMessage(),
                    'field' => 'email',
                ]
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Throwable $exception) {
            $this->logger->error('Login error: ' . $exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'message' => 'An unexpected error occurred'
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Application\Command\RegisterUserCommand;
use App\Registration\Presentation\Web\Request\UserRegisterRequestDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UserRegisterAction
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] UserRegisterRequestDTO $request,
    ): JsonResponse {
        try {
            $command = new RegisterUserCommand(
                $request->name,
                $request->email,
                $request->password,
                $request->phone,
                $request->country,
                $request->city,
                $request->isPartner()
            );

            $this->commandBus->dispatch($command);

            return new JsonResponse([
                'success' => true,
                'message' => 'Registration successful',
            ], Response::HTTP_CREATED);
        } catch (\DomainException $exception) {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'message' => $exception->getMessage(),
                    'field' => 'email'
                ]
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'message' => 'An error occurred during registration. Please try again.'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

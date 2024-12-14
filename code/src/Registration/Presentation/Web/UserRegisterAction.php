<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Application\Command\RegisterUserCommand;
use App\Registration\Presentation\Responder\RegistrationJsonResponder;
use App\Registration\Presentation\Web\Request\UserRegisterRequestDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final readonly class UserRegisterAction
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private LoggerInterface $logger,
        private RegistrationJsonResponder $responder,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] UserRegisterRequestDTO $request,
    ): Response {
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

            try {
                $this->commandBus->dispatch($command);
            } catch (\Throwable $busException) {
                $originalException = $busException->getPrevious() ?? $busException;

                if ($originalException instanceof \DomainException) {
                    throw $originalException;
                }

                throw $busException;
            }

            return $this->responder->success()->respond();
        } catch (\DomainException $exception) {
            return $this->responder->validationError($exception->getMessage(), 'email')->respond();
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->responder->error($exception)->respond();
        }
    }
}

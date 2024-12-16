<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Application\Command\RegisterUserCommand;
use App\Registration\Presentation\Web\Request\UserRegisterRequestDTO;
use App\Registration\Presentation\Web\Response\RegistrationJsonResponder;
use App\Shared\Domain\ValueObject\Country;
use App\Shared\Domain\ValueObject\Email;
use App\Shared\Domain\ValueObject\Location;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserRegisterAction
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] UserRegisterRequestDTO $request,
        MessageBusInterface $commandBus,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        RegistrationJsonResponder $responder,
    ): RegistrationJsonResponder {
        try {
            $command = new RegisterUserCommand(
                $request->name,
                Email::fromString($request->email),
                $request->password,
                $request->phone,
                new Location(
                    new Country(
                        $translator->trans('countries' . $request->countryCode),
                        $request->countryCode,
                    ),
                ),
                $request->isPartner()
            );

            try {
                $commandBus->dispatch($command);
            } catch (\Throwable $busException) {
                $originalException = $busException->getPrevious() ?? $busException;

                if ($originalException instanceof \DomainException) {
                    throw $originalException;
                }

                throw $busException;
            }

            return $responder->success($translator->trans('registration_successful'))->respond();
        } catch (\DomainException $exception) {
            return $responder->validationError($exception->getMessage(), 'email')->respond();
        } catch (\Throwable $exception) {
            $logger->error($exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $responder->error($exception)->respond();
        }
    }
}

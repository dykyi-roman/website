<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use Anhskohbo\NoCaptcha\NoCaptcha;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\Registration\Application\UserRegistration\Command\RegisterUserCommand;
use Site\Registration\Presentation\Web\Request\UserRegisterRequestDTO;
use Site\Registration\Presentation\Web\Response\RegistrationJsonResponder;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserRegisterAction
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] UserRegisterRequestDTO $dto,
        MessageBusInterface $commandBus,
        NoCaptcha $captcha,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        RegistrationJsonResponder $responder,
        bool $noCaptchaEnabled = false,
    ): RegistrationJsonResponder {
        try {
            if ($noCaptchaEnabled && !$captcha->verifyResponse((string) $dto->g_recaptcha_response)) {
                return $responder->validationError($translator->trans('register_invalid_captcha'))->respond();
            }

            $command = new RegisterUserCommand(
                $dto->name,
                Email::fromString($dto->email),
                $dto->password,
                $dto->phone,
                new Location(
                    new Country(
                        $dto->countryCode,
                    ),
                ),
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

            return $responder->success($translator->trans('registration_success'))->respond();
        } catch (\DomainException $exception) {
            return $responder->validationError($exception->getMessage(), 'email')->respond();
        } catch (\Throwable $exception) {
            $logger->error($exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $responder->validationError($translator->trans('unexpected_registration_error'))->respond();
        }
    }
}

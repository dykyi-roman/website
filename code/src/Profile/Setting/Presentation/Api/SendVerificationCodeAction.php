<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\Setting\Application\SettingsAccount\Command\SendVerificationCodeCommand;
use Profile\Setting\Presentation\Api\Request\SendVerificationCodeRequestDto;
use Profile\Setting\Presentation\Api\Response\VerificationJsonResponder;
use Profile\User\DomainModel\Service\UserFetcherInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SendVerificationCodeAction
{
    #[Route('/v1/settings/profile/verification/send', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/settings/profile/verification/send',
        description: 'Sends a 6-digit verification code to the user\'s email or phone',
        summary: 'Send verification code to email or phone',
        tags: ['Settings']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'type',
                    description: 'Verification type',
                    type: 'string',
                    enum: ['email', 'phone']
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Code sent successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
            ]
        )
    )]
    public function __invoke(
        #[MapRequestPayload] SendVerificationCodeRequestDto $request,
        UserFetcherInterface $userFetcher,
        MessageBusInterface $messageBus,
        VerificationJsonResponder $responder,
        TranslatorInterface $translator,
    ): VerificationJsonResponder {
        try {
            $user = $userFetcher->fetch();

            $recipient = match ($request->type) {
                'email' => $user->email()->value,
                'phone' => $user->getPhone() ?? throw new \InvalidArgumentException('Phone number is not set'),
                default => throw new \InvalidArgumentException('Invalid verification type'),
            };

            $messageBus->dispatch(
                new SendVerificationCodeCommand(
                    userId: $user->id(),
                    type: $request->type,
                    recipient: $recipient
                )
            );

            return $responder->success('Ok')->respond();
        } catch (\Throwable) {
            return $responder->error($translator->trans('error_send_verification_code'))->respond();
        }
    }
}

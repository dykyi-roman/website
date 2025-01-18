<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\Setting\Application\SettingsAccount\Command\VerifyCodeCommand;
use Profile\Setting\Presentation\Api\Request\VerifyCodeRequestDto;
use Profile\Setting\Presentation\Api\Response\VerificationJsonResponder;
use Profile\User\Application\GetCurrentUser\Service\UserFetcherInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class VerifyCodeAction
{
    #[Route('/v1/profile/verifications/{type}', name: 'api_settings_profile_verification_verify', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/profile/verifications/{type}',
        description: 'Verifies and completes a verification request',
        summary: 'Complete verification',
        tags: ['Profile']
    )]
    #[OA\Parameter(
        name: 'type',
        description: 'Type of verification (email or phone)',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', enum: ['email', 'phone'])
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'code',
                    description: 'Verification code',
                    type: 'string',
                    maxLength: 6,
                    minLength: 6
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Code verified successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid verification code',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Invalid verification code'),
            ]
        )
    )]
    public function __invoke(
        #[MapRequestPayload] VerifyCodeRequestDto $request,
        string $type,
        UserFetcherInterface $userFetcher,
        MessageBusInterface $messageBus,
        VerificationJsonResponder $responder,
        TranslatorInterface $translator,
    ): VerificationJsonResponder {
        try {
            $user = $userFetcher->fetch();
            $messageBus->dispatch(
                new VerifyCodeCommand(
                    userId: $user->id(),
                    type: $type,
                    code: $request->code
                )
            );

            return $responder->success('Ok')->respond();
        } catch (\Throwable) {
            return $responder->error($translator->trans('settings.account.error.invalid_verification_code'))->respond();
        }
    }
}

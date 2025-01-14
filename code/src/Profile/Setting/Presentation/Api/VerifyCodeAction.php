<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\Setting\Application\SettingsAccount\Command\VerifyCodeCommand;
use Profile\Setting\Presentation\Api\Request\VerifyCodeRequestDto;
use Profile\Setting\Presentation\Api\Response\VerificationJsonResponder;
use Profile\User\DomainModel\Service\UserFetcherInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class VerifyCodeAction
{
    #[Route('/v1/settings/profile/verification/verify', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/settings/profile/verification/verify',
        description: 'Verifies the 6-digit code sent to email or phone',
        summary: 'Verify the code',
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
        UserFetcherInterface $userFetcher,
        MessageBusInterface $messageBus,
        VerificationJsonResponder $responder,
        TranslatorInterface $translator,
    ): VerificationJsonResponder {
        $user = $userFetcher->fetch();

        try {
            $messageBus->dispatch(
                new VerifyCodeCommand(
                    userId: $user->id(),
                    type: $request->type,
                    code: $request->code
                )
            );

            return $responder->success()->respond();
        } catch (\Throwable) {
            return $responder->error($translator->trans('settings.account.error.invalid_verification_code'))->respond();
        }
    }
}

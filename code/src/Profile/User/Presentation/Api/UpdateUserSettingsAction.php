<?php

declare(strict_types=1);

namespace Profile\User\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\User\Application\GetCurrentUser\Service\UserFetcherInterface;
use Profile\User\Application\UpdateUserSettings\Command\UpdateUserSettingsCommand;
use Profile\User\Application\UpdateUserSettings\Exception\UserExistException;
use Profile\User\DomainModel\Exception\AuthenticationException;
use Profile\User\DomainModel\Exception\UserNotFoundException;
use Profile\User\Presentation\Api\Request\UpdateUserSettingsRequestDto;
use Profile\User\Presentation\Api\Response\UpdateUserSettingsJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/v1/users', name: 'api_users_settings_update', methods: ['PUT'])]
#[OA\Put(
    path: '/v1/users',
    description: 'Update user settings',
    summary: 'Update user settings',
    tags: ['Users']
)]
#[OA\RequestBody(
    required: true,
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'email', type: 'string'),
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'avatar', type: 'string'),
        ]
    )
)]
#[OA\Response(
    response: 200,
    description: 'Settings updated successfully',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'message', type: 'string'),
        ],
        type: 'object'
    )
)]
#[OA\Response(
    response: 400,
    description: 'Invalid input or user already exists',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string'),
        ],
        type: 'object'
    )
)]
#[OA\Response(
    response: 401,
    description: 'User not authenticated',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string'),
        ],
        type: 'object'
    )
)]
#[OA\Response(
    response: 404,
    description: 'User not found',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string'),
        ],
        type: 'object'
    )
)]
final readonly class UpdateUserSettingsAction
{
    public function __invoke(
        #[MapRequestPayload] UpdateUserSettingsRequestDto $request,
        UserFetcherInterface $userFetcher,
        MessageBusInterface $messageBus,
        UpdateUserSettingsJsonResponder $responder,
        TranslatorInterface $translator,
    ): UpdateUserSettingsJsonResponder {
        try {
            $messageBus->dispatch(
                new UpdateUserSettingsCommand(
                    userId: $userFetcher->fetch()->id(),
                    name: $request->name,
                    email: $request->email,
                    phone: $request->phone,
                    avatar: $request->avatar,
                )
            );

            return $responder->success($translator->trans('settings.account.success'))->respond();
        } catch (AuthenticationException) {
            return $responder->error($translator->trans('settings.account.error.not_authenticated'))->respond();
        } catch (UserExistException) {
            return $responder->error($translator->trans('settings.account.error.already_exists'))->respond();
        } catch (UserNotFoundException) {
            return $responder->error($translator->trans('settings.account.error.not_found'))->respond();
        } catch (\InvalidArgumentException $exception) {
            return $responder->error($translator->trans('settings.account.error.invalid_input'))->respond();
        } catch (\Throwable) {
            return $responder->error($translator->trans('settings.account.error.save_failed'))->respond();
        }
    }
}

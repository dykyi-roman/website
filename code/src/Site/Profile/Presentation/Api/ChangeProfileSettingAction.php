<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Api;

use Symfony\Component\Routing\Annotation\Route;

final class ChangeProfileSettingAction
{
    #[Route('/v1/profile/settings', name: 'api_profile_settings_change', methods: ['PUT'])]
    public function __invoke()
    {

    }
}
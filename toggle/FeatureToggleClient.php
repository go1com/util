<?php

namespace go1\util\toggle;

use go1\util\model\User;
use Qandidate\Toggle\Context;
use Qandidate\Toggle\ToggleManager;

class FeatureToggleClient
{
    private $toggleManager;

    public function __construct(ToggleManager $toggleManager)
    {
        $this->toggleManager = $toggleManager;
    }

    public function available(string $featureName, string $portalName = null, string $env = null, User $user = null): bool
    {
        $context = new Context();
        $portalName && $context->set('portal', [$portalName]);
        $env && $context->set('env', $env);
        $user && $context->set('user_id', $user->id);

        return $this->toggleManager->active($featureName, $context);
    }
}

<?php

namespace go1\util\error;

use go1\util\Error;
use Symfony\Component\HttpFoundation\JsonResponse;

class PortalError extends Error
{
    # Specific errors inside portal service
    const PORTAL_USER_PLAN_REACHED  = 3002;

    public static function createPortalUserPlanReached(string $instance): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => "You hit the maximum number of user accounts allowed for portal {$instance}. Please contact your portal administrator for assistance.",
                'code' => self::PORTAL_USER_PLAN_REACHED
            ],
            Error::NOT_ACCEPTABLE
        );
    }
}

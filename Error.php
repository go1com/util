<?php

namespace go1\util;

use Assert\LazyAssertionException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class Error
{
    # Common HTTP error codes
    # #####################
    const BAD_REQUEST                   = 400;
    const UNAUTHORIZED                  = 401;
    const PAYMENT_REQUIRED              = 402;
    const FORBIDDEN                     = 403;
    const NOT_FOUND                     = 404;
    const METHOD_NOT_ALLOWED            = 405;
    const NOT_ACCEPTABLE                = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT               = 408;
    const CONFLICT                      = 409;
    const PRECONDITION_FAILED           = 412;
    const PAYLOAD_TOO_LARGE             = 413;
    const UPGRADE_REQUIRED              = 426;
    const TOO_MANY_REQUESTS             = 429;
    const HEADER_TOO_LARGE              = 431;
    const SERVICE_UNAVAILABLE           = 503;
    const GATEWAY_TIMEOUT               = 504;

    # Error inside services
    # #####################
    const API_ERROR        = 1000;
    const QUEUE_ERROR      = 2000;
    const PORTAL_ERROR     = 3000;
    const PORTAL_NO_LEGACY = 3001;
    const USER_ERROR       = 4000;
    const LO_ERROR         = 5000;
    const LOB_ERROR        = 6000;
    const ENROLMENT_ERROR  = 7000;
    const OUTCOME_ERROR    = 8000;
    const ENTITY_ERROR     = 9000;
    const GRAPHIN_ERROR    = 10000;
    const RULES_ERROR      = 11000;
    const CLOUDINARY_ERROR = 12000;
    const S3_ERROR         = 13000;
    const FINDER_ERROR     = 14000;
    const HISTORY_ERROR    = 15000;
    const ONBOARD_ERROR    = 17000;

    # Credit service
    # ---------------------
    const CREDIT_ERROR                         = 16000;
    const CREDIT_NOT_FOUND                     = 16001;
    const CREDIT_NOT_AVAILABLE                 = 16002;
    const CREDIT_PRODUCT_UNMATCH               = 16003;
    const CREDIT_INVALID_TRANSACTION_REFERENCE = 16004;
    const CREDIT_CANNOT_UPDATE_PROPERTIES      = 16005;

    # Error outside services
    # #####################
    const X_SERVICE_UNREACHABLE = 80000;
    const ONBOARD_STRIPE_ERROR  = 17001;

    public static function throw(Exception $e)
    {
        throw $e;
    }

    public static function isBadServerResponse(int $code): bool
    {
        return ($code >= 500) && ($code <= 599);
    }

    public static function createMissingOrInvalidJWT(): JsonResponse
    {
        return new JsonResponse(['message' => 'Missing or invalid JWT.'], 403);
    }

    public static function simpleErrorJsonResponse($msg, $code = 400): JsonResponse
    {
        return new JsonResponse(['message' => ($msg instanceof Exception) ? $msg->getMessage() : $msg], $code);
    }

    public static function jr($msg)
    {
        return static::simpleErrorJsonResponse($msg, 400);
    }

    public static function jr403($msg): JsonResponse
    {
        return static::simpleErrorJsonResponse($msg, 403);
    }

    public static function jr404($msg): JsonResponse
    {
        return static::simpleErrorJsonResponse($msg, 404);
    }

    public static function jr406($msg): JsonResponse
    {
        return static::simpleErrorJsonResponse($msg, 406);
    }

    public static function jr500($msg): JsonResponse
    {
        return static::simpleErrorJsonResponse($msg, 500);
    }

    public static function createLazyAssertionJsonResponse(LazyAssertionException $e, int $httpCode = 400): JsonResponse
    {
        $data = ['message' => $e->getMessage()];

        foreach ($e->getErrorExceptions() as $error) {
            $data['error'][] = [
                'path'    => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return new JsonResponse($data, $httpCode);
    }
}

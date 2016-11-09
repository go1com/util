<?php

namespace go1\util;

class ErrorCodes
{
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
}

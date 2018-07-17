<?php

namespace go1\util\user;

class UserStatus
{
    const INACTIVE  = 0;
    const ACTIVE    = 1;
    const VIRTUAL   = 2;// Linked to user by HAS_ACCOUNT_VIRTUAL
}

<?php

namespace go1\util\activity;

class ActionType
{
    const CREATE  = 0;
    const UPDATE  = 1;
    const DELETE  = 2;
    const IMPORT  = 3;
    const APPROVE = 4;
    const REJECT  = 5;

    # User actions.
    const USER_LOGIN_SUCCESS = 100;
    const USER_LOGIN_FAILED  = 101;
    const USER_LOGOUT        = 102;
}

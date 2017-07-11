<?php

namespace go1\util\activity;

class ActionType
{
    const CREATE  = 1;
    const IMPORT  = 2;
    const UPDATE  = 3;
    const DELETE  = 4;
    const APPROVE = 5;
    const REJECT  = 6;

    # User actions.
    const USER_LOGIN_SUCCESS = 100;
    const USER_LOGIN_FAILED  = 101;
    const USER_LOGOUT        = 102;
}

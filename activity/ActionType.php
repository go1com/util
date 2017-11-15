<?php

namespace go1\util\activity;

class ActionType
{
    const CREATE  = 1;
    const UPDATE  = 2;
    const DELETE  = 3;
    const IMPORT  = 4;
    const APPROVE = 5;
    const REJECT  = 6;
    const PASSED  = 'Passed';
    const FAILED  = 'Failed';

    # User actions.
    const USER_LOGIN_SUCCESS = 100;
    const USER_LOGIN_FAILED  = 101;
    const USER_LOGOUT        = 102;
}

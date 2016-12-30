<?php

namespace go1\util;

class Queue
{
    const LO_CREATE       = 'lo.create'; # Body: LO object, no lo.items should be expected.
    const LO_UPDATE       = 'lo.update'; # Body: LO object with extra property: origin.
    const LO_DELETE       = 'lo.delete'; # Body: LO object.
    const USER_CREATE     = 'user.create';
    const USER_UPDATE     = 'user.update';
    const RO_CREATE       = 'ro.create';
    const RO_UPDATE       = 'ro.update';
    const RO_DELETE       = 'ro.delete';
    const VOTE_CREATE     = 'vote.create';
    const VOTE_DELETE     = 'vote.delete';
    const CUSTOMER_CREATE = 'customer.create';
    const CUSTOMER_UPDATE = 'customer.update';
    const CUSTOMER_DELETE = 'customer.delete';
}

<?php

namespace go1\util\portal;

class PortalStatuses
{
    const ONBOARDING = -100;
    const DELETED    = -2;
    const DISABLED   = -1;
    const QUEUED     = 0;
    const ENABLED    = 1;

    const TIERS  = ['Unclassified', 'Trial', 'Paid', 'Free', 'Test', 'Inactive'];
    const STAGES = ['Pre-onboarding', 'Onboarding', 'Established', 'Advocate', 'Inactive'];
}

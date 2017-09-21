<?php

namespace go1\util\group;

class GroupTypes
{
    const DEFAULT         = 'default';         # Portal's groups for discussion, which contain users & notes.
    const CONTENT         = 'content';         # contain courses per portal
    const CONTENT_PACKAGE = 'content_package'; # contain CONTENT groups & portals.
    const CONTENT_SHARING = 'content_sharing'; # contain portals that course author would like to share. This group is similar to `content_packing` but it only contains one course.
    const SYSTEM          = 'system';          # Portal group, created by onboard wizard or portal sharing process, contain any items shared to a portal
    const ALL             = [self::DEFAULT, self::CONTENT_PACKAGE, self::CONTENT_SHARING, self::CONTENT, self::SYSTEM];
}

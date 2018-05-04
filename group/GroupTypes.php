<?php

namespace go1\util\group;

class GroupTypes
{
    const DEFAULT          = 'default';          # Portal's groups for discussion, which contain users & notes.
    const CONTENT          = 'content';          # contain courses per portal
    const CONTENT_PACKAGE  = 'content_package';  # contain CONTENT groups & portals.
    const CONTENT_SHARING  = 'content_sharing';  # contain portals that course author would like to share. This group is similar to `content_packing` but it only contains one course.
    const SYSTEM           = 'system';           # Portal group, created by onboard wizard or portal sharing process, contain any items shared to a portal
    const REPORT_ENROLMENT = 'report_enrolment'; # Date reporting: Enrolment.
    const COLLECTION       = 'collection';       # Contains courses only.

    const ALL = [
        self::DEFAULT,
        self::CONTENT_PACKAGE, self::CONTENT_SHARING, self::CONTENT,
        self::SYSTEM,
        self::REPORT_ENROLMENT,
        self::COLLECTION,
    ];

    public static function label(string $type): string
    {
        switch ($type) {
            case self::CONTENT:
                return 'Content';

            case self::CONTENT_PACKAGE:
                return 'Recipient';

            case self::DEFAULT:
                return 'Discussion';

            case self::REPORT_ENROLMENT:
                return 'Enrolment report';

            case self::COLLECTION:
                return 'Collection';
        }

        return '';
    }

    public static function graphLabel(string $type): string
    {
        switch ($type) {
            case self::CONTENT:
                return 'GroupContent';

            case self::CONTENT_PACKAGE:
                return 'GroupContentPackage';

            case self::CONTENT_SHARING:
                return 'GroupContentSharing';

            case self::SYSTEM:
                return 'GroupSystem';

            case self::REPORT_ENROLMENT:
                return $type;

            case self::COLLECTION:
                return 'GroupCollection';
        }

        return 'GroupDefault';
    }

    public static function value(string $label): string
    {
        switch ($label) {
            case "Content":
            case "content":
                return self::CONTENT;

            case "Recipient":
            case "recipient":
                return self::CONTENT_PACKAGE;

            case "Discussion":
            case "discussion":
                return self::DEFAULT;

            case "Collection":
            case "collection":
                return self::COLLECTION;

            default:
                return '';
        }
    }
}

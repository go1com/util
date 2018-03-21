<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\portal\PortalHelper;
use go1\util\schema\InstallTrait;
use go1\util\user\Roles;

/**
 *
 * Use me:
 *
 * // Default
 * $one = new class { use OneMock; };
 * $one->install($db);
 *
 * // OVerride something
 * $one = new class {
 *      use OneMock;
 *
 *      public $instanceName = 'go1training.mygo1.com';
 *
 *      public function installCourses($db) {
 *          parent::installCourses($db);
 *
 *          # Create more learning pathway, courses, …
 *      }
 * };
 */
trait OneMockTrait
{
    use InstallTrait;
    use PortalMockTrait;
    use UserMockTrait;
    use LoMockTrait;
    use EnrolmentMockTrait;

    // The #accounts
    public $accountsName = 'accounts-dev.gocatalyze.com';
    public $accountsPublicKey;
    public $accountsPrivateKey;
    public $accountsRoleAdminId;

    // Admin on #accounts
    public $rootMail    = 'admin@go1.com';
    public $rootUuid    = '6414ec1c-ec79-4f3f-8e74-13b7ec86c597';
    public $rootId;
    public $rootSubUuid = 'b54e95c9-07b2-43ea-916d-d7cb250f325a';
    public $rootSubId;
    public $rootJwt;

    // The portal
    public $instanceName          = 'qa.mygo1.com';
    public $instanceVersion       = PortalHelper::STABLE_VERSION;
    public $instanceId;
    public $instancePublicKey;
    public $instancePrivateKey;
    public $instanceConfiguration = [];
    public $instanceFeatures      = [
        'marketplace' => true,
        'user_invite' => true,
        'auth0'       => false,
    ];

    // Portal roles
    public $roleAdminId;
    public $roleTutorId;
    public $roleStudentId;
    public $roleAuthenticatedId;
    public $roleManagerId;

    // Portal admin
    public $adminMail      = 'admin@qa.mygo1.com';
    public $adminUuid      = '6414ec1c-ec79-4f3f-8e74-13b7ec86c597';
    public $adminId;
    public $adminProfileId = 123;
    public $adminSubUuid   = 'b54e95c9-07b2-43ea-916d-d7cb250f325a';
    public $adminSubId;
    public $adminJwt;

    // Portal student.
    public $studentMail      = 'student@qa.mygo1.com';
    public $studentId;
    public $studentProfileId = 456;
    public $studentSubUuid   = '5dc1f6f1-ee89-4a5f-b03a-6a689ef7f012';
    public $studentSubId;
    public $studentUuid      = '231e11aa-c36e-4c51-aac7-2e905e773cf2';
    public $studentJwt;

    // Basic course
    public $course = [
        'id'    => null,
        'type'  => 'course',
        'title' => 'Basic first aid',
        'items' => [
            [
                'id'    => null,
                'type'  => 'module',
                'title' => 'Initial Response to an Accident Scene',
                'items' => [
                    ['id' => null, 'type' => 'resource', 'title' => '1. Introduction & Applicable Regulations'],
                    ['id' => null, 'type' => 'video', 'title' => '1. Responding to an Accident Scene'],
                ],
            ],
            [
                'id'    => null,
                'type'  => 'module',
                'title' => 'Bleeding, Shock, and Burns & Scalds',
                'items' => [
                    ['id' => null, 'type' => 'resource', 'title' => '1. Bleeding Injuries'],
                    ['id' => null, 'type' => 'resource', 'title' => '2. Shock"'],
                    ['id' => null, 'type' => 'resource', 'title' => '3. Basic First Aid: Module 2 Quiz'],
                    ['id' => null, 'type' => 'resource', 'title' => '4. Bonus Video: First Aid for Shock & Bleeding'],
                ],
            ],
        ],
    ];

    public function install(Connection $db)
    {
        if (!$db->getSchemaManager()->tablesExist('gc_instance') || !$db->getSchemaManager()->tablesExist('gc_roles')) {
            $this->installGo1Schema($db);
        }

        $this->installAccounts($db);
        $this->installPortal($db);
    }

    public function installAccounts(Connection $db)
    {
        $this->createPortal($db, ['title' => $this->accountsName]);
        $this->accountsRoleAdminId = $this->createRole($db, ['instance' => $this->accountsName, 'name' => Roles::ROOT]);
    }

    public function installPortal(Connection $db)
    {
        if ($db->fetchColumn('SELECT 1 FROM gc_instance WHERE title = ?', [$this->instanceName])) {
            return null;
        }

        $this->instanceId = $this->createPortal($db, [
            'title'   => $this->instanceName,
            'version' => $this->instanceVersion,
            'data'    => [
                'features'      => $this->instanceFeatures,
                'configuration' => $this->instanceConfiguration,
            ],
        ]);

        $this->instancePublicKey = $this->createPortalPublicKey($db, ['instance' => $this->instanceName]);
        $this->instancePrivateKey = $this->createPortalPrivateKey($db, ['instance' => $this->instanceName]);

        $this->installPortalUsers($db);
        $this->installCourses($db);
    }

    public function installPortalUsers(Connection $db)
    {
        $this->installPortalRoles($db);

        // Set admin user
        $this->link(
            $db,
            EdgeTypes::HAS_ACCOUNT,
            $this->adminId = $this->createUser($db, ['instance' => $this->accountsName, 'mail' => $this->adminMail, 'uuid' => $this->adminUuid, 'profile_id' => $this->adminProfileId]),
            $this->adminSubId = $this->createUser($db, ['instance' => $this->instanceName, 'mail' => $this->adminMail, 'uuid' => $this->adminSubUuid])
        );
        $this->link($db, EdgeTypes::HAS_ROLE, $this->adminSubId, $this->roleAdminId);
        $this->link($db, EdgeTypes::HAS_ROLE, $this->adminSubId, $this->roleAuthenticatedId);
        $this->adminJwt = JWT::encode(
            $this->getPayload([
                'id'            => $this->adminId,
                'instance_name' => $this->instanceName,
                'profile_id'    => $this->adminProfileId,
                'mail'          => $this->adminMail,
                'roles'         => [Roles::ADMIN, Roles::AUTHENTICATED],
            ]),
            'INTERNAL'
        );

        // Setup student user
        $this->link(
            $db,
            EdgeTypes::HAS_ACCOUNT,
            $this->studentId = $this->createUser($db, ['instance' => $this->accountsName, 'mail' => $this->studentMail, 'uuid' => $this->studentUuid, 'profile_id' => $this->studentProfileId]),
            $this->studentSubId = $this->createUser($db, ['instance' => $this->instanceName, 'mail' => $this->studentMail, 'uuid' => $this->studentSubUuid])
        );
        $this->link($db, EdgeTypes::HAS_ROLE, $this->studentSubId, $this->roleStudentId);
        $this->link($db, EdgeTypes::HAS_ROLE, $this->studentSubId, $this->roleAuthenticatedId);
        $this->studentJwt = JWT::encode(
            $this->getPayload([
                'id'            => $this->studentId,
                'instance_name' => $this->instanceName,
                'profile_id'    => $this->studentProfileId,
                'mail'          => $this->studentMail,
                'roles'         => [Roles::STUDENT, Roles::AUTHENTICATED],
            ]),
            'INTERNAL'
        );
    }

    public function installPortalRoles(Connection $db)
    {
        $this->roleAdminId = $this->createRole($db, ['instance' => $this->instanceName, 'name' => Roles::ADMIN]);
        $this->roleAuthenticatedId = $this->createRole($db, ['instance' => $this->instanceName, 'name' => Roles::AUTHENTICATED]);
        $this->roleTutorId = $this->createRole($db, ['instance' => $this->instanceName, 'name' => Roles::TUTOR]);
        $this->roleStudentId = $this->createRole($db, ['instance' => $this->instanceName, 'name' => Roles::STUDENT]);
        $this->roleManagerId = $this->createRole($db, ['instance' => $this->instanceName, 'name' => Roles::MANAGER]);
    }

    public function installCourses(Connection $db)
    {
        $save = function (&$node, $parentType = null, $parentId = null) use (&$db, &$save) {
            $node['id'] = $this->createLO($db, [
                'type'        => $node['type'],
                'title'       => $node['title'],
                'instance_id' => $this->instanceId,
            ]);

            if ($parentType && $parentId) {
                $type = ('course' === $parentType) ? EdgeTypes::HAS_MODULE : EdgeTypes::HAS_LI;
                $this->link($db, $type, $parentId, $node['id']);
            }

            $this->createEnrolment($db, [
                'profile_id' => $this->studentProfileId,
                'lo_id'      => $node['id'],
                'status'     => EnrolmentStatuses::IN_PROGRESS,
                'changed'    => time(),
            ]);

            if (!empty($node['items'])) {
                foreach ($node['items'] as &$item) {
                    $save($item, $node['type'], $node['id']);
                }
            }
        };

        $save($this->course);
    }
}

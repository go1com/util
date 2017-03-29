<?php

namespace go1\util\lo;

use Exception;
use InvalidArgumentException;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\portal\PortalChecker;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class LoAccessChecker extends AccessChecker
{
    const LO_OPERATION_BROWSE   = 'lo.browse';
    const LO_OPERATION_CREATE   = 'lo.create';
    const LO_OPERATION_UPDATE   = 'lo.update';
    const LO_OPERATION_DELETE   = 'lo.delete';
    const LO_OPERATION_ARCHIVE  = 'lo.archive';

    private $db;
    private $portalChecker;
    private $loChecker;
    private $cache;
    private $cacheId = 'loAccess:%USER%:%OPERATION%:%INSTANCE%';

    public function __construct(
        Connection $connection,
        PortalChecker $portalChecker,
        LoChecker $loChecker,
        CacheProvider $cache
    )
    {
        $this->db = $connection;
        $this->portalChecker = $portalChecker;
        $this->loChecker = $loChecker;
        $this->cache = $cache;
    }

    public function access(string $op, Request $req, $options, bool $refresh = false)
    {
        if (!$user = $this->validUser($req)) {
            return new JsonResponse(['message' => 'Invalid user.'], 403);
        }

        $this->cacheId = str_replace(['%USER%', '%OPERATION%', '%INSTANCE%'], [$user->id, $op, $options->instanceId], $this->cacheId);
        if (!$refresh) {
            if ($this->cache->contains($this->cacheId)) {
                if ($e = $this->cache->fetch($this->cacheId)) {
                    return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
                }
            }
        }

        try {
            switch ($op) {
                case self::LO_OPERATION_CREATE:
                    $this->loCreateAccess($req, $user, $options);
                    break;

                case self::LO_OPERATION_UPDATE:
                    $this->loUpdateAccess($req, $user, $options);
                    break;

                case self::LO_OPERATION_BROWSE:
                case self::LO_OPERATION_DELETE:
                case self::LO_OPERATION_ARCHIVE:
                    break;

                default:
                    throw new InvalidArgumentException('Invalid operation.', 400);
            }
        }
        catch (Exception $e) {
            $this->cache->save($this->cacheId, $e, $tll = 120);
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        }
    }

    private function loBrowseAccess(Request $req, stdClass $user, $o) {
        //@TODO
    }

    private function loCreateAccess(Request $req, stdClass $user, $o) {
        if (!$this->hasAccount($req, $o->instanceName)) {
            throw new InvalidArgumentException('Account not found.', 404);
        }

        $portal = $this->portalChecker->load($this->db, $o->instanceId);
        if (!$this->isPortalTutor($req, $portal->title)) {
            if(!$this->portalChecker->allowPublicWriting($portal) && (!$o->linkSourceId || !$this->loChecker->isParentLosAuthor($this->db, 0, $o->type, $user->id, $o->linkSourceId))) {
                throw new AccessException('Only admin or tutor or parent learning object\'s author can create learning object in portal.', 403);
            }
        }
    }

    private function loUpdateAccess(Request $req, stdClass $user, $o) {
        if (!$this->hasAccount($req, $o->instanceName)) {
            throw new InvalidArgumentException('Account not found.', 404);
        }

        $portal = $this->portalChecker->load($this->db, $o->instanceId);
        if (!$this->isPortalTutor($req, $portal->title)) {
            $lo = LoHelper::load($this->db, $o->id);
            if(!$this->loChecker->isAuthor($this->db, $o->id, $user->id) && !$this->loChecker->isParentLosAuthor($this->db, $o->id, $lo->type, $user->id)) {
                throw new AccessException('Only admin or tutor or parent learning object\'s author can update learning object in portal.', 403);
            }
        }
    }
}

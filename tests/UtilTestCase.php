<?php

namespace go1\util\tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\DriverManager;
use Firebase\JWT\JWT;
use go1\clients\MqClient;
use go1\util\schema\InstallTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Service;
use go1\util\UtilServiceProvider;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class UtilTestCase extends TestCase
{
    use InstallTrait;
    use UserMockTrait;

    protected $db;
    protected $queue;
    protected $queueMessages;

    protected $portalChecker;
    protected $loChecker;

    public function setUp()
    {
        $this->db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($this->db);

        $this->queue = $this->getMockBuilder(MqClient::class)->setMethods(['publish'])->disableOriginalConstructor()->getMock();
        $this
            ->queue
            ->method('publish')
            ->willReturnCallback(function ($body, $routingKey) {
                $this->queueMessages[$routingKey][] = $body;
            });
    }

    protected function getContainer()
    {
        $logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMockForAbstractClass();

        return (new Container)
            ->register(new UtilServiceProvider, [
                    'logger'       => $logger,
                    'client'       => new Client,
                    'cache'        => new ArrayCache,
                    'queueOptions' => [
                        'host' => '172.31.11.129',
                        'port' => '5672',
                        'user' => 'go1',
                        'pass' => 'go1',
                    ],
                ] + Service::urls(['queue', 'user', 'mail', 'portal', 'rules', 'currency', 'lo', 'sms', 'graphin'], 'qa')
            );
    }

    protected function middlewarePreProcess(Request &$request)
    {
        self::coreMiddleware($request);
        self::jwtMiddleware($request);
    }

    /**
     * @param Request $request
     *
     * @see go1\app\providers\CoreMiddlewareProvider
     */
    private function coreMiddleware(Request $request) {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : []);
        }
    }

    /**
     * @param Request $request
     *
     * @see go1\middleware\JwtMiddleware
     */
    private function jwtMiddleware(Request $request) {
        $auth = $request->headers->get('Authorization') ?: $request->headers->get('authorization');
        if ($auth) {
            if (0 === strpos($auth, 'Bearer ')) {
                $token = substr($auth, 7);
            }
        }

        $token = $request->query->get('jwt', isset($token) ? $token : null);
        if ($token && (2 === substr_count($token, '.'))) {
            $token = explode('.', $token);
            $request->request->set('jwt.payload', JWT::jsonDecode(JWT::urlsafeB64Decode($token[1])));
        }
    }
}

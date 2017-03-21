<?php
namespace go1\util;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DevelServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $c)
    {
    }

    public function boot(Application $app)
    {
        $app->get('/devel/php', function () {
            return new Response(
                '<!DOCTYPE html><html lang="en"><head>'
                . '<meta charset="utf-8">'
                . '<meta http-equiv="X-UA-Compatible" content="IE=edge">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1">'
                . '<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">'
                . '<link href="//bootswatch.com/flatly/bootstrap.min.css" rel="stylesheet">'
                . '</head>'
                . '<body>'
                . '<div class="container">'
                . ' <form method="POST" action="' . (isset($_GET['jwt']) ? $_GET['jwt'] : '') . '">'
                . '     <textarea class="form-control" rows="10" id="code" name="code"></textarea>'
                . '     <div><button type="submit" class="btn btn-default">Submit</button></div>'
                . ' </form>'
                . ' <div style="padding-top: 45px;"><iframe name="result" height="500px" width="100%"></iframe></div>'
                . '</div>'
                . '</body></html>'
            );
        });

        $app->post('/devel/php', function (Request $req) {
            $checker = new AccessChecker;
            if ($checker->isAccountsAdmin($req)) {
                $code = $req->get('code');
                if (0 === strpos($code, '<?php')) {
                    $code = substr($code, 5);
                }

                if (!empty($code)) {
                    return new Response(eval($code));
                }
            }

            return new Response(null, 204);
        });
    }
}

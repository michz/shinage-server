<?php
declare(strict_types=1);

namespace mztx\ShinageOnlinePlayerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class ViewController extends Controller
{
    // @TODO This is just copied from player bundle. Perhaps it should be improved a bit.
    public function viewAction(Request $request, string $screenGuid)
    {
        /** @var RouterInterface $router */
        $router = $this->get('router');

        $playerHtml = __DIR__ . '/../../../vendor/mztx/shinage-player-bundle/Resources/public/player.html';
        $appContent = file_get_contents($playerHtml);
        $appContent = str_replace(
            [
                '%%screen_guid%%',
                '%%preview_mode%%',
                '%%base_url%%',
                '%%current_url%%'
            ],
            [
                $screenGuid,
                1,
                $request->getSchemeAndHttpHost() . $request->getBasePath(),
                $router->generate('shinage.player.current')
            ],
            $appContent
        );
        return new Response($appContent);
    }
}

<?php
/**
 * @url https://fishpig.com/
 */
if (PHP_SAPI !== 'cli') {
    $router = new \FishPig\Util\Controller\OPcache\Router(
        new \FishPig\Util\Model\OPcache\Config,
        $_SERVER
    );

    if ($action = $router->match()) {
        $action->execute();
    }
}

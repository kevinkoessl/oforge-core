<?php

namespace Oforge\Engine\Modules\Auth\Middleware;

use Oforge\Engine\Modules\Auth\Services\AuthService;
use Oforge\Engine\Modules\Auth\Services\Permissions;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

class SecureMiddleware {

    /** @var string $urlPathName The named path for redirects */
    protected $urlPathName = '';

    /**
     * Middleware call before the controller call
     *
     * @param Request $request
     * @param Response $response
     *
     * @return Response|null
     * @throws \Oforge\Engine\Modules\Core\Exceptions\ServiceNotFoundException
     */
    public function prepend($request, $response) : ?Response
    {
        $controllerMethod = Oforge()->View()->get("meta")["controller_method"];

        /**
         * @var $permissionService Permissions
         */
        $permissionService = Oforge()->Services()->get("permissions");

        $permissions = $permissionService->get($controllerMethod);
        $auth = null;

        if (isset($_SESSION['auth'])) {
            $auth = $_SESSION['auth'];
        }

        if (isset($permissions)) {
            
            /**
             * @var $authService AuthService
             */
            $authService = Oforge()->Services()->get("auth");
            $user = $authService->decode($auth);

            if ($this->isUserValid($user, $permissions)) {
                Oforge()->View()->assign([
                    'user' => $user
                ]);
                //nothing to do. proceed
            } else {

                /*
                TODO: If there is a secured area, there should be either a message when redirecting
                      or a 401 status code.
                */

                /**
                 * @var Router $router
                 */
                $router = Oforge()->App()->getContainer()->get("router");
                if (!empty($this->urlPathName)) {
                    $uri = $router->pathFor($this->urlPathName);
                    return $response = $response->withRedirect($uri, 302);
                }
                return $response = $response->withRedirect('/', 302);
            }
        }
        return $response;
    }

    /**
     * @param $user
     * @param $permissions
     *
     * @return bool
     */
    protected function isUserValid($user, $permissions) {
        return (!is_null($user) &&
            isset($user["role"]) && $user["role"] <= $permissions["role"] &&
            isset($user["type"]) && $user["type"] == $permissions["type"]);
    }
}
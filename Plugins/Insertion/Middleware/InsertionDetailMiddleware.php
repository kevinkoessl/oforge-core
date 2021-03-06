<?php

namespace Insertion\Middleware;

use Insertion\Models\Insertion;
use Insertion\Services\InsertionProfileService;
use Insertion\Services\InsertionService;
use Oforge\Engine\Modules\I18n\Helper\I18N;

class InsertionDetailMiddleware {
    public function __invoke($request, $response, $next) {
        $uri  = $request->getUri();
        $path = $uri->getPath();

        //Split path into chunks
        $pathChunks = explode("/", $path);

        if (sizeof($pathChunks) == 4) {
            if (is_numeric($pathChunks[3])) {
                /**
                 * @var $service InsertionService
                 */
                $service = Oforge()->Services()->get('insertion');
                /**
                 * @var $insertion Insertion
                 */
                $insertion = $service->getInsertionById($pathChunks[3]);
                $router = Oforge()->App()->getContainer()->get('router');


                if ($insertion != null) {
                    $typeTitle = str_replace(" ", "-", strtolower(I18N::translate($insertion->getInsertionType()->getName())));
                    $title     = str_replace(" ", "-", strtolower($insertion->getContent()[0]->getTitle()));
                    $title     = str_replace("/", "", $title);

                    if ($typeTitle == $pathChunks[1]) {
                        if (urlencode($title) != $pathChunks[2]) {

                            $url = "/" . urlencode($typeTitle) . "/" . urlencode($title) . "/" . $insertion->getId();

                            return $response->withRedirect($url, 301);
                        }

                        $result = $router->pathFor('insertions_detail', ["id" => $pathChunks[3]]);

                        $newUri  = $uri->withPath($result);
                        $request = $request->withUri($newUri);
                    }
                }

                /** @var InsertionProfileService $serviceProfile */
                $serviceProfile = Oforge()->Services()->get('insertion.profile');
                $profile = $serviceProfile->getById($pathChunks[3]);
                if($profile != null) {
                    if (urlencode(I18N::translate('insertion_url_profile')) == $pathChunks[1]) {
                        $title     = urlencode(str_replace(" ", "-", strtolower($profile->getImprintName())));
                        $title     = str_replace("/", "", $title);

                        if($title == $pathChunks[2]) {
                            $result = $router->pathFor('insertions_profile', ["id" => $pathChunks[3]]);

                            $newUri  = $uri->withPath($result);
                            $request = $request->withUri($newUri);
                        }
                    }
                }
            }
        }

        return $next($request, $response);
    }
}

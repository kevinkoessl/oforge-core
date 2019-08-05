<?php

namespace Insertion\Services;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FrontendUserManagement\Models\User;
use FrontendUserManagement\Services\UserService;
use Insertion\Models\AttributeKey;
use Insertion\Models\AttributeValue;
use Insertion\Models\Insertion;
use Insertion\Models\InsertionAttributeValue;
use Insertion\Models\InsertionContent;
use Insertion\Models\InsertionType;
use Insertion\Models\InsertionUserBookmark;
use Insertion\Models\InsertionUserSearchBookmark;
use Oforge\Engine\Modules\Core\Abstracts\AbstractDatabaseAccess;
use Oforge\Engine\Modules\Core\Exceptions\ServiceNotFoundException;
use Oforge\Engine\Modules\I18n\Models\Language;
use Slim\Router;

class InsertionSearchBookmarkService extends AbstractDatabaseAccess {
    public function __construct() {
        parent::__construct([
            'insertion' => Insertion::class,
            'search'    => InsertionUserSearchBookmark::class,
        ]);
    }

    public function add(InsertionType $insertionType, User $user, array $params) : bool {
        $bookmark = InsertionUserSearchBookmark::create(["insertionType" => $insertionType, "user" => $user, "params" => $params]);
        $this->entityManager()->create($bookmark);
        return true;
    }

    public function remove(int $id) : bool {
        $bookmark = $this->repository("search")->find($id);
        if (isset($bookmark)) {
            $this->entityManager()->remove($bookmark);

            return true;
        }

        return false;
    }

    public function list(User $user = null) : array {
        if (is_null($user)) {
            return $bookmarks = $this->repository("search")->findAll();
        } else {
            return $bookmarks = $this->repository("search")->findBy(["user" => $user]);
        }
    }

    public function setLastChecked($bookmark) {
        if (is_a($bookmark, InsertionUserSearchBookmark::class)) {
            $bookmark->setChecked();
            $this->entityManager()->update($bookmark);
        } elseif (is_int($bookmark)) {
            $bookmark = $this->repository('search')->find($bookmark);
            $bookmark->setChecked();
            $this->entityManager()->update($bookmark);
        }
        $this->entityManager()->flush();
    }

    public function toggle(InsertionType $insertionType, User $user, array $params) {
        $bookmarks = $this->repository("search")->findBy(["insertionType" => $insertionType, "user" => $user]);

        $bookmark = null;
        /** @var InsertionUserSearchBookmark $found */
        foreach ($bookmarks as $found) {
            if (!empty(array_diff_key($found->getParams(), $params)) || !empty(array_diff_key($params, $found->getParams()))) {
                continue;
            }

            if (empty($this->array_diff_assoc_recursive($found->getParams(), $params))
                && empty($this->array_diff_assoc_recursive($params, $found->getParams()))) {
                $bookmark = $found;
            }
        }

        if ($bookmark != null) {
            return $this->remove($bookmark->getId());
        }

        return $this->add($insertionType, $user, $params);
    }

    public function hasBookmark(int $insertionType, int $user, array $params) : bool {
        $bookmarks = $this->repository("search")->findBy(["insertionType" => $insertionType, "user" => $user]);

        /** @var InsertionUserSearchBookmark $found */
        foreach ($bookmarks as $found) {
            if (!empty(array_diff_key($found->getParams(), $params)) || !empty(array_diff_key($params, $found->getParams()))) {
                continue;
            }

            if (empty($this->array_diff_assoc_recursive($found->getParams(), $params))
                && empty($this->array_diff_assoc_recursive($params, $found->getParams()))) {
                return true;
            }
        }

        return false;
    }

    private function array_diff_assoc_recursive($array1, $array2) {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $newDiff = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if (!empty($newDiff)) {
                        $difference[$key] = $newDiff;
                    }
                }
            } elseif (!isset($array2[$key]) || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }

    public function getUrl($id, ?array $params) {
        /** @var Router $router */
        $router = Oforge()->App()->getContainer()->get('router');
        $url    = $router->pathFor('insertions_listing', ["type" => $id], isset($params) ? $params : [] );

        return $url;
    }

}

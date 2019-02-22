<?php

namespace Oforge\Engine\Modules\Notifications;

use Oforge\Engine\Modules\Core\Abstracts\AbstractBootstrap;
use Oforge\Engine\Modules\Notifications\Models\BackendNotification;
use Oforge\Engine\Modules\Notifications\Controller\BackendNotificationController;
use Oforge\Engine\Modules\Notifications\Services\BackendNotificationService;

class Bootstrap extends AbstractBootstrap {
    /**
     * Bootstrap constructor.
     */
    public function __construct() {
        $this->endpoints = [
            '/backend/notifications/{id}' => [
                'controller'   => BackendNotificationController::class,
                'name'         => 'backend_notifications',
                'assets_scope' => 'Backend',
            ],
        ];

        $this->services = [
            'backend.notifications' => BackendNotificationService::class,
        ];

        $this->models = [
            BackendNotification::class,
        ];
    }
}
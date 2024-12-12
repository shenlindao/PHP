<?php

namespace Geekbrains\Application1\Application;

use Geekbrains\Application1\Domain\Controllers\UserController;

class Bootstrap {
    public static function initializeContainer() {
        $container = new Container();

        // Регистрируем сервисы
        $container->set(Application::class, new Application($container));
        $container->set(Render::class, new Render($container));
        $container->set(UserController::class, new UserController($container));

        return $container;
    }
}
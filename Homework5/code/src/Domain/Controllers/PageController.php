<?php

namespace Geekbrains\Application1\Domain\Controllers;

use Geekbrains\Application1\Application\Render;
use Geekbrains\Application1\Application\Application;
use Geekbrains\Application1\Application\Container;

class PageController {
    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function actionIndex() {

        $render = $this->container->get(Render::class);
        
        return $render->renderPage('page-index.twig', [
            'title' => 'Главная страница',
            'time' => date("Y-m-d H:i:s")
        ]);
    }
}
<?php

namespace Geekbrains\Application1\Domain\Controllers;

use Geekbrains\Application1\Domain\Models\Phone;
use Geekbrains\Application1\Application\Render;
use Geekbrains\Application1\Application\Application;
use Geekbrains\Application1\Application\Container;

class AboutController
{

    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function actionIndex() {
        
        $render = $this->container->get(Render::class);

        $phone = (new Phone())->getPhone();

        return $render->renderPage('about.twig', [
            'phone' => $phone
        ]);
    }
}
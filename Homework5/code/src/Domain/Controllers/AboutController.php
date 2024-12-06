<?php

namespace Geekbrains\Application1\Domain\Controllers;
use Geekbrains\Application1\Domain\Models\Phone;
use Geekbrains\Application1\Application\Render;

class AboutController
{
    public function actionIndex() {
        $phone = (new Phone())->getPhone();
        $render = new Render();

        return $render->renderPage('about.twig', [
            'phone' => $phone
        ]);
    }
}
<?php

namespace Geekbrains\Application1\Application;

use Exception;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Render
{

    private string $viewFolder = 'src/Domain/Views/';
    private FilesystemLoader $loader;
    private Environment $environment;
    private $container;

    public function __construct(Container $container)
    {
        $this->loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . "/../" . $this->viewFolder);
        $this->environment = new Environment($this->loader, [
            // 'cache' => $_SERVER['DOCUMENT_ROOT'] . '/../cache/',
        ]);
        $this->container = $container;
    }

    public function renderPage(string $contentTemplateName = 'page-index.twig', array $templateVariables = [])
    {

        $application = $this->container->get(Application::class);

        // Получаем данные боковой панели через приложение
        $sidebarData = $application->getSidebarData();
        $template = $this->environment->load('/layouts/main.twig');

        $templateVariables['content_template_name'] = $contentTemplateName;
        $templateVariables['title'] = $templateVariables['title'] ?? 'Имя страницы';
        $templateVariables['sidebarData'] = $sidebarData;

        if (isset($_SESSION['auth']['user_name'])) {
            $templateVariables['user_authorized'] = true;
            $templateVariables['user_name'] = $_SESSION['auth']['user_name'];
        } else {
            $templateVariables['user_authorized'] = false;
        }

        return $template->render($templateVariables);
    }

    public static function renderExceptionPage(Exception $exception): string
    {
        $contentTemplateName = "error.twig";
        $viewFolder = 'src/Domain/Views';

        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . "/../" . $viewFolder);

        $environment = new Environment($loader, [
            // 'cache' => $_SERVER['DOCUMENT_ROOT'] . '/../cache/',
        ]);

        $template = $environment->load('layouts/main.twig');

        $templateVariables = [];
        $templateVariables['content_template_name'] = $contentTemplateName;
        $templateVariables['message'] = $exception->getMessage();

        return $template->render($templateVariables);
    }

    public function renderPageWithForm(string $contentTemplateName = 'page-index.twig', array $templateVariables = [])
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $templateVariables['csrf_token'] = $_SESSION['csrf_token'];

        return $this->renderPage($contentTemplateName, $templateVariables);
    }
}

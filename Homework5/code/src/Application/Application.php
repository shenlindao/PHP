<?php

namespace Geekbrains\Application1\Application;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Geekbrains\Application1\Domain\Controllers\AbstractController;
use Geekbrains\Application1\Infrastructure\Config;
use Geekbrains\Application1\Infrastructure\Storage;
use Geekbrains\Application1\Application\Auth;
use Geekbrains\Application1\Domain\Controllers\UserController;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class Application
{
    private $container;

    private const APP_NAMESPACE = 'Geekbrains\Application1\Domain\Controllers\\';

    private string $controllerName;
    private string $methodName;

    public static Config $config;

    public static Storage $storage;

    public static Auth $auth;

    public static Logger $logger;

    public array $sidebarData;

    public function __construct(Container $container)
    {
        Application::$config = new Config();
        Application::$storage = new Storage();
        Application::$auth = new Auth();
        Application::$logger = new Logger('application_logger');
        Application::$logger->pushHandler(
            new StreamHandler($_SERVER['DOCUMENT_ROOT'] . "/log/" . Application::$config->get()['log']['LOGS_FILE'] . "-" . date("Y-m-d") . ".log", Level::Debug)
        );
        Application::$logger->pushHandler(
            new FirePHPHandler()
        );
        $this->container = $container;
    }

    public function run(): string
    {
        session_start();

        $routeArray = explode('/', ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $controllerName = isset($routeArray[0]) && $routeArray[0] !== '' ? $routeArray[0] : 'page';

        // Определяем контроллер и метод по умолчанию
        $this->controllerName = Application::APP_NAMESPACE . ucfirst($controllerName) . 'Controller';
        $this->methodName = isset($routeArray[2]) && $routeArray[2] !== '' ? 'action' . ucfirst($routeArray[2]) : 'actionIndex';

        if ($controllerName === 'user') {
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $actionMap = [
                'POST' => [
                    'save' => 'actionSave',
                    'update' => 'actionUpdate',
                    'delete' => 'actionDelete',
                    'login' => 'actionLogin',
                    'refresh' => 'actionIndexRefresh',
                ],
                'GET' => [
                    'hash' => 'actionHash',
                    'auth' => 'actionAuth',
                    'logout' => 'actionLogout',
                ],
            ];
            $method = $_SERVER['REQUEST_METHOD'];
            $route = $routeArray[1] ?? null;
            $this->methodName = $actionMap[$method][$route] ?? $this->methodName;
        } elseif (in_array($controllerName, ['adduser', 'updateuser', 'deleteuser'])) {
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $methodMap = [
                'adduser' => 'addUserForm',
                'updateuser' => 'updateUserForm',
                'deleteuser' => 'deleteUserForm',
            ];
            $this->methodName = $methodMap[$controllerName];
        }

        // Проверяем существование контроллера
        if (class_exists($this->controllerName)) {
            // Проверяем существование метода
            if (method_exists($this->controllerName, $this->methodName)) {
                $controllerInstance = new $this->controllerName($this->container);
                if ($controllerInstance instanceof AbstractController) {
                    if ($this->checkAccessToMethod($controllerInstance, $this->methodName)) {
                        return call_user_func_array(
                            [$controllerInstance, $this->methodName],
                            []
                        );
                    } else {
                        // Логгирование отсутствия доступа
                        $logUser = isset($_SESSION['auth']['user_name']) ? "пользователя {$_SESSION['auth']['user_name']}" : 'неавторизованного пользователя';
                        $logMessage = "Попытка {$logUser} получить доступ к методу {$this->methodName}";
                        Application::$logger->error($logMessage);
                        throw new \Exception("Нет доступа к методу");
                    }
                } else {
                    return call_user_func_array(
                        [$controllerInstance, $this->methodName],
                        []
                    );
                }
            } else {
                // Логгирование отсутствия метода
                $logMessage = "Метод " . $this->methodName . " не существует в контроллере " . $this->controllerName . " | ";
                $logMessage .= "Попытка вызова адреса " . $_SERVER['REQUEST_URI'];
                Application::$logger->error($logMessage);
                throw new \Exception("Метод не существует");
            }
        } else {
            return $this->renderError(404);
        }
    }

    // Рендер шаблона ошибки
    private function renderError(int $statusCode): string
    {
        http_response_code($statusCode);
        
        $viewFolder = 'src/Domain/Views';

        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . "/../" . $viewFolder);
        $twig = new Environment($loader);

        $contentTemplateName = "{$statusCode}.twig";

        $templateVariables = [
            'content_template_name' => $contentTemplateName,
            'message' => "Произошла ошибка с кодом: $statusCode",
        ];

        try {
            $template = $twig->load('layouts/main.twig');
            return $template->render($templateVariables);
        } catch (\Twig\Error\LoaderError $e) {
            return "Ошибка при загрузке шаблона: " . $e->getMessage();
        }
    }

    private function checkAccessToMethod(AbstractController $controllerInstance, string $methodName): bool
    {
        $isAllowed = false;

        $userRoles = $controllerInstance->getUserRoles();

        $rules = $controllerInstance->getActionsPermissions($methodName);

        if (!empty($rules)) {
            foreach ($rules as $rolePermission) {
                if (in_array($rolePermission, $userRoles)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        return $isAllowed;
    }

    public function getSidebarData(): array
    {
        $menuItems = [
            'Все пользователи' => ['url' => '/user', 'method' => 'actionIndex'],
            'Добавить пользователя' => ['url' => '/adduser', 'method' => 'addUserForm'],
            'Изменить пользователя' => ['url' => '/updateuser', 'method' => 'updateUserForm'],
            'Удалить пользователя' => ['url' => '/deleteuser', 'method' => 'deleteUserForm'],
        ];

        $sidebarData = [];

        $UserController = new UserController($this->container);

        foreach ($menuItems as $label => $item) {
            if ($this->checkAccessToMethod($UserController, $item['method'])) {
                $sidebarData[] = array_merge($item, ['label' => $label]);
            }
        }

        return $sidebarData;
    }
}

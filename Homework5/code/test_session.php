<?php
// Старт сессии
session_start();

// Устанавливаем сессионную переменную
if (!isset($_SESSION['visited'])) {
    $_SESSION['visited'] = 1;
    echo "Привет, это первый визит!";
} else {
    $_SESSION['visited']++;
    echo "Вы посетили эту страницу {$_SESSION['visited']} раз.";
}
?>

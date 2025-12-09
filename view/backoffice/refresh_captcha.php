<?php
session_start();

// Générer un nouveau CAPTCHA
$_SESSION['captcha'] = rand(1000, 9999);

// Retourner le captcha en JSON
echo json_encode([
    'captcha' => $_SESSION['captcha']
]);

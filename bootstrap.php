<?php
session_start();

require_once __DIR__ . '/connect.php';

function geckode_current_user() {
  return $_SESSION['geckode_user'] ?? null;
}

function geckode_is_logged_in() {
  return geckode_current_user() !== null;
}

function geckode_flash_set($type, $message) {
  $_SESSION['geckode_flash'] = [
    'type' => $type,
    'message' => $message,
  ];
}

function geckode_flash_get() {
  if (!isset($_SESSION['geckode_flash'])) {
    return null;
  }

  $flash = $_SESSION['geckode_flash'];
  unset($_SESSION['geckode_flash']);
  return $flash;
}

function geckode_redirect($path) {
  header('Location: ' . $path);
  exit;
}

<?php
// src/Auth.php

class Auth
{
    private $pdo;
    private $logger;

    public function __construct($pdo, $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password)
    {
        $stmt = $this->pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'];
            if ($this->logger) {
                $this->logger->logUserAction($user['id'], $user['username'], 'LOGIN', 'User logged in successfully');
            }
            return true;
        }
        if ($this->logger) {
            $this->logger->logUserAction(0, $username, 'LOGIN_FAILED', 'Invalid credentials');
        }
        return false;
    }

    public function logout()
    {
        if ($this->logger && isset($_SESSION['user_id'])) {
            $this->logger->logUserAction($_SESSION['user_id'], $_SESSION['username'] ?? 'Unknown', 'LOGOUT', 'User logged out');
        }
        session_destroy();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function hasRole($role)
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    public function getUser()
    {
        return $_SESSION;
    }
    public function getRoles()
    {
        $stmt = $this->pdo->query("SELECT * FROM roles");
        return $stmt->fetchAll();
    }

    public function createUser($username, $password, $roleId)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password_hash, role_id) VALUES (?, ?, ?)");
        $res = $stmt->execute([$username, $hash, $roleId]);
        if ($res && $this->logger && isset($_SESSION['user_id'])) {
            $this->logger->logUserAction($_SESSION['user_id'], $_SESSION['username'], 'CREATE_USER', "Created user $username");
        }
        return $res;
    }

    public function getAllUsers()
    {
        $stmt = $this->pdo->query("SELECT u.id, u.username, u.created_at, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id");
        return $stmt->fetchAll();
    }
}
?>
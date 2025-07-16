<?php

namespace App\Models;

use PDO;
use Ramsey\Uuid\Uuid;

class User
{
    private $conn;
    private $allowedRoles = ['admin', 'super_admin', 'teacher'];

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function validateRole($role)
    {
        return in_array($role, $this->allowedRoles);
    }

    public function findByUsername($username)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRefreshToken($userId, $token)
    {
        $stmt = $this->conn->prepare("UPDATE users SET refresh_token = :token WHERE id = :id");
        return $stmt->execute(['token' => $token, 'id' => $userId]);
    }

    public function getByRefreshToken($token)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE refresh_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createWithHashedPassword($data)
    {
        if (!$this->validateRole($data['role'])) {
            throw new \Exception("Role tidak valid.");
        }

        $uuid = Uuid::uuid4()->toString();

        $stmt = $this->conn->prepare("
            INSERT INTO users (id, username, password, email, role)
            VALUES (:id, :username, :password, :email, :role)
        ");

        return $stmt->execute([
            'id'       => $uuid,
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'email'    => $data['email'],
            'role'     => $data['role'],
        ]);
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT id, username, email, role FROM users WHERE role IN ('admin', 'super_admin', 'teacher')");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        if (!$this->validateRole($data['role'])) {
            throw new \Exception("Role tidak valid.");
        }

        $stmt = $this->conn->prepare("
            UPDATE users SET username = :username, email = :email, role = :role
            WHERE id = :id
        ");

        return $stmt->execute([
            'username' => $data['username'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'id'       => $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

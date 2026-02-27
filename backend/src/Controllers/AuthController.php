<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

class AuthController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (!isset($data['email'], $data['password'], $data['name'])) {
            $response->getBody()->write(json_encode(['error' => 'Missing fields']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password, name, role, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['email'],
                $hashedPassword,
                $data['name'],
                $data['role'] ?? 'viewer'
            ]);

            $userId = $this->db->lastInsertId();

            $response->getBody()->write(json_encode([
                'message' => 'User registered successfully',
                'userId' => $userId
            ]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (!isset($data['email'], $data['password'])) {
            $response->getBody()->write(json_encode(['error' => 'Missing fields']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $stmt = $this->db->prepare("SELECT id, password, name, role FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user || !password_verify($data['password'], $user['password'])) {
                $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $token = JWT::encode(
                [
                    'userId' => $user['id'],
                    'email' => $data['email'],
                    'role' => $user['role'],
                    'iat' => time(),
                    'exp' => time() + (24 * 60 * 60) // 24 hours
                ],
                getenv('JWT_SECRET'),
                'HS256'
            );

            $response->getBody()->write(json_encode([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $data['email'],
                    'role' => $user['role']
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}

<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;

class AuthController
{
    private $db;
    private $config;

    public function __construct($db)
    {
        $this->db = $db;
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(getenv('JWT_SECRET') ?: 'dev-secret-key')
        );
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

            $now = new DateTimeImmutable();
            $token = $this->config->builder()
                ->issuedAt($now)
                ->expiresAt($now->modify('+24 hours'))
                ->withClaim('userId', $user['id'])
                ->withClaim('email', $data['email'])
                ->withClaim('role', $user['role'])
                ->getToken($this->config->signer());

            $response->getBody()->write(json_encode([
                'token' => $token->toString(),
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

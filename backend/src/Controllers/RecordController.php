<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RecordController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create(Request $request, Response $response, $args): Response
    {
        $user = $request->getAttribute('user');
        $deviceId = $args['id'];
        $data = $request->getParsedBody();

        if (!isset($data['recordDate'], $data['recordType'], $data['description'])) {
            $response->getBody()->write(json_encode(['error' => 'Missing fields']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            // Check device exists and user has permission
            $stmt = $this->db->prepare("SELECT user_id FROM devices WHERE id = ?");
            $stmt->execute([$deviceId]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$device || ($device['user_id'] != $user['userId'] && $user['role'] !== 'owner')) {
                $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            $stmt = $this->db->prepare("
                INSERT INTO technical_records (device_id, user_id, record_date, record_type, description, technician, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $deviceId,
                $user['userId'],
                $data['recordDate'],
                $data['recordType'],
                $data['description'],
                $data['technician'] ?? null,
                $data['notes'] ?? null
            ]);

            $recordId = $this->db->lastInsertId();

            $response->getBody()->write(json_encode([
                'message' => 'Record created',
                'recordId' => $recordId
            ]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function list(Request $request, Response $response, $args): Response
    {
        $deviceId = $args['id'];

        try {
            $stmt = $this->db->prepare("
                SELECT id, record_date, record_type, description, technician, notes, created_at
                FROM technical_records
                WHERE device_id = ?
                ORDER BY record_date DESC
            ");
            $stmt->execute([$deviceId]);
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($records));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}

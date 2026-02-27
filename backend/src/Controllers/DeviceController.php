<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeviceController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        $data = $request->getParsedBody();

        if (!isset($data['name'], $data['type'], $data['location'], $data['manufacturer'], $data['serialNumber'])) {
            $response->getBody()->write(json_encode(['error' => 'Missing fields']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO devices (name, type, location, manufacturer, serial_number, install_date, user_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $data['name'],
                $data['type'],
                $data['location'],
                $data['manufacturer'],
                $data['serialNumber'],
                $data['installDate'] ?? date('Y-m-d'),
                $user['userId']
            ]);

            $deviceId = $this->db->lastInsertId();

            $response->getBody()->write(json_encode([
                'message' => 'Device created',
                'deviceId' => $deviceId
            ]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function show(Request $request, Response $response, $args): Response
    {
        $id = $args['id'];

        try {
            $stmt = $this->db->prepare("
                SELECT id, name, type, location, manufacturer, serial_number, install_date, created_at
                FROM devices
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$device) {
                $response->getBody()->write(json_encode(['error' => 'Device not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write(json_encode($device));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function showFull(Request $request, Response $response, $args): Response
    {
        $id = $args['id'];

        try {
            $stmt = $this->db->prepare("
                SELECT id, name, type, location, manufacturer, serial_number, install_date, created_at
                FROM devices
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$device) {
                $response->getBody()->write(json_encode(['error' => 'Device not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Get technical records
            $recordStmt = $this->db->prepare("
                SELECT id, record_date, record_type, description, technician, notes, created_at
                FROM technical_records
                WHERE device_id = ?
                ORDER BY record_date DESC
            ");
            $recordStmt->execute([$id]);
            $device['records'] = $recordStmt->fetchAll(\PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($device));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function update(Request $request, Response $response, $args): Response
    {
        $user = $request->getAttribute('user');
        $id = $args['id'];
        $data = $request->getParsedBody();

        try {
            // Check ownership
            $stmt = $this->db->prepare("SELECT user_id FROM devices WHERE id = ?");
            $stmt->execute([$id]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$device || ($device['user_id'] != $user['userId'] && $user['role'] !== 'owner')) {
                $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            $updateStmt = $this->db->prepare("
                UPDATE devices 
                SET name = COALESCE(?, name),
                    type = COALESCE(?, type),
                    location = COALESCE(?, location),
                    manufacturer = COALESCE(?, manufacturer),
                    serial_number = COALESCE(?, serial_number),
                    updated_at = NOW()
                WHERE id = ?
            ");

            $updateStmt->execute([
                $data['name'] ?? null,
                $data['type'] ?? null,
                $data['location'] ?? null,
                $data['manufacturer'] ?? null,
                $data['serialNumber'] ?? null,
                $id
            ]);

            $response->getBody()->write(json_encode(['message' => 'Device updated']));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function delete(Request $request, Response $response, $args): Response
    {
        $user = $request->getAttribute('user');
        $id = $args['id'];

        try {
            // Check ownership
            $stmt = $this->db->prepare("SELECT user_id FROM devices WHERE id = ?");
            $stmt->execute([$id]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$device || ($device['user_id'] != $user['userId'] && $user['role'] !== 'owner')) {
                $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            $deleteStmt = $this->db->prepare("DELETE FROM devices WHERE id = ?");
            $deleteStmt->execute([$id]);

            $response->getBody()->write(json_encode(['message' => 'Device deleted']));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        try {
            $stmt = $this->db->prepare("
                SELECT id, name, type, location, manufacturer, serial_number, install_date, created_at
                FROM devices
                WHERE user_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user['userId']]);
            $devices = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($devices));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}

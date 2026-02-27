<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function generate(Request $request, Response $response, $args): Response
    {
        $user = $request->getAttribute('user');
        $deviceId = $args['id'];

        try {
            // Check device exists and belongs to user
            $stmt = $this->db->prepare("SELECT user_id FROM devices WHERE id = ?");
            $stmt->execute([$deviceId]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$device || ($device['user_id'] != $user['userId'] && $user['role'] !== 'owner')) {
                $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            $domain = getenv('QR_DOMAIN') ?: 'https://qrscanner.local';
            $qrUrl = $domain . '/device/' . $deviceId;

            // Generate QR Code
            $qrCode = new QrCode($qrUrl);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Return as base64
            $base64 = base64_encode($result->getString());

            // Store in database
            $updateStmt = $this->db->prepare("UPDATE devices SET qr_code = ? WHERE id = ?");
            $updateStmt->execute([$base64, $deviceId]);

            $response->getBody()->write(json_encode([
                'deviceId' => $deviceId,
                'qr' => $base64,
                'url' => $qrUrl
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}

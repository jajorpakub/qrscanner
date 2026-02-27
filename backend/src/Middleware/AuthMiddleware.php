<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\Clock\SystemClock;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || strpos($authHeader, 'Bearer ') === false) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Missing or invalid token']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $config = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText(getenv('JWT_SECRET') ?: 'dev-secret-key')
            );

            $token = $config->parser()->parse($token);

            $config->setValidationConstraints(
                new SignedWith($config->signer(), $config->signingKey()),
                new ValidAt(new SystemClock())
            );

            if (!$config->validator()->validate($token, ...$config->validationConstraints())) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(['error' => 'Invalid token']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $claims = $token->claims();
            $user = [
                'userId' => $claims->get('userId'),
                'email' => $claims->get('email'),
                'role' => $claims->get('role'),
            ];

            $request = $request->withAttribute('user', $user);
            
            return $handler->handle($request);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Invalid token: ' . $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}

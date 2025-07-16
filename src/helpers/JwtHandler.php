<?php

namespace App\Helpers;

class JwtHandler
{
    private $secret;
    private $accessExp;
    private $refreshExp;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET_KEY'];
        $this->accessExp = time() + ($_ENV['JWT_ACCESS_TOKEN_EXP'] ?? 3600);        
        $this->refreshExp = time() + ($_ENV['JWT_REFRESH_TOKEN_EXP'] ?? 604800);    
    }

    public function generateAccessToken($payload)
    {
        return $this->generateToken($payload, $this->accessExp);
    }

    public function generateRefreshToken()
    {
        return bin2hex(random_bytes(32));
    }

    private function generateToken($payload, $exp)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = $exp;
        $payload['iat'] = time();
        $payload = json_encode($payload);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $this->secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$base64Header.$base64Payload.$base64Signature";
    }
    public function validateToken($token)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    list($header, $payload, $signature) = $parts;
    $validSig = base64_encode(hash_hmac('sha256', "$header.$payload", $this->secret, true));
    $validSig = str_replace(['+', '/', '='], ['-', '_', ''], $validSig);

    if ($signature !== $validSig) return false;

    $decoded = json_decode(base64_decode($payload), true);
    if (!$decoded || $decoded['exp'] < time()) return false;

    return $decoded;
}

}

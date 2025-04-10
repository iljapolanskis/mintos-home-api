<?php
declare(strict_types=1);

namespace App\Auth\Service;

use InvalidArgumentException;
use Throwable;

class TokenEncoder
{
    public function encodeId(int $id, string $token): string
    {
        try {
            return base64_encode(json_encode([
                'id' => $id,
                'token' => $token,
            ], JSON_THROW_ON_ERROR));
        } catch (Throwable) {
            throw new InvalidArgumentException('Failed to encode token');
        }
    }

    /**
     * @param string $token
     * @return int
     * @throws InvalidArgumentException
     */
    public function decodeId(string $token): int
    {
        $data = json_decode(base64_decode($token), true);

        if (isset($data['id']) && is_numeric($data['id'])) {
            return (int)$data['id'];
        }

        throw new InvalidArgumentException('Invalid token');
    }
}

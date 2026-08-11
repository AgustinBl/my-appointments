<?php

namespace App\Auth;

use InvalidArgumentException;

/**
 * Serializacion y firma de JWT con HMAC-SHA512.
 *
 * Solo arma y verifica el sobre (base64url + firma); no sabe nada de
 * usuarios ni de expiracion, de eso se ocupa JwtManager.
 */
class JwtCodec
{
    /**
     * Unico algoritmo aceptado. El "alg" que viene en el header del token
     * NUNCA se lee: fijarlo aca es lo que corta los dos ataques clasicos
     * contra JWT, el de "alg": "none" y el de confusion de algoritmos
     * (mandar HS512 firmado con una clave publica RSA).
     */
    private const ALGORITHM = 'HS512';

    private const HASH = 'sha512';

    /** @var string */
    private $key;

    public function __construct(string $key)
    {
        if (trim($key) === '') {
            throw new InvalidArgumentException(
                'No hay clave para firmar los JWT: revisa JWT_KEY o APP_KEY en el .env'
            );
        }

        $this->key = $key;
    }

    /**
     * @param  array  $claims
     * @return string
     */
    public function encode(array $claims): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => self::ALGORITHM,
        ]));

        $payload = $this->base64UrlEncode(json_encode($claims));

        return $header . '.' . $payload . '.' . $this->sign($header . '.' . $payload);
    }

    /**
     * Verifica la firma y devuelve los claims, o null si el token esta mal
     * formado o la firma no corresponde.
     *
     * @param  string  $token
     * @return array|null
     */
    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // hash_equals compara en tiempo constante. Un === comun tarda mas
        // cuanto mas bytes acerta el atacante, y eso alcanza para ir
        // adivinando la firma byte a byte.
        if (! hash_equals($this->sign($header . '.' . $payload), $signature)) {
            return null;
        }

        $claims = json_decode($this->base64UrlDecode($payload), true);

        return is_array($claims) ? $claims : null;
    }

    private function sign(string $input): string
    {
        return $this->base64UrlEncode(hash_hmac(self::HASH, $input, $this->key, true));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'), true);
    }
}

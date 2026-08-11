<?php

namespace App\Auth;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Emision y validacion de los JWT de la API.
 *
 * Reemplaza al paquete JCarlosR/larajwt, que quedo sin mantenimiento desde
 * 2019 y ademas estaba clavado a dev-master.
 */
class JwtManager
{
    /** @var JwtCodec */
    private $codec;

    /** @var Cache */
    private $cache;

    /** @var int segundos de vida del token */
    private $ttl;

    /** @var string */
    private $issuer;

    /** @var string */
    private $audience;

    public function __construct(JwtCodec $codec, Cache $cache, array $config)
    {
        $this->codec = $codec;
        $this->cache = $cache;
        $this->ttl = (int) ($config['ttl'] ?? 60 * 60 * 24 * 30);
        $this->issuer = (string) ($config['issuer'] ?? '');
        $this->audience = (string) ($config['audience'] ?? '');
    }

    /**
     * Emite un token para el usuario.
     *
     * @param  Authenticatable  $user
     * @param  array  $extraClaims
     * @return string
     */
    public function issue(Authenticatable $user, array $extraClaims = []): string
    {
        $now = time();

        return $this->codec->encode($extraClaims + [
            'sub' => $user->getAuthIdentifier(),
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'exp' => $now + $this->ttl,
            'iat' => $now,
            'nbf' => $now,
            'jti' => $this->newTokenId(),
        ]);
    }

    /**
     * Devuelve los claims si el token es valido en todo sentido: firma,
     * vigencia, emisor, destinatario y que no haya sido invalidado por un
     * logout. Si algo no cierra devuelve null, sin distinguir el motivo
     * (no le sirve a un cliente legitimo y si a uno que esta probando).
     *
     * @param  string  $token
     * @return array|null
     */
    public function claims(string $token): ?array
    {
        $claims = $this->codec->decode($token);

        if ($claims === null) {
            return null;
        }

        // Sin sub no hay a quien autenticar, y sin jti no se puede revocar.
        if (! isset($claims['sub'], $claims['jti'], $claims['exp'])) {
            return null;
        }

        $now = time();

        if ($now >= (int) $claims['exp']) {
            return null;
        }

        if (isset($claims['nbf']) && $now < (int) $claims['nbf']) {
            return null;
        }

        if (($claims['iss'] ?? '') !== $this->issuer) {
            return null;
        }

        if (($claims['aud'] ?? '') !== $this->audience) {
            return null;
        }

        if ($this->isInvalidated((string) $claims['jti'])) {
            return null;
        }

        return $claims;
    }

    /**
     * Revoca un token puntual (logout). Un JWT es autocontenido, asi que la
     * unica forma de darlo de baja antes del "exp" es anotarlo. La marca se
     * guarda por lo que le quedaba de vida al token: despues de eso caduca
     * solo y la entrada ya no hace falta.
     *
     * @param  string  $jti
     * @param  int|null  $expiresAt  timestamp del claim "exp" del token
     */
    public function invalidate(string $jti, int $expiresAt = null): void
    {
        $seconds = $expiresAt !== null
            ? max(1, $expiresAt - time())
            : $this->ttl;

        $this->cache->put($this->invalidationKey($jti), true, $seconds);
    }

    private function isInvalidated(string $jti): bool
    {
        return (bool) $this->cache->get($this->invalidationKey($jti), false);
    }

    private function invalidationKey(string $jti): string
    {
        return 'jwt:invalidated:' . $jti;
    }

    /**
     * larajwt usaba uniqid(), que es el timestamp en hexa: dos tokens
     * emitidos en el mismo microsegundo colisionan y, peor, el id de
     * cualquier token es adivinable.
     */
    private function newTokenId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Exception $e) {
            // random_bytes solo falla si no hay fuente de entropia. Emitir
            // un token con id predecible seria peor que no emitirlo.
            throw new \RuntimeException('No hay entropia para generar el id del token.', 0, $e);
        }
    }
}

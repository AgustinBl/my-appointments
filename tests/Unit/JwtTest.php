<?php

namespace Tests\Unit;

use App\Auth\JwtCodec;
use App\Auth\JwtManager;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Cubre la emision y validacion de los JWT de la API.
 *
 * No arranca Laravel a proposito: JwtCodec y JwtManager no dependen del
 * framework, asi que los tests corren sin base de datos ni contenedor.
 */
class JwtTest extends TestCase
{
    private const KEY = 'base64:0123456789abcdef0123456789abcdef0123456789a=';
    private const ISSUER = 'MyAppointments';
    private const AUDIENCE = '';

    private function codec(string $key = self::KEY): JwtCodec
    {
        return new JwtCodec($key);
    }

    private function manager(JwtCodec $codec = null, int $ttl = 3600): JwtManager
    {
        return new JwtManager(
            $codec ?: $this->codec(),
            new Repository(new ArrayStore()),
            ['ttl' => $ttl, 'issuer' => self::ISSUER, 'audience' => self::AUDIENCE]
        );
    }

    private function user($id = 3): Authenticatable
    {
        return new class($id) implements Authenticatable {
            private $id;

            public function __construct($id)
            {
                $this->id = $id;
            }

            public function getAuthIdentifierName()
            {
                return 'id';
            }

            public function getAuthIdentifier()
            {
                return $this->id;
            }

            public function getAuthPassword()
            {
                return '';
            }

            public function getRememberToken()
            {
                return '';
            }

            public function setRememberToken($value)
            {
                //
            }

            public function getRememberTokenName()
            {
                return '';
            }
        };
    }

    // --- JwtCodec ---------------------------------------------------------

    public function testCodificaYDecodificaLosMismosClaims()
    {
        $codec = $this->codec();
        $claims = ['sub' => 3, 'iss' => 'MyAppointments'];

        $this->assertSame($claims, $codec->decode($codec->encode($claims)));
    }

    public function testElHeaderDeclaraHs512()
    {
        $token = $this->codec()->encode(['sub' => 1]);
        $header = explode('.', $token)[0];
        $json = base64_decode(strtr($header, '-_', '+/'));

        $this->assertSame(['typ' => 'JWT', 'alg' => 'HS512'], json_decode($json, true));
    }

    public function testRechazaUnaFirmaManipulada()
    {
        $token = $this->codec()->encode(['sub' => 1]);
        $manipulado = substr($token, 0, -6) . 'AAAAAA';

        $this->assertNull($this->codec()->decode($manipulado));
    }

    public function testRechazaUnPayloadAlteradoConLaFirmaOriginal()
    {
        $codec = $this->codec();
        [$header, , $signature] = explode('.', $codec->encode(['sub' => 1]));

        $otroPayload = rtrim(strtr(base64_encode(json_encode(['sub' => 999])), '+/', '-_'), '=');

        $this->assertNull($codec->decode("$header.$otroPayload.$signature"));
    }

    public function testRechazaElAtaqueDeAlgNone()
    {
        $codec = $this->codec();
        $payload = explode('.', $codec->encode(['sub' => 1]))[1];
        $header = rtrim(strtr(base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'none'])), '+/', '-_'), '=');

        $this->assertNull($codec->decode("$header.$payload."));
    }

    public function testRechazaUnTokenFirmadoConOtraClave()
    {
        $token = $this->codec('otra-clave-totalmente-distinta')->encode(['sub' => 1]);

        $this->assertNull($this->codec()->decode($token));
    }

    public function testRechazaTokensMalFormados()
    {
        $codec = $this->codec();

        $this->assertNull($codec->decode('no-es-un-token'));
        $this->assertNull($codec->decode('a.b'));
        $this->assertNull($codec->decode(''));
    }

    public function testNoAceptaUnaClaveVacia()
    {
        $this->expectException(InvalidArgumentException::class);

        new JwtCodec('   ');
    }

    // --- JwtManager -------------------------------------------------------

    public function testEmiteUnTokenConLosClaimsEsperados()
    {
        $claims = $this->manager()->claims($this->manager()->issue($this->user(3)));

        $this->assertNotNull($claims);
        $this->assertSame(3, $claims['sub']);
        $this->assertSame(self::ISSUER, $claims['iss']);
        $this->assertSame(self::AUDIENCE, $claims['aud']);
        $this->assertArrayHasKey('jti', $claims);
        $this->assertGreaterThan(time(), $claims['exp']);
    }

    public function testUnTokenRecienEmitidoEsValido()
    {
        $manager = $this->manager();

        $this->assertNotNull($manager->claims($manager->issue($this->user())));
    }

    public function testRechazaUnTokenVencido()
    {
        $codec = $this->codec();
        $vencido = $codec->encode([
            'sub' => 3,
            'iss' => self::ISSUER,
            'aud' => self::AUDIENCE,
            'exp' => time() - 1,
            'jti' => 'abc',
        ]);

        $this->assertNull($this->manager($codec)->claims($vencido));
    }

    public function testRechazaUnTokenQueTodaviaNoEmpezoAValer()
    {
        $codec = $this->codec();
        $futuro = $codec->encode([
            'sub' => 3,
            'iss' => self::ISSUER,
            'aud' => self::AUDIENCE,
            'exp' => time() + 3600,
            'nbf' => time() + 600,
            'jti' => 'abc',
        ]);

        $this->assertNull($this->manager($codec)->claims($futuro));
    }

    public function testRechazaUnTokenDeOtroEmisor()
    {
        $codec = $this->codec();
        $ajeno = $codec->encode([
            'sub' => 3,
            'iss' => 'OtraApp',
            'aud' => self::AUDIENCE,
            'exp' => time() + 3600,
            'jti' => 'abc',
        ]);

        $this->assertNull($this->manager($codec)->claims($ajeno));
    }

    public function testRechazaUnTokenSinJtiPorqueNoSePodriaRevocar()
    {
        $codec = $this->codec();
        $sinJti = $codec->encode([
            'sub' => 3,
            'iss' => self::ISSUER,
            'aud' => self::AUDIENCE,
            'exp' => time() + 3600,
        ]);

        $this->assertNull($this->manager($codec)->claims($sinJti));
    }

    public function testInvalidarUnTokenLoDejaInservible()
    {
        $manager = $this->manager();
        $token = $manager->issue($this->user());

        $claims = $manager->claims($token);
        $this->assertNotNull($claims);

        $manager->invalidate($claims['jti'], $claims['exp']);

        $this->assertNull($manager->claims($token));
    }

    public function testInvalidarUnTokenNoAfectaALosDemas()
    {
        $manager = $this->manager();
        $unTokenDelUsuario = $manager->issue($this->user(3));
        $otroTokenDelUsuario = $manager->issue($this->user(3));

        $claims = $manager->claims($unTokenDelUsuario);
        $manager->invalidate($claims['jti'], $claims['exp']);

        $this->assertNull($manager->claims($unTokenDelUsuario));
        $this->assertNotNull($manager->claims($otroTokenDelUsuario));
    }

    public function testCadaTokenLlevaUnJtiDistintoEImpredecible()
    {
        $manager = $this->manager();
        $ids = [];

        for ($i = 0; $i < 50; $i++) {
            $ids[] = $manager->claims($manager->issue($this->user()))['jti'];
        }

        $this->assertCount(50, array_unique($ids));
        // 16 bytes en hexa. uniqid(), que es lo que usaba larajwt, da 13.
        $this->assertSame(32, strlen($ids[0]));
    }

    /**
     * Los tokens que la app movil ya tiene guardados fueron emitidos por el
     * paquete larajwt (HMAC-SHA512, misma clave y mismos claims). Tienen que
     * seguir siendo validos para no forzar a todos a loguearse de nuevo.
     */
    public function testAceptaUnTokenEmitidoPorElPaqueteViejo()
    {
        $header = rtrim(strtr(base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS512'])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode([
            'sub' => 3,
            'iss' => self::ISSUER,
            'aud' => self::AUDIENCE,
            'exp' => time() + 3600,
            'iat' => time(),
            'nbf' => time(),
            'jti' => '6a74dbebc30da', // uniqid(), el formato que usaba larajwt
        ])), '+/', '-_'), '=');

        // Firmado como lo hacia lcobucci/jwt: HMAC-SHA512 sobre "header.payload".
        $firma = rtrim(strtr(base64_encode(
            hash_hmac('sha512', "$header.$payload", self::KEY, true)
        ), '+/', '-_'), '=');

        $claims = $this->manager()->claims("$header.$payload.$firma");

        $this->assertNotNull($claims);
        $this->assertSame(3, $claims['sub']);
    }
}

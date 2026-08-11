<?php

namespace App\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

/**
 * Guard que autentica por JWT en el header Authorization.
 *
 * Se registra como driver "jwt" en JwtServiceProvider y lo usa el guard
 * "api" de config/auth.php.
 */
class JwtGuard implements Guard
{
    use GuardHelpers;

    /** @var JwtManager */
    private $jwt;

    /** @var Request */
    private $request;

    /** @var string|null */
    private $token;

    /** @var array */
    private $claims = [];

    /**
     * Si ya se intento resolver el usuario del token. Hace falta aparte de
     * $user porque un token invalido tambien resuelve, a null, y sin esto
     * cada llamada a user() reintentaria.
     *
     * @var bool
     */
    private $resolved = false;

    public function __construct(JwtManager $jwt, UserProvider $provider, Request $request)
    {
        $this->jwt = $jwt;
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Usuario autenticado, o null.
     *
     * larajwt resolvia esto en el constructor, con lo que consultaba la base
     * en todo request que construyera el guard, tuviera o no token. Aca se
     * resuelve al primer uso.
     *
     * @return Authenticatable|null
     */
    public function user()
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;

        // bearerToken() de Laravel ya parsea el prefijo "Bearer " del header.
        $token = $this->request->bearerToken();

        if (empty($token)) {
            return null;
        }

        $claims = $this->jwt->claims($token);

        if ($claims === null) {
            return null;
        }

        $this->token = $token;
        $this->claims = $claims;

        return $this->user = $this->provider->retrieveById($claims['sub']);
    }

    /**
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return $this->attempt($credentials, false);
    }

    /**
     * @param  array  $credentials
     * @param  bool  $login
     * @return bool
     */
    public function attempt(array $credentials = [], $login = true)
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user && $this->provider->validateCredentials($user, $credentials)) {
            if ($login) {
                $this->setUser($user);
            }

            return true;
        }

        return false;
    }

    /**
     * @param  Authenticatable  $user
     * @return Authenticatable
     */
    public function login(Authenticatable $user)
    {
        $this->setUser($user);

        return $user;
    }

    /**
     * Cierra la sesion y revoca el token con el que se llego, para que no
     * siga sirviendo hasta su "exp".
     */
    public function logout(): void
    {
        if ($this->user() && isset($this->claims['jti'])) {
            $this->jwt->invalidate(
                (string) $this->claims['jti'],
                isset($this->claims['exp']) ? (int) $this->claims['exp'] : null
            );
        }

        $this->user = null;
        $this->token = null;
        $this->claims = [];
        $this->resolved = true;
    }

    /**
     * @param  Authenticatable  $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        $this->resolved = true;

        return $this;
    }

    /**
     * Token con el que se autentico el request actual.
     *
     * @return string|null
     */
    public function token(): ?string
    {
        $this->user();

        return $this->token;
    }

    /**
     * @return array
     */
    public function claims(): array
    {
        $this->user();

        return $this->claims;
    }

    /**
     * @param  string  $name
     * @return mixed|null
     */
    public function claim(string $name)
    {
        return $this->claims()[$name] ?? null;
    }

    /**
     * Laravel reinyecta el Request cuando lo reemplaza en el contenedor.
     *
     * @param  Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->resolved = false;
        $this->user = null;

        return $this;
    }
}

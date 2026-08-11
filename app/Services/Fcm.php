<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Cliente de Firebase Cloud Messaging sobre la API HTTP v1.
 *
 * La API legacy (POST a fcm.googleapis.com/fcm/send con el "server key") se
 * dio de baja en junio de 2024, asi que ya no alcanza con una clave: hay que
 * firmar un JWT con la service account del proyecto, canjearlo por un access
 * token OAuth2 y mandar un mensaje por dispositivo al endpoint v1.
 *
 * Ningun metodo publico lanza excepciones: los errores se devuelven en el
 * array de resultado y se loguean, para que una notificacion fallida no tumbe
 * la cancelacion o confirmacion de una cita.
 */
class Fcm
{
    const OAUTH_TOKEN_URI = 'https://oauth2.googleapis.com/token';
    const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    const SEND_ENDPOINT = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';
    const ACCESS_TOKEN_CACHE_KEY = 'fcm.access_token';

    /** @var array|null Credenciales de la service account, ya decodificadas. */
    protected $credentials;

    /**
     * ¿Hay credenciales validas cargadas? Sirve para avisar en pantalla en vez
     * de intentar el envio y fallar.
     */
    public function isConfigured()
    {
        try {
            $this->credentials();

            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * Envia una notificacion a un dispositivo.
     *
     * @return array{success: bool, invalid_token: bool, error: string|null}
     */
    public function sendToToken($deviceToken, $title, $body, array $data = [])
    {
        try {
            $projectId = $this->projectId();
            $url = sprintf(self::SEND_ENDPOINT, $projectId);
            $payload = json_encode($this->buildMessage($deviceToken, $title, $body, $data));

            $response = $this->send($url, $payload, $this->accessToken());

            // Un 401 suele ser el access token vencido (o un reloj corrido):
            // lo tiramos de la cache y reintentamos una unica vez.
            if ($response['status'] === 401) {
                Cache::forget(self::ACCESS_TOKEN_CACHE_KEY);
                $response = $this->send($url, $payload, $this->accessToken());
            }
        } catch (RuntimeException $e) {
            Log::warning('FCM: no se pudo enviar la notificacion. '.$e->getMessage());

            return ['success' => false, 'invalid_token' => false, 'error' => $e->getMessage()];
        }

        if ($response['status'] === 200) {
            return ['success' => true, 'invalid_token' => false, 'error' => null];
        }

        $error = $this->describeError($response);
        Log::warning('FCM: envio rechazado ('.$response['status'].'). '.$error);

        return [
            'success' => false,
            'invalid_token' => $this->isInvalidToken($response),
            'error' => $error,
        ];
    }

    /**
     * Envia la misma notificacion a varios dispositivos. La API v1 no acepta
     * multicast, asi que va un request por token.
     *
     * @return array{sent: int, failed: int, invalid_tokens: array, error: string|null}
     */
    public function sendToTokens(array $deviceTokens, $title, $body, array $data = [])
    {
        $result = ['sent' => 0, 'failed' => 0, 'invalid_tokens' => [], 'error' => null];

        foreach (array_unique(array_filter($deviceTokens)) as $deviceToken) {
            $sent = $this->sendToToken($deviceToken, $title, $body, $data);

            if ($sent['success']) {
                $result['sent']++;
                continue;
            }

            $result['failed']++;
            $result['error'] = $result['error'] ?: $sent['error'];

            if ($sent['invalid_token']) {
                $result['invalid_tokens'][] = $deviceToken;
            }
        }

        return $result;
    }

    /**
     * Cuerpo del mensaje v1. Ojo: en `data` todos los valores tienen que ser
     * strings, la API rechaza numeros o booleanos.
     */
    protected function buildMessage($deviceToken, $title, $body, array $data)
    {
        $message = [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                ],
            ],
        ];

        if ($data) {
            $message['data'] = array_map('strval', $data);
        }

        return ['message' => $message];
    }

    /**
     * Access token OAuth2, cacheado hasta un minuto antes de que venza.
     *
     * @throws RuntimeException
     */
    protected function accessToken()
    {
        $cached = Cache::get(self::ACCESS_TOKEN_CACHE_KEY);

        if ($cached) {
            return $cached;
        }

        $credentials = $this->credentials();
        $tokenUri = $credentials['token_uri'] ?? self::OAUTH_TOKEN_URI;

        $response = $this->post($tokenUri, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->signedAssertion($credentials, $tokenUri),
        ]), ['Content-Type: application/x-www-form-urlencoded']);

        if ($response['status'] !== 200 || empty($response['body']['access_token'])) {
            throw new RuntimeException('Google rechazo la service account: '.$this->describeError($response));
        }

        $token = $response['body']['access_token'];
        $expiresIn = (int) ($response['body']['expires_in'] ?? 3600);

        Cache::put(self::ACCESS_TOKEN_CACHE_KEY, $token, max($expiresIn - 60, 60));

        return $token;
    }

    /**
     * JWT firmado con la clave privada de la service account (RS256), que es
     * lo que Google canjea por el access token.
     *
     * @throws RuntimeException
     */
    protected function signedAssertion(array $credentials, $tokenUri)
    {
        $now = time();

        $input = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']))
            .'.'
            .$this->base64Url(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => self::SCOPE,
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

        // Si la clave viajo por una variable de entorno, los saltos de linea
        // pueden haber quedado escapados y openssl no la reconoce.
        $privateKey = openssl_pkey_get_private(str_replace('\n', "\n", $credentials['private_key']));

        if ($privateKey === false) {
            throw new RuntimeException('La clave privada de la service account no es valida.');
        }

        if (! openssl_sign($input, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('No se pudo firmar el JWT para autenticar contra Google.');
        }

        return $input.'.'.$this->base64Url($signature);
    }

    /**
     * Credenciales de la service account. Acepta una ruta a un archivo JSON o
     * el JSON completo pegado en la variable de entorno.
     *
     * @throws RuntimeException
     */
    protected function credentials()
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $source = config('fcm.credentials');

        if (empty($source)) {
            throw new RuntimeException('Falta configurar FCM_CREDENTIALS.');
        }

        if (strpos(ltrim($source), '{') === 0) {
            $json = $source;
        } else {
            $path = $this->absolutePath($source);

            if (! is_readable($path)) {
                throw new RuntimeException("No se puede leer el archivo de credenciales de Firebase ($path).");
            }

            $json = file_get_contents($path);
        }

        $credentials = json_decode($json, true);

        if (! is_array($credentials)) {
            throw new RuntimeException('El JSON de la service account de Firebase esta mal formado.');
        }

        foreach (['client_email', 'private_key'] as $key) {
            if (empty($credentials[$key])) {
                throw new RuntimeException("El JSON de la service account no tiene la clave \"$key\".");
            }
        }

        return $this->credentials = $credentials;
    }

    /**
     * @throws RuntimeException
     */
    protected function projectId()
    {
        $projectId = config('fcm.project_id') ?: ($this->credentials()['project_id'] ?? null);

        if (empty($projectId)) {
            throw new RuntimeException('No se pudo determinar el project id de Firebase (FCM_PROJECT_ID).');
        }

        return $projectId;
    }

    protected function absolutePath($path)
    {
        $isAbsolute = strpos($path, '/') === 0 || preg_match('/^[A-Za-z]:[\\\\\/]/', $path);

        return $isAbsolute ? $path : base_path($path);
    }

    protected function send($url, $payload, $accessToken)
    {
        return $this->post($url, $payload, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$accessToken,
        ]);
    }

    /**
     * POST con cURL. Devuelve el status y el body ya decodificado; un fallo de
     * red se reporta como status 0 con el mensaje de cURL.
     */
    protected function post($url, $payload, array $headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [
            'status' => $status,
            'body' => $raw === false ? null : json_decode($raw, true),
            'curl_error' => $curlError ?: null,
        ];
    }

    /**
     * ¿El token ya no sirve? Pasa cuando el usuario desinstalo la app o
     * Firebase le rotó el token: hay que borrarlo de la base.
     */
    protected function isInvalidToken(array $response)
    {
        if (! in_array($response['status'], [400, 404], true)) {
            return false;
        }

        $status = $response['body']['error']['status'] ?? null;

        if ($status === 'NOT_FOUND') {
            return true;
        }

        foreach ($response['body']['error']['details'] ?? [] as $detail) {
            if (($detail['errorCode'] ?? null) === 'UNREGISTERED') {
                return true;
            }
        }

        return false;
    }

    protected function describeError(array $response)
    {
        if (! empty($response['curl_error'])) {
            return $response['curl_error'];
        }

        $error = $response['body']['error']['message']
            ?? $response['body']['error_description']
            ?? $response['body']['error']
            ?? 'respuesta inesperada de Firebase';

        return is_string($error) ? $error : json_encode($error);
    }

    protected function base64Url($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

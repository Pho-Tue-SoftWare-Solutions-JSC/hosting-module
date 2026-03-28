<?php

namespace Hosting\Hitechcloud\lib;

/**
 * Hitechcloud API Client
 *
 * HTTP client using cURL for communicating with Hitechcloud Agent API.
 */
class HitechcloudAPI
{
    /** @var string Base URL of the Hitechcloud API */
    protected $baseUrl;

    /** @var string API key for authentication */
    protected $apiKey;

    /** @var int Connection timeout in seconds */
    protected $connectTimeout = 15;

    /** @var int Request timeout in seconds */
    protected $timeout = 30;

    /** @var array Last response data */
    protected $lastResponse = [];

    /** @var array Collected errors */
    protected $errors = [];

    /**
     * @param string $baseUrl  Base URL of the Hitechcloud API (e.g. https://server.example.com:8443)
     * @param string $apiKey   API key for X-API-Key header authentication
     */
    public function __construct($baseUrl, $apiKey)
    {
        if (empty($baseUrl) || empty($apiKey)) {
            throw new \InvalidArgumentException('Base URL and API Key are required for HitechcloudAPI');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey  = $apiKey;
    }

    /**
     * Get collected errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get last response
     *
     * @return array
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Clear errors
     */
    public function clearErrors()
    {
        $this->errors = [];
    }

    // -------------------------------------------------------------------------
    // Core HTTP methods
    // -------------------------------------------------------------------------

    /**
     * Send an HTTP request to the Hitechcloud API
     *
     * @param string $method   HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint path (e.g. /api/v1/accounts)
     * @param array  $data     Request body data (sent as JSON for POST/PUT)
     * @return array Decoded JSON response
     * @throws \RuntimeException on cURL or API errors
     */
    public function request($method, $endpoint, $data = [])
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $method = strtoupper($method);

        $ch = curl_init();

        $headers = [
            'X-API-Key: ' . $this->apiKey,
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        $curlErrno    = curl_errno($ch);
        curl_close($ch);

        if ($curlErrno) {
            $error = "cURL error ({$curlErrno}): {$curlError}";
            $this->errors[] = $error;
            throw new \RuntimeException($error);
        }

        $decoded = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "Invalid JSON response from API (HTTP {$httpCode}): " . substr($responseBody, 0, 500);
            $this->errors[] = $error;
            throw new \RuntimeException($error);
        }

        $this->lastResponse = [
            'http_code' => $httpCode,
            'body'      => $decoded,
        ];

        if ($httpCode >= 400) {
            $message = isset($decoded['message']) ? $decoded['message'] : 'Unknown API error';
            $detail  = isset($decoded['detail'])  ? $decoded['detail']  : '';
            $error   = "API error (HTTP {$httpCode}): {$message}" . ($detail ? " - {$detail}" : '');
            $this->errors[] = $error;
            throw new \RuntimeException($error);
        }

        return $decoded;
    }

    /**
     * @param string $endpoint
     * @param array  $params   Query parameters
     * @return array
     */
    public function get($endpoint, $params = [])
    {
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        return $this->request('GET', $endpoint);
    }

    /**
     * @param string $endpoint
     * @param array  $data
     * @return array
     */
    public function post($endpoint, $data = [])
    {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * @param string $endpoint
     * @param array  $data
     * @return array
     */
    public function put($endpoint, $data = [])
    {
        return $this->request('PUT', $endpoint, $data);
    }

    /**
     * @param string $endpoint
     * @param array  $data
     * @return array
     */
    public function delete($endpoint, $data = [])
    {
        return $this->request('DELETE', $endpoint, $data);
    }

    // -------------------------------------------------------------------------
    // Domain-specific API methods
    // -------------------------------------------------------------------------

    /**
     * Check API health / test connection
     *
     * @return array
     */
    public function getHealth()
    {
        return $this->get('/api/v1/health');
    }

    /**
     * Create a hosting account
     *
     * @param array $params Account parameters:
     *   - username     (string) cPanel-style username
     *   - password     (string)
     *   - domain       (string) primary domain
     *   - plan_name    (string) hosting plan identifier
     *   - disk_quota   (int)    disk quota in MB
     *   - bandwidth    (int)    bandwidth limit in MB
     *   - max_domains  (int)
     *   - max_databases(int)
     *   - max_ftp      (int)
     *   - max_cronjobs (int)
     *   - php_version  (string) e.g. "8.2"
     *   - shell_access (bool)
     *   - ssl_enabled  (bool)
     *   - backup_enabled (bool)
     * @return array
     */
    public function createAccount($params)
    {
        return $this->post('/api/v1/accounts', $params);
    }

    /**
     * Suspend a hosting account
     *
     * @param string $username
     * @param string $reason
     * @return array
     */
    public function suspendAccount($username, $reason = 'Suspended by billing system')
    {
        return $this->post('/api/v1/accounts/' . urlencode($username) . '/suspend', [
            'reason' => $reason,
        ]);
    }

    /**
     * Unsuspend a hosting account
     *
     * @param string $username
     * @return array
     */
    public function unsuspendAccount($username)
    {
        return $this->post('/api/v1/accounts/' . urlencode($username) . '/unsuspend');
    }

    /**
     * Terminate (delete) a hosting account
     *
     * @param string $username
     * @return array
     */
    public function terminateAccount($username)
    {
        return $this->delete('/api/v1/accounts/' . urlencode($username));
    }

    /**
     * Change account password
     *
     * @param string $username
     * @param string $newPassword
     * @return array
     */
    public function changePassword($username, $newPassword)
    {
        return $this->put('/api/v1/accounts/' . urlencode($username) . '/password', [
            'password' => $newPassword,
        ]);
    }

    /**
     * Change account package / resource limits
     *
     * @param string $username
     * @param array  $package  Package parameters (plan_name, disk_quota, bandwidth, etc.)
     * @return array
     */
    public function changePackage($username, $package)
    {
        return $this->put('/api/v1/accounts/' . urlencode($username) . '/package', $package);
    }

    /**
     * Get account details
     *
     * @param string $username
     * @return array
     */
    public function getAccount($username)
    {
        return $this->get('/api/v1/accounts/' . urlencode($username));
    }

    /**
     * List all accounts
     *
     * @return array
     */
    public function listAccounts()
    {
        return $this->get('/api/v1/accounts');
    }
}

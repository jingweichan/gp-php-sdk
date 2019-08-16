<?php

namespace GP;

/**
 * Class GP
 */
abstract class GP
{
    /**
     * Country code (alpha-2).
     *
     * @var string
     */
    protected $countryCode = 'SG';

    /**
     * Currency code.
     *
     * @var string
     */
    protected $currency = 'SGD';

    /**
     * Is the environment on staging?
     *
     * @var bool
     */
    protected $isProduction = false;

    /**
     * Partner ID.
     *
     * @var string
     */
    protected $partnerId;

    /**
     * Partner secret.
     *
     * @var string
     */
    protected $partnerSecret;

    /**
     * Client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * Client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * Merchant ID.
     *
     * @var string
     */
    protected $merchantId;

    /**
     * Staging API URL.
     *
     * @var string
     */
    private $stagingApiUrl;

    /**
     * Production API URL.
     *
     * @var string
     */
    private $productionApiUrl;

    /**
     * OAuth2 path.
     *
     * @var string
     */
    private $oauth2Path;

    /**
     * Version 1 of the partner path.
     *
     * @var string
     */
    private $partnerV1Path;

    /**
     * Version 2 of the partner path.
     *
     * @var string
     */
    private $partnerV2Path;

    /**
     * GP constructor.
     *
     * @param string $partnerId Partner ID
     * @param string $partnerSecret Partner Secret
     * @param string $clientId Client ID
     * @param string $clientSecret Client Secret
     * @param string $merchantId Merchant ID
     */
    public function __construct(
        string $partnerId,
        string $partnerSecret,
        string $clientId,
        string $clientSecret,
        string $merchantId
    ) {
        $this->partnerId = $partnerId;
        $this->partnerSecret = $partnerSecret;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->merchantId = $merchantId;

        $this->stagingApiUrl = base64_decode('aHR0cHM6Ly9wYXJ0bmVyLWFwaS5zdGctbXl0ZWtzaS5jb20=');
        $this->productionApiUrl = base64_decode('aHR0cHM6Ly9wYXJ0bmVyLWFwaS5ncmFiLmNvbQ==');

        $this->oauth2Path = base64_decode('L2dyYWJpZC92MS9vYXV0aDI=');
        $this->partnerV1Path = base64_decode('L2dyYWJwYXkvcGFydG5lci92MQ==');
        $this->partnerV2Path = base64_decode('L2dyYWJwYXkvcGFydG5lci92Mg==');
    }

    /**
     * Set country code (alpha-2).
     * Accepted values are SG, MY, VN, PH, and TH.
     *
     * @param string $countryCode Country code
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Set country's currency.
     * Accepted values are SGD, MYR, VND, PHP, and THB.
     *
     * @param string $currency Country currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * Indicate whether to use production or staging environment.
     */
    public function useProduction(): void
    {
        $this->isProduction = true;
    }

    /**
     * Get API URL.
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->isProduction ? $this->productionApiUrl : $this->stagingApiUrl;
    }

    /**
     * Generate a web URL that provides a web interface for Oauth authentication. This helps the end-user login or register for GP.
     *
     * @param string $codeVerifier Code verifier
     * @param string $requestToken Request token
     * @param string $redirectUri Redirect URI
     * @param string $scope Scope (payment.one_time_charge or payment.recurring_charge)
     *
     * @return string
     */
    public function getOauthAuthorizeUrl(
        string $codeVerifier,
        string $requestToken,
        string $redirectUri,
        string $scope
    ): string {
        $data = [
            'acr_values' => 'consent_ctx:countryCode=' . $this->countryCode . ',currency=' . $this->currency,
            'client_id' => $this->clientId,
            'code_challenge' => $this->generateCodeChallenge($codeVerifier),
            'code_challenge_method' => 'S256',
            'nonce' => $this->generateNonce(16),
            'redirect_uri' => $redirectUri,
            'request' => $requestToken,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $this->generateNonce(7),
        ];

        return $this->getApiUrl() . $this->getOauth2UrlPath('/authorize?') . http_build_query($data);
    }

    /**
     * Generate the oauth token by passing ​code​ received in the return URL from GP.
     *
     * @param string $code Code
     * @param string $redirectUri Redirect URI
     * @param string $codeVerifier Code verifier
     *
     * @return object
     */
    public function getAccessToken(string $code, string $redirectUri, string $codeVerifier): object
    {
        $data = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
            'code_verifier' => $codeVerifier,
        ];

        return $this->callApi('POST', $this->getOauth2UrlPath('/token'), [], $data);
    }

    /**
     * Generate nonce.
     *
     * @param int $length Length
     *
     * @return string
     */
    public function generateNonce(int $length = 32): string
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (\Exception $ex) {
            return '';
        }
    }

    /**
     * Refund a full or partial refunds for a specific transaction.
     *
     * @param string $accessToken OAuth access token
     * @param string $txId order ID
     * @param string $groupTxId partner transaction ID
     * @param string $originTxID original partner transaction ID
     * @param int $amount ​Transaction amount as integer
     * @param string $description description of the charge (optional)
     *
     * @return object
     */
    public function refund(string $accessToken, string $txId, string $groupTxId, string $originTxID, int $amount, string $description = ''): object
    {
        $data = [
            'partnerTxID' => $txId,
            'partnerGroupTxID' => $groupTxId,
            'originTxID' => $originTxID,
            'amount' => $amount,
            'currency' => $this->currency,
            'merchantID' => $this->merchantId,
            'description' => $description,
        ];

        $date = $this->generateHeaderDate();

        return $this->callApi('POST', $this->getPartnerUrlPath('v2', '/refund'), [
            'Date' => $date,
            'Authorization' => 'Bearer ' . $accessToken,
            'X-GID-AUX-POP' => $this->generatePopSignature($accessToken, $date),
        ], $data);
    }

    /**
     * Check the status of a transaction.
     *
     * @param string $accessToken OAuth access token
     * @param string $txId order ID
     *
     * @return object
     */
    public function checkChargeStatus(string $accessToken, string $txId): object
    {
        $data = [
            'currency' => $this->currency,
        ];

        $date = $this->generateHeaderDate();

        return $this->callApi('GET', $this->getPartnerUrlPath('v2', '/charge/' . $txId . '/status') . '?' . http_build_query($data), [
            'Date' => $date,
            'Authorization' => 'Bearer ' . $accessToken,
            'X-GID-AUX-POP' => $this->generatePopSignature($accessToken, $date),
        ]);
    }

    /**
     * Check the status of a refund.
     *
     * @param string $accessToken OAuth access token
     * @param string $txId order ID
     *
     * @return object
     */
    public function checkRefundStatus(string $accessToken, string $txId): object
    {
        $data = [
            'currency' => $this->currency,
        ];

        $date = $this->generateHeaderDate();

        return $this->callApi('GET', $this->getPartnerUrlPath('v2', '/refund/' . $txId . '/status') . '?' . http_build_query($data), [
            'Date' => $date,
            'Authorization' => 'Bearer ' . $accessToken,
            'X-GID-AUX-POP' => $this->generatePopSignature($accessToken, $date),
        ]);
    }

    /**
     * Get the Partner API URL path.
     *
     * @param string $version Version (v1 or v2)
     * @param string Path
     *
     * @return string
     */
    protected function getPartnerUrlPath(string $version = 'v2', $path = ''): string
    {
        return ($version === 'v2' ? $this->partnerV2Path : $this->partnerV1Path) . $path;
    }

    /**
     * Generate code challenge.
     *
     * @param string $codeVerifier Code verifier
     *
     * @return string
     */
    protected function generateCodeChallenge(string $codeVerifier): string
    {
        return $this->base64UrlEncode(hash('sha256', $codeVerifier, true));
    }

    /**
     * Generate POP Signature for authentication with API via X-GID-AUX-POP header.
     *
     * @param string $accessToken Access token
     * @param string $date Date
     *
     * @return string
     */
    protected function generatePopSignature(string $accessToken, string $date): string
    {
        $timestamp = strtotime($date);
        $message = $timestamp . $accessToken;
        $signature = hash_hmac('sha256', $message, $this->clientSecret, true);
        $payload = [
            'time_since_epoch' => $timestamp,
            'sig' => $this->base64UrlEncode($signature),
        ];

        return $this->base64UrlEncode(json_encode($payload));
    }

    /**
     * Generate HMAC signature for authentication with API.
     *
     * @param string $method Request method
     * @param string $headerContentType Content-Type header
     * @param string $headerDate Date header
     * @param string $requestUrl Request URL
     * @param string $requestBody Request body
     *
     * @return string
     */
    protected function generateHmacSignature(
        string $method,
        string $headerContentType,
        string $headerDate,
        string $requestUrl,
        string $requestBody
    ): string {
        $data = $method . "\n" . $headerContentType . "\n" . $headerDate . "\n" . $requestUrl . "\n" . base64_encode(hash('sha256', $requestBody, true)) . "\n";

        return base64_encode(hash_hmac('sha256', $data, $this->partnerSecret, true));
    }

    /**
     * Requires a special case of base64encode for URLs.
     *
     * @param string $url URL
     *
     * @return string
     */
    protected function base64UrlEncode(string $url): string
    {
        return str_replace(['=', '+', '/'], ['', '-', '_'], base64_encode($url));
    }

    /**
     * Generate date format (RFC1123/RFC2822) without timezone.
     *
     * @param string $dateFormat Date format
     *
     * @return string
     */
    protected function generateHeaderDate(string $dateFormat = 'D, d M Y H:i:s \G\M\T'): string
    {
        return gmdate($dateFormat);
    }

    /**
     * Call API.
     *
     * @param string $method HTTP method
     * @param string $path Request path
     * @param array $headers Request Headers
     * @param array $body Request JSON body
     *
     * @return object|null
     */
    protected function callApi(string $method, string $path, array $headers, array $body = []): ?object
    {
        $payload = [
            'headers' => array_merge(['Date' => $this->generateHeaderDate()], $headers),
            'json' => $body,
            'debug' => false,
        ];

        try {
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->getApiUrl(),
            ]);
            $response = $client->request($method, $path, $payload);

            return json_decode($response->getBody()->getContents(), false);
        } catch (\GuzzleHttp\Exception\GuzzleException $ex) {
            return null;
        }
    }

    /**
     * Get OAuth2 API URL path.
     *
     * @param string $path Path
     *
     * @return string
     */
    private function getOauth2UrlPath(string $path = ''): string
    {
        return $this->oauth2Path . $path;
    }
}

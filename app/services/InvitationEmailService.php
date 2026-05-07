<?php

/**
 * Servico de envio de convite por e-mail usando Microsoft Graph API.
 */
class InvitationEmailService
{
    private const GRAPH_SCOPE = 'https://graph.microsoft.com/.default';
    private const DEFAULT_TOKEN_URL_TEMPLATE = 'https://login.microsoftonline.com/%s/oauth2/v2.0/token';
    private const COMMON_TOKEN_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private const DEFAULT_SEND_MAIL_ENDPOINT = 'https://graph.microsoft.com/v1.0/users/%s/sendMail';

    /**
     * Envia o e-mail de convite para testar o app.
     */
    public function sendInvitation(string $recipientEmail): void
    {
        if (!extension_loaded('curl')) {
            throw new Exception('Extensao cURL nao habilitada no PHP.');
        }

        $tenantId = trim((string) Config::get('GRAPH_TENANT_ID', getenv('GRAPH_TENANT_ID') ?: ''));
        $clientId = trim((string) Config::get('GRAPH_CLIENT_ID', getenv('GRAPH_CLIENT_ID') ?: ''));
        $clientSecret = trim((string) Config::get('GRAPH_CLIENT_SECRET', getenv('GRAPH_CLIENT_SECRET') ?: ''));
        $senderEmail = trim((string) Config::get('GRAPH_SENDER_EMAIL', getenv('GRAPH_SENDER_EMAIL') ?: Config::get('MAIL_USERNAME', '')));
        $debugEnabled = $this->isTruthy(Config::get('GRAPH_DEBUG', getenv('GRAPH_DEBUG') ?: 'false'));
        $timeoutSeconds = (int) Config::get('GRAPH_TIMEOUT_SECONDS', getenv('GRAPH_TIMEOUT_SECONDS') ?: 20);

        if ($tenantId === '' || $clientId === '' || $clientSecret === '' || $senderEmail === '') {
            throw new Exception(
                'Configuracao Graph incompleta no .env. Defina GRAPH_TENANT_ID, GRAPH_CLIENT_ID, GRAPH_CLIENT_SECRET e GRAPH_SENDER_EMAIL.'
            );
        }

        $tokenResponse = $this->requestAccessToken(
            $tenantId,
            $clientId,
            $clientSecret,
            $timeoutSeconds
        );

        if ($tokenResponse['http_code'] < 200 || $tokenResponse['http_code'] >= 300) {
            $msg = 'Falha ao autenticar no Microsoft Graph (HTTP ' . $tokenResponse['http_code'] . ').';
            $msg .= $this->extractGraphError($tokenResponse['body']);
            if ($debugEnabled) {
                $msg .= "\n\n--- GRAPH DEBUG (TOKEN) ---\n" . $this->sanitizeForDebug($tokenResponse['body']);
            }
            throw new Exception($msg);
        }

        $tokenData = json_decode($tokenResponse['body'], true);
        $accessToken = $tokenData['access_token'] ?? '';
        if ($accessToken === '') {
            $msg = 'Resposta de token invalida do Microsoft Graph.';
            if ($debugEnabled) {
                $msg .= "\n\n--- GRAPH DEBUG (TOKEN) ---\n" . $this->sanitizeForDebug($tokenResponse['body']);
            }
            throw new Exception($msg);
        }

        $payload = [
            'message' => [
                'subject' => 'Convite especial: teste o app Qual e a musica?',
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $this->buildHtmlBody(),
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $recipientEmail,
                        ],
                    ],
                ],
            ],
            'saveToSentItems' => true,
        ];

        $sendResponse = $this->sendMailRequest(
            $accessToken,
            $senderEmail,
            $payload,
            $timeoutSeconds
        );

        // sendMail retorna 202 Accepted em caso de sucesso.
        if ($sendResponse['http_code'] < 200 || $sendResponse['http_code'] >= 300) {
            $msg = 'Falha ao enviar e-mail via Microsoft Graph (HTTP ' . $sendResponse['http_code'] . ').';
            $msg .= $this->extractGraphError($sendResponse['body']);
            if ($debugEnabled) {
                $msg .= "\n\n--- GRAPH DEBUG (SEND) ---\n" . $this->sanitizeForDebug($sendResponse['body']);
            }
            throw new Exception($msg);
        }
    }

    /**
     * Solicita access token via client_credentials.
     */
    private function requestAccessToken(
        string $tenantId,
        string $clientId,
        string $clientSecret,
        int $timeoutSeconds
    ): array {
        $customTokenUrl = trim((string) Config::get('GRAPH_TOKEN_URL', getenv('GRAPH_TOKEN_URL') ?: ''));
        $tokenUrl = $this->resolveTokenUrl($customTokenUrl, $tenantId);

        $tokenResponse = $this->sendTokenRequest($tokenUrl, $clientId, $clientSecret, $timeoutSeconds);

        if ($customTokenUrl === '' && $this->isTenantNotFoundError($tokenResponse['body']) && $tokenUrl !== self::COMMON_TOKEN_URL) {
            $tokenResponse = $this->sendTokenRequest(self::COMMON_TOKEN_URL, $clientId, $clientSecret, $timeoutSeconds);
        }

        return $tokenResponse;
    }

    private function resolveTokenUrl(string $customTokenUrl, string $tenantId): string
    {
        if ($customTokenUrl !== '') {
            if (str_contains($customTokenUrl, '{tenant}')) {
                if ($tenantId === '') {
                    throw new Exception('GRAPH_TOKEN_URL contém {tenant}, mas GRAPH_TENANT_ID não foi configurado.');
                }
                return str_replace('{tenant}', rawurlencode($tenantId), $customTokenUrl);
            }

            return $customTokenUrl;
        }

        if ($tenantId === '') {
            throw new Exception('GRAPH_TENANT_ID é obrigatório quando GRAPH_TOKEN_URL não é definido.');
        }

        return sprintf(self::DEFAULT_TOKEN_URL_TEMPLATE, rawurlencode($tenantId));
    }

    private function sendTokenRequest(
        string $tokenUrl,
        string $clientId,
        string $clientSecret,
        int $timeoutSeconds
    ): array {
        $postFields = http_build_query([
            'client_id' => $clientId,
            'scope' => self::GRAPH_SCOPE,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        return $this->httpRequest(
            $tokenUrl,
            [
                'Content-Type: application/x-www-form-urlencoded',
            ],
            $postFields,
            $timeoutSeconds
        );
    }

    private function isTenantNotFoundError(string $body): bool
    {
        $decoded = json_decode($body, true);
        if (!is_array($decoded) || !isset($decoded['error'])) {
            return false;
        }

        $error = $decoded['error'];
        if (is_array($error) && isset($error['code']) && (string) $error['code'] === '90002') {
            return true;
        }

        if (is_string($error) && str_contains($body, 'AADSTS90002')) {
            return true;
        }

        return false;
    }

    /**
     * Envia o e-mail usando o endpoint sendMail do Graph.
     */
    private function sendMailRequest(
        string $accessToken,
        string $senderEmail,
        array $payload,
        int $timeoutSeconds
    ): array {
        $sendUrl = $this->resolveSendMailUrl($senderEmail);
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($jsonPayload === false) {
            throw new Exception('Falha ao serializar payload do e-mail para JSON.');
        }

        return $this->httpRequest(
            $sendUrl,
            [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            $jsonPayload,
            $timeoutSeconds
        );
    }

    private function resolveSendMailUrl(string $senderEmail): string
    {
        $sendUrl = trim((string) Config::get('GRAPH_SEND_MAIL_ENDPOINT', getenv('GRAPH_SEND_MAIL_ENDPOINT') ?: ''));
        if ($sendUrl === '') {
            $sendUrl = self::DEFAULT_SEND_MAIL_ENDPOINT;
        }

        if (str_contains($sendUrl, '%s')) {
            return sprintf($sendUrl, rawurlencode($senderEmail));
        }

        if (str_contains($sendUrl, '{sender}')) {
            return str_replace('{sender}', rawurlencode($senderEmail), $sendUrl);
        }

        if (str_contains($sendUrl, '{senderEmail}')) {
            return str_replace('{senderEmail}', rawurlencode($senderEmail), $sendUrl);
        }

        return $sendUrl;
    }

    /**
     * Executa chamada HTTP via cURL.
     */
    private function httpRequest(
        string $url,
        array $headers,
        string $body,
        int $timeoutSeconds
    ): array {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Falha ao inicializar cURL.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => max(5, $timeoutSeconds),
            CURLOPT_CONNECTTIMEOUT => max(5, $timeoutSeconds),
        ]);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            throw new Exception('Erro de conexao com Microsoft Graph: ' . $curlError);
        }

        return [
            'http_code' => $httpCode,
            'body' => (string) $responseBody,
        ];
    }

    private function extractGraphError(string $body): string
    {
        if ($body === '') {
            return '';
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return '';
        }

        $error = $decoded['error'] ?? null;
        if (is_array($error)) {
            $code = $error['code'] ?? '';
            $message = $error['message'] ?? '';
            if ($code !== '' || $message !== '') {
                return ' ' . trim($code . ': ' . $message);
            }
        }

        return '';
    }

    private function sanitizeForDebug(string $body): string
    {
        $sanitized = $body;

        // Mascara qualquer token JWT em texto.
        $sanitized = preg_replace(
            '/[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+/',
            '[JWT_REDACTED]',
            $sanitized
        ) ?? $sanitized;

        // Mascara campo access_token em JSON.
        $sanitized = preg_replace(
            '/("access_token"\s*:\s*")[^"]+(")/i',
            '$1***$2',
            $sanitized
        ) ?? $sanitized;

        return $sanitized;
    }

    private function isTruthy(mixed $value): bool
    {
        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    private function buildHtmlBody(): string
    {
        $fallbackUrl = (string) Config::get('APP_LOCAL_URL', (string) Config::get('APP_URL', 'http://localhost/qualamusica'));
        $appUrl = function_exists('app_base_url') ? app_base_url() : rtrim($fallbackUrl, '/');

        return '
        <div style="margin:0;padding:24px;background:#f6f6f6;font-family:Segoe UI,Arial,sans-serif;color:#2d2f2f;">
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e7e8e8;">
                <tr>
                    <td style="background:#4953ac;padding:24px 28px;color:#ffffff;">
                        <h1 style="margin:0;font-size:24px;line-height:1.3;">Convite para testar o app Qual e a musica?</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 16px 0;font-size:16px;line-height:1.6;">
                            Ola! Esperamos que voce esteja bem.
                        </p>
                        <p style="margin:0 0 16px 0;font-size:16px;line-height:1.6;">
                            Gostariamos de convidar voce para testar o aplicativo <strong>Qual e a musica?</strong>.
                        </p>
                        <p style="margin:0 0 16px 0;font-size:16px;line-height:1.6;">
                            O app foi desenvolvido para <strong>estimular ou apoiar a reabilitacao da memoria, da atencao e de outras funcoes cognitivas</strong>, especialmente em idosos e pacientes que tiveram AVC ou qualquer outra perda de memoria.
                        </p>
                        <p style="margin:0 0 20px 0;font-size:16px;line-height:1.6;">
                            Sua participacao e muito importante para avaliarmos e melhorarmos essa experiencia.
                        </p>
                        <div style="margin:24px 0;">
                            <a href="' . htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#fdd400;color:#1f2937;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:700;">
                                Acessar o app
                            </a>
                        </div>
                        <p style="margin:0;font-size:15px;line-height:1.6;color:#5a5c5c;">
                            Agradecemos pela atencao e pelo apoio.
                        </p>
                        <p style="margin:8px 0 0 0;font-size:15px;line-height:1.6;color:#5a5c5c;">
                            Equipe Qual e a musica?
                        </p>
                    </td>
                </tr>
            </table>
        </div>';
    }
}

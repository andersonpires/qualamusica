<?php
/**
 * Classe Config - Carrega variáveis de ambiente
 * 
 * Responsável por:
 * - Carregar arquivo .env
 * - Fornecer acesso centralizado às configurações
 * - Garantir valores padrão seguros
 */

class Config
{
    /**
     * Variáveis carregadas
     */
    private static array $vars = [];

    /**
     * Flag para indicar se já foi carregado
     */
    private static bool $loaded = false;

    /**
     * Carrega arquivo .env na raiz do projeto
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = dirname(dirname(dirname(__FILE__))) . '/.env';

        if (!file_exists($envFile)) {
            throw new Exception('Arquivo .env não encontrado em: ' . $envFile);
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignora comentários
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse da linha
            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove aspas se houver
            if (($value[0] ?? null) === '"' && ($value[-1] ?? null) === '"') {
                $value = substr($value, 1, -1);
            }

            self::$vars[$key] = $value;
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }

        self::$loaded = true;
    }

    /**
     * Obtém valor de configuração
     * 
     * @param string $key Chave da configuração
     * @param mixed $default Valor padrão se não existir
     * @return mixed Valor da configuração
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars[$key] ?? $default;
    }

    /**
     * Verifica se configuração existe
     */
    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset(self::$vars[$key]);
    }

    /**
     * Obtém valor booleano com parsing seguro para variáveis de ambiente.
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return $default;
        }

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
    }
}

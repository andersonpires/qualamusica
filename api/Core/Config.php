<?php
/**
 * Classe Config - Carrega variáveis de ambiente.
 */
class Config
{
    /**
     * Variáveis carregadas.
     */
    private static array $vars = [];

    /**
     * Flag para indicar se já foi carregado.
     */
    private static bool $loaded = false;

    /**
     * Carrega arquivo .env na raiz do projeto (quando existir)
     * e complementa com variáveis de ambiente do servidor/container.
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = dirname(dirname(dirname(__FILE__))) . '/.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                if (strpos($line, '=') === false) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if (($value[0] ?? null) === '"' && ($value[-1] ?? null) === '"') {
                    $value = substr($value, 1, -1);
                }

                self::$vars[$key] = $value;
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
        }

        // Fallback: ambiente do runtime (Docker/EasyPanel).
        foreach ($_ENV as $key => $value) {
            if (!array_key_exists((string) $key, self::$vars) && (is_scalar($value) || $value === null)) {
                self::$vars[(string) $key] = (string) $value;
            }
        }

        $envList = getenv();
        if (is_array($envList)) {
            foreach ($envList as $key => $value) {
                if (!array_key_exists((string) $key, self::$vars) && (is_scalar($value) || $value === null)) {
                    self::$vars[(string) $key] = (string) $value;
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtém valor de configuração.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars[$key] ?? $default;
    }

    /**
     * Verifica se configuração existe.
     */
    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset(self::$vars[$key]);
    }

    /**
     * Obtém valor booleano com parsing seguro.
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

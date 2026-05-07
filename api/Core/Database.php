<?php
/**
 * Classe Database - Gerencia conexão PDO com o banco
 * 
 * Responsável por:
 * - Criar conexão PDO única (singleton)
 * - Garantir UTF-8 e pt-BR
 * - Fornecer preparação segura de statements
 */

class Database
{
    /**
     * Instância singleton da conexão
     */
    private static ?PDO $connection = null;

    /**
     * Obtém conexão PDO (singleton)
     * 
     * @return PDO Conexão ativa
     * @throws Exception Se houver erro na conexão
     */
    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        Config::load();

        $host = trim((string) Config::get('DB_HOST', ''));
        $portRaw = Config::get('DB_PORT', '');
        $dbName = trim((string) Config::get('DB_NAME', ''));
        $user = trim((string) Config::get('DB_USER', ''));
        $pass = (string) Config::get('DB_PASS', '');

        if ($host === '' || $dbName === '' || $user === '' || $pass === '' || $portRaw === '') {
            throw new Exception('Configuração de banco incompleta. Defina DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS no .env.');
        }

        $port = filter_var($portRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65535]
        ]);

        if ($port === false) {
            throw new Exception('DB_PORT inválida no .env.');
        }

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

            self::$connection = new PDO(
                $dsn,
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Define charset e locale para UTF-8 e pt-BR
            self::$connection->exec("SET NAMES utf8mb4");
            self::$connection->exec("SET CHARACTER SET utf8mb4");

        } catch (PDOException $e) {
            throw new Exception('Erro ao conectar no banco de dados: ' . $e->getMessage());
        }

        return self::$connection;
    }

    /**
     * Prepara um statement de forma segura
     * Uso: $stmt = Database::prepare("SELECT * FROM musicas WHERE id = ?");
     *      $result = $stmt->execute([1]);
     * 
     * @param string $sql SQL a preparar
     * @return PDOStatement
     */
    public static function prepare(string $sql): PDOStatement
    {
        return self::getConnection()->prepare($sql);
    }

    /**
     * Executa query segura com parameters
     * Uso: Database::query("SELECT * FROM musicas WHERE id = ?", [1]);
     * 
     * @param string $sql SQL a executar
     * @param array $params Parameters para prepared statement
     * @return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Executa query e retorna todos os resultados
     * 
     * @param string $sql SQL a executar
     * @param array $params Parameters
     * @return array Array de resultados
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Executa query e retorna primeira linha
     * 
     * @param string $sql SQL a executar
     * @param array $params Parameters
     * @return array|false Array com resultado ou false
     */
    public static function fetch(string $sql, array $params = [])
    {
        return self::query($sql, $params)->fetch();
    }

    /**
     * Obtém último ID inserido
     */
    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
}

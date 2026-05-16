<?php

declare(strict_types=1);

class Model
{
    protected $db;
    private static $connection = null;

    public function __construct()
    {
        $this->db = self::connect();
    }

    private static function connect(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $configFile = APP_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

        if (!file_exists($configFile)) {
            throw new RuntimeException('File config database tidak ditemukan.');
        }

        $config = require $configFile;

        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";

        self::$connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }

    public function db(): PDO
    {
        return $this->db;
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    protected function fetch(string $sql, array $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    protected function execute(string $sql, array $params = []): bool
    {
        return $this->query($sql, $params)->rowCount() >= 0;
    }

    protected function countRows(string $sql, array $params = []): int
    {
        return (int) $this->query($sql, $params)->fetchColumn();
    }

    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    protected function beginTransaction(): void
    {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    protected function commit(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    protected function rollBack(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    protected function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (Throwable $error) {
            $this->rollBack();
            throw $error;
        }
    }
}
<?php


namespace AppConfig;


use Log\LoggerInterface;
use PDO;
use PDOException;

class DbConfig
{
    private $logger;
    private $pdo = null;
    private static $myself = null;

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public static function getInstance(string $dbHost, string $dbName, string $dbUser, string $dbPassword,
                                       LoggerInterface $logger, bool $isDbEnabled = false): DbConfig
    {
        if (self::$myself === null) {
            self::$myself = new DbConfig($dbHost, $dbName, $dbUser, $dbPassword, $logger, $isDbEnabled);
        }
        return self::$myself;
    }

    public function createDbTables(): bool
    {
        $queries = @file_get_contents('word_hyphenation_db.sql');
        if ($queries === false) {
            return false;
        }
        $queriesArr = explode(';', $queries);
        $this->pdo->beginTransaction();
        foreach ($queriesArr as $query) {
            if (!$this->pdo->exec($query) === false) {
                $this->logger->critical('Cannot execute SQL query: `{sql}` Rollback changes.', array('sql' => $query));
                $this->pdo->rollBack();
                return false;
            }
        }
        $this->pdo->commit();
        return true;
    }

    private function __construct(string $dbHost, string $dbName, string $dbUser, string $dbPassword,
                                 LoggerInterface $logger, bool $isDbEnabled = false)
    {
        $this->logger = $logger;
        if ($isDbEnabled) {
            $dsn = 'mysql:dbname=' . $dbName . ';host=' . $dbHost . ';charset=utf8';
            try {
                $this->pdo = new PDO($dsn, $dbUser, $dbPassword);
            } catch (PDOException $e) {
                $this->logger->critical('Cannot connect to database {dsn}', array('dsn' => $dsn));
                exit();
            }
        }
    }
}

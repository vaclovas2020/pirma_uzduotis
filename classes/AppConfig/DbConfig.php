<?php


namespace AppConfig;


use Log\LoggerInterface;
use PDO;
use PDOException;

class DbConfig
{
    private $logger;
    private $pdo = null;

    public function __construct(string $dbHost, string $dbName, string $dbUser, string $dbPassword,
                                LoggerInterface $logger)
    {
        $this->logger = $logger;
        $dsn = "mysql:dbname={$dbName};host={$dbHost};charset=utf8";
        try {
            $this->pdo = new PDO($dsn, $dbUser, $dbPassword);
        } catch (PDOException $e){
            $this->logger->critical('Cannot connect to database {dsn}', array('dsn'=>$dsn));
            exit();
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }


    public function createDbTables(): bool{
        $queries = @file_get_contents('word_hyphenation_db.sql');
        if ($queries === false){
            return false;
        }
        $queriesArr = explode(';', $queries);
        $this->pdo->beginTransaction();
        foreach ($queriesArr as $query){
            if (!$this->pdo->exec($query) === false){
                $this->logger->critical("Cannot execute SQL query: '{sql}' Rollback changes.", array('sql'=>$query));
                $this->pdo->rollBack();
                return false;
            }
        }
        $this->pdo->commit();
        return true;
    }
}

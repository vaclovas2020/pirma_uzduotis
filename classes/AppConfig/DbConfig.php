<?php


namespace AppConfig;


use Log\LoggerInterface;
use PDO;
use PDOException;

class DbConfig
{

    private $dbHost = "localhost";
    private $dbName = "word_hyphenation_db";
    private $dbUser = "root";
    private $dbPassword = "Q1w5e9r7t5y3@";
    private $logger;

    public function __construct(string $dbHost, string $dbName, string $dbUser, string $dbPassword,
                                LoggerInterface $logger)
    {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->logger = $logger;
    }

    public function getPDOConnector(): PDO
    {
        $pdo = null;
        $dsn = "mysql:dbname={$this->dbName};host={$this->dbHost}";
        try {
            $pdo = new PDO($dsn, $this->dbUser, $this->dbPassword);
        } catch (PDOException $e){
            $this->logger->critical('Cannot connect to database {dsn}', array('dsn'=>$dsn));
        }
        return $pdo;
    }

}

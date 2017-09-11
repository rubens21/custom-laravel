<?php

namespace Tests;
use Illuminate\Database\MySqlConnection;
use PDO;
use PDOException;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Illuminate\Database\MySqlConnection
     */
    private $db;

    public function setup()
    {
        parent::setUp();
        try {
            $pdo = new PDO('mysql:dbname='.env('DB_NAME').';host='.env('DB_HOST'), env('DB_USER'), env('DB_PASS'));
            $this->db = new MySqlConnection($pdo, env('DB_USER'), env('DB_PASS'));
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    /**
     * @return \Illuminate\Database\MySqlConnection
     */
    public function getConnection(){
        return $this->db;
    }
}

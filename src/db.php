<?php

namespace Cachesistemas\Sicoob;

use PDO;
use PDOException;


class DB extends PDO
{

    public $host = DB_HOST;
    public $base = DB_BASE;
    public $user = DB_USER;
    public $pass = DB_SENHA;
    protected $conn;

    public function __construct()
    {
        $this->conexao();
    }


    private  function conexao()
    {

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->base . "", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES 'utf8';");
        } catch (PDOException $e) {
            print "Erro: " . $e->getMessage();
            die();
        }
    }

    public function  comando($sql)
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return   $e->getMessage();
        }
    }




    public  function select($sql)
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return   $e->getMessage();
        }
    }
}

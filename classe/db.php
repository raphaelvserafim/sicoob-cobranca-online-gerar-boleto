<?php
 

class DB extends PDO
{

    public $host = DB_HOST;
    public $base = DB_BASE;
    public $user = DB_USER;
    public $pass = DB_SENHA;

    public function __construct()
    {
         $this->con = $this->conexao();
    }


    public  function conexao()
    {

        try {
            $this->con = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->base . "", $this->user, $this->pass);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->con->exec("SET NAMES 'utf8';");
        } catch (PDOException $e) {
            print "Erro: " . $e->getMessage();
            die();
        }
        return $this->con;
    }

    public function query($sql)
    {
        try {
            $stmt = $this->con->prepare($sql);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return   $e->getMessage();
        }
    }


    

    public  function select($sql)
    {
        try {
            $stmt = $this->con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return   $e->getMessage();
        }
    }
}

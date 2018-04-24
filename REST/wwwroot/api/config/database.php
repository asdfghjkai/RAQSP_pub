<?php
class Database{
    //Code Reference:
    // Database credentials supplied https://www.codeofaninja.com/2017/02/create-simple-rest-api-in-php.html

    private $host = "localhost";
    private $db_name = "raqsp";
    private $username = "";
    private $password = "";
    public $conn;

    // get the database connection
    public function getConnection(){

        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>

<?php

class DtbServer{
  private $conn;
  private $result;
  private $sql;

  public function __construct(){
     $this->conn = pg_connect("host=192.168.2.11 port=5432 dbname=dbsisgov user=ussisgov password=pgdesenv");
  }

  public function Exec($dsSql){
    $this->sql = $dsSql;
    $this->result = @pg_query($this->conn, $dsSql);

    return $this->result !== false;
  }

  public function Query($dsSql){
    $this->sql = $dsSql;
    $this->result = @pg_query($this->conn, $dsSql);

    return $this->result !== false;
  }

  public function getMessage(){
    return ["dsMsg"=>pg_last_error($this->conn).". sql: ". $this->sql, "flTipo"=>"E"];
  }

  public function FetchArray(){
    return pg_fetch_array($this->result); 
  }

  public function Begin(){
    @pg_query($this->conn, "BEGIN");
  }

  public function Rollback(){
    @pg_query($this->conn, "ROLLBACK");
  }

  public function Commit(){
    @pg_query($this->conn, "COMMIT");
  }

}
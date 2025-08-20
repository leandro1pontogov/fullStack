<?php

class TbSala{
  private $idsala;
  private $nmsala;
  private $dslocalizacao;
  private $nrcapacidade;
  private $txrecursosdisponiveis;

  public function __construct(){
    $this->idsala = "";
    $this->nmsala = "";
    $this->dslocalizacao = "";
    $this->nrcapacidade = "";
    $this->txrecursosdisponiveis = "";
  }

  public function Set($prpTbSala, $valTbSala){
    $this->$prpTbSala = $valTbSala;
  }

  public function Get($prpTbSala){
    return $this->$prpTbSala;
  }

  public function LoadObject($resSet){
    $objTbSala = new TbSala();

    $objTbSala->Set("idsala", $resSet["idsala"]);
    $objTbSala->Set("nmsala", $resSet["nmsala"]);
    $objTbSala->Set("dslocalizacao", $resSet["dslocalizacao"]);
    $objTbSala->Set("nrcapacidade", $resSet["nrcapacidade"]);
    $objTbSala->Set("txrecursosdisponiveis", $resSet["txrecursosdisponiveis"]);

    return $objTbSala;
  }

  public function Insert($objTbSala){
    $dtbServer = new DtbServer();

    $dsSql = "INSERT INTO
                shtreinamento.tbsala(
                idsala,
                nmsala,
                dslocalizacao,
                nrcapacidade,
                txrecursosdisponiveis
                )
                VALUES(
                (SELECT NEXTVAL('shtreinamento.sqidsala')),
                '".$objTbSala->Get("nmsala") ."',
                '".$objTbSala->Get("dslocalizacao") ."',
                '".$objTbSala->Get("nrcapacidade") ."',
                '".$objTbSala->Get("txrecursosdisponiveis") ."'
                );";

    if(!$dtbServer->Exec($dsSql)){
      $arrMsg = $dtbServer->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public function Update($objTbSala){
    $dtbServer = new DtbServer();

    $dsSql = "UPDATE 
                shtreinamento.tbsala
                SET
                  idsala = ".$objTbSala->Get("idsala") .",
                  nmsala = ".$objTbSala->Get("nmsala") .", 
                  dslocalizacao = ".$objTbSala->Get("dslocalizacao") .", 
                  nrcapacidade = ".$objTbSala->Get("nrcapacidade") .", 
                  txrecursosdisponiveis = ".$objTbSala->Get("txrecursosdisponiveis") ."
                WHERE
                  idsala = ".$objTbSala->Get("idsala") .";";
                  
  if(!$dtbServer->Exec($dsSql)){
      $arrMsg = $dtbServer->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public function Delete($objTbSala){
    $dtbServer = new DtbServer();

    $dsSql = "DELETE FROM 
                shtreinamento.tbsala
              WHERE
                idsala = " . $objTbSala->Get("idsala") . ";";
    
  if(!$dtbServer->Exec($dsSql)){
      $arrMsg = $dtbServer->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public static function LoadByIdSala($idSala){
    $dtbServer = new DtbServer();
    $objTbSala = new TbSala();

    $dsSql = "SELECT * FROM
                shtreinamento.tbsala
              WHERE idsala = " . $idSala . " ";

    if(!$dtbServer->Query($dsSql)){
      return $dtbServer->getMessage()["dsMsg"];
    }else{
      $resSet = $dtbServer->FetchArray();
      $objTbSala = $objTbSala->LoadObject($resSet);
      return $objTbSala;
    }
  }

  public static function ListByCondicao($strCondicao, $strOrdenacao){
    $dtbServer = new DtbServer();
    $objTbSala = new TbSala();

    $dsSql = "SELECT * FROM
                shtreinamento.tbsala
              WHERE
                1 = 1";

    if($strCondicao){
      $dsSql .= $strCondicao;
    }

    if($strOrdenacao){
      $dsSql .= " ORDER BY ". $strOrdenacao;
    }

    if(!$dtbServer->Query($dsSql)){
      return $dtbServer->getMessage()["dsMsg"];
    }else{
      while($resSet = $dtbServer->FetchArray()){
        $aroTbSala[] = $objTbSala->LoadObject($resSet);
      }
        return $aroTbSala;
    }
  }
}
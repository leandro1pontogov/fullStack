<?php

class TbReserva{
  private $idreserva;
  private $idsala;
  private $colaboradorsala;
  private $dtdata;
  private $hrinicio;
  private $hrfim;
  private $dtbLink;

  public function __construct(){
    $this->idreserva = "";
    $this->idsala = "";
    $this->colaboradorsala = "";
    $this->dtdata = "";
    $this->hrinicio = "";
    $this->hrfim = "";
  }

  public function Set($prpTbReserva, $valTbReserva){
    $this->$prpTbReserva = $valTbReserva;
  }

  public function SetDtbLink($dtbLink){
    $this->dtbLink = $dtbLink;
  }

  public function Get($prpTbReserva){
    return $this->$prpTbReserva;
  }

  public function LoadObject($resSet){
    $objTbReserva = new TbReserva();

    $objTbReserva->Set("idreserva", $resSet["idreserva"]);
    $objTbReserva->Set("idsala", $resSet["idsala"]);
    $objTbReserva->Set("idcolaboradorsala", $resSet["idcolaboradorsala"]);
    $objTbReserva->Set("dtdata", $resSet["dtdata"]);
    $objTbReserva->Set("hrinicio", $resSet["hrinicio"]);
    $objTbReserva->Set("hrfim", $resSet["hrfim"]);

    if(!isset($GLOBALS["_intTotalReserva"])){
      $GLOBALS["_intTotalReserva"] = $resSet["_inttotal"];
    }
    
    return $objTbReserva;
  }

  public function GetObjTbSala(){
    if($this->objTbSala == null){
      $this->objTbSala = new TbSala();

      if($this->Get("idsala") != ""){
        $this->objTbSala = TbSala::LoadByIdSala($this->Get("idsala"));
      }
    }
    return $this->objTbSala;
  }

  public function GetObjColaborador(){
    if($this->objTbColaborador == null){
      $this->objTbColaborador = new TbColaborador();

      if($this->Get("idcolaboradorsala") != ""){
        $this->objTbColaborador = TbColaborador::LoadByIdColaborador($this->Get("idcolaboradorsala"));
      }
    }
    return $this->objTbColaborador;
  }

  public function Insert($objTbReserva){
    if($this->dtbLink == null){
      $this->dtbLink = new DtbServer();
    }
    $fmt = new Format();

    $dsSql = "INSERT INTO
                shtreinamento.tbreserva(
                  idreserva,
                  idsala,
                  idcolaboradorsala,
                  dtdata,
                  hrinicio,
                  hrfim
                )
              VALUES (
                (SELECT NEXTVAL('shtreinamento.sqidreserva')),
                ".$fmt->escSqlQuotes($objTbReserva->Get("idsala")).",
                ".$fmt->escSqlQuotes($objTbReserva->Get("idcolaboradorsala")).",
                '".$objTbReserva->Get("dtdata")."',
                '".$objTbReserva->Get("hrinicio")."',
                '".$objTbReserva->Get("hrfim")."'
              );";

    if(!$this->dtbLink->Exec($dsSql)){
      $arrMsg = $this->dtbLink->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }

    return $arrMsg;
  }

  public function Update($objTbReserva){
    if($this->dtbLink == null){
      $this->dtbLink = new DtbServer();
    }

    $dsSql = "UPDATE
                shtreinamento.tbreserva
              SET
                idreserva = ".$objTbReserva->Get("idreserva").",
                idsala = ".$objTbReserva->Get("idsala").",
                idcolaboradorsala = ".$objTbReserva->Get("idcolaboradorsala").",
                dtdata = ".$objTbReserva->Get("dtdata").",
                hrinicio = ".$objTbReserva->Get("hrinicio").",
                hrfim = ".$objTbReserva->Get("hrfim")."
              WHERE
                idreserva = ".$objTbReserva->Get("idreserva").";";

    if(!$this->dtbLink->Exec($dsSql)){
      $arrMsg = $this->dtbLink->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }

    return $arrMsg;
  }

  public function Delete($objTbReserva){
    if($this->dtbLink == null){
      $this->dtbLink = new DtbServer();
    }

    $dsSql = "DELETE FROM
                shtreinamento.tbreserva
              WHERE
                idreserva = ".$objTbReserva->Get("idreserva").";";

    if(!$this->dtbLink->Exec($dsSql)){
      $arrMsg = $this->dtbLink->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }

    return $arrMsg;
  }

  public static function LoadByIdReserva($idReserva){
    $dtbServer = new DtbServer();
    $objTbReserva = new TbReserva();

    $dsSql = "SELECT * FROM
                shtreinamento.tbreserva
              WHERE
                idreserva = ".$idReserva.";";

    
    if(!$dtbServer->Query($dsSql)){
      return $dtbServer->getMessage()["dsMsg"];
    }else{
      $resSet = $dtbServer->FetchArray();
      $objTbReserva = $objTbReserva->LoadObject($resSet);
      return $objTbReserva;
    }
  }

  public static function ListByCondicao($strCondicao, $strOrdenacao){
    $dtbServer = new DtbServer();
    $objTbReserva = new TbReserva();

    $dsSql = "SELECT
                *,
                COUNT(*) OVER() _inttotal
              FROM
                shtreinamento.tbreserva
              WHERE
                1 = 1 ";

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
        $aroTbReserva[] = $objTbReserva->LoadObject($resSet);
      }
        return $aroTbReserva;
    }

  }

}
<script>

	$(function () {
    //-----------------------------------------------------------------------------------------//
    //Instanciando os campos da tela de cadastro
    //-----------------------------------------------------------------------------------------//
    $("#dtData").kendoDatePicker();
    $("#hrInicio").kendoTimePicker();
    $("#hrFim").kendoTimePicker();
    $("#BtnSala").kendoButton(
      {
        spriteCssClass: "k-pg-icon k-i-l1-c2",
        enable: <?=$blSalaSelecionada ? "false" : "true" ?>,
        click: function(){
          OpenWindow(true, "ConsultaSala", "controller/sala/ctrSala.php?action=winConsulta", "Janela Consulta Sala", "frmCadastroReserva")
        }
      }
    );
    $("#BtnColaborador").kendoButton({
      spriteCssClass: "k-pg-icon k-i-l1-c2",
        click: function(){
          OpenWindow(true, "ConsultaColaborador", "controller/colaborador/ctrColaborador.php?action=winConsulta", "Janela Consulta Colaborador", "frmCadastroReserva")
        }
    });
    
    
    //-----------------------------------------------------------------------------------------//

    //-----------------------------------------------------------------------------------------//
    //Barra de a��es
    //-----------------------------------------------------------------------------------------//
    $("#frmCadastroReserva #BarAcoes").kendoToolBar({
      items: [
        {
          type: "spacer",
        },
        {
          type: "buttonGroup",
          buttons: [
            {
              id: "BtnGravar",
              spriteCssClass: "k-pg-icon k-i-l1-c5",
              text: "Gravar",
              group: "actions",
              attributes: { tabindex: "33" },
              click: function () {

                $.post(
                  "controller/reserva/ctrReserva.php?action=gravar",
                   $("#frmCadastroReserva").serialize(),
                   function(response){
                    Message(response.flDisplay, response.flTipo, response.dsMsg);
                    if(response.flTipo == "S"){
                      $("#frmConsultaReserva #BtnPesquisar").click()
                      if(!<?=$blSalaSelecionada ? "true" : "false" ?>){
                        $("#frmCadastroReserva #BtnLimpar").click()
                      }else{
                        $("#WinCadastroReserva").data("kendoWindow").close();
                      }
                    }
                   },
                   "json"
                )
              }
            },
            {
              id: "BtnExcluir",
              spriteCssClass: "k-pg-icon k-i-l1-c7",
              text: "Excluir",
              group: "actions",
              enable: false,
              attributes: { tabindex: "34" },
              click: function () {

                $.post(
                  "controller/reserva/ctrReserva.php?action=excluir",
                  $("#frmCadastroReserva").serialize(),
                   function(response){
                    Message(response.flDisplay, response.flTipo, response.dsMsg);
                    if(response.flTipo == "S"){
                      $("#frmConsultaReserva #BtnPesquisar").click()
                      $("#WinCadastroReserva").data("kendoWindow").close()
                    }
                  },
                  "json"
                )
              }
            },
            {
              id: "BtnLimpar",
              spriteCssClass: "k-pg-icon k-i-l1-c6",
              text: "Limpar",
              group: "actions",
              attributes: { tabindex: "34" },
              click: function () {

                $("#WinCadastroReserva").data("kendoWindow").refresh(
                  {
                    url: "controller/reserva/ctrReserva.php?action=incluir"
                  }
              )

              }
            },
            {
              id: "BtnFechar",
              spriteCssClass: "k-pg-icon k-i-l1-c4",
              text: "Fechar",
              group: "actions",
              attributes: { tabindex: "35" },
              click: function () {
                $("#WinCadastroReserva").data("kendoWindow").close()
              }
            }

          ]
        }
      ]
    })
    //-----------------------------------------------------------------------------------------//

    //-----------------------------------------------------------------------------------------//
    //A��es diversas da tela de cadastro
    //-----------------------------------------------------------------------------------------//
    if($("#frmCadastroReserva #idReserva").val() != ""){
      $("#frmCadastroReserva #BarAcoes").data("kendoToolBar").enable("#BtnExcluir")
    }
    $("#WinCadastroReserva").data("kendoWindow").center().open();
    //-----------------------------------------------------------------------------------------//
	})

</script>

<div class="k-form">
  <form id="frmCadastroReserva" style="height: 100%;">
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td style="text-align: right; width: 120px;">Id:</td>
        <td>
          <input type="text" id="idReserva" name="idReserva" tabindex="-1" class="k-textbox k-input-disabled" readonly="readonly" value="<?php echo $objTbReserva->Get("idreserva") ?>">
        </td>
      </tr>
    </table>
     <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td style="text-align: right; width: 120px;">Sala:</td>
        <td>
          <input type="text" id="idSala" name="idSala" tabindex="-1" class="k-textbox k-input-disabled" style="width: 60px;" readonly="readonly" value="<?php echo $objTbReserva->GetObjTbSala()->Get("idsala") ?>">
          <span id="BtnSala" style="cursor: pointer; width: 24px; height: 24px;" title="consultarSalas"></span>
           <input type="text" id="nmSala" name="nmSala" tabindex="1" class="k-textbox" style="width: 511px;" value="<?php echo $objTbReserva->GetObjTbSala()->Get("nmsala") ?>">
        </td>
      </tr>
    </table>
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td style="text-align: right; width: 120px;">Colaborador:</td>
        <td>
          <input type="text" id="idColaborador" name="idColaborador" style="width: 60px;" tabindex="-1" class="k-textbox k-input-disabled" readonly="readonly" value="<?php echo $objTbReserva->GetObjColaborador()->Get("idcolaboradorsala") ?>">
          <span id="BtnColaborador" style="cursor: pointer; width: 24px; height: 24px;" title="consultarColaboradores"></span>
           <input type="text" id="nmColaborador" name="nmColaborador" tabindex="1" class="k-textbox" style="width: 511px;" value="<?php echo $objTbReserva->GetObjColaborador()->Get("nmcolaboradorsala") ?>">
        </td>
      </tr>
    </table>
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px;">Data:</td>
        <td>
          <input type="text" id="dtData" name="dtData" tabindex="2" style="width: 100px;" value="<?php echo $objTbReserva->Get("dtdata") ?>">
        </td>
      </tr>
    </table>
     <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px;">Hora inicio:</td>
        <td>
          <input type="text" id="hrInicio" name="hrInicio" tabindex="3" style="width: 100px;" value="<?php echo $objTbReserva->Get("hrinicio") ?>">
        </td>
        <td class="k-required" style="text-align: right; width: 334px;">Hora Fim:</td>
        <td>
          <input type="text" id="hrFim" name="hrFim" tabindex="3" style="width: 100px;" value="<?php echo $objTbReserva->Get("hrfim") ?>">
        </td>
      </tr>
    </table>
    

    <div id="BarAcoes"></div>

  </form>
</div>
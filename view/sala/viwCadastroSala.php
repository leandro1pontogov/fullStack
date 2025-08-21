<script>

	$(function () {
    //-----------------------------------------------------------------------------------------//
    //Instanciando os campos da tela de cadastro
    //-----------------------------------------------------------------------------------------//
    $("#nrCapacidade").kendoNumericTextBox({
		  min: 0,
		  format: ""
	  })
    //-----------------------------------------------------------------------------------------//

    //-----------------------------------------------------------------------------------------//
    //Barra de ações
    //-----------------------------------------------------------------------------------------//
    $("#frmCadastroSala #BarAcoes").kendoToolBar({
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
                  "controller/sala/ctrSala.php?action=gravar",
                   $("#frmCadastroSala").serialize(),
                   function(response){
                    Message(response.flDisplay, response.flTipo, response.dsMsg);
                    if(response.flTipo == "S"){
                      $("#frmConsultaSala #BtnPesquisar").click()
                      $("#frmCadastroSala #BtnLimpar").click()
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
                  "controller/sala/ctrSala.php?action=excluir",
                  $("#frmCadastroSala").serialize(),
                   function(response){
                    Message(response.flDisplay, response.flTipo, response.dsMsg);
                    if(response.flTipo == "S"){
                      $("#frmConsultaSala #BtnPesquisar").click()
                      $("#WinCadastroSala").data("kendoWindow").close()
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

                $("#WinCadastroSala").data("kendoWindow").refresh(
                  {
                    url: "controller/sala/ctrSala.php?action=incluir"
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
                $("#WinCadastroSala").data("kendoWindow").close()
              }
            }

          ]
        }
      ]
    })
    //-----------------------------------------------------------------------------------------//

    //-----------------------------------------------------------------------------------------//
    //Ações diversas da tela de cadastro
    //-----------------------------------------------------------------------------------------//
    if($("#frmCadastroSala #idSala").val() != ""){
      $("#frmCadastroSala #BarAcoes").data("kendoToolBar").enable("#BtnExcluir")
    }
    $("#WinCadastroSala").data("kendoWindow").center().open();
    //-----------------------------------------------------------------------------------------//
	})

</script>

<div class="k-form">
  <form id="frmCadastroSala" style="height: 100%;">
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td style="text-align: right; width: 120px;">Id:</td>
        <td>
          <input type="text" id="idSala" name="idSala" tabindex="-1" class="k-textbox k-input-disabled" readonly="readonly" value="<?php echo $objTbSala->Get("idsala") ?>">
        </td>
      </tr>
    </table>
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px;">Nome:</td>
        <td>
          <input type="text" id="nmSala" name="nmSala" tabindex="1" class="k-textbox" style="width: 600px;" value="<?php echo $objTbSala->Get("nmsala") ?>">
        </td>
      </tr>
    </table>
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px;">Localizacao:</td>
        <td>
          <input type="text" id="dsLocalizacao" name="dsLocalizacao" tabindex="2" class="k-textbox" style="width: 600px;" value="<?php echo $objTbSala->Get("dslocalizacao") ?>">
        </td>
      </tr>
    </table>
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px;">Capacidade de Pessoas:</td>
        <td>
          <input id="nrCapacidade" name="nrCapacidade" tabindex="3" style="width: 100px;" value="<?php echo $objTbSala->Get("nrcapacidade") ?>">
        </td>
      </tr>
    </table>
      <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px; vertical-align: top;">Recursos Disponiveis:</td>
        <td>
          <textarea id="txRecursosDisponiveis" name="txRecursosDisponiveis" tabindex="4" class=" k-textbox" style="width: 600px; height: 80px; resize:none"><?php echo $objTbSala->Get("txrecursosdisponiveis") ?></textarea>
        </td>
      </tr>
    </table>

    <div id="BarAcoes"></div>

  </form>
</div>
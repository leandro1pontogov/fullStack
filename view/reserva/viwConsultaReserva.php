<?php
@header("Content-Type: text/html; charset=ISO-8859-1", true);
?>

<script>
	$(function (){

		$("#frmConsultaReserva #BtnSala").kendoButton(
      {
        spriteCssClass: "k-pg-icon k-i-l1-c2",
        enable: <?=$blSalaSelecionada ? "false" : "true" ?>,
        click: function(){
          OpenWindow(true, "ConsultaSala", "controller/sala/ctrSala.php?action=winConsulta", "Janela Consulta Sala", "frmConsultaReserva")
        }
      }
    );
		$("#frmConsultaReserva #BtnColaborador").kendoButton(
      {
        spriteCssClass: "k-pg-icon k-i-l1-c2",
        enable: <?=$blColaboradorSelecionado ? "false" : "true" ?>,
        click: function(){
          OpenWindow(true, "ConsultaColaborador", "controller/colaborador/ctrColaborador.php?action=winConsulta", "Janela Consulta Colaborador", "frmConsultaReserva")
        }
      }
    );
		
		var arrDataSource = [
			{
				name: "idreserva",
				type: "integer",
				label: "Id Reserva",
				visibleFilter: 'true',
				orderFilter: '2',

				orderGrid: '1',
				widthGrid: '70',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '70',
				positionPreview: '1',
			},
      {
				name: "idsala",
				type: "integer",
				label: "Id Sala",
				visibleFilter: 'true',
				orderFilter: '3',

				orderGrid: '2',
				widthGrid: '70',
				hiddenGrid: 'true',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '70',
				positionPreview: '2',
			},
      {
				name: "nmsala",
				type: "string",
				label: "Nome Sala",
				visibleFilter: 'true',
				orderFilter: '4',

				orderGrid: '3',
				widthGrid: '70',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '120',
				positionPreview: '3',
			},
      {
				name: "idcolaboradorsala",
				type: "integer",
				label: "Id Colaborador",
				visibleFilter: 'false',
				orderFilter: '4',

				orderGrid: '3',
				widthGrid: '70',
				hiddenGrid: 'true',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '70',
				positionPreview: '3',
			},
      {
				name: "nmcolaboradorsala",
				type: "string",
				label: "Nome Colaborador",
				visibleFilter: 'false',
				orderFilter: '4',

				orderGrid: '3',
				widthGrid: '70',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '120',
				positionPreview: '3',
			},
			{
				name: "dtdata",
				type: "date",
				label: "Data",
				visibleFilter: 'false',
				orderFilter: '5',

				orderGrid: '4',

				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '600',
				positionPreview: '4',
			},
			{
				name: "hrinicio",
				type: "time",
				label: "Hora Inicio",
				visibleFilter: 'false',
				orderFilter: '6',

				orderGrid: '5',
				widthGrid: '',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '600',
				positionPreview: '5',
			},
			{
				name: "hrfim",
				type: "time",
				label: "Hora Fim",
				visibleFilter: 'false',
				orderFilter: '7',

				orderGrid: '6',
				widthGrid: '150',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '70',
				positionPreview: '6'
			}
		]

		//----------------------------------------------------------------------------------------------------------------//
		//Configura tela para utilizar o Splitter
		//----------------------------------------------------------------------------------------------------------------//
		arrDataSource = LoadConfigurationQuery(arrDataSource, "ConsultaReserva");
		//----------------------------------------------------------------------------------------------------------------//

		
		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando os campos combo da pesquisa
		//----------------------------------------------------------------------------------------------------------------//
		createPgFilter(arrDataSource, "ConsultaReserva");
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Área de botões de ações
		//----------------------------------------------------------------------------------------------------------------//
		$("#frmConsultaReserva #BarAcoes").kendoToolBar({
			items: [
				{
					type: "spacer",
				},
				{
					type: "buttonGroup",
					buttons: [
						{
							id: "BtnIncluir",
							spriteCssClass: "k-pg-icon k-i-l1-c1",
							text: "Incluir",
							group: "actions",
							attributes: { tabindex: "30" },
							click: function () {
								OpenWindow(true, "CadastroReserva", "controller/reserva/ctrReserva.php?action=incluir", "Janela Cadastro Reserva")
							}
						},
						{
							id: "BtnEditar",
							spriteCssClass: "k-pg-icon k-i-l1-c3",
							text: "Editar",
							group: "actions",
							enable: false,
							attributes: { tabindex: "31" },
							click: function () {

								let grid = $("#GrdConsultaReserva").data("kendoGrid")
								let ReservaSelecionado = grid.dataItem(grid.select())

								OpenWindow(true, "CadastroReserva", "controller/reserva/ctrReserva.php?action=editar&idReserva=" + ReservaSelecionado.idreserva, "Janela Cadastro Reserva")
							}
						},
						{
							id: "BtnFechar",
							spriteCssClass: "k-pg-icon k-i-l1-c4",
							text: "Fechar",
							group: "actions",
							attributes: { tabindex: "32" },
							click: function () {
								$("#WinConsultaReserva").data("kendoWindow").close();
							}
						},
					]
				}
			],
		});
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Filtro extra da consulta
		//----------------------------------------------------------------------------------------------------------------//
		function getExtraFilter(){
			let arrFilds = LoadFilterSplitter("ConsultaReserva", arrDataSource)
			return arrFilds;
		}
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando o DataSource na consulta
		//----------------------------------------------------------------------------------------------------------------//
		let DtsConsultaReserva = new kendo.data.DataSource({
			pageSize: 100,
			serverPaging: true,
			serverFiltering: true,
			serverSorting: true,
			transport: {
				read: {
					url: "controller/reserva/ctrReserva.php",
					type: "GET",
					dataType: "json",
					data: function(){
						return {
							action: "ListReserva",
							filters: getExtraFilter()
						}					
					}
				}
			},
			schema: {
				data: "jsnReserva",
				total: "jsnTotal",
				model:{
					fields: getModelDataSource(arrDataSource)
				},
				errors: "error"
			}
		})
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando o botão de consulta
		//----------------------------------------------------------------------------------------------------------------//
		$("#frmConsultaReserva #BtnPesquisar").kendoButton({
			click: function(){
				DtsConsultaReserva.filter(getExtraFilter())
				DtsConsultaReserva.read()
			}
		})
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando o Grid na consulta
		//----------------------------------------------------------------------------------------------------------------//
		$("#frmConsultaReserva #GrdConsultaReserva").kendoGrid({
			pdf: SetPdfOptions("Listagem de Reservas"),
			pdfExport: function () {
				tituloPdfExport = "Listagem de Reservas"
			},
			dataSource: DtsConsultaReserva,
			height: getHeightGridQuery("ConsultaReserva"),
			columns: getColumnsQuery(arrDataSource),
			selectable: "row",
			resizable: true,
			reorderable: true,
			navigatable: true,
			columnMenu: true,
			filterable: true,
			sortable: {
				mode: "multiple",
				allowUnsort: true
			},
			sort: function () {
			},
			change: function () {
				$("#frmConsultaReserva #BarAcoes").data("kendoToolBar").enable("#BtnEditar")
			},
			dataBound: function () {
				LoadGridExportActions("frmConsultaReserva", "GrdConsultaReserva", true)
			},
			pageable: {
				pageSizes: [100, 300, 500, "all"],
				numeric: false,
				input: true
			},
			columns: getColumnsQuery(arrDataSource),
			columnShow: function (e) {
				setWidthOnShowColumnGrid(e, 'ConsultaReserva');
			},
			columnHide: function (e) {
				setWidthOnHideColumnGrid(e, 'ConsultaReserva');
			},
			filter: function (e) {
				mountFilteredScreen('filterColumn', e, 'ConsultaReserva', arrDataSource, DtsConsultaReserva, getExtraFilter());
			}
		});
		$("#frmConsultaReserva #GrdConsultaReserva").on("dblclick", "tbody>tr", function(){
			$("#frmConsultaReserva #BtnEditar").click();
		})
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Ação para abrir a tela de consulta
		//----------------------------------------------------------------------------------------------------------------//
		$("#WinConsultaReserva").data("kendoWindow").open();
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Cria a tela de visualização do item do grid na consulta e faz outras coisas...
		//----------------------------------------------------------------------------------------------------------------//
		createScreenPreview(arrDataSource, "ConsultaReserva");
		//----------------------------------------------------------------------------------------------------------------//

	})
</script>

<div class="k-form">
	<form id="frmConsultaReserva" style="height: 100%;">
		<div id="splConsulta">
			<div id="splHeader">
				<div class="k-bg-blue screen-filter-content">
					<table width="100%" border="0" cellspacing="0" role="presentation">
						<tr>
							<td style="width: 120px; text-align: right;">Sala:</td>
							<td style="padding-left: 3px;">
								<input type="text" tabindex="-1" id="idSala" name="idSala" class="k-input-disabled k-textbox" readonly="readonly" style="width: 60px; background-color: #e8e8e8;" value="<?php echo $objTbReserva->Get("idsala")?>">
								<span id="BtnSala" style="cursor: pointer; width: 24px; height: 24px;" title="consultarSalas"></span>
								<input type="text" tabindex="-1" id="nmSala" name="nmSala" class="k-input-disabled k-textbox" readonly="readonly" style="width: 430px; background-color: #e8e8e8;">
							</td>
						</tr>
						<tr>
							<td style="width: 120px; text-align: right;">Colaborador:</td>
							<td style="padding-left: 3px;">
								<input type="text" tabindex="-1" id="idColaborador" name="idColaborador" class="k-input-disabled k-textbox" readonly="readonly" style="width: 60px; background-color: #e8e8e8;">
								<span id="BtnColaborador" style="cursor: pointer; width: 24px; height: 24px;" title="consultarSalas"></span>
								<input type="text" tabindex="-1" id="nmColaborador" name="nmColaborador" class="k-input-disabled k-textbox" readonly="readonly" style="width: 430px; background-color: #e8e8e8;">
							</td>
						</tr>
					</table>
					<table>
						<tr>
							<td style="width: 120px;text-align: right;vertical-align: top;padding-top: 6px;">
								Filtro(s):
							</td>

							<td>
								<div id="fltConsultaReserva" style="width: auto;"></div>
							</td>

							<td style="vertical-align: bottom;padding-bottom: 5px;">
								<span id="BtnPesquisar" style="cursor: pointer;width: 100px;height: 24px;"
									title="Pesquisar" data-role="button" class="k-button k-button-icon" role="button"
									aria-disabled="false" tabindex="29">
									<span class="k-sprite k-pg-icon k-i-l1-c2"
										style="margin: 0 auto; text-align: center;"></span>
									<span style="margin: 0 auto; margin-right: 3px;">Pesquisar</span>
								</span>

								<span id="BtnAddFilter"
									style="cursor: pointer;width: 21px !important;height: 21px !important"
									title="Adicionar Filtro" data-role="button" class="k-button k-button-icon"
									role="button" aria-disabled="false" tabindex="">
									<span class="k-sprite k-pg-icon k-i-l1-c1"
										style="margin: 0 auto;margin-top: 1.4px;"></span>
								</span>
							</td>
						</tr>
					</table>

					<div id="BarAcoes" style="text-align: right;height: 28px;"></div>
				</div>
			</div>
			<div id="splMiddle">
				<div id="GrdConsultaReserva" data-use-state-screen="true">

				</div>
			</div>
			<div id="splFooter">
				<div id="bottonConsultaReserva">
					<div id="tabStripConsultaReserva">
						<ul>
							<li id="tabDadosGerais" class="k-state-active"><label>Detalhes</label></li>
						</ul>
						<div id="tabDadosGeraisVisualizacaoConsultaReserva"></div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
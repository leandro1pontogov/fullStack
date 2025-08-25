<?php
@header("Content-Type: text/html; charset=ISO-8859-1", true);
?>

<script>
	$(function (){
		
		var arrDataSource = [
			{
				name: "idsala",
				type: "integer",
				label: "Id",
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
				name: "nmsala",
				type: "string",
				label: "Nome",
				visibleFilter: 'true',
				orderFilter: '3',

				orderGrid: '2',

				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '600',
				positionPreview: '2',
			},
			{
				name: "dslocalizacao",
				type: "string",
				label: "Localizacao",
				visibleFilter: 'true',
				orderFilter: '4',

				orderGrid: '3',
				widthGrid: '',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '600',
				positionPreview: '3',
			},
			{
				name: "nrcapacidade",
				type: "integer",
				label: "Capacidade",
				visibleFilter: 'true',
				orderFilter: '5',

				orderGrid: '4',
				widthGrid: '200',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '300',
				positionPreview: '4'
			},
			{
				name: "txrecursosdisponiveis",
				type: "string",
				label: "Recursos Disponiveis",
				visibleFilter: 'true',
				orderFilter: '6',

				orderGrid: '5',
				widthGrid: '150',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '70',
				positionPreview: '7'
			}
		]

		//----------------------------------------------------------------------------------------------------------------//
		//Configura tela para utilizar o Splitter
		//----------------------------------------------------------------------------------------------------------------//
		arrDataSource = LoadConfigurationQuery(arrDataSource, "ConsultaSala");
		//----------------------------------------------------------------------------------------------------------------//

		
		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando os campos combo da pesquisa
		//----------------------------------------------------------------------------------------------------------------//
		createPgFilter(arrDataSource, "ConsultaSala");
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Área de botões de ações
		//----------------------------------------------------------------------------------------------------------------//
		$("#frmConsultaSala #BarAcoes").kendoToolBar({
			items: [
				{
					type: "spacer"
				},
				{
					type: "buttonGroup",
					buttons: [
						{
							id: "BtnAcessoRapido",
							spriteCssClass: "k-pg-icon k-i-l2-c10",
							text: "Acesso Rapido <span class='k-icon k-i-arrow-s' style='width:12px'></span>",
							group: "actions",
							enable: false,
							attributes: { tabindex: "34" }
						}
					]
				},
				{
					type: "buttonGroup",
					buttons: [
						{
							id: "BtnReservar",
							spriteCssClass: "k-pg-icon k-i-l3-c5",
							text: "Reservar",
							group: "actions",
							enable: false,
							attributes: { tabindex: "34" },
							click: function () {
								var GrdConsultaSala = $("#frmConsultaSala #GrdConsultaSala").data("kendoGrid");
								var RstSala = GrdConsultaSala.dataItem(GrdConsultaSala.select());

								OpenWindow(true, "CadastroReserva", "controller/reserva/ctrReserva.php?action=incluir&idSala=" + RstSala.idsala , "Janela Cadastro Reserva")
							}
						}
					]
				},
				{
					type: "buttonGroup",
					buttons: [
						{
							id: "BtnSelecionar",
							spriteCssClass: "k-pg-icon k-i-l9-c4",
							text: "Selecionar",
							group: "actions",
							enable: false,
							attributes: { tabindex: "33" },
							click: function () {
								var GrdConsultaSala = $("#frmConsultaSala #GrdConsultaSala").data("kendoGrid");
								var RstSala = GrdConsultaSala.dataItem(GrdConsultaSala.select());

								$("<?=$frmResult?> #idSala").val(RstSala.idsala).change();
								$("<?=$frmResult?> #nmSala").val(RstSala.nmsala).change();

								$("#WinConsultaSala").data("kendoWindow").close();
							}
						}
					]
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
								OpenWindow(true, "CadastroSala", "controller/sala/ctrSala.php?action=incluir", "Janela Cadastro Sala")
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

								let grid = $("#GrdConsultaSala").data("kendoGrid")
								let SalaSelecionado = grid.dataItem(grid.select())

								OpenWindow(true, "CadastroSala", "controller/sala/ctrSala.php?action=editar&idSala=" + SalaSelecionado.idsala, "Janela Cadastro Sala")
							}
						},
						{
							id: "BtnFechar",
							spriteCssClass: "k-pg-icon k-i-l1-c4",
							text: "Fechar",
							group: "actions",
							attributes: { tabindex: "32" },
							click: function () {
								$("#WinConsultaSala").data("kendoWindow").close();
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
			let arrFilds = LoadFilterSplitter("ConsultaSala", arrDataSource)
			return arrFilds;
		}
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando o DataSource na consulta
		//----------------------------------------------------------------------------------------------------------------//
		let DtsConsultaSala = new kendo.data.DataSource({
			pageSize: 100,
			serverPaging: true,
			serverFiltering: true,
			serverSorting: true,
			transport: {
				read: {
					url: "controller/sala/ctrSala.php",
					type: "GET",
					dataType: "json",
					data: function(){
						return {
							action: "ListSala",
							filters: getExtraFilter()
						}					
					}
				}
			},
			schema: {
				data: "jsnSala",
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
		$("#frmConsultaSala #BtnPesquisar").kendoButton({
			click: function(){
				DtsConsultaSala.filter(getExtraFilter())
				DtsConsultaSala.read()
			}
		})
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando o Grid na consulta
		//----------------------------------------------------------------------------------------------------------------//
		$("#frmConsultaSala #GrdConsultaSala").kendoGrid({
			pdf: SetPdfOptions("Listagem de Salas"),
			pdfExport: function () {
				tituloPdfExport = "Listagem de Salas"
			},
			dataSource: DtsConsultaSala,
			height: getHeightGridQuery("ConsultaSala"),
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
				$("#frmConsultaSala #BarAcoes").data("kendoToolBar").enable("#BtnEditar");
				$("#frmConsultaSala #BarAcoes").data("kendoToolBar").enable("#BtnReservar");
				$("#frmConsultaSala #BarAcoes").data("kendoToolBar").enable("#BtnAcessoRapido");
				if("<?php echo $frmResult?>" != ""){
					$("#frmConsultaSala #BarAcoes").data("kendoToolBar").enable("#BtnSelecionar")
				}
			},
			dataBound: function () {
				LoadGridExportActions("frmConsultaSala", "GrdConsultaSala", true)
			},
			pageable: {
				pageSizes: [100, 300, 500, "all"],
				numeric: false,
				input: true
			},
			columns: getColumnsQuery(arrDataSource),
			columnShow: function (e) {
				setWidthOnShowColumnGrid(e, 'ConsultaSala');
			},
			columnHide: function (e) {
				setWidthOnHideColumnGrid(e, 'ConsultaSala');
			},
			filter: function (e) {
				mountFilteredScreen('filterColumn', e, 'ConsultaSala', arrDataSource, DtsConsultaSala, getExtraFilter());
			}
		});
		$("#frmConsultaSala #GrdConsultaSala").on("dblclick", "tbody>tr", function(){
			$("#frmConsultaSala #BtnEditar").click();
		})
		//----------------------------------------------------------------------------------------------------------------//

		if($("#menuAcessoRapidoSala").data("kendoContextMenu")){
			$("#menuAcessoRapidoSala").data("kendoContextMenu").destroy()
		}

		$("#frmConsultaSala #menuAcessoRapidoSala").kendoContextMenu({
			target: "#frmConsultaSala #BtnAcessoRapido",
			alignToAnchor: true,
			showOn: "click",
			select: function(e){
				var GrdConsultaSala = $("#frmConsultaSala #GrdConsultaSala").data("kendoGrid");
				var RstSala = GrdConsultaSala.dataItem(GrdConsultaSala.select());

				if(e.item.id == "BtnSala"){
					//OpenWindow(false, "ConsultaReserva&idSala=" + RstSala.idsala + "&flAcessoRapido=S&")
					OpenWindow(false, "ConsultaReserva", "controller/reserva/ctrReserva.php?action=winConsulta&idSala=" + RstSala.idsala , "Janela Consulta Reserva")
				}
			}
		})

		//----------------------------------------------------------------------------------------------------------------//
		//Ação para abrir a tela de consulta
		//----------------------------------------------------------------------------------------------------------------//
		$("#WinConsultaSala").data("kendoWindow").open();
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Cria a tela de visualização do item do grid na consulta e faz outras coisas...
		//----------------------------------------------------------------------------------------------------------------//
		createScreenPreview(arrDataSource, "ConsultaSala");
		//----------------------------------------------------------------------------------------------------------------//

	})
</script>

<div class="k-form">
	<form id="frmConsultaSala" style="height: 100%;">
		<div id="splConsulta">
			<div id="splHeader">
				<div class="k-bg-blue screen-filter-content">
					<table>
						<tr>
							<td style="width: 120px;text-align: right;vertical-align: top;padding-top: 6px;">
								Filtro(s):
							</td>

							<td>
								<div id="fltConsultaSala" style="width: auto;"></div>
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
				<div id="GrdConsultaSala" data-use-state-screen="true">

				<ul id="menuAcessoRapidoSala">
					<li id="BtnSala"><span class="k-pg-icon k-i-l4-c9"></span>&nbsp;Salas</li>
				</ul>

				</div>
			</div>
			<div id="splFooter">
				<div id="bottonConsultaSala">
					<div id="tabStripConsultaSala">
						<ul>
							<li id="tabDadosGerais" class="k-state-active"><label>Detalhes</label></li>
						</ul>
						<div id="tabDadosGeraisVisualizacaoConsultaSala"></div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
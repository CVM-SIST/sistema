<form class="form-horizontal ng-pristine ng-valid" id='contracargos_deb' method="post" >
	<div id="paso2" style="margin-top:30px;">		
		   <div class="well">
			<legend>Registrar Contracargo</legend>

				<div class="form-group" id="tarj">
					<label for="Tarjeta" class="col-sm-4">Tarjeta</label>
						<div class="col-sm-8">
							<label class="col-sm-4"><?=$tarjeta->descripcion?></label>

                                                <input type="hidden" name="periodo" id="periodo" value="<?=$periodo?>">
                                                <input type="hidden" name="id_marca" id="id_marca" value="<?=$id_marca?>">
                                                <input type="hidden" name="id_cabecera" id="id_cabecera" value="<?=$id_cabecera?>">

						</div>
				</div>
                        	<div class="form-group" id="fechadeb">
                                	<label for="fechadeb" class="col-sm-4">Fecha Debito</label>
                                		<div class="col-sm-8">
                                        		<label class="col-sm-4"><?=$fecha_debito?></label>
                                		</div>
                        	</div>
                        	<div class="form-group" id="cant">
                                	<label for="cant" class="col-sm-4">Cantidad Generada</label>
                                		<div class="col-sm-8">
                                        		<label align="right" class="col-sm-4"><?=$cant_generada?></label>
                                		</div>
                        	</div>
                        	<div class="form-group" id="impo">
                                	<label for="impo" class="col-sm-4">Importe Generado</label>
                                		<div class="col-sm-8">
                                        		<label align="right" class="col-sm-4"><?=$total_generado?></label>
                                		</div>
                        	</div>
                        	<div class="form-group" id="4tarj">
					<label for="nrotarjeta" class="col-sm-4">Nro Tarjeta (ult 4)</label>
	                			<div class="col-sm-8">
		               				<input name="nrotarjeta" id="nrotarjeta" type="number" min="0" max="9999" style="width:200px;" >
	                			</div>
				</div> 
                                <div class="form-group" id="rng">
                                        <label for="nrorenglon" class="col-sm-4">Nro Renglon</label>
                                                <div class="col-sm-8">
                                                        <input name="nrorenglon" id="nrorenglon" type="number" min="0" max="999" style="width:200px;" >
                                                </div>
                                </div>
                        	<div class="form-group" id="impo">
                                        <label for="importe" class="col-sm-4">Importe</label>
                                        	<div class="col-sm-8">
                                                	<input name="importe" id="importe" type="number" style="width:200px;" >
                                        	</div>
                        	</div>


		</div>	
		<div align="center" style="width:100%">
			<div class="form-group">
				<div class="col-sm-3">
                        		<button class="btn-success" data-text="do" data-action="<?=$baseurl?>admin/debtarj/contracargo/do" > Carga Contracargo </button>
				</div>
				<div class="col-sm-3">
                        		<button class="btn-success" data-text="do-final" data-action="<?=$baseurl?>admin/debtarj/contracargo/do-final" > Cierra Contracargos </button>
				</div>
				<div class="col-sm-6">
                                        <label for="cant_rech" class="col-sm-2">Cantidad Rechazados</label>
                                        <label align="right" class="col-sm-2"><?=$cant_rechazados?></label>
                                        <label for="impo_rech" class="col-sm-2">Importe Rechazados</label>
                                        <label align="right" class="col-sm-2"><?=$impo_rechazados?></label>
				</div>
			</div>
		</div>
		</form>
	</div>			        
	<div class="clearfix"></div>
</form>

<div id="rp-detalles">
	<div class="panel panel-default table-dynamic">
    	<div class="panel-heading"><strong><span class="fa fa-user"></span> Detalles de Contracargos: <?=$tarjeta->descripcion?> <?=$fecha_debito?></strong></div>
		<table class="table table-bordered table-striped table-responsive table-resumen">
			<thead>
				<tr>
					<th><div class="th-resumen">Renglon</div></th>
					<th><div class="th-resumen">SID</div></th>
					<th><div class="th-resumen">Apellido Nombre</div></th>
					<th><div class="th-resumen">Nro Tarjeta</div></th>
					<th><div class="th-resumen">Importe</div></th>
				</tr>
			</thead>
			<tbody id="reg-resumen">
				<?
				foreach ($tabla as $fila) {				
	                                $largo = strlen($fila->nro_tarjeta);
					$nrotarj = substr($fila->nro_tarjeta,0,4)."****".substr($fila->nro_tarjeta,$largo-4,$largo);
				?>
				<tr>
					<td><?=$fila->nro_renglon?></td>
					<td><?=$fila->sid?></td>
					<td><?=$fila->apynom?></td>
					<td><?=$nrotarj?></td>
					<td><?=$fila->importe?></td>
				</tr>
				<?
				}
				?>											
			</tbody>
			<style type="text/css">
			.socios_desc{max-height: 24px; margin-top: 5px; overflow: hidden; float: left; width: 70%;}
			.ver_mas{float: right; width: 30%;}
			</style>
		</table>
	</div>
</div>

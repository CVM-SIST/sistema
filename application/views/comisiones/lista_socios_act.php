<div class="page page-chart" data-ng-controller="MorrisChart">
    <div class="row">

	<section class="page page-profile">
        <div class="panel-heading"><strong><span class="fa fa-question-circle"></span> ACCESO SUB-COMISION <?=$nombre_comision?> </strong></div>

    	<div class="panel panel-default">
        	<div class="panel-body">

		<form class="form-horizontal ng-pristine ng-valid" action="#" method="post" id="comsoc-activ-form" enctype="multipart/form-data">


                	<div class="form-group col-lg-18">
                     		<label for="" class="col-sm-9">Actividad</label>
                     		<div class="col-sm-5">
                       			<span class=" ui-select">
                       			<select name="actividad" id="actividad" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                                        <option value="0" <? if($id_actividad == 0){ echo 'selected'; } ?>> Total Comision</option>
                       			<? foreach ( $actividades as $actividad ) { ?>
				                        <option value="<?=$actividad->Id?>" <? if($actividad->Id == $id_actividad){ echo 'selected'; } ?>><?=$actividad->nombre?></option>
                        			<?}?>
                       			</select>
                       			</span>
                     		</div>
                	</div>

                        <div class="form-group col-lg-18">
                                <label for="" class="col-sm-9">Estado</label>
                                <div class="col-sm-5">
                                        <span class=" ui-select">
                                        <select name="estado" id="estado" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                                        	<option value="-1" <? if($estado < 0){ echo 'selected'; } ?>>Todos los socios</option>
                                                <option value="0" <? if($estado == 0){ echo 'selected'; } ?>>Socios Activos</option>
                                                <option value="1" <? if($estado == 1){ echo 'selected'; } ?>>Socios Suspendidos</option>
                                        </select>
                                        </span>
                                </div>
                        </div>

	
                	<div class="form-group col-lg-18">
                     		<div class="col-sm-5">
                                       <button class="btn btn-success">Procesar</button> <i id="reg-cargando" class="fa fa-spinner fa-spin hidden"></i>
                     		</div>
                     		<div class="col-sm-5">
					<? if ( $mora == 1 ) { $xlink="lista_morosos"; } else { $xlink="lista_socios_act"; } ?>
					<a class="btn btn-primary" href="<?=$baseurl?>comisiones/<?=$xlink?>/excel/<?=$id_actividad?>">Baja a Excel</a>
                     		</div>

                	</div>
		</form>



		<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="socioact_table">
                <thead>
                <tr>
                      <td>Actividad</td>
                      <td align="right">SID</td>
                      <td align="right">Apellido y Nombre</td>
                      <td align="right">DNI</td>
                      <td align="right">Categoria</td>
                      <td align="right">Domicilio</td>
                      <td align="right">Email</td>
                      <td align="right">Estado</td>
                      <td align="right">Saldo</td>
                      <td align="right">Ultimo Pago</td>
                      <td align="right">Observaciones</td>
                      <td align="right">Opciones</td>
                </tr>
                </thead>

	    		<tbody>

	    		<?
	    		if($socioact_tabla){
	    			foreach ($socioact_tabla as $socio) {	    	
					switch ( $socio->suspendido ) {
						case 0: $xestado="ACTIVO"; break;
						case 1: $xestado="SUSPENDIDO"; break;
						default: $xestado="XXX"; break;
					}
					if ( $socio->beca == "normal" ) {
						if ( $socio->federado == 1 ) { 
							$xobserv = "Sin Beca - Federado";
						} else {
							$xobserv = "Sin Beca - NO Federado";
						}
					} else {
						if ( $socio->federado == 1 ) { 
							$xobserv = $socio->beca."- Federado";
						} else {
							$xobserv = $socio->beca."- NO Federado";
						}
					}
					$categ = substr($socio->nacimiento,0,4);
	    		?>
				<tr>				
					<td><?=$socio->aid."-".$socio->descr_act?></td>
					<td align="right"><?=$socio->Id?></td>
					<td align="right"><?=$socio->apellido.", ".$socio->nombre?></td>
					<td align="right"><?=$socio->dni?></td>
					<td align="right"><?=$categ?></td>
					<td align="right"><?=$socio->domicilio?></td>
					<td align="right"><?=$socio->mail?></td>
					<td align="right"><?=$xestado?></td>
					<td align="right"><?=$socio->saldo?></td>
					<td align="right"><?=$socio->ult_pago?></td>
					<td align="right"><?=$xobserv?></td>
					<td align="right"><a href="<?=$baseurl?>comisiones/resumen/<?=$socio->Id?>">Resumen</a></td>
				</tr>
				<?
					}
					}
				?>
			</tbody>
		</table>

            </form>
       		</div>
    	</div>
	</section>
    </div>
</div>    

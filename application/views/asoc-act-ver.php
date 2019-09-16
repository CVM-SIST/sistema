<div class="page page-charts ng-scope" data-ng-controller="morrisChartCtrl">
    <div class="row">

	<section class="page page-profile">
    	<div class="panel panel-default">

		<div class="panel-body">
			<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="socios_table">
                        	<thead>
                           		<tr>
                                	<th>Cantidad Total Socios</th>
                                	<th>Cantidad Socios Activos</th>
                                	<th>Cantidad Socios Desactualizados segun filtro</th>
                           		</tr>
                        	</thead>
                        	<tbody>
					<td align="right"><?=$cant_socios?></td>
					<td align="right"><?=$cant_socios_activos?></td>
					<td align="right"><?=$cant_socios_filtro?></td>
                        	</tbody>
			</table>

		</div>
        	<div class="panel-body">
		<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="socios_table">
			<thead>
	        	   <tr>
	            		<th>SID</th>
	            		<th>DNI</th>
	            		<th>Apellido</th>
	            		<th>Nombre</th>	                      
	            		<th>Email</th>
	            		<th>Telefono</th>
	            		<th>Celular</th>
	            		<th>Ult.Actualizacion</th>
	        	   </tr>
	    		</thead>
	    		<tbody>
	    		<?
	    		if($socios){
	    			foreach ($socios as $socio) {	    	
	    		?>
				<tr>				
					<td><a id="act_datos" href="<?=$baseurl?>admin/socios/act-datos-socio/<?=$actividad?>/<?=$email?>/<?=$telefono?>/<?=$socio->Id?>"><?=$socio->Id?></a></td>
					<td align="right"><?=$socio->dni?></td>
					<td align="right"><?=$socio->apellido?></td>
					<td align="right"><?=$socio->nombre?></td>
					<td align="right"><?=$socio->mail?></td>
					<td align="right"><?=$socio->telefono?></td>
					<td align="right"><?=$socio->celular?></td>
					<td align="right"><?=$socio->update_ts?></td>
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

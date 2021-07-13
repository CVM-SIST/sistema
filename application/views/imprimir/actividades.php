<div class="container" style="margin-top:50px;">
    <div class="starter-template">
        <h1>Actividades</h1>
        <div id="actividades_print">
		<div class="col-sm-5">
			<label>Comisiones:</label>
			<select class="form-control" id="comisiones_select">

				<option value="" >Seleccione Comisión</option>
				<? foreach ($comisiones as $comision) {
				?>
					<option value="<?=$comision->id?>" <? if($comision_sel == $comision->id){ echo 'selected'; } ?>><?=$comision->descripcion?></option>
				<?
				}
				?>
			</select>
		</div>

	        <div class="col-xs-6">
	        	<label>Actividades:</label>
	        	<select class="form-control" id="actividades_select"> 
	        		<option value="">Seleccione Actividad</option>
	        		<?
	        		foreach ($actividades as $actividad) {        		
	        		?>
	        		<option value="<?=$actividad->Id?>"  <? if($actividad_sel == $actividad->Id){ echo 'selected'; } ?>><?=$actividad->nombre?></option>
	        		<?
	        		}
	        		?>
	    		</select>
	    	</div>
	    	<div class="clearfix"></div>
	        </div>
    		<div id="listado_actividad" class="hidden">
		</div>
  	</div>
</div><!-- /.container -->

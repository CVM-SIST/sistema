<div class="container" style="margin-top:50px;">
	<div class="starter-template hidden-print">
		<h1>Morosos</h1>
		<div id="actividades_print">
			<div class="col-sm-5">
				<label>Comision</label>
				<select class="form-control" id="com_mora_select">

					<option value="" >Seleccione Comisión</option>
					<? foreach ($comisiones as $comision) { ?>
						<option value="<?=$comision->id?>" <? if($comision_sel == $comision->id){ echo 'selected'; } ?>><?=$comision->descripcion?></option>
					<? } ?>
				</select>
			</div>
			<div class="col-sm-5">
				<label>Actividad</label>
				<select class="form-control" id="act_mora_select" >

					<option value="">Seleccione Actividad</option>
					<option value="cs" <? if('cs' == $actividad_sel){ echo 'selected'; } ?>>Cuota Social</option>
					<?  foreach ($actividades as $actividad) { ?>
						<option value="<?=$actividad->Id?>" <? if($actividad->Id == $actividad_sel){ echo 'selected'; } ?>><?=$actividad->nombre?></option>
					<? } ?>
				</select>                    
			</div>				
			<div class="clearfix">
			</div>			
			<div id="listado_morosos" class="hidden">
			</div>
		</div>
	</div>
</div><!-- /.container -->

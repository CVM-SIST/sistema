<div class="container" style="margin-top:50px;">
	<div class="starter-template hidden-print">
		<h1>Cuenta Digital</h1>
		<?
		if($fecha1 && $fecha2){
			$value = 'value="'.date('d/m/Y',strtotime($fecha1)).' - '.date('d/m/Y',strtotime($fecha2)).'"';
		}else{
			$value = '';
		}
		?>
			<input class="form-control" name="daterange" <?=$value?>>		
	</div>
	<?
	if($ingresos){
	?>
	<div class="">
		<div class="pull-right hidden-print">
		    <button class="btn btn-info" onclick="print()"><i class="fa fa-print"></i> Imprimir</button>
		    <a href="<?=base_url()?>imprimir/cuentadigital_excel/<?=$fecha1?>/<?=$fecha2?>/" class="btn btn-success"><i class="fa fa-cloud-download"></i> Excel</a>
		</div>
		<h3 class="page-header">Pagos ingresados del <?=date('d/m/Y',strtotime($fecha1))?> al <?=date('d/m/Y',strtotime($fecha2))?></h3>
	</div>
	<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="ingresos_table">
	    <thead>
	        <tr>	            
	            <th>Fecha</th>
	            <th>Socio</th>
	            <th>Monto</th>	            
	            <th class="hidden-print">Operaciones</th>	           
	        </tr>
	    </thead>
		        
	    <tbody>
	    	<?
	    	$total = 0;	    	
	    	foreach ($ingresos as $ingreso) {   
	    		$total = $total + $ingreso->monto;
	    	?>
	        <tr>
	        	<td><?=$ingreso->fecha?> <?=$ingreso->hora?></td>	        	
	        	<td>
	        		#<?=$ingreso->sid?> - 
	        		<? 
	        		if(!$ingreso->socio){
	        		?>
					<label class="label label-danger">SOCIO INEXISTENTE</label>
	        		<?
	        		}else{
	        		?>
	        		<?=$ingreso->socio->nombre?> <?=$ingreso->socio->apellido?>
					<?
	        		}
	        		?>
	        	</td>
	        	<td>$ <?=$ingreso->monto?></td>	        	
	        	<td class="hidden-print"><a href="<?=base_url()?>admin/socios/resumen/<?=$ingreso->sid?>" class="btn btn-warning btn-sm" target="_blank"><i class="fa fa-external-link"></i> Ver Resumen</a></td>	        
	        </tr>
	        <?
	    	}
	    	?>
	    </tbody>
	    <tfoot>
	    	<td colspan="2">Total</td>
	    	<td colspan="2">$ <?=number_format($total,2)?></td>
	    </tfoot>
	</table>
	<?
	}
	?>
</div>

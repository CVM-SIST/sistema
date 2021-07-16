<meta charset="utf-8">
<style>
@media print {
    #actividades_print,#actividades_table_length,#actividades_table_filter,#actividades_table_info,#actividades_table_paginate{display:none;}
    td{ font-size: 12px;}
}
</style>
<div class="pull-left">
	<? if( $filtro == "cs" ) { ?>
			<h3>Cuota Social: <?=count($morosos)?> socios</h3>
	<? } else { 
		if ( $filtro > 0 ) { ?>
			<h3><?=$actividad->nombre?>: <?=count($morosos)?> socios</h3>
		<? } else { ?>
			<h3>Actividades de la Comisión <?=$comision->descripcion?>: <?=count($morosos)?> socios</h3>
		<? } 
	} ?>
</div>
<div class="pull-right hidden-print">
    <button class="btn btn-info" onclick="print()"><i class="fa fa-print"></i> Imprimir</button>
	<? if( $filtro == "cs" ) { ?>
    			<a href="<?=base_url()?>imprimir/morosos_excel/cs" class="btn btn-success"><i class="fa fa-cloud-download"></i> Excel</a>
	<? } else { 
		if ( $filtro > 0 ) { ?>
    			<a href="<?=base_url()?>imprimir/morosos_excel/<?=$actividad->Id?>" class="btn btn-success"><i class="fa fa-cloud-download"></i> Excel</a>
		<?  } else { ?>
    			<a href="<?=base_url()?>imprimir/morosos_excel/<?=-$comision->id?>" class="btn btn-success"><i class="fa fa-cloud-download"></i> Excel</a>
	<? } 
	} ?>
</div>

		<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="morosos_table">
		    <thead>
		        <tr>
		            <th>DNI</th>
		            <th>ID</th>
		            <th>Nombre</th>
		            <th>Teléfonos</th>
		            <th>Domicilio</th>
		            <th>Actividad</th>			            
		            <th>Estado Asoc</th>			            
		            <th>Deuda Cta Soc</th>			            
		            <th>UltPago Cuota</th>			            
		            <th>Deuda Actividad</th>			            
		            <th>UltPago Actividad</th>			            
		            <th class="hidden-print">Operaciones</th>	           
		        </tr>
		    </thead>
			        
		    <tbody>
		    	<?
		    	
		    	foreach ($morosos as $ingreso) {    	
				switch ( $ingreso['estado'] ) {
					case 1: $xestado="SUSP"; break;
					case 0: $xestado="ACTI"; break;
				}
		    	?>
		        <tr>			        	
		        	<td><?=$ingreso['dni']?></td>
		        	<td><?=$ingreso['sid']?></td>
		        	<td><?=$ingreso['apynom']?></td>
		        	<td><?=$ingreso['telefono']?></td>
		        	<td><?=$ingreso['domicilio']?></td>
		        	<td><?=$ingreso['actividad']?></td>
		        	<td><?=$xestado?></td>
		        	<td>$ <?=$ingreso['deuda_cuota']*-1?></td>			        	
		        	<td><?=date('d/m/Y',strtotime($ingreso['gen_cuota']))?></td>			        	
		        	<td>$ <?=$ingreso['deuda_activ']*-1?></td>			        	
		        	<td><?=$ingreso['gen_activ']?></td>			        	
		        	<td class="hidden-print"><a href="<?=base_url()?>admin/socios/resumen/<?=$ingreso['sid']?>" class="btn btn-warning btn-sm" target="_blank"><i class="fa fa-external-link"></i> Ver Resumen</a></td>	        
		        </tr>
		        <?
		    	}		    	
		    	?>
		    </tbody>
		</table>
<script type="text/javascript">
	$('#morosos_table').DataTable({
		"language": {
	 	   "url": "<?=base_url()?>scripts/ES_ar.txt"	 	   
		},
		"order": [[ 3, "desc" ]],
        "paging": false

	});
</script>

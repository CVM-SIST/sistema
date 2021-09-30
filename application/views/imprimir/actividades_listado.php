<meta charset="utf-8">
<style>
@media print {
    #actividades_print,#actividades_table_length,#actividades_table_filter,#actividades_table_info,#actividades_table_paginate{display:none;}
    td{ font-size: 12px;}
}
</style>
<div class="pull-left">
	<? if( $actividad != '' ) { ?>
		<h3><?=$actividad->nombre?>: <?=count($socios)?> socios</h3>
	<? } else { ?>
		<h3>Actividades de la Comisión <?=$comision->descripcion?>: <?=count($socios)?> socios</h3>
	<? } ?>
</div>
<div class="pull-right hidden-print">
    <button class="btn btn-info" onclick="print()"><i class="fa fa-print"></i> Imprimir</button>
	<? if( $actividad != '' ) { ?>
    		<a href="<?=base_url()?>imprimir/actividades_excel/<?=$actividad->Id?>" class="btn btn-success"><i class="fa fa-cloud-download"></i> Excel</a>
	<? } else { ?>
    		<a href="<?=base_url()?>imprimir/actividades_excel/<?=-$comision->id?>" class="btn btn-success"><i class="fa fa-cloud-download"></i> Excel</a>
	<? } ?>
</div>
<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="actividades_table">
    <thead>
        <tr>
            <th>Socio</th>
            <th>Tutor</th>
            <th>DNI</th>
            <th>Fecha de Nacimiento</th>            
            <th>Telefono</th>
            <th>Observaciones</th>
            <th>Meses Adeudados</th>
            <th>Monto Adeudado</th>
            <th>Deuda CS</th>
            <th>Deuda Seguro</th>
            <th>Deuda Actividad</th>
            <th>Estado</th>
            <th>Debito</th>
            <th class="hidden-print">Resumen</th>
        </tr>
    </thead>
    <tbody>
    	<?
    	foreach ($socios as $socio) {
    	?>
        <tr>
            <td>#<?=@$socio->Id?>-<?=@$socio->socio?></td>
            <td><?if(@$socio->tutor==0) { echo "0"; } else { echo @$socio->tutor; } ?></td>
            <td><?=@$socio->dni?></td>
            <td><?=date('d/m/Y',strtotime(@$socio->nacimiento))?></td>
            <td><?=@$socio->fijocel?></td>
            <td><?=$socio->observaciones?></td>
            <td>
                <?
                if($socio->deuda){                      
                    $hoy = new DateTime();
                    $d2 = new DateTime($socio->deuda->generadoel);                
                    $interval = $d2->diff($hoy);
                    $meses = $interval->format('%m');
                    if($meses > 0){
                        ?>
                        <div class="label label-danger">Debe <?=$meses?> <? if($meses > 1){ echo 'Meses';}else{echo 'Mes';} ?></div>                
                        <?
                    }else{
                        if( $hoy->format('%m') == $d2->format('%m')){
                        ?>
                        <div class="label label-warning">Mes Actual</div>
                        <?
                        }else{                    
                        ?>
                        <div class="label label-warning">Mes Actual</div>
                        <?                
                        }
                    }
                }else{
                    ?>
                    <label class="label label-success">Cuota al Día</label>
                    <?
                }                
                ?>
            </td>
            <td align="right">$ <?=number_format($socio->monto_adeudado*-1,2)?></td>
            <td align="right">$ <?=number_format($socio->deuda_cs*-1,2)?></td>
            <td align="right">$ <?=number_format($socio->deuda_seg*-1,2)?></td>
            <td align="right">$ <?=number_format($socio->deuda_act*-1,2)?></td>
            <td><? if($socio->suspendido == 1){ echo '<label class="label label-danger"><i class="fa fa-warning"></i> SUSPENDIDO</label>'; }else{ echo '<label class="label label-success"><i class="fa fa-check"></i> ACTIVO</label>'; } ?></td>
            <td> <?if ($socio->debito) {echo "Tiene Debito Tarj";} else {echo "Efectivo";} ?> </td>
            <td class="hidden-print"><a href="<?=base_url()?>admin/socios/resumen/<?=$socio->Id?>" class="btn btn-warning btn-sm" target="_blank"><i class="fa fa-external-link"></i> Ver Resumen</a></td>
        </tr> 
        <?
    	}
        ?>          
    </tbody>   
</table>
<?
function time_ago($date,$granularity=2) {
    $retval = '';
    $date = strtotime($date);
    $difference = time() - $date;
    $periods = array(
        'mes' => 2628000
        );

    foreach ($periods as $key => $value) {
        if ($difference >= $value) {
            $time = floor($difference/$value);
            $difference %= $value;
            $retval .= ($retval ? ' ' : '').$time.' ';
            $retval .= (($time > 1) ? $key.'es' : $key);
            $granularity--;
        }else{
            $retval = "1 Mes";
        }
        if ($granularity == '0') { break; }
    }
    return ''.$retval.'';      
}
?>
<script type="text/javascript">
	$('#actividades_table').DataTable({
		"language": {
	 	   "url": "<?=base_url()?>scripts/ES_ar.txt"	 	   
		},
		"order": [[ 3, "desc" ]],
        "paging": false

	});
</script>

<meta charset="utf-8">
<style>
@media print {
    #actividades_print,#actividades_table_length,#actividades_table_filter,#actividades_table_info,#actividades_table_paginate{display:none;}
    td{ font-size: 12px;}
}
</style>
<div class="container" style="margin-top:80px;">
<?
if($socios){
?>
<div class="pull-left">
    <h3>Becas | <?=date('d/m/Y H:i')?></h3>
</div>
<div class="pull-right hidden-print">
    <button class="btn btn-info" onclick="print()"><i class="fa fa-print"></i> Imprimir</button>
    <a href="<?=base_url()?>imprimir/becas_excel/" class="btn btn-success"><i class="fa fa-cloud-download"></i> Excel</a>
</div>
<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="socios_table">
    <thead>
        <tr>
            <th>Socio</th>
            <th>Nombre y Apellido</th>
            <th>Teléfono</th>
            <th>DNI</th>
            <th>Fecha de Nacimiento</th>
            <th>Fecha de Alta</th>
            <th>Actividad</th>        
            <th>Tipo Beca</th>        
            <th>%/$ Becado</th>        
            <th>Deuda CS</th>        
            <th>Deuda Actividad</th>        
            <th>Deuda Seguro</th>        
            <th class="hidden-print">Resumen</th>
        </tr>
    </thead>
            
    <tbody>
        <?
    	foreach ($socios as $socio) {    	
		switch ( $socio->monto_porcentaje ) {
		case 0:
			$tipo_beca = "BECA $";
			break;
		case 1:
			$tipo_beca = "BECA %";
			break;
		case 2:
			$tipo_beca = "BONIF SERV $";
			break;
		case 3:
			$tipo_beca = "BONIF SERV %";
			break;
		case 4:
			$tipo_beca = "BONIF COMPET $";
			break;
		case 5:
			$tipo_beca = "BONIF COMPET %";
			break;
		case 6:
			$tipo_beca = "BONIF HNO $";
			break;
		case 7:
			$tipo_beca = "BONIF HNO %";
			break;
		default:
			$tipo_beca = "XYX";
			break;
			}
    	?>
        <tr>
            <td><?=$socio->Id?></td>
            <td><?=$socio->nombre?> <?=$socio->apellido?></td>
            <td><?=$socio->fijocel?></td>
            <td><?=$socio->dni?></td>
            <td align="center"><?=date('d/m/Y',strtotime($socio->nacimiento))?></td>
            <td align="center"><?=date('d/m/Y',strtotime($socio->alta))?></td>
            <td><?=$socio->descr_actividad?></td>
            <td><?=$tipo_beca?></td>
            <td><?=$socio->descuento?><? if ( $socio->monto_porcentaje == 0 ) { echo '$'; } else { echo '%'; } ?></td>
            <td><?=$socio->deuda_cs?></td>
            <td><?=$socio->deuda_act?></td>
            <td><?=$socio->deuda_seg?></td>
            <td class="hidden-print"><a href="<?=base_url()?>admin/socios/resumen/<?=$socio->Id?>" class="btn btn-info btn-sm" target="_blank"><i class="fa fa-external-link"></i> Ver Resumen</a></td>
        </tr>
        <? }  ?>
    </tbody>
</table>
<? } ?>
</div>

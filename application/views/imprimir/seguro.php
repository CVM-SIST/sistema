<meta charset="utf-8">
<style>
@media print {
    #seguro_print,#seguro_table_length,#seguro_table_filter,#seguro_table_info,#seguro_table_paginate{display:none;}
    td{ font-size: 12px;}
}
</style>
<div class="container" style="margin-top:50px;">
    <div class="starter-template">
    <h3>Listado para Seguro - <?=date('d/m/Y')?></h3>
    </div>

	<div class="pull-right hidden-print">
    	<button class="btn btn-info" onclick="print()"><i class="fa fa-print"></i> Imprimir</button>
    	<a href="<?=base_url()?>imprimir/seguro_excel" class="btn btn-success"><i class="fa fa-cloud-download"></i> Excel</a>
	</div>

    <table id="seguro_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Actividad</th>
                <th>Nombre y Apellido</th>
                <th>DNI</th>
                <th>Fecha de Nacimiento</th>
            </tr>
        </thead>
    	        
        <tbody>
        	<?
        	foreach ($socios as $socio) {    	
        	?>
            <tr>
                <td><?=@$socio->descr_actividad?> </td>
                <td><?=@$socio->apynom?> </td>
                <td><?=@$socio->dni?></td>
                <td><?=@$socio->nacimiento?></td>
            </tr> 
            <?
        	}
            ?>          
        </tbody>   
    </table>
</div>

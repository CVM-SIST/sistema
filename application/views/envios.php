<div class="page page-charts ng-scope" data-ng-controller="morrisChartCtrl">
   <div class="row">
      <div class="col-md-12">
         <section class="panel panel-default">
            <div class="panel-heading">
               <span class="glyphicon glyphicon-th"></span> ENVIOS
               <div class="pull-right" style="margin-top:-7px;">
                  <a href="<?=base_url()?>admin/envios/nuevo" class="btn btn-success"><i class="fa fa-plus"></i> Nuevo Envio</a>
               </div>
            </div>
            <div class="panel-body">
               <table class="table table-bordered">
                  <thead>
                     <tr>
                        <th>Nombre</th>
                        <th>Env / Err / Total</th>
                        <th>% Envio</th>
                        <th>Fecha</th>
                        <th>Opciones</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?
                     if($envios){
                     foreach ($envios as $envio) {                     
                     ?>
                     <tr>
                        <td><span class="color-success"><?=$envio->titulo?></span></td>
			<?
			if ( $envio->estado == 98 || $envio->estado == 99 ) {
			?>
                        	<td align="center">Testing</td>
			<?
			} else {
			?>
                        	<td align="center"><?=$envio->enviados?>/<?=$envio->errores?>/<?=$envio->total?></td>
			<?
 			}
			?>
                        <td align="right"><?=number_format((($envio->enviados+$envio->errores)/$envio->total)*100,2)?>%</td>
                        <td><?=date('d/m/Y H:i:s',strtotime($envio->creado_el))?></td>
                        <td>
                           <?
                           if( $envio->estado == 99 ){
                           ?>
                           	<a <i class="fa fa-play"></i> Test en despacho  </a>  | 
                           <?
			   } else {
                           	if( $envio->estado == 98 ){
                           		?>
                           			<a <i class="fa fa-play"></i> Test enviado  </a>  | 
                           			<a href="<?=base_url()?>admin/envios/ok/<?=$envio->Id?>"><i class="fa fa-pencil"></i> Enviar Todo </a>  | 
                           			<a href="<?=base_url()?>admin/envios/reenviar/<?=$envio->Id?>"><i class="fa fa-pencil"></i> Reenviar Test </a>  | 
                           		<?
				} else {
                           		if( ( $envio->enviados + $envio->errores ) < $envio->total  ){
                           		?>
                           			<a <i class="fa fa-play"></i> Enviando... </a>  | 
                           		<?
                           		}
				}
			   }
                           ?>
                           <a href="<?=base_url()?>admin/envios/editar/<?=$envio->Id?>"><i class="fa fa-pencil"></i> Editar </a>  | 
                           <a id="del_confirm" data-msj="Seguro que desea eliminar este envio?" href="<?=base_url()?>admin/envios/eliminar/<?=$envio->Id?>"><i class="fa fa-trash-o"></i> Eliminar</a>
                        </td>
                     </tr>
                     <?
                     }
                     }
                     ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

  <div class="page page-table" >
    <div class="panel panel-default table-dynamic">
        <div class="panel-heading"><strong><span class="fa fa-user"></span> RESUMEN DE SOCIO</strong></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-1 bg-info" align="center" style="padding:15px; margin-bottom:10px;">
                            <i class="fa fa-users text-large stat-icon"></i>
                    </div>
                    <div class="form-group col-lg-5" style="padding-top:20px;">
                       

                        <div id="r2-data" <? if($socio->Id != 0){ echo 'class="hidden"'; }?>>
                            <form id="buscar_resumen">
                                <div class="col-sm-7">
                                    <input type="text" name="r2" id="r2" class="form-control" placeholder="Ingrese Nombre, Apellido o DNI del socio">
                                </div>
                                <div class="col-sm-4">
                                    <a href="#" id="r-buscar" data-id="r2" class="btn btn-primary">Buscar</a> <i id="r2-loading" class="fa fa-spinner fa-spin hidden"></i>
                                </div>
                            </form>
                        </div>
                        <div id="r2-result" <? if($socio->Id == 0){ echo 'class="hidden size-h3"'; }else{ echo 'class="size-h3"'; }?>>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <? echo $socio->nombre.' '.$socio->apellido.' ('.$socio->dni.')'; ?> <a href="#" onclick="cleear('r2')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>
                        </div>
                        <input type="hidden" name="r2-id" id="r2-id" class="form-control" value="<?=$socio->Id?>">
                    </div> 
		<? if ( $rango < 2 ) { ?>
                    <div class="form-group col-lg-6 <? if(!$socio->Id){ echo 'hidden'; } ?>" style="padding-top:20px;" id="accesos_directos">
                        <a id="acceso_editar" class="btn btn-success" href="<?=$baseurl?>admin/socios/editar/<?=$socio->Id?>"><i class="fa fa-user"></i> Editar este socio</a>                        
                        <a id="acceso_cupon" class="btn btn-info" href="<?=$baseurl?>admin/pagos/cupon/<?=$socio->Id?>"><i class="fa fa-dollar"></i> Generar Cupón</a>
                        <div class="btn-group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-plus"></i> Más Acciones...
                            <span class="caret"></span>
                            </button><ul class="dropdown-menu" role="menu" aria-labelledby="btnGroupDrop1">
                                <li><a id="acceso_actividad" href="<?=$baseurl?>admin/actividades/asociar/<?=$socio->Id?>">Asociar Actividad</a></li>
                                <li><a id="acceso_pago" href="<?=$baseurl?>admin/pagos/registrar/<?=$socio->Id?>">Registrar Pago</a></li>
                                <li><a id="acceso_deuda" href="<?=$baseurl?>admin/pagos/deuda/<?=$socio->Id?>">Financiar Deuda</a></li>
                                <li><a id="acceso_resumen" href="<?=$baseurl?>admin/socios/enviar_resumen/<?=$socio->Id?>">Enviar Resumen</a></li>
                                <li><a id="acceso_debtarj" href="<?=$baseurl?>admin/debtarj/nuevo-get/<?=$socio->Id?>">Adherir Debito Tarjeta</a></li>
                                <li><a id="imprimir_carnet" data-id="<?=$socio->Id?>" href="#">Imprimir Carnet Papel</a></li>
                                <li><a id="imprimir_tarjeta" data-id="<?=$socio->Id?>" href="#">Imprimir Credencial Plastico</a></li>
                                <li><a id="acceso_financiar" href="<?=$baseurl?>admin/pagos/deuda/<?=$socio->Id?>">Financiar Deuda</a></li>
                                <li><a id="acceso_suspender" href="<?=$baseurl?>admin/socios/suspender/<?=$socio->Id?>">Suspender Socio</a></li>
                                <li><a id="acceso_reinscribir" href="<?=$baseurl?>admin/socios/reinscribir/<?=$socio->Id?>">Reinscribir Socio</a></li>

                            </ul>
                        </div>
		        <div>
                		<a href="<?=base_url()?>admin/socios/resumen/<?=$this->uri->segment(4)?>/excel" class="btn btn-primary">Bajar a Excel</a>
				<? if ( $carnet_ultimp == 0 ) { ?>
                                	<a class="btn btn-primary" id="imprimir_tarjeta" data-id="<?=$socio->Id?>" href="#">Imprimir Credencial Plastico</a>
				<? } else { ?>
                			<a class="btn btn-primary">Credencial Impresa <?=$carnet_ultimp?></a>
				<? } ?>
			</div>
                        	<!--<a id="acceso_cupon" class="btn btn-warning" href="<?=$baseurl?>admin/actividades/asociar/<?=$socio->Id?>"><i class="fa fa-dollar"></i> Asociar Actividad</a>-->
                        	<br><br>
                    </div>                   
		<? } else { 
			if ( $rango < 3 ) { ?>
                    		<div class="form-group col-lg-6 <? if(!$socio->Id){ echo 'hidden'; } ?>" style="padding-top:20px;" id="accesos_directos">
                        		<a id="acceso_editar" class="btn btn-success" href="<?=$baseurl?>admin/socios/editar/<?=$socio->Id?>"><i class="fa fa-user"></i> Editar este socio</a>                        
                    		</div>                   
                    		<div>
                        		<a href="<?=base_url()?>admin/socios/resumen/<?=$this->uri->segment(4)?>/excel" class="btn btn-primary">Bajar a Excel</a>
                    		</div>
			<? } else { ?>
                    		<div class="form-group col-lg-6 <? if(!$socio->Id){ echo 'hidden'; } ?>" style="padding-top:20px;" id="accesos_directos">
                        		<a id="acceso_editar" class="btn btn-success" href="<?=$baseurl?>admin/socios/editar/<?=$socio->Id?>"><i class="fa fa-user"></i> Ver este socio</a>                        
                    		</div>                   
                    		<div>
                        		<a href="<?=base_url()?>admin/socios/resumen/<?=$this->uri->segment(4)?>/excel" class="btn btn-primary">Bajar a Excel</a>
                    		</div>
			<? } ?>

		<? } ?>
                </div>            
                <div class="col-lg-12" id="asociar-div" style="display:none;">
                
                </div>
            </div>
        </div>
    </div>
  </div>

    <!-- Modal -->            
        <div class="panel-body" data-ng-controller="ModalDemoCtrl">

            <script type="text/ng-template" id="myModalContent.html">                    
                <div class="modal-header">
                    <h3>Detalle de Cuota</h3>
                </div>
                <div class="modal-body" id="detalle_de_cuota">
                    <div class="col-sm-6">                    
                            <h3><strong>Titular:</strong> <?=$cuota['titular']?> 
                                <?
                                if($cuota['categoria'] == 'Grupo Familiar'){
                                ?>
                                <a target="_self" href="<?=base_url()?>admin/socios/resumen/<?=$cuota['tid']?>" class="btn btn-success"> Ver Resumen </a>
                                <? } ?>
                            </h3>
                            <h5><strong>Categoría:</strong> <?=$cuota['categoria']?></h5>
                            <?
                            if($cuota['categoria'] == 'Grupo Familiar'){
                            ?>                        
                            <h5><strong>Integrantes</strong></h5>
                            <ul>
                                <? foreach ($cuota['familiares'] as $familiar) { ?>         
                                <li><?=$familiar['datos']->nombre?> <?=$familiar['datos']->apellido?></li>
                                <?
                                }
                                ?>
                            </ul>
                            <?
                            }
                            ?>      
                        </div>
                        
                        <table class="table table-hover" width="80%;">
                            <thead>
                                <tr>                        
                                    <th>Descripción</th>
                                    <th>Monto</th>                        
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        Cuota Mensual <?=$cuota['categoria']?>
                                        <? if($cuota['descuento'] > 0.00){ ?>
                                        - $ <?=$cuota['cuota_neta']?> &nbsp;<label class="label label-info"><?=$cuota['descuento']?>% BECADO</label>
                                        <? } ?>
                                    </td>
                                    <td>$<?=$cuota['cuota']?></td>
                                </tr>
                                <? foreach ($cuota['actividades']['actividad'] as $actividad) {?>
                                <tr>
				    <? if($actividad->descuento > 0){ 
				    			$tb = $actividad->monto_porcentaje;
				                    switch ( $tb ) {
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
                                    <td>Cuota Mensual <?=$actividad->nombre?> - $ <?=$actividad->precio?> <label class="label label-info"><?=$actividad->descuento?> <? echo $tipo_beca; ?></label></td>
                                    <td>$<?if ( $tb == 0 || $tb == 2 || $tb == 4 || $tb == 6 ) { if ( $actividad->precio == 0 ) { echo '0.00';} else { echo $actividad->precio - $actividad->descuento; } ; } else { echo $actividad->precio - ($actividad->precio * $actividad->descuento / 100); }?><td>
                                    <? }else{ ?>                   
				    	<? if ( $actividad->seguro > 0 && $actividad->federado == 0 ) { ?>
					<? 	$valor = $actividad->precio + $actividad->seguro; ?>
                                    	<? }else{ ?>                   
					<? 	$valor = $actividad->precio ; ?>
                                    	<? } ?>                   
                                    <td>Cuota Mensual <?=$actividad->nombre?></td>
                                    <td>$<?=$valor?><td>
                                    <? } ?>
                                </tr>   
                                <?
                                } 
                                if($cuota['familiares'] != 0){
                                    foreach ($cuota['familiares'] as $familiar) {
                                        foreach($familiar['actividades']['actividad'] as $actividad){                           
                                        ?>
                                        <tr>                    
                                            <? if($actividad->descuento > 0){ ?>
	                                    		<? if ( $actividad->seguro > 0 && $actividad->federado == 0 ) { ?>
								<? $seguro = $actividad->seguro; ?>
                                                	<? }else{ ?>                   
								<? $seguro = 0; ?>
							<? } 
                                                        $tb = $actividad->monto_porcentaje;
                                                    	switch ( $tb ) {
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
                                                <td>Cuota Mensual <?=$actividad->nombre?> [<?=$familiar['datos']->nombre.' '.$familiar['datos']->apellido?> ] - $ <?=$actividad->precio?> <label class="label label-info"><?=$actividad->descuento?><? echo $tipo_beca; ?></label></td>
                                                <td>$<?if ( $tb == 0 || $tb == 2 || $tb == 4 || $tb == 6 ) { if ( $actividad->precio == 0 ) { echo '0.00'; } else { echo $actividad->precio - $actividad->descuento + $seguro; } ; } else { echo $actividad->precio - ($actividad->precio * $actividad->descuento / 100) + $seguro; }?><td>
                                                <? }else{ ?>                   
	                                    		<? if ( $actividad->seguro > 0 && $actividad->federado == 0 ) { ?>
								<? $valor = $actividad->precio + $actividad->seguro; ?>
                                                	<? }else{ ?>                   
								<? $valor = $actividad->precio ; ?>
                                            		<? } ?>     
                                                <td>Cuota Mensual <?=$actividad->nombre?> [<?=$familiar['datos']->nombre.' '.$familiar['datos']->apellido?> ]</td>
                                                <td>$<?=$valor?><td>
                                            <? } ?>     

                                        </tr>
                                        <?       
                                        }                                   
                                    }
                                }
                                if($cuota['excedente'] >= 1){
                                ?>
                                        <tr>                    
                                            <td>Socio Extra (x<?=$cuota['excedente']?>)</td>
                                            <td>$<?=$cuota['monto_excedente']?><td>
                                        </tr>
                                <?
                                }

                                if($cuota['financiacion']){
                                    foreach ($cuota['financiacion'] as $plan) {
                                ?>
                                        <tr>
                                            <td>Financiación de Deuda (<?=$plan->detalle?> - Cuota <?=$plan->actual?>/<?=$plan->cuotas?>)</td>
                                            <td>$<?=round($plan->monto/$plan->cuotas,2)?><td>
                                        </tr>
                                <?
                                    }
                                }                                
                                ?>                                               
                            </tbody>
                            <tfoot>
                                <tr>                        
                                    <th>Total</th>
                                    <th>$<?=$cuota['total']?></th>                        
                                </tr>
                            </tfoot>
                        </table>            
                    </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?=base_url()?>admin/pagos/cupon/<?=$this->uri->segment(4)?>" class="btn btn-primary">Generar Cupón</a>
                    <button class="btn btn-alert" id="modal_close" ng-click="cancel()">Cerrar</button>
                </div>
            </script>
            <button class="btn btn-primary hidden" id="modal_open" ng-click="open()">vm</button>

        </div>
    <!-- end Modal -->

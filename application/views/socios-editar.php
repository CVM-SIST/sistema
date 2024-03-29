<section class="page page-profile">
    <div class="panel panel-default">

	<? if ( $rango < 3 ) { ?>
        	<div class="panel-heading"><strong><span class="fa fa-plus"></span> Editar Socio</strong></div>
	<? } else { ?>
        	<div class="panel-heading"><strong><span class="fa fa-plus"></span> Ver Socio</strong></div>
	<? } ?>

        <div class="panel-body">
            <?
            if(!$socio){
            ?>
            El Socio ingresado no existe en nuestra base de datos.<br><br>
            <a href="<?=$baseurl?>admin/socios" class="btn btn-primary">Volver al Listado de Socios</a>
            <?
            }else{
            ?>
		<? if ( $rango < 3 ) { ?>
            		<form class="form-horizontal ng-pristine ng-valid" action="<?=$baseurl?>admin/socios/guardar/<?=$socio->Id?>" method="post">
		<? } else { ?>
            		<form class="form-horizontal ng-pristine ng-valid" >
		<? } ?>

                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Nombre</label>
                    <div class="col-sm-9">
                        <input type="text" id="nombre" value="<?=$socio->nombre?>" name="nombre" class="form-control">
                    </div>
                </div>
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Apellido</label>
                    <div class="col-sm-9">
                        <input type="text" id="apellido" value="<?=$socio->apellido?>" name="apellido" class="form-control">
                    </div>
                </div>
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">DNI</label>                    
                    <div class="col-sm-9">
                        <input type="number" value="<?=$socio->dni?>" class="form-control" name="dni" required>
                    </div>
                </div>                
                                   
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">E-Mail</label>
                    <div class="col-sm-9">
                        <input type="mail"  value="<?=$socio->mail?>" class="form-control" id="mail" name="mail">
			<input type="hidden" name="mail_orig" id="mail_orig" value='<?=$socio->mail?>' class="form-control">
			<input type="hidden" name="validmail_ts" id="validmail_ts" value='<?=$socio->validmail_ts?>' class="form-control">
			<input type="hidden" name="validmail_st" id="validmail_st" value='<?=$socio->validmail_st?>' class="form-control">
                    </div>
                </div>    

                <div class="form-group col-lg-6" data-ng-controller="DatepickerDemoCtrl">
                    <label for="" class="col-sm-3">Fecha de Nacimiento</label>
                    <div class="col-sm-9">
                        <div class="input-group ui-datepicker">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="date" id="fechan"
                                   class="form-control"                                   
                                   close-text="Cerrar" name="nacimiento" value="<?=$socio->nacimiento?>">
                        </div>  
                    </div>
                </div> 
                
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Domicilio</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?=$socio->domicilio?>" id="domicilio" name="domicilio">
                    </div>
                </div>        
                <div class="clearfix"></div>
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Localidad</label>
                    <div class="col-sm-9">
                        <input type="text" id="localidad" value="<?=$socio->localidad?>" class="form-control" name="localidad">  
                    </div>
                </div>       
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Nacionalidad</label>
                    <div class="col-sm-9">
                        <input type="text" id="nacionalidad" value="<?=$socio->nacionalidad?>" class="form-control" name="Nacionalidad">
                    </div>
                </div> 
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Teléfono</label>
                    <div class="col-sm-9">
                        <input type="text" value="<?=$socio->telefono?>" class="form-control" name="telefono">
                    </div>
                </div>       
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Celular</label>
                    <div class="col-sm-9">
                        <input type="text" value="<?=$socio->celular?>" class="form-control" name="celular">
                    </div>
                </div>               
                <div class="clearfix"></div>

                

                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Tutor de Grupo Familiar</label>
                        <div id="tutor_dni-data" <? if($tutor->Id != 0){ echo 'class="hidden"'; }?>>
                            <div class="col-sm-5">
                                <input type="number" name="tutor_dni" id="tutor_dni" value='<? if($tutor->Id != 0) { echo $tutor->dni; } else { echo "0"; } ?>' class="form-control">
                                <input type="hidden" name="tutor_sid" id="tutor_sid" value='<? if($tutor->Id != 0) { echo $tutor->Id; } else { echo "0"; } ?>' class="form-control">
                                <input type="hidden" name="tutor_orig" id="tutor_orig" value='<?=$tutor->Id?>' class="form-control">
                            </div>
                            <div class="col-sm-4">
                                <a href="#" id="r-buscar" data-id="tutor_dni" class="btn btn-primary">Buscar</a> <i id="tutor_dni-loading" class="fa fa-spinner fa-spin hidden"></i>
                            </div>
                        </div>
                        <div id="tutor_dni-result" <? if($tutor->Id == 0){ echo 'class="hidden"'; }?>>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <? echo $tutor->nombre.' '.$tutor->apellido.' ('.$tutor->dni.')'; ?> <a href="#" onclick="cleear('tutor_dni')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>
                        </div>
                </div>

                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Categoría de Socio</label>
                    <div class="col-sm-9">
                        <span class=" ui-select">
                            <select name="categoria" id="s_cate" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                                <?
                                foreach ($categorias as $cat) {                                                           
                                ?>
                                    <option value="<?=$cat->Id?>" data-precio="<?=$cat->precio?>" <? if($cat->Id == $socio->categoria){echo 'selected';} ?>><?=$cat->nomb?></option>
                                <?
                                }
                                ?>
                            </select>
                        </span>
                    </div>
                </div>  
                
                                  
                <div class="form-group col-lg-12">
                    <label for="" class="col-sm-6">Credencial Impresa</label>
                    <div class="alert alert-info"><?=$carnet_ultimp?></div>
                </div>
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Foto</label>
                    <div class="col-sm-9" align="center">
                        <div id="my_camera" style="width:320px; height:240px; float:right; border:2px solid #23ae89"></div> 
                    </div>
                </div>
                <div class="form-group col-lg-6" align="center">
                    <div class="col-sm-12">
                        <div align="center" class="pull-left" style="background-color:#EEE;">
                            <div id="my_result" style="width:320px; height:220px; overflow:hidden">
                            <? 
                            if(file_exists("images/socios/".$socio->Id.".jpg")){
                            ?>
                                <img src="<?=$baseurl?>images/socios/<?=$socio->Id?>.jpg?recall=<?=rand(0,99999)?>" width="100%">
                            <?
                            }else{
                            ?>
                                <img src="<?=$baseurl?>images/noPic.jpg">
                            <?
                            }
                            ?>
                            </div>
                            <div style="margin-top:4px;">

                                <a href="javascript:void(take_snapshot())" class="btn btn-success" style="background-color:#23ae89; color:#FFF;"><i class="fa fa-camera"></i> Capturar Imágen</a>
                                <span class="btn btn-success fileinput-button">                              
                                    <span><i class="fa fa-cloud-upload"></i> Subir Imágen</span>
                                    <!-- The file input field used as target for the file upload widget -->
                                    <input id="fileupload" type="file" name="files[]" multiple>
                                </span>
                                <div id="progress" class="progress" style="diplay:none;">
                                    <div class="progress-bar progress-bar-success"></div>
                                </div>
                            </div>
                        </div>                                                                            
                    </div>
                </div>                 
                <div class="clearfix"></div>   
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Socio #</label>
                    <div class="col-sm-9">
                        <input type="number" name="socio_n" class="form-control" value="<?=$socio->socio_n?>" required>                        
                    </div>
                </div> 
                <div class="col-lg-6">
                    <p>
                        <li>El número de socio personalizado deberá ser menor a 28.852.</li>
                        <li>Para volver al número de socio de sistema ingrese 0 (cero)</li>
                    </p>
                </div>
                <?
                $alta = explode(' ',$socio->alta);
                ?>
                <div class="form-group col-lg-6" data-ng-controller="DatepickerDemoCtrl">
                    <label for="" class="col-sm-3">Fecha de Ingreso</label>
                    <div class="col-sm-9">
                        <div class="input-group ui-datepicker">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="date"
                                   class="form-control"                                   
                                   close-text="Cerrar" name="alta" value="<?=$alta[0]?>">
                        </div>  
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="form-group col-lg-6">
                    <div class="alert alert-info">Este socio abonará $ <span id="a_pagar"></span> de cuota social.</div>
                </div>
                <div class="clearfix"></div>
                <div class="form-group col-lg-6">
                    <label for="" class="col-sm-3">Observaciones</label>
                    <div class="col-sm-9">
                        <textarea name="observaciones" id="" class="form-control" rows="4"><?=$socio->observaciones?></textarea>
                    </div>
                </div>  
                <div class="clearfix"></div>
                <? if ( $rango < 3 ) { ?>
                	<button type="submit" id="save_btn" class="btn btn-success">Guardar</button>
                <? } ?>

            </form>
            <?
            }
            ?>
        </div>
    </div>
</section>

<!-- Modal -->            
                <div class="panel-body" data-ng-controller="ModalDemoCtrl">
                    <script type="text/ng-template" id="myModalContent.html">                    
                        <div class="modal-header">
                            <h3>Agregar Socio</h3>
                        </div>
                        <div class="modal-body">
                            <form class="form-horizontal" data-id="" id="form-tutor" action="#" method="post">
                                <div class="form-group col-lg-12">
                                    <label for="" class="col-sm-3">DNI</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="tutor-dni" name="tutor-dni" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group col-lg-12">
                                    <label for="" class="col-sm-3">Nombre</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="tutor-nombre" name="tutor-nombre" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group col-lg-12">
                                    <label for="" class="col-sm-3">Apellido</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="tutor-apellido" name="tutor-apellido" class="form-control">
                                    </div>
                                </div>                                
                                <div class="form-group col-lg-12">
                                    <label for="" class="col-sm-3">Teléfono</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="tutor-telefono" name="tutor-telefono" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group col-lg-12">
                                    <label for="" class="col-sm-3">Email</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="tutor-email" name="tutor-email" class="form-control">
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" onclick="submit_tutor();">Agregar</button>
                            <button class="btn btn-alert" id="modal_close" ng-click="cancel()">Cancel</button>
                        </div>
                    </script>
                    <button class="btn btn-primary hidden" id="modal_open" ng-click="open()">vm</button>
                </div>
            <!-- end Modal -->

                    <div class="page page-profile">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong><span class="fa fa-group"></span> USUARIOS ACCESO COMISIONES </strong></div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    foreach ($profesores as $profesor) {                                       
                                    ?>
                                    <tr>
                                        <td><?=$profesor->Id?></td>
                                        <td><span class="color-success"><?=$profesor->apellido?> <?=$profesor->nombre?></td>
                                        <td>
                                            <a href="<?=$baseurl?>admin/actividades/profesores/editar/<?=$profesor->Id?>"><i class="fa fa-gear"></i> Editar</a>  | 
                                            <a id="btn-eliminar-profesor" href="<?=$baseurl?>admin/actividades/profesores/eliminar/<?=$profesor->Id?>"><i class="fa fa-times"></i> Eliminar</a>
                                        </td>
                                    </tr>                                    
                                    <?
                                    }
                                    ?>                              

                                </tbody>
                            </table>
                        </div>
                    </div>
<section class="page page-profile">
    <div class="panel panel-default">
        <div class="panel-heading"><strong><span class="fa fa-plus"></span> Agregar Usuario de Comision</strong></div>
        <div class="panel-body">
            <form class="form-horizontal ng-pristine ng-valid" action="<?=$baseurl?>admin/actividades/profesores/nuevo" method="post">

                <div class="form-group">
                    <label for="" class="col-sm-2">Nombre</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Apellido</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="apellido" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">DNI</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="dni" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">SID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" id="sid-select" name="sid" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Dirección</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="direccion">
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Teléfono</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="telefono">
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Celular</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="celular">
                    </div>
                </div> 
                <div class="form-group">
                    <label for="" class="col-sm-2">Comisión</label>
                    <div class="col-sm-10">
			<select name="comision" id="comision" >
				<?
				foreach ($comisiones as $comision) {                                       
				?>
                          		<option value="<?=$comision->id?>" ><?=$comision->descripcion?></option>
				
				<? } ?>
			</select>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="" class="col-sm-2">Puesto en la Comisión</label>
                    <div class="col-sm-10">
			<select name="puesto" id="puesto" >
                        	<option value="0" >Operador</option>
                        	<option value="1" >Presidente</option>
                        	<option value="2" >VicePresidente</option>
                        	<option value="3" >Tesorero</option>
                        	<option value="4" >Secretario</option>
                        	<option value="5" >Otro</option>
			</select>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="" class="col-sm-2">Email</label>
                    <div class="col-sm-10">
                        <input type="email" class="form-control" name="mail">
                    </div>
                </div> 
                <div class="form-group">
                    <label for="" class="col-sm-2">Contraseña</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control" name="pass">
                    </div>
                </div>                                
                <button type="submit" id="btn_profesor" class="btn btn-success">Agregar</button>
            </form>
        </div>
    </div>
</section>                    

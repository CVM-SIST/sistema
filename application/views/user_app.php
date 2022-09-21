                    <div class="page page-profile">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong><span class="fa fa-group"></span> USUARIOS ACCESO APP </strong></div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Login</th>
                                        <th>DNI</th>
                                        <th>email</th>
                                        <th>Entidad</th>
                                        <th>Nivel</th>
					<th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    foreach ($users as $user) {                                       
                                    ?>
                                    <tr>
                                        <td><?=$user->id?></td>
                                        <td><span class="color-success"> <?=$user->login?> </td>
                                        <td><span class="color-success"> <?=$user->dni?> </td>
                                        <td><span class="color-success"> <?=$user->email?> </td>
                                        <td><span class="color-success"> <?=$user->id_entidad?> </td>
                                        <td><span class="color-success"> <?=$user->nivel?> </td>
                                        <td>
                                            <a href="<?=$baseurl?>admin/socios/user_app_edit/<?=$user->id?>"><i class="fa fa-gear"></i> Editar</a>  | 
                                            <a id="btn-eliminar-userapp" href="<?=$baseurl?>admin/socios/user_app_edit/eliminar/<?=$user->id?>"><i class="fa fa-times"></i> Eliminar</a>
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
        <div class="panel-heading"><strong><span class="fa fa-plus"></span> Agregar Usuario de APP</strong></div>
        <div class="panel-body">
            <form class="form-horizontal ng-pristine ng-valid" action="<?=$baseurl?>admin/socios/user_app_set" method="post">

                <div class="form-group">
                    <label for="" class="col-sm-2">Login</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="login" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">DNI</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="dni" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Email</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="email" required>
                    </div>
                </div>
                <button type="submit" id="btn_userapp" class="btn btn-success">Agregar</button>
            </form>
        </div>
    </div>
</section>                    

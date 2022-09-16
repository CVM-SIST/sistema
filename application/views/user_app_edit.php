                    
<section class="page page-profile">
    <div class="panel panel-default">
        <div class="panel-heading"><strong><span class="fa fa-plus"></span> Editar Usuario Acceso APP</strong></div>
        <div class="panel-body">
            <form class="form-horizontal ng-pristine ng-valid" action="<?=$baseurl?>admin/socios/user_app_upd/<?=$user->id?>" method="post">

                <div class="form-group">
                    <label for="" class="col-sm-2">Login</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="login" value="<?=$user->login?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">DNI</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="dni" value="<?=$user->dni?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Email</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="email" value="<?=$user->email?>" required>
                    </div>
                </div>
                <button type="submit" id="btn_user_app" class="btn btn-success">Guardar Cambios</button>
            </form>
        </div>
    </div>
</section>                    

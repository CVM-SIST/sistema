<section class="page page-profile">
    <div class="panel panel-default">
        <div class="panel-heading"><strong><span class="fa fa-question-circle"></span> ACCESO SUB-COMISION <?=$nombre_comision?> </strong></div>
        <div class="panel-body">
                <div class="form-group col-lg-12">
                    <label for="" class="col-sm-12">Cambio de Password</label>
               	</div>

		<form class="form-horizontal ng-pristine ng-valid " action="<?=base_url()?>comisiones/upd_pwd/" method="post">

                <div id="rp-client2"></div>
                    <div class="form-group">
                        <div class="col-sm-3"></div>
<? if ( $flag ) { ?>
                        <label for="" class="col-sm-2"> <?=$mensaje?> </label>
<?
} else {
?>
                        <label for="" class="col-sm-2">  </label>
<?
} ?>
		    </div>
                    <div class="form-group">
                        <div class="col-sm-3"></div>
                        <label for="" class="col-sm-2">Password Actual</label>
                        <div class="col-sm-3">
                            <input type="password" name="old_pwd" class="form-control" style="width:200px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-3"></div>
                        <label for="" class="col-sm-2">Password Nuevo</label>
                        <div class="col-sm-3">
                            <input type="password" name="new_pwd1" class="form-control" style="width:200px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-3"></div>
                        <label for="" class="col-sm-2">Reingrese Nueva Pwd</label>
                        <div class="col-sm-3">
                            <input type="password" name="new_pwd2" class="form-control" style="width:200px;">
                        </div>
                    </div>

                    <button class="btn btn-primary btn-block" action="submit">Cambiar</button>

                <div class="clearfix"></div>
            </form>

       	</div>
    </div>
</section>

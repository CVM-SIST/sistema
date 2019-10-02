<section class="page page-profile">
    <div class="panel panel-default">
        <div class="panel-heading"><strong><span class="fa fa-plus"></span> Agregar Platea</strong></div>
        <div class="panel-body">
            <form class="form-horizontal ng-pristine ng-valid" action="<?=$baseurl?>admin/socios/plateas-do-alta" method="post">
                <div class="form-group">
                    <label for="" class="col-sm-2">Socio</label>
                    <div class="col-sm-10">
                        <label name="socio" ><?=$socio?></label>
                    </div>
                        <input type="hidden" class="form-control" name="sid" value=<?=$sid?>>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Actividad</label>
                    <div class="col-sm-10">
                        <select name="actividad" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
				<option value="">---------</option>
                        	<option value="1" <?if($actividad=='1') { echo "selected"; } ?>>Futbol</option>
                        	<option value="2" <?if($actividad=='2') { echo "selected"; } ?>>Basquet</option>

                        </select>
                    </div>
                </div>  

                <div class="form-group">
                    <label for="" class="col-sm-2">Descripcion</label>
                    <div class="col-sm-10">
                        <select name="descripcion" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
				<option value="">---------</option>
                        	<option value="Federal Futbol 2019" <?if($actividad=='1') { echo "selected"; } ?>>Federal Futbol 2019</option>
                        	<option value="Federal Basquet 2019" <?if($actividad=='2') { echo "selected"; } ?>>Federal Basquet 2019</option>
                        </select>
                    </div>
                </div>                               
                <div class="form-group">
                    <label for="" class="col-sm-2">Fila</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="fila">
                    </div>
                </div>                               
                <div class="form-group">
                    <label for="" class="col-sm-2">Numero</label>
                    <div class="col-sm-10"
                        <input type="text" class="form-control" name="numero">
                    </div>
                </div>                               
                <div class="form-group">
                    <label for="" class="col-sm-2">Importe</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="importe">
                    </div>
                </div>                               
                <div class="form-group">
                    <label for="" class="col-sm-2">Cuotas</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="cuotas">
                    </div>
                </div>                               
                <div class="form-group">
                    <label for="" class="col-sm-2">Valor Cuota</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="valor_cuota">
                    </div>
                </div>                               
                <div class="form-group">
                    <label for="" class="col-sm-2">Se cobra?</label>
                    <div class="col-sm-10">
                        <select name="se_cobra" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
				<option value="">---------</option>
                        	<option value="1">SI</option>
                        	<option value="0">NO</option>
                        </select>
                    </div>
                </div>                               

                
                <button type="submit" class="btn btn-success">Agregar</button>
            </form>
        </div>
    </div>
</section>                    

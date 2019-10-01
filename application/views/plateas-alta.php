<section class="page page-profile">
    <div class="panel panel-default">
        <div class="panel-heading"><strong><span class="fa fa-plus"></span> Agregar Platea</strong></div>
        <div class="panel-body">
            <form class="form-horizontal ng-pristine ng-valid" action="<?=$baseurl?>admin/socios/plateas-alta2" method="post">
                <div class="form-group">
                    <label for="" class="col-sm-2">Socio</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="sid" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Actividad</label>
                    <div class="col-sm-10">
                        <select name="actividad" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
				<option value="">---------</option>
                        	<option value="1">Futbol</option>
                        	<option value="2">Basquet</option>
                        </select>
                    </div>
                </div>  

                <button type="submit" class="btn btn-success">Agregar</button>
            </form>
        </div>
    </div>
</section>                    

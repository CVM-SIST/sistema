<form class="form-horizontal ng-pristine ng-valid" action="<?=$baseurl?>admin/socios/act-datos-ver" method="post" id="asoc-act-filtro-form" enctype="multipart/form-data">
    <br>
                <div class="form-group col-lg-18">
                     <label for="" class="col-sm-9">Ultima Actualizacion</label>
                     <div class="col-sm-5">
                       <span class=" ui-select">
                       <select name="actividad" id="actividad" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
				<option value="0" >Sin filtro meses</option>
				<option value="6" >+ 6 meses</option>
				<option value="12" >+ 12 meses</option>
				<option value="18" >+ 18 meses</option>
                       </select>
                       </span>
                     </div>
                </div>

                <div class="form-group col-lg-18">
                     <label for="fuente" class="col-sm-9">Email</label>
                     <div class="col-sm-5">
                       <span class="ui-select">
                       <select id="email" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
				<option value="" > Seleccionar filtro email</option>
				<option value="con" > Con Email </option>
				<option value="cone" > Con Email con Error</option>
				<option value="sin" > Sin Email</option>
                       </select>
                       </span>
                     </div>
                </div>
                <div class="form-group col-lg-18">
                     <label for="fuente" class="col-sm-9">Telefono</label>
                     <div class="col-sm-5">
                       <span class="ui-select">
                       <select id="telefono" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
				<option value="" > Seleccionar filtro telefono</option>
				<option value="con" > Con Telefono </option>
				<option value="sin" > Sin Telefono</option>
                       </select>
                       </span>
                     </div>
                </div>


		<div class="clearfix"></div>

		<br>
		<br>

                <div class="form-group col-lg-18">
                     <div class="col-sm-5">
                                        <button class="btn btn-success">Procesar</button> <i id="reg-cargando" class="fa fa-spinner fa-spin hidden"></i>

		     </div>
		</div>


</form> 


    <div class="panel panel-default table-dynamic">
      	<div class="panel-heading"><strong><span class="fa fa-dollar"></span> BUSQUEDA SOCIOS</strong></div>
	      	<div class="panel-body">
	                <form id="busqueda_socio" action="<?=base_url()?>admin/socios/busqueda_do" method="post">

                		<div class="form-group col-sm-12">
                     			<div class="col-sm-6">
                     				<label for="" class="col-sm-3">Apellido</label>
                       				<input type="text" class="col-sm-9" name="apellido" id="apellido" > </input>
                     			</div>
                     			<div class="col-sm-6">
                     				<label for="" class="col-sm-3">Nombre</label>
                       				<input type="text" class="col-sm-9" name="nombre" id="nombre" > </input>
                     			</div>
                		</div>

                                <div class="form-group col-sm-12">
                                        <div class="col-sm-6">
                                                <label for="" class="col-sm-3">Domicilio</label>
                                                <input type="text" class="col-sm-9" name="domicilio" id="domicilio" > </input>
                                        </div>
                                        <div class="col-sm-6">
                                                <label for="" class="col-sm-3">Email</label>
                                                <input type="text" class="col-sm-9" name="email" id="email" > </input>
                                        </div>
                                </div>

                                <div class="form-group col-sm-12">
                                        <div class="col-sm-6">
                                                <label for="" class="col-sm-3">Categoria</label>
                                                <input type="text" class="col-sm-9" name="categoria" id="categoria" > </input>
                                        </div>
                                        <div class="col-sm-6">
                                                <label for="" class="col-sm-3">Suspendido</label>
                                                <input type="text" class="col-sm-9" name="suspendido" id="suspendido" > </input>
                                        </div>
                                </div>

                		<div class="form-group col-sm-12">
					<button type="submit" class="btn btn-success">Buscar</button>
				</div>
			</form>

		</div>
        </div> 
    </div>

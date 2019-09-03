<div class="page page-table" data-ng-controller="tableCtrl_tarj">
    <div class="panel panel-default table-dynamic">
      	<div class="panel-heading"><strong><span class="fa fa-dollar"></span> REGISTRAR CONTRACARGOS</strong></div>
	      	<div class="panel-body">
		      	<div class="cols-lg-12">
		    		<div class="col-lg-1 bg-info" align="center" style="padding:15px; margin-bottom:10px;">
						<i class="fa fa-users text-large stat-icon"></i>
	                </div>
	                <form id="contra_reg_form" action="<?=$baseurl?>admin/debtarj/contracargo/getcab" method="post">

		                <div class="form-group col-lg-18">
                     			<label for="" class="col-sm-9">Marca Tarjeta</label>
                     			<div class="col-sm-5">
                       				<span class=" ui-select">
                       					<select name="marca" id="marca" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                       						<? foreach ( $tarjetas as $tarjeta ) { ?>
                                					<option value="<?=$tarjeta->id?>" ><?=$tarjeta->descripcion?></option>
                        					<?}?>
                       					</select>
                       				</span>
                     			</div>
                		</div>
                		<div class="form-group col-lg-18">
                     			<label for="" class="col-sm-9">Periodo Debito AAAAMM</label>
                     			<div class="col-sm-5">
                        			<input type="text" name="periodo" id="periodo">
                     			</div>
                		</div>

                                        <div class="col-sm-4">
                                            <button id="buscar_deb" class="btn btn-primary">Buscar</button> <i id="bd-loading" class="fa fa-spinner fa-spin hidden"></i>
                                        </div>

			</form>
		</div>
        </div> 
    </div>
</div>

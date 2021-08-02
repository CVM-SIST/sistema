<style type="text/css">
	<?

	if ( $carnet_sel ) {
		switch ( $carnet_sel ) {
			// Clasico Papel
			case 1:
				$frente=base_url()."images/carnet-frente-new.png";
				$dorso=base_url()."images/carnet-dorso-new.png";
				break;
			// Prensa
			case 2:
				$frente=base_url()."images/Prensa_Frente_300.jpg";
				$dorso=base_url()."images/Prensa_Dorso_300.jpg";
				break;
			// Comercio
			case 3:
				$frente=base_url()."images/Comercio_Frente.jpg";
				$dorso=base_url()."images/Comercio_Dorso.jpg";
				break;
			// VM Racing
			case 4:
				$frente=base_url()."images/VMRacing_Frente.jpg";
				$dorso=base_url()."images/VMRacing_Dorso.jpg";
				break;
			// Credencial Plastico
			case 5:
				$frente=base_url()."images/Plastico_2021_Frente.jpg";
				$dorso=base_url()."images/Plastico_2021_Dorso.jpg";
				break;
			// Clasico Papel
			default:
				$frente=base_url()."images/carnet-frente-new.png";
				$dorso=base_url()."images/carnet-dorso-new.png";
				break;
		}
	} else {
		$frente=base_url()."images/carnet-frente-new.png";
		$dorso=base_url()."images/carnet-dorso-new.png";
	}

        ?>
        .cred_frente{
            float: left;
            margin-top: 50px;
            margin-left: 50px;
            width: 244px;
	    height: 153px;
            background-image:url(<?=$frente?>);
	    background-size: 100% 100%;
        }
        .cred_dorso{
            float: left;
            margin-top: 50px;
            margin-left: 50px;
            width: 244px;
	    height: 153px;
            background-image:url(<?=$dorso?>);
            background-size: 100% 100%;
        }
        .cred_menu{
            float: left;
            margin-top: 50px;
            margin-left: 50px;
            width: 244px;
            height: 153px;
            line-height: 15px;
        }
        .linea_menu{
            height:75px;overflow: hidden;
        }
        .imagen{
            margin-top:44px;
            margin-left: 30px;
            width: 80px;
            float: left;
        }
        .datos{
            float:right;
            width: 175px;
            color: #000;
            margin-top:50px;
            line-height: 15px;
        }
        .clear{
            clear: both;
        }
        .barcode{
            margin-top: 125px;
            margin-left: 15px;
        }
</style>
<section class="page page-profile">
    <div class="panel panel-default">
        <div class="panel-heading"><strong><span class="fa fa-plus"></span> Imprimir Carnets </strong></div>
        <div class="panel-body" id="carnet_seleccion">
                <form class="form-horizontal ng-pristine ng-valid" >

                <div class="form-group">
                    <label for="" class="col-sm-2">Categoria</label>
                    <div class="col-sm-10">
                        <select name="categoria" id="categoria" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                        <option value="0" > TODAS </option>
			<? foreach ( $categorias as $categoria ) {  ?>
                        	<option value="<?=$categoria->Id?>" <?if ($cat_sel) { if ( $cat_sel == $categoria->Id ) { echo 'selected';} } ?>> <?=$categoria->nomb?> </option>
			<? } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Con Foto</label>
                    <div class="col-sm-10">
                        <select name="foto" id="foto" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                        <option value="-1" <? if ( $foto_sel ) { if ( $foto_sel== '-1' ) { echo 'selected'; } } ?>> TODOS </option>
                        <option value="SI" <? if ( $foto_sel ) { if ( $foto_sel == 'SI' ) { echo 'selected'; } } ?>> CON FOTO </option>
                        <option value="NO" <? if ( $foto_sel ) { if ( $foto_sel == 'NO' ) { echo 'selected'; } } ?>> SIN FOTO </option>
                        </select>
                    </div>
                </div>                                 
                <div class="form-group">
                    <label for="" class="col-sm-2">Actividades x Comisión</label>
                    <div class="col-sm-10">
                        <select name="comision" id="comision" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                        <option value="0" <? if ( $com_sel ) { if ( $com_sel == 0 ) { echo 'selected'; } } ?>> TODAS </option>
                        <option value="-1" <? if ( $com_sel ) { if ( $com_sel == -1 ) { echo 'selected'; } } ?>> SIN ACTIVIDAD </option>
                        <? foreach ( $comisiones as $comision ) {  ?>
                                <option value="<?=$comision->id?>" <? if ( $com_sel ) { if ( $com_sel == $comision->id ) { echo 'selected'; } } ?>> <?=$comision->descripcion?> </option>
                        <? } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-2">Diseño Carnet</label>
                    <div class="col-sm-10">
                        <select name="carnet" id="carnet" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                        <option value="1" <? if ( $carnet_sel ) { if ( $carnet_sel == 1 ) { echo 'selected'; } } ?>> CVM Clasico (Papel) </option>
                        <option value="2" <? if ( $carnet_sel ) { if ( $carnet_sel == 2 ) { echo 'selected'; } } ?>> Prensa (Papel)      </option>
                        <option value="3" <? if ( $carnet_sel ) { if ( $carnet_sel == 3 ) { echo 'selected'; } } ?>> Comercio (Papel)    </option>
                        <option value="4" <? if ( $carnet_sel ) { if ( $carnet_sel == 4 ) { echo 'selected'; } } ?>> VM Racing (Papel)   </option>
                        <option value="5" <? if ( $carnet_sel ) { if ( $carnet_sel == 5 ) { echo 'selected'; } } ?>> Credencial Plastica </option>
                        </select>
					<input type="hidden" name="impresion" id="impresion" value="0">                                
                    </div>
                </div>


                <button type="submit" id="btn_carnet_buscar" class="btn btn-success">Buscar Socios</button>

		<div id="ver_plastico" style="display: none">
                        <div class="cred_frente" id="plas_frente">
                        </div>
                        <div class="cred_dorso" id="plas_dorso">
                                <div class="datos" id="carnet_data" >
                                </div>
                                <div class="imagen">
                                </div>
                        </div>
                        <div class="cred_menu">
                                <div class="nap"> <div class="btn btn-success" id="carnet_print_fte" > Imprimir Frente </div> </div>
                                <div class="nap"> <div class="btn btn-success" id="carnet_print" > Imprimir </div> </div>
                                <div class="nap"> <div class="btn btn-success" id="carnet_prox" > Siguiente </div> </div>
                                        <input type="hidden" id="sid_visible" value='0' >
                                        <input type="hidden" id="sids" value='0' >
                                        <input type="hidden" id="datos" value='0' >
                                <div class="nap" id="nxm" > </div>
                        </div>
		</div>


			</div>
        </div>
    </div>
</section>                    

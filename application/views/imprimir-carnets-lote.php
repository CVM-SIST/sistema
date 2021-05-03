	<html>
		<head>
			<meta charset="utf-8">
			<title>Imprimir</title>
			<style type="text/css">
			body{
				font-family: 'Arial';
				font-size: 10px;            
				background: #ffffff;            
			}
			strong{
				font-size: 10px;
				font-weight: normal; 
			}
			.nap{
				height:15px;overflow: hidden;
			}
			.hoja{
				width: 600px;
			}
			.carnet{
				width: 300px;
				height: 194px;
				float: right;
			}



		    <?
		    switch ($carnet){
	
		case '2':
			$frente=base_url()."images/Prensa_Frente_300.jpg";
			$dorso=base_url()."images/Prensa_Dorso_300.jpg";
			?>
        		.frente{
				background-image:url(<?=$frente?>); 
	    			background-size: 100% 100%;
        		}
        		.dorso{
            			background-image:url(<?=$dorso?>); 
	    			background-size: 100% 100%;
        		}
        		.imagen{
			margin-top:73px;
            			margin-left: 25px;
            			width: 80px;
            			float: left;
        		}
        		.datos{
            			float:right;
            			width: 165px;
            			color: #000;
				margin-top:85px;
            			line-height: 28px;
        		}
        		.clear{
            			clear: both;
        		}
        		.barcode{
            			margin-top: 0px;
        		}
			<?
			break;
                case '3':
                        $frente=base_url()."images/Comercio_Frente.jpg";
                        $dorso=base_url()."images/Comercio_Dorso.jpg";
                        ?>
                        .frente{
                                background-image:url(<?=$frente?>);
                                background-size: 100% 100%;
                        }
                        .dorso{
                                background-image:url(<?=$dorso?>);
                                background-size: 100% 100%;
                        }
                        .datos{
                                float:left;
                                width: 205px;
                                color: #000;
                                margin-top:75px;
                                margin-left:20px;
                                line-height: 28px;
                        }
                        .clear{
                                clear: both;
                        }
                        .barcode{
                                width: 80px;
                                margin-left: 30px;
                                margin-bottom: 1px;
                        }
                        <?
			break;
                case '4':
                        $frente=base_url()."images/VMRacing_Frente.jpg";
                        $dorso=base_url()."images/VMRacing_Dorso.jpg";
                        ?>
                        .frente{
                                background-image:url(<?=$frente?>);
                                background-size: 100% 100%;
                        }
                        .imagen{
	                        margin-top:73px;
                                margin-left: 25px;
                                width: 80px;
                                float: left;
                        }

                        .dorso{
                                background-image:url(<?=$dorso?>);
                                background-size: 100% 100%;
                        }
                        .datos{
                                float:right;
                                width: 165px;
                                color: #000;
                                margin-top:57px;
                                margin-left:10px;
                                line-height: 15px;
                        }
                        .clear{
                                clear: both;
                        }
                        .barcode{
                                width: 80px;
                                margin-left: 30px;
                                margin-bottom: 1px;
                        }
                        <?
                        break;

		default:
			$frente=base_url()."images/carnet-frente-new.png";
			$dorso=base_url()."images/carnet-dorso-new.png";
			?>
        		.frente{
            			background-image:url(<?=$frente?>); 
	    			background-size: 100% 100%;
        		}
        		.dorso{
            			background-image:url(<?=$dorso?>); 
	    			background-size: 100% 100%;
        		}
        		.imagen{
            			margin-top:50px;
            			margin-left: 15px;
            			width: 80px;
            			float: left;
        		}
        		.datos{
            			float:right;
            			width: 165px;
            			color: #FFF;
            			margin-top:50px;
            			line-height: 15px;
        		}
        		.clear{
            			clear: both;
        		}
        		.barcode{
            			margin-top: 0px;
        		}
			<?
            }
            ?>

		</style>
	</head>

	<div class="hoja">

        <? 
	
	foreach ( $socios as $soc_carnet ) {

                $socio = $soc_carnet['socio'];
                $cupon = $soc_carnet['cupon'];
                $monto = $soc_carnet['monto'];

	    $apynom = substr($socio->nombre.", ".$socio->apellido, 0, 30);
	    $apynom_2 = substr($socio->nombre." ".$socio->apellido, 0, 30);
	    if ( $socio->categoria == 14 ) {
        ?>
                <div class="carnet frente"></div>
    		<div class="carnet dorso">
                        <div class="imagen">
                        <?
                                if(file_exists('images/socios/'.$socio->Id.'.jpg')){
                        ?>
                                        <img src="<?=base_url()?>images/image_carnet.php?img=socios/<?=$socio->Id?>.jpg" width="80">
                        <?
                        }else{
                        ?>
                                        <img src="<?=base_url()?>images/noPic.jpg" width="80">
                        <?
                        }
                        ?>
                        </div>
                <?
                        if($socio->socio_n){
                                $num = $socio->socio_n;
                        }else{
                                $num = $socio->Id;
                        }
                        $fecha = explode(' ', $socio->alta);
                        $fecha = explode('-', $fecha[0]);
                        $fecha = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];
                ?>

                        <div class="datos">

                        <div class="nap" style="font-weight:bold"><?=$apynom_2?></div>
                        <div class="nap" style="font-weight:bold"> SOCIO MASCOTA </div>
                        <div class="nap" style="font-weight:bold">Socio No. <?=$num?></div>
                        <div class="nap" style="font-weight:bold">Ingreso <?=$fecha?></div>

                        </div>
                        <div class="barcode">
                                <?
                                        if( file_exists("images/cupones/".$cupon->Id.".png") ){
                                ?>
                                        <br>
                                        <img src="<?=base_url()?>images/cupones/<?=$cupon->Id?>.png" >
                                <?
                                        }
                                ?>
                        </div>

                </div>
        	<? 
	    } else {
            switch ($carnet){

		case '2':
	?>	
		<div class="carnet frente">
        		<div class="imagen">
            		<?
            			if(file_exists('images/socios/'.$socio->Id.'.jpg')){
            		?>
                			<img src="<?=base_url()?>images/image_carnet.php?img=socios/<?=$socio->Id?>.jpg" width="80">
            		<?
            		}else{
            		?>
                			<img src="<?=base_url()?>images/noPic.jpg" width="80">
            		<?
            		}
            		?>
        		</div>
        		<div class="datos">
            	
			<div style="font-weight:bold"><?=$apynom?></div>
            		<div style="font-weight:bold">DNI <?=$socio->dni?></div>
            		<div style="font-weight:bold"><?=$socio->observaciones?></div>
        		</div>
        	</div>
    		<div class="carnet dorso"> </div>
	<? break;

                case '3':
        ?>
                <div class="carnet frente">

                        <?
                        $fecha = explode(' ', $socio->alta);
                        $fecha = explode('-', $fecha[0]);
                        $fecha = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];

                        ?>
                        <div class="datos">

                        	<div style="font-weight:bold">
					<div style="width: 80%; float:left"> <?=$apynom_2?></div>
					<div style="width: 20%; float:right"> <?=$socio->Id?></div>
                        	</div>
                        	<div style="font-weight:bold">
                        		<div style="width: 80%; float:left"><?=$socio->domicilio?></div>
					<div style="width: 20%; float:right"> <?=$fecha?></div>
                        	</div>
                        </div>
			<div class="barcode">
                		<?
                			if( file_exists("images/cupones/".$cupon->Id.".png") ){
                		?>
                			<br>
                			<img src="<?=base_url()?>images/cupones/<?=$cupon->Id?>.png" >
                		<?
                			}
                		?>
                	</div>
                </div>
                <div class="carnet dorso"> </div>


        <? break;

                case '4':
        ?>
                <div class="carnet dorso"> </div>
                <div class="carnet frente">

                        <?
                        $fecha = explode(' ', $socio->alta);
                        $fecha = explode('-', $fecha[0]);
                        $fecha = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];

                        ?>
                        <div class="datos">
                        	<div style="font-weight:bold">
					<div style="font-weight:bold"> <?=$apynom_2?> </div>
					<div style="font-weight:bold"> <?=$socio->Id?> </div>
                        		<div style="font-weight:bold"> <?=$socio->domicilio?> </div>
					<div style="font-weight:bold"> <?=$fecha?> </div>
                        	</div>
                        </div>
			<div class="barcode">
                		<?
                			if( file_exists("images/cupones/".$cupon->Id.".png") ){
                		?>
                			<br>
                			<img src="<?=base_url()?>images/cupones/<?=$cupon->Id?>.png" >
                		<?
                			}
                		?>
                	</div>
                </div>


        <? break;

		default:
	?>
		<div class="carnet frente"></div>
    		<div class="carnet dorso">
        	<div class="imagen">
            		<?
            			if(file_exists('images/socios/'.$socio->Id.'.jpg')){
            		?>
                			<img src="<?=base_url()?>images/image_carnet.php?img=socios/<?=$socio->Id?>.jpg" width="80">
            		<?
            		}else{
            		?>
                			<img src="<?=base_url()?>images/noPic.jpg" width="80">
            		<?
            		}
            		?>
        	</div>
        	
		<?
        		if($socio->socio_n){
            			$num = $socio->socio_n;
        		}else{
            			$num = $socio->Id;
        		}
        		$fecha = explode(' ', $socio->alta);
        		$fecha = explode('-', $fecha[0]);
        		$fecha = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];
        	?>

        	<div class="datos">
            	<div class="nap" style="font-weight:bold"><?=$apynom?></div>
            	<div class="nap" style="font-weight:bold">DNI <?=$socio->dni?></div>
            	<div class="nap" style="font-weight:bold">Socio No. <?=$num?></div>
            	<div class="nap" style="font-weight:bold">Ingreso <?=$fecha?></div>
        	</div>
        	<div align="right" class="barcode">
            	<?
            	if( file_exists("images/cupones/".$cupon->Id.".png") ){
            	?>
            	<br>
            	<img src="<?=base_url()?>images/cupones/<?=$cupon->Id?>.png" >  
            	<?
            	}
            	?>
    		</div>
	   </div>
	<?
		break;
		}
           } 
	}
        ?>
	</div>

</html>

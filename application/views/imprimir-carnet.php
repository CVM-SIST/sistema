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
			.carnet{
				width: 300px;
				height: 194px;
				float: right;
			}
		    <?
		    switch ($socio->categoria){
	
		case '11':
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
            			width: 265px;
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
                case '15':
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

	<!-- <body onload="window.print(); window.close();"> -->

        <? 
	    $apynom = substr(trim($socio->nombre).", ".trim($socio->apellido), 0, 30);
	    $apynom_2 = substr(trim($socio->nombre)." ".trim($socio->apellido), 0, 30);
            switch ($socio->categoria){

		case '11':
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

                case '14':
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
        <? break;

                case '15':
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
	<?
		break;
	}
        ?>

    <!--
		<div style="float:left; width:48%">
            <div style="float:left; width:100px; border:2px solid #000; height:100px;">
            <?
            if(file_exists('images/socios/'.$socio->Id.'.jpg')){
                
            ?>
                <img src="<?=base_url()?>images/image_carnet.php?img=socios/<?=$socio->Id?>.jpg" width="100">
            <?
            }else{
            ?>
                <img src="<?=base_url()?>images/g1.jpg" width="100">
            <?
            }
            ?>                
            </div>
            <div align="left" style="float:left; width:60%; padding-left:4%; line-height:20px;">
                &nbsp;<strong>Apellido:</strong> <?=$socio->apellido?><br>
                &nbsp;<strong>Nombres:</strong> <?=$socio->nombre?><br>
                &nbsp;<strong>D.N.I.:</strong> <?=$socio->dni?><br>

                <? 
                if($socio->socio_n){
                    $num = $socio->socio_n;
                }else{
                    $num = $socio->Id;
                }
                ?>

                &nbsp;<strong>Socio N°:</strong> <?=$num?><br>

                <?
                $fecha = explode(' ', $socio->alta);
                $fecha = explode('-', $fecha[0]);
                $fecha = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];
                ?>

                <strong>Fecha de Ingreso:</strong> <?=$fecha?>

            </div>
            <div class="clear:both;"></div>
        </div>
        <div style="float:left; width:44%; border-left:1px dotted #000; padding-left:1%" align="center">
            <div style="float:left; width:29%;">
                <img src="<?=base_url()?>images/carnet.png" width="90">
            </div>
            <div style="float:left; width:65%; padding-top:0px;">
                <?
                if( file_exists("images/cupones/".$cupon->Id.".png") ){
                ?>
                    <img src="<?=base_url()?>images/cupones/<?=$cupon->Id?>.png">                   
                <div align="center" style="padding-top:10px; padding-left: 35px">
                    Valor de la Cuota: $ <?=$monto?>
                </div>
                <?
                }
                ?>
            </div>
            <div style="clear:both;"></div>
            <span style="font-size:10px;">Este carnet carece de validez si no se acompaña con el recibo actualizado de la cuota social.</span>
      
        </div>
	</body>-->
</html>

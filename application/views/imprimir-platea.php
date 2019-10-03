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
            		float: left;
        	}
            <?
            switch ($platea->actividad){
	
		case 'Futbol':
			$frente=base_url()."images/Platea_Futbol_Frente_2019.jpg";
			$dorso=base_url()."images/Platea_Futbol_Dorso_2019.jpg";
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
            			width: 250px;
            			color: #000;
				margin-left:28px;
				margin-top:85px;
            			line-height: 30px;
        		}
        		.clear{
            			clear: both;
        		}
        		.barcode{
            			margin-top: 0px;
        		}
			<?
			break;
		case 'Basquet':
			$frente=base_url()."images/Platea_Basquet_Frente_2019.jpg";
			$dorso=base_url()."images/Platea_Basquet_Dorso_2019.jpg";
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
            			width: 250px;
            			color: #FFF;
				margin-left:28px;
            			margin-top:85px;
            			line-height: 30px;
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
		$largo1=strlen(trim($platea->socio));
		if ( $largo1 > 40 ) {
			$relleno1=5;
			$nombre_socio=substr(trim($platea->socio), 0, 40);
		} else {
			if ( $largo1 < 25 ) {
				if ( $largo1 < 15 ) {
					$relleno1=63-$largo1;
					$nombre_socio=trim($platea->socio);
				} else { 
					$relleno1=50-$largo1;
					$nombre_socio=trim($platea->socio);
				}
			} else {
				$relleno1=50-$largo1;
				$nombre_socio=trim($platea->socio);
			}
		}
		$largo2=strlen($platea->dni);
		$relleno2=70-$largo2;
?>

		<div class="carnet frente">
        		<div class="datos">
            	
			   <div style="font-weight:bold">
				<?=$platea->socio?>  
<?for ($i=1; $i<=$relleno1; $i++ ) { echo "&nbsp"; }?>
				<?=$platea->fila?>
			   </div>
            		   <div style="font-weight:bold">
				<?=$platea->dni?> 
<?for ($i=1; $i<=$relleno2; $i++ ) { echo "&nbsp"; }?>
				<?=$platea->numero?>
			   </div>
        		</div>

        	</div>
    		<div class="carnet dorso"> </div>


</html>

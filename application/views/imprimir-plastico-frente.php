<html>
	<head>
		<meta charset="utf-8">
		<title>Imprimir</title>
		<style type="text/css">
		body{
			font-family: 'Arial';
			font-size: 12px;            
			background: #ffffff;            
		}
        .carnet{
            margin-top:2px;
            width: 300px;
            height: 194px;
            float: left;
        }
	<?

        $ent_directorio = $this->session->userdata('ent_directorio');
        $frente=base_url()."entidades/".$ent_directorio."/carnet-frente.jpg";
        $dorso=base_url()."entidades/".$ent_directorio."/carnet-dorso.jpg";

	?>
        .frente{
            background-image:url(<?=$frente?>); 
	    background-size: 100% 100%;
        }
        .clear{
            clear: both;
        }
		</style>
	</head>

	<!-- <body onload="window.print(); window.close();"> -->

    		<div class="carnet frente">
		</div>

</html>

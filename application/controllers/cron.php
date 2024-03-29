<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Cron extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        if($_GET['order'] != 'asdqwe'){exit('No Permitido');}
        $this->load->helper('url');
    }

	function index()
	{
		return false;
	}

    public function decode($value='')
    {
        $this->load->database();
        $this->db->where('barcode', '');
        $query = $this->db->get('cupones',10,0);        
        if( $query->num_rows() == 0 ){ return false; }
        $cupones = $query->result();
        $query->free_result();
        foreach ($cupones as $cupon) {
            var_dump($cupon);
            $content = file_get_contents("https://zxing.org/w/decode?u=http%3A%2F%2Fclubvillamitre.com%2Fimages%2Fcupones%2F".$cupon->Id.".png");
            list($a,$estado1) = explode('<title>',$content);
            $estado = explode('</title>',$estado1);
            if($estado[0] == "Decode Succeeded"){
                list($a,$pre1) = explode('<pre>',$estado[1]);
                $pre = explode('</pre>',$pre1);                
                $cup = array('barcode' => $pre[0]);
                $this->db->where('Id', $cupon->Id);
                $this->db->update('cupones', $cup);
            }
        }
    }

    function depuracion_files(){ // esta funcion depura archivos viejos
        $this->load->model("pagos_model");
	$cupones = $this->pagos_model->get_cupones_old();
	$cant=0;
	foreach ( $cupones as $cupon ) {
		$cant++;
		if ( $cant < 30 ) {
                	$cupon = 'images/cupones/'.$cupon->Id.'.png';
			unlink($cupon);
		}
        }
	echo "Hay $cant cupones para depurar";
    }

    function facturacion(){ // esta funcion genera la facturacion del mes el dia
    

    $this->load->model("pagos_model");
	$this->load->model("socios_model");
	$this->load->model("debtarj_model");
	$this->load->model("tarjeta_model");
	$this->load->model("general_model");

        if ($this->uri->segment(3)) {
		$xhoy = $this->uri->segment(3);
	} else {
		$xhoy=date('Y-m-d');
	}
	
	echo $xhoy;

	// Periodo y fechas del proceso.....
	$xanio=date('Y', strtotime($xhoy));
	$xmes=date('m', strtotime($xhoy));
	$xperiodo=date('Ym', strtotime($xhoy));
	$xahora=date('Y-m-d G:i:s', strtotime($xhoy));
	$xlim1=date('Y-m-',strtotime($xhoy)).'25';
	$xlim2=date('Y-m-t', strtotime($xhoy));

        //log
        $file = './application/logs/facturacion-'.$xanio.'-'.$xmes.'.log';        
        $file_col = './application/logs/cobranza_col-'.$xanio.'-'.$xmes.'.csv';        
        if( !file_exists($file) ){
            echo "creo";
            $log = fopen($file,'w');
            $col = fopen($file_col,'w');
        }else{
            echo "existe";
            $log = fopen($file,'a');
            $col = fopen($file_col,'a');
        }
        //chequeamos el estado del cron
        if(!$cron_state = $this->pagos_model->check_cron($xperiodo)){
            //el cron ya finalizó
            $txt = date('H:i:s').": Intento de ejecución de Cron Finalizado! \n";
            fwrite($log, $txt);            
            exit();
        }
  
        if($cron_state == 'iniciado'){
            $txt = date('H:i:s').": Inicio de Cron... \n";
            fwrite($log, $txt);                        

	    $debitos=$this->debitos_tarjetas($xperiodo, $log); // aplicamos todos los pagos de los debitos realizados en el mes
	    $this->pagos_model->update_facturacion_cron($xperiodo,4,$debitos['cant'],$debitos['importe']);

            $soc_susp=$this->suspender($log); // suspendemos socios que deban mas de 4 meses de cuota social
	    // Actualizo el registro de facturacion_cron con los socios suspendidos
	    $this->pagos_model->update_facturacion_cron($xperiodo,1,$soc_susp,0);
            $this->db->truncate('facturacion_mails'); //limpiamos la db de mails
            fwrite($log, date('H:i:s').' - Truncate mails\n');                        

	    // Meto una cabecera para identificar los envios de mails de facturacion del mes
            $envio = array(
                    'titulo' => "Facturacion Mes $xperiodo",
                    'grupo' => "FactMes",
                    'data' => json_encode(array("total"=>0, "enviados"=>0, "errores"=>0))
                    );
	    $this->general_model->insert_envio($envio);

            //$this->email_a_suspendidos();
            //fwrite($log, date('H:i:s').' - Emails suspendidos');                        
            $this->db->update('socios',array('facturado'=>0)); //establecemos todos los socios como no facturados
            fwrite($log, date('H:i:s').' - Indicador facturado en 0 \n');                        
		echo "cumpleaños";
    		$cumpleanios = $this->socios_model->get_cumpleanios(); //buscamos los que cumplen 18 años
		$cump=0;
    		foreach ($cumpleanios as $menor) {
    			$this->socios_model->actualizar_menor($menor->Id); //los quitamos del grupo familiar y cambiamos la categoria a mayor
                $txt = date('H:i:s').": Actualización de categoría socio a mayor #".$menor->Id.'-'.$menor->apellido.', '.$menor->nombre." \n";
                fwrite($log, $txt);   
			$cump++;
    		}
            fwrite($log, date('H:i:s').' - Cambio de categoria mayor \n');                        
	    // Actualizo el registro de facturacion_cron con los socios que cambiaron de categoria por mayoria de edad
	    $this->pagos_model->update_facturacion_cron($xperiodo,2,$cump,0);

        }else if($cron_state == 'en_curso'){
            $txt = date('H:i:s').": Reanudando Cron... \n";
            fwrite($log, $txt);
        }
  

	// Busco los socios que tienen que pagar
	$socios = $this->socios_model->get_socios_pagan(true);
	// Si no encontre ninguno logeo y corto
        if(!$socios){ 
            $txt = date('H:i:s').": No se encontraron socios a facturar \n";
            fwrite($log, $txt);
            exit(); 
        }else{
	// Logeo la cantidad de total de asociados encontrados para facturar
            $txt = date('H:i:s').": Se encontraron ".count($socios)." socios a facturar \n";
            fwrite($log, $txt);            
        }
	// Ciclo los asociados a facturar
	foreach ($socios as $socio) {		
		// Busco el valor de la cuota social a pagar
		$cuota = $this->pagos_model->get_monto_socio($socio->Id);
		// Si tiene categoria de NO SOCIO no genero cuota
		$descripcion = '<strong>Categoría:</strong> '.$cuota['categoria'];
		if ( $cuota['categoria'] != 'No Socio' ) {
			// Si es un grupo familiar detallo los integrantes
			if($cuota['categoria'] == 'Grupo Familiar'){
				$descripcion .= '<br><strong>Integrantes:</strong> ';
				foreach ($cuota['familiares'] as $familiar) {
		    			$descripcion .= "<li>".$familiar['datos']->nombre." ".$familiar['datos']->apellido."</li>";
		    		}
			}
			$descripcion .= '<br><strong>Detalles</strong>:<br>';
			$descripcion .= 'Cuota Mensual '.$cuota['categoria'].' -';
                	if($cuota['descuento'] > 0.00){
                		$descripcion .= "$ ".$cuota['cuota_neta']." &nbsp;<label class='label label-info'>".$cuota['descuento']."% BECADO</label>";
            		}
            		$descripcion .= '$ '.$cuota['cuota'].'<br>';

			// Inserto el pago de la cuota (tipo=1)
            		$pago = array(
                		'sid' => $socio->Id, 
                		'tutor_id' => $socio->Id,
                		'aid' => 0, 
                		'generadoel' => $xhoy,
                		'descripcion' => $descripcion,
                		'monto' => $cuota['cuota'],                
                		'tipo' => 1,                
                		);
			// Si tiene la cuota social bonificada la doy por paga (estado=0)
                	if($pago['monto'] <= 0){                    
				$pago['estado'] = 0;
                    		$pago['pagadoel'] = $xahora;
                	}
            		$this->pagos_model->insert_pago_nuevo($pago);
		}

		// Ciclo las actividades que tiene relacionadas el asociado
		foreach ($cuota['actividades']['actividad'] as $actividad) {	       
			// Por ahora no facturamos la primer cuota en la facturacion
			// Si la actividad tiene cuota inicial y es la primer cuota a facturar le ponemos cuota inicial
			// TODO AHG lo comento hasta definir bien si se va a facturar con el mes, por ahora solo con el alta de la relacion
			/*
			if ($actividad->cuota_inicial > 0) {
				// TODO AHG Falta condicionar solo a la primer vez - usar $actividad->alta y date
                		$descr_inicial = 'Cuota Inicial '.$actividad->nombre.' - $ '.$actividad->cuota_inicial.'<br>';
	                        // Inserto el pago de la actividad (tipo=4)
                        	$pago = array(
                                	'sid' => $socio->Id,
                                	'tutor_id' => $socio->Id,
                                	'aid' => $actividad->Id,
                                	'generadoel' => $xhoy,
                                	'descripcion' => $descr_inicial,
                                	'monto' => $actividad->cuota_inicial,
                                	'tipo' => 4,
                        	);
                        	$this->pagos_model->insert_pago_nuevo($pago);
			}
			// Fin comentario TODO AHG
			*/

			// Facturamos el valor mensual de la actividad
                    	$valor = $actividad->precio;
			// AHG 20220628 si tiene seguro lo sumo al valor - modificacion solicitada por Simon
			if ( $actividad->seguro > 0 && $actividad->federado == 0 ) {
                                $valor = $actividad->precio + $actividad->seguro;
			}
                	$descripcion .= 'Cuota Mensual '.$actividad->nombre.' - $ '.$valor;
	        	$desc_pago = 'Cuota Mensual '.$actividad->nombre.' - $ '.$valor;
			// Fin comentario 20220628
                	if($actividad->descuento > 0){
				$tbeca = $actividad->monto_porcentaje;
				switch ( $tbeca ) {
                			case 0:
                        			$tipo_beca = "BECA $";
                        			break;
                			case 1:
                        			$tipo_beca = "BECA %";
                        			break;
                			case 2:
                        			$tipo_beca = "BONIF SERV $";
                        			break;
                			case 3:
                        			$tipo_beca = "BONIF SERV %";
                        			break;
                			case 4:
                        			$tipo_beca = "BONIF COMPET $";
                        			break;
                			case 5:
                        			$tipo_beca = "BONIF COMPET %";
                        			break;
                			case 6:
                        			$tipo_beca = "BONIF HNO $";
                        			break;
                			case 7:
                        			$tipo_beca = "BONIF HNO %";
                        			break;
                			default:
                        			$tipo_beca = "XYX";
                        			break;
                        	}
				if ( $tbeca == 0 || $tbeca == 2 || $tbeca == 4 || $tbeca == 6 ) {
					if ( $actividad->precio > 0 ) {
                    				$valor = $actividad->precio + $actividad->seguro - $actividad->descuento;
					} else {
						$valor = 0;
					}
				} else {
                    			$valor = $actividad->precio + $actividad->seguro - ( ( $actividad->precio + $actividad->seguro ) * $actividad->descuento / 100);
				}
                    		$descripcion .= '&nbsp; <label class="label label-info">'.$actividad->descuento.'-'.$tipo_beca.'</label> $ '.$valor;                    
                    		$desc_pago .= '<label class="label label-info">'.$actividad->descuento.'-'.$tipo_beca.'</label> $ '.$valor;
	 		} 
                	$descripcion .= '<br>';
                	$desc_pago .= '<br>';

			// Inserto el pago de la actividad (tipo=4)
                	$pago = array(
                    		'sid' => $socio->Id,
                    		'tutor_id' => $socio->Id,
                    		'aid' => $actividad->Id,
                 		'generadoel' => $xhoy,
                    		'descripcion' => $desc_pago,
                    		'monto' => $valor,
                    		'tipo' => 4,
                    	);
			// Si tiene la actividad bonificada la doy por paga (estado=0)
                	if($pago['monto'] <= 0){                    
				$pago['estado'] = 0;
                    		$pago['pagadoel'] = $xahora;
                	}
                	$this->pagos_model->insert_pago_nuevo($pago);

			// AHG 20220628 - comento la facturacion por separado del seguro - solicitado por Simon
			// Si la actividad tiene seguro y el socio no es federado de la actividad facturo el seguro
			//if ( $actividad->seguro > 0 && $actividad->federado == 0 ) {
                		//$descripcion .= 'Seguro '.$actividad->nombre.' - $ '.$actividad->seguro;
				//$des = 'Seguro '.$actividad->nombre.' - $ '.$actividad->seguro;

				// Inserto el pago del seguro
                		//$pago = array(
                    			//'sid' => $socio->Id,
                    			//'tutor_id' => $socio->Id,
                    			//'aid' => $actividad->Id,
                 			//'generadoel' => $xhoy,
                    			//'descripcion' => $des,
                    			//'monto' => $actividad->seguro,
                    			//'tipo' => 6,
                    		//);
                		//$this->pagos_model->insert_pago_nuevo($pago);
			//}
			// FIN AHG 20220628
	        } 

		// Si tiene familiares a cargo
	        if($cuota['familiares'] != 0){
			// Ciclo cada familiar
               		foreach ($cuota['familiares'] as $familiar) {
				// Busco las actividades de ese familiar
               			foreach($familiar['actividades']['actividad'] as $actividad){		               		
					$valor = $actividad->precio;
					// AHG 20220628 si tiene seguro lo sumo al valor - modificacion solicitada por Simon
					if ( $actividad->seguro > 0 && $actividad->federado == 0 ) {
                                		$valor = $actividad->precio + $actividad->seguro;
					}
					// Fin comentario 20220628
                    			$descripcion .= 'Cuota Mensual '.$actividad->nombre.' ['.$familiar['datos']->nombre.' '.$familiar['datos']->apellido.'] - $ '.$valor;
                    			$desc_pago .= 'Cuota Mensual '.$actividad->nombre.' ['.$familiar['datos']->nombre.' '.$familiar['datos']->apellido.'] - $ '.$valor;
                        		if($actividad->descuento > 0){
                                		$tbeca = $actividad->monto_porcentaje;
                                		switch ( $tbeca ) {
                                        		case 0:
                                                		$tipo_beca = "BECA $";
                                                		break;
                                        		case 1:
                                                		$tipo_beca = "BECA %";
                                                		break;
                                        		case 2:
                                                		$tipo_beca = "BONIF SERV $";
                                                		break;
                                        		case 3:
                                                		$tipo_beca = "BONIF SERV %";
                                                		break;
                                        		case 4:
                                                		$tipo_beca = "BONIF COMPET $";
                                                		break;
                                        		case 5:
                                                		$tipo_beca = "BONIF COMPET %";
                                                		break;
                                        		case 6:
                                                		$tipo_beca = "BONIF HNO $";
                                                		break;
                                        		case 7:
                                                		$tipo_beca = "BONIF HNO %";
                                                		break;
                                        		default:
                                                		$tipo_beca = "XYX";
                                                		break;
                                		}
                                		if ( $tbeca == 0 || $tbeca == 2 || $tbeca == 4 || $tbeca == 6 ) {
                                        		if ( $actividad->precio > 0 ) {
                                                		$valor = $actividad->precio + $actividad->seguro - $actividad->descuento;
                                        		} else {
                                                		$valor = 0;
                                        		}
                                		} else {
                                        		$valor = $actividad->precio + $actividad->seguro - ( ( $actividad->precio + $actividad->seguro ) * $actividad->descuento / 100);
                                		}
                                		$descripcion .= '&nbsp; <label class="label label-info">'.$actividad->descuento.'-'.$tipo_beca.'</label> $ '.$valor;
                                		$desc_pago .= '<label class="label label-info">'.$actividad->descuento.'-'.$tipo_beca.'</label> $ '.$valor;
                        		}
                        		$descripcion .= '<br>';
                        		$desc_pago .= '<br>';

					// Inserto el pago de la actividad del familia (tipo=4)
                    			$pago = array(
                        			'sid' => $familiar['datos']->Id,
                        			'tutor_id' => $socio->Id,
                        			'aid' => $actividad->Id,
                        			'generadoel' => $xhoy,
                        			'descripcion' => $desc_pago,
                        			'monto' => $valor,
                        			'tipo' => 4,
                        			);
			
					// Si tiene la actividad bonificada la doy por paga (estado=0)
                			if($pago['monto'] <= 0){                    
						$pago['estado'] = 0;
                    				$pago['pagadoel'] = $xahora;
                			}

                    			$this->pagos_model->insert_pago_nuevo($pago);

					// AHG 20220628 comento la facturacion separada del seguro - solicitado por Simon
                        		// Si la actividad tiene seguro y el socio no es federado de la actividad facturo el seguro
                        		//if ( $actividad->seguro > 0 && $actividad->federado == 0 ) {
                                		//$descripcion .= 'Seguro '.$actividad->nombre.' - $ '.$actividad->seguro;
                                		//$des = 'Seguro '.$actividad->nombre.' - $ '.$actividad->seguro;
		
                                		// Inserto el pago del seguro
                                		//$pago = array(
                                        		//'sid' => $socio->Id,
                                        		//'tutor_id' => $socio->Id,
                                        		//'aid' => $actividad->Id,
                                        		//'generadoel' => $xhoy,
                                        		//'descripcion' => $des,
                                        		//'monto' => $actividad->seguro,
                                        		//'tipo' => 6,
                                		//);
                                		//$this->pagos_model->insert_pago_nuevo($pago);
                        		//}

               			}
               		}
           	}

		// Cuota Excedente
           	if($cuota['excedente'] >= 1){
                	$descripcion .= 'Socio Extra (x'.$cuota['excedente'].') - $ '.$cuota['monto_excedente'].'<br>';
	         	$des = 'Socio Extra (x'.$cuota['excedente'].') - $ '.$cuota['monto_excedente'].'<br>';
			// Inserto el pago de la cuota excedente
                	$pago = array(
                    		'sid' => $socio->Id,    
                    		'tutor_id' => $socio->Id,                
                    		'aid' => 0,
                    		'generadoel' => $xhoy,
                    		'descripcion' => $des,
                    		'monto' => $cuota['monto_excedente'],
                    		'tipo' => 1,
                    		);
                		$this->pagos_model->insert_pago_nuevo($pago);
		}

		//financiacion de deuda
		$deuda_financiada = 0;
		$planes = $this->pagos_model->get_financiado_mensual($socio->Id);
		// Si tiene planes de financiacion activos
            	if($planes){
			// Ciclo cada plan
    			foreach ($planes as $plan) {                
                		$this->pagos_model->update_cuota($plan->Id);

				$ncuota=$plan->actual+1;
                    		$descripcion .= 'Financiación de Deuda ('.$plan->detalle.' - Cuota: '.$ncuota.'/'.$plan->cuotas.') - $ '.round($plan->monto/$plan->cuotas,2).'<br>';
                    		$des = 'Financiación de Deuda ('.$plan->detalle.' - Cuota: '.$ncuota.'/'.$plan->cuotas.') - $ '.round($plan->monto/$plan->cuotas,2).'<br>';
				// Inserto el pago del plan de financiacion (tipo=3)
                    		$pago = array(
                        		'sid' => $socio->Id,  
                        		'tutor_id' => $socio->Id,                  
                        		'aid' => 0,
                        		'generadoel' => $xhoy,
                        		'descripcion' => $des,
                        		'monto' => round($plan->monto/$plan->cuotas,2),
                        		'tipo' => 3,
                        		);
                    		$this->pagos_model->insert_pago_nuevo($pago);

    				$deuda_financiada = $deuda_financiada + round($plan->monto/$plan->cuotas,2);

    			}
                	$deuda_financiada = round($deuda_financiada,2);
            	}else{
                	$deuda_financiada = 0;                
            	}
            	//end financiacion de deuda	

		// Obtiene el saldo total de la ultima fila de facturacion!!!
	        $total = $this->pagos_model->get_socio_total($socio->Id);
		// Le agrega la cuota facturada este mes al total del saldo
	        $total = $total - ($cuota['total']);
		$data = array(
			"sid" => $socio->Id,
			"date" => $xhoy,
			"descripcion" => $descripcion,
			"debe" => $cuota['total'],
			"haber" => '0',
			"total" => $total
		);

            	$deuda = $this->pagos_model->get_deuda($socio->Id);

		// Inserta el registro de facturacion del mes
		$this->pagos_model->insert_facturacion($data);

		// Actualizo en facturacion_cron el asociado facturado
		$this->pagos_model->update_facturacion_cron($xperiodo,3, 1, $cuota['total']);

		// armo mail
		$mail = $this->socios_model->get_resumen_mail($socio->Id);

            	$cuota3 = $mail['resumen'];            

		// Armo encabezado con escudo y datos de cabecera
		$cuerpo  = "<table class='table table-hover' style='font-family:verdana' width='100%' >";
        	$cuerpo .= "<thead>";
		$cuerpo .= "<th> <img src='http://clubvillamitre.com/images/cvm-encabezado-mail.jpg' alt='' ></th>";
                $cuerpo .= "</tr>";
        	$cuerpo .= "</thead>";
		$cuerpo .= "</table>";

		// Datos del Titular
		if ( $mail['socio_n'] > 0 ) {
            		$cuerpo .= '<h3 style="font-family:verdana"><strong>Titular:</strong> '.$mail['sid'].'-'.$cuota3['titular'].' ( Nro. Socio: '.$mail['socio_n'].')</h3>';
		} else {
            		$cuerpo .= '<h3 style="font-family:verdana"><strong>Titular:</strong> '.$mail['sid'].'-'.$cuota3['titular'].'</h3>';
		}

		// Analizo deuda previa a la facturación para poner mensaje acorde
                if($deuda < 0 ){
                    	$cuerpo .= "<h4 style='font-family:verdana' ><strong>Al dia de la fecha Ud. adeuda $ ".abs($deuda)."</strong></h4>";
                    	$cuerpo .= "<h4 style='font-family:verdana' ><strong>PONGASE EN CONTACTO CON SECRETARIA PARA REGULARIZAR SU SITUACION</strong></h4>";
                } else {
			if($deuda == 0) {
                    		$cuerpo .= "<h4 style='font-family:verdana' ><strong>Usted esta al dia con sus cuotas</strong></h4>";
			} else {
				if ( $mail['debtarj'] == null ) {
                    			$cuerpo .= "<h4 style='font-family:verdana' ><strong>Usted posee un saldo a favor de $ ".abs($deuda)."</strong></h4>";                
				}
			}
            	}

		// Si es con grupo familiar
            	if($cuota3['categoria'] == 'Grupo Familiar'){
                	$cuerpo .= "<h5 style='font-family:verdana;'><strong>Integrantes</strong></h5><ul>";
                	foreach ($cuota3['familiares'] as $familiar) {          
                    		$cuerpo .= "<li style='font-family:verdana;'>".$familiar['datos']->nombre." ".$familiar['datos']->apellido."</li>";                    
                	}                    
                	$cuerpo .= '</ul>';            
            	}
            

		// Armo tabla de conceptos facturados en el mes

		// Titulos
            	$cuerpo .= '<table class="table table-hover" width="100%" style="font-family: "Verdana";">
                		<thead>
                    		  <tr style="background-color: #666 !important; color:#FFF;">                        
                        	    <th style="padding:5px;" align="left">Facturacion del Mes</th>
                        	    <th style="padding:5px;" align="right">Monto</th>                        
                    		  </tr>
                		</thead>
                	    <tbody> ';

		// Cuota de Socio
		$cuerpo .= '<tr style="background: #CCC;">
                        	<td style="padding: 5px;">Cuota Mensual '.$cuota3['categoria'].'</td>
                        	<td style="padding: 5px;" align="right">$ '.$cuota3['cuota'].'</td>
                    	    </tr>';
		// Si tiene descuento en la cuota social
		if($cuota3['descuento'] != 0.00){                        
                        $cuerpo .= '<tr style="background: #CCC;">                    
                                	<td style="padding: 5px;">Descuento sobre cuota social</td>
                                	<td style="padding: 5px;" align="right">'.$cuota3['descuento'].'%</td>
                            	</tr>';                        
		}
	
		// Actividades
		foreach ($cuota3['actividades']['actividad'] as $actividad) {
			// Seguro sumado al valor de la actividad para no discriminarlo 20220628 - Solicitado por Simon
		    $valor = $actividad->precio + $actividad->seguro;
                    $cuerpo .= '<tr style="background: #CCC;">
                        	  <td style="padding: 5px;">Cuota Mensual '.$actividad->nombre.'</td>
                        	  <td style="padding: 5px;" align="right">$ '.$valor.'</td>
                    		</tr>';                        

		    // Si tiene descuento lo pongo detallado
		    if ( $actividad->descuento > 0 ) {
			$tbeca = $actividad->monto_porcentaje;
			if ( $tbeca == 0 || $tbeca == 2 || $tbeca == 4 || $tbeca == 6 ) {
				$msj_act=$actividad->descuento."$ ";
				$msj_act_valor=$actividad->precio+$actividad->seguro-$actividad->descuento;
			} else {
				$msj_act=$actividad->descuento."% ";
				$msj_act_valor=($actividad->precio+$actividad->seguro) * $actividad->descuento / 100;
			}
                    	$cuerpo .= '<tr style="background: #CCC;">
                        	  	<td style="padding: 5px;">Descuento sobre Actividad '.$actividad->nombre.$msj_act.'</td>
                        	  	<td style="padding: 5px;" align="right">-$ '.$msj_act_valor.'</td>
                    			</tr>';                        
		    }
		    // Si tiene seguro lo pongo detallado
		    // Seguro sumado al valor de la actividad para no discriminarlo 20220628 - Solicitado por Simon
		    //if ( $actividad->seguro > 0 ) {
                    	//$cuerpo .= '<tr style="background: #CCC;">
                        	  	//<td style="padding: 5px;">Seguro Actividad '.$actividad->nombre.'</td>
                        	  	//<td style="padding: 5px;" align="right">$ '.$actividad->seguro.'</td>
                    			//</tr>';                        
		    //}
                } 

		// Familiares
		if($cuota3['familiares'] != 0){
			foreach ($cuota3['familiares'] as $familiar) {
				foreach($familiar['actividades']['actividad'] as $actividad){                           
					// Seguro sumado al valor de la actividad para no discriminarlo 20220628 - Solicitado por Simon
		    			$valor = $actividad->precio + $actividad->seguro;
                            		$cuerpo .= '<tr style="background: #CCC;">                    
                                			<td style="padding: 5px;">Cuota Mensual '.$actividad->nombre.' ['.$familiar['datos']->nombre.' '.$familiar['datos']->apellido.' ]</td>
                                			<td style="padding: 5px;" align="right">$ '.$valor.'</td>
                            			    </tr>';
                    			// Si tiene descuento lo pongo detallado
                    			if ( $actividad->descuento > 0 ) {
						$tbeca = $actividad->monto_porcentaje;
                        			if ( $tbeca == 0 || $tbeca == 2 || $tbeca == 4 || $tbeca == 6) {
                                			$msj_act=$actividad->descuento."$ ";
                                			$msj_act_valor=$actividad->precio+$actividad->seguro-$actividad->descuento;
                        			} else {
                                			$msj_act=$actividad->descuento."% ";
                                			$msj_act_valor=($actividad->precio+$actividad->seguro) * $actividad->descuento / 100;
                        			}
                        			$cuerpo .= '<tr style="background: #CCC;">
                                        			<td style="padding: 5px;">Descuento sobre Actividad '.$actividad->nombre.$msj_act.'</td>
                                        			<td style="padding: 5px;" align="right">-$ '.$msj_act_valor.'</td>
                                        		</tr>';                        
                    			}
                    			// Si tiene seguro lo pongo detallado
					// Seguro sumado al valor de la actividad para no discriminarlo 20220628 - Solicitado por Simon
                    			//if ( $actividad->seguro > 0 ) {
                        			//$cuerpo .= '<tr style="background: #CCC;">
                                        			//<td style="padding: 5px;">Seguro Actividad '.$actividad->nombre.'</td>
                                        			//<td style="padding: 5px;" align="right">$ '.$actividad->seguro.'</td>
                                        			//</tr>';                        
                    			//}

                            	}                                   
                        }
		}

		// Cuota Excedente
		if($cuota3['excedente'] >= 1){
			$cuerpo .='<tr style="background: #CCC;">                    
                                	<td style="padding: 5px;">Socio Extra (x'.$cuota3['excedente'].')</td>
                                	<td style="padding: 5px;" align="right">$ '.$cuota3['monto_excedente'].'</td>
                                   </tr>';                        
		}

		// Financiacion
		if($cuota3['financiacion']){
			foreach ($cuota3['financiacion'] as $plan) {                 
				$cuerpo .= '<tr style="background: #CCC;">                    
                                		<td style="padding: 5px;">Financiacion de Deuda - Cuota '.$plan->actual.'/'.$plan->cuotas.' ('.$plan->detalle.')</td>
                                		<td style="padding: 5px;" align="right">$ '.round($plan->monto/$plan->cuotas,2).'</td>
                            		</tr>';
                        }
		}

                $cuerpo .= '</tbody>
                	<tfoot>
                    	<tr>                        
                        	<th style="font-family:verdana;" align="left">TOTAL FACTURADO DEL MES</th>
                        	<th style="font-family:verdana;" align="right">$ '.$cuota3['total'].'</th>                        
                    	</tr> ';

		$resta_pagar = $cuota3['total'];
		if ( $deuda < 0 ) {
			$abs_deuda = abs($deuda);
			$abonar = abs($deuda)+$cuota3['total'];
			$cuerpo .= '<tr>                        
                        		<th style="font-family:verdana;" align="left">DEUDA ANTERIOR</th>
                        		<th style="font-family:verdana;" align="right">$ '.$abs_deuda.'</th>                        
                    		    </tr> 
                    		    <tr>                        
                        		<th style="font-family:verdana;" align="left">TOTAL A ABONAR</th>
                        		<th style="font-family:verdana;" align="right">$ '.$abonar.'</th>                        
                    		    </tr> ';
		} else { 
			if ( $deuda > 0 ) {
				if ( $mail['debtarj'] == null ) {
	                        	$cuerpo .= '<tr>                        
                                        		<th style="font-family:verdana;" align="left">SALDO A FAVOR ANTERIOR</th>
                                        		<th style="font-family:verdana;" align="right">$ '.abs($deuda).'</th>                        
                                	    	</tr> ';
				} else {
	                        	$cuerpo .= '<tr>                        
                                        		<th style="font-family:verdana;" align="left">UD. ESTA ADHERIDO AL DEBITO AUTOMATICO</th>
                                	    	</tr> ';
		
				}
				$resta_pagar=$cuota3['total']-$deuda;
				if ( $resta_pagar > 0 ) {
					$cuerpo .= '<tr>                        
                                        		<th style="font-family:verdana;" align="left">TOTAL A ABONAR</th>
                                        		<th style="font-family:verdana;" align="right">$ '.$resta_pagar.'</th>                        
                                		</tr> ';
				} else { 
					if ( $resta_pagar < 0 ) {
						$cuerpo .= '<tr>                        
               	                         		<th style="font-family:verdana;" align="left">QUEDA A FAVOR</th>
               	                         		<th style="font-family:verdana;" align="right">$ '.abs($resta_pagar).'</th>                        
               	                 			</tr>';
					} else {
						if ( $resta_pagar == 0 ) {
							$cuerpo .= '<tr>                        
               	                         			<th style="font-family:verdana;" align="left">USTED ESTA AL DIA CON SUS PAGOS</th>
               	                         			<th style="font-family:verdana;" align="right">$ 0</th>                        
               	                 				</tr>';
						}
					}
				}

			}
		}
                $cuerpo .= '</tfoot> </table>';
            
            	// genero cupon para cuenta digital
		/* COMENTO ESTA GENERACION PORQUE ME VOY A QUEDAR CON UN SOLO CUPON PARA CADA ASOCIADO
            	$this->load->model('pagos_model');
            	$cupon = $this->pagos_model->get_cupon($mail['sid']);
            	if($cupon->monto == $cuota3['total']){
                	$cupon = base_url().'images/cupones/'.$cupon->Id.'.png';
            	}else{
                	$cupon = $this->cuentadigital($mail['sid'],$cuota3['titular'],$cuota3['total']);
                	if($cupon && $mail['sid'] != 0){
                    		$cupon_id = $this->pagos_model->generar_cupon($mail['sid'],$cuota3['total'],$cupon);
                    		$data = base64_decode($cupon['image']);
                    		$img = imagecreatefromstring($data);
                    		if ($img !== false) {
                        		//@header('Content-Type: image/png');
                        		imagepng($img,'images/cupones/'.$cupon_id.'.png',0);
                        		imagedestroy($img);
                        		$cupon = base_url().'images/cupones/'.$cupon_id.'.png';
                    		}else {
                        		echo 'Ocurrió un error.';
                		        $cupon = '';
                    		}
                	}
            	}

            	if($cupon){
                	$cuerpo .= '<br><br><img src="'.$cupon.'">';
            	}
		*/

		$cuerpo .= '';

		$acobrar= $mail['deuda'] - $cuota3['total'];

            	if($acobrar < 0){
                	$total = abs($acobrar);
                	$cuerpo .= '<p style="font-family:verdana; font-style:italic;">Recuerde que tiene 10 dias para regularizar su situacion, contactese con Secretaria</p>';

                    	// Aca grabo el archivo para mandar a cobrar a COL
			$col_periodo=$xperiodo;
			$col_socio=$socio->Id;
			$col_dni=$socio->dni;
			$col_apynom=$socio->apellido." ".$socio->nombre;
			$col_importe=$total;
			$col_fecha_lim=$xlim1;
			$col_recargo="0";
			$col_fecha_lim2=$xlim2;
            		$txt = '"'.$col_periodo.'","'.$col_socio.'","'.$col_dni.'","'.$col_apynom.'","'.$col_importe.'","'.$col_fecha_lim.'","'.$col_recargo.'","'.$col_fecha_lim2.'"'."\r\n";
            		fwrite($col, $txt);            

			// Actualizo en facturacion_cron el asociado facturado
			$this->pagos_model->update_facturacion_cron($xperiodo,5, 1, $col_importe);

			// Grabo en el archivo de facturacion_col
			$facturacion_col = array(
				'id' => 0,
                        	'sid' => $col_socio,
                        	'periodo' => $col_periodo,
                        	'importe' => $col_importe,
                        	'cta_socio' => 0,
                        	'actividades' => 0
                    	);
                    	$this->pagos_model->insert_facturacion_col($facturacion_col);

            	} else {
			if ($resta_pagar > 0 ) {
                		$cuerpo .= '<p style="font-family:verdana; font-style:italic;">Recuerde que tiene hasta el dia 10 para cancelar su saldo</p>';
			}
		}

                $cuerpo .= "<p style='font-family:verdana'>Le informamos que los socios que paguen sus cuotas con <b>tarjeta de credito VISA, COOPEPLUS o BBPS</b> tendran beneficios extras como sorteos de entradas a eventos deportivos del Club, Indumentaria, Vouchers de comida, entradas al cine, etc; entre otros. <b>LLAME A SECRETARIA Y HAGA EL CAMBIO</b> </p>";

                $cuerpo .= "<p style='font-family:verdana'>Recuerde que estando al dia Ud. puede disfrutar de los <b>beneficios de nuestra RED</b> </p>";
		$cuerpo .= "<p style='font-family:verdana'> <a href='https://villamitre.com.ar/beneficios-2/'>En este link podra encontrar COMERCIOS ADHERIDOS Y DESCUENTOS<img src='http://clubvillamitre.com/images/Logo-Red-de-BeneficiosOK_70.jpg'></a></p>";
		$cuerpo .= "<br> <br>";

		$cuerpo .= "<img src='http://clubvillamitre.com/images/cvm-zocalo-mail.jpg' alt=''>";

            	$email = array(
                    'email' => $mail['mail'],
                    'body' => $cuerpo
                );
		$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
            	if(preg_match($regex, $mail['mail'])){
                	$this->db->insert('facturacion_mails',$email);
            	}

		// fin armado mail

	 
		// Registro pago2 verificar.....
            	$this->pagos_model->registrar_pago2($socio->Id,0);
	
		// Actualizado el estado de socios como facturado (facturado=1)
            	$this->db->where('Id', $socio->Id);
            	$this->db->update('socios', array('facturado'=>1));

		// Registro en el log que asociado facture
            	$txt = date('H:i:s').": Socio #".$socio->Id." DNI=".$socio->dni."-".TRIM($socio->apellido).", ".TRIM($socio->nombre)." facturado \n";
            	fwrite($log, $txt);            

	}
	// Actualizo en la tabla facturacion_cron que termino el proceso de facturacion
        $this->db->like('date',$xhoy,'after');
        $this->db->update('facturacion_cron', array('en_curso'=>0));
	// Registro en el log que el proceso de facturacion termino
        $txt = date('H:i:s').": Cron Finalizado \n";
        fwrite($log, $txt);            
        fclose($log);      
        fclose($col);      

	$totales=$this->pagos_model->get_facturacion_cron($xperiodo);
	if ( $totales ) {
		$info_total="Los totales facturados son: <br> Socios Suspendidos: $totales->socios_suspendidos <br> Socios Pasados a Mayores: $totales->socios_cambio_mayor <br> Socios Facturados: $totales->socios_facturados por un total de $ $totales->total_facturado <br> Socios en Debito Tarjeta: $totales->socios_debito por un total de $ $totales->total_debito <br> Mandado a Cobranza COL: $totales->socios_col socios por un total de $ $totales->total_col";
	} else {
		$info_total="No encontre registro en facturacion_cron !!!!";
	}

	// Subo el archivo al WS de La Coope para que se importe
	// Primero lo renombre con el md5sum del contenido
        $file_col = './application/logs/cobranza_col-'.$xanio.'-'.$xmes.'.csv';
	$md5 = md5_file($file_col);
        $file_col_new = './application/logs/asociados_'.$md5.'.csv';
	rename($file_col,$file_col_new);
	// Luego llamo a la rutina que lo sube con el WS
	$this->_sube_facturacion_COL($file_col_new);

	// Me mando email de aviso que el proceso termino OK
        mail('cvm.agonzalez@gmail.com', "El proceso de Facturación Finalizó correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de facturación finalizó correctamente ".$xahora."\n".$info_total,'From: avisos_cvm@clubvillamitre.com'."\r\n");
	echo $info_total;
	}

    public function email_a_suspendidos()
    {
        $this->load->model('socios_model');
        $this->db->where('estado',1);
        $this->db->where('suspendido',1);
        $query = $this->db->get('socios');
        $socios_suspendidos = $query->result();
        //var_dump($socios_suspendidos);die;
        foreach ($socios_suspendidos as $socio) {
            $mail = $this->socios_model->get_resumen_mail($socio->Id);
            $total = ($mail['deuda']*-1);

            if($total <= 0){ continue; }

            $cuerpo = '<meta charset="UTF-8"><p><img src="http://clubvillamitre.com/images/vm-head.png" alt="" /></p>';
            $cuerpo .= '<h3><strong>Titular:</strong> '.$socio->nombre.' '.$socio->apellido.'</h3>';
            $cuerpo .= '<div style="padding:20px;background-color: #fdefee; border-color: #fad7db; color: #b13d31;">USUARIO SUSPENDIDO POR FALTA DE PAGO</div>';

            $this->db->where('sid', $socio->Id);
            $this->db->order_by('date', 'asc');
            $query = $this->db->get('facturacion');
            if( $query->num_rows() == 0 ){ continue; }
            $facturacion = $query->result();
            
            $cuerpo .= '<table width="100%" border="1">';
            $cuerpo .= '<thead><tr><th>Fecha</th><th>Descripcion</th><th>Debe</th><th>Haber</th><th>Total</th></tr></thead><tbody>';


            foreach ($facturacion as $f) {            
                $cuerpo .= '<tr><td>'.date('d-m-Y',strtotime($f->date)).'</td><td>'.$f->descripcion.'</td><td align="center">$ '.$f->debe.'</td><td align="center">$ '.$f->haber.'</td><td align="center">$ '.$f->total*(-1).'</td></tr>';
            }

            $cuerpo .= '</tbody></table>';
            $cuerpo .= '';

            $cuerpo .= '<h3>Su deuda total con el Club es de: $ '.$total.'</h3>';

            $cupon = $this->cuentadigital($socio->Id,$socio->nombre.' '.$socio->apellido,$total);
            if($cupon && $mail['sid'] != 0){
                $cupon_id = $this->pagos_model->generar_cupon($socio->Id,$total,$cupon);
                $data = base64_decode($cupon['image']);
                $img = imagecreatefromstring($data);
                if ($img !== false) {
                    //@header('Content-Type: image/png');
                    imagepng($img,'images/cupones/'.$cupon_id.'.png',0);
                    imagedestroy($img);
                    $cupon = base_url().'images/cupones/'.$cupon_id.'.png';
                }else {
                    echo 'Ocurrió un error.';
                    $cupon = '';
                }
            }

            $cuerpo .= '<br><br><img src="'.$cupon.'">';

            $email = array(
                    'email' => $socio->mail,
                    'body' => $cuerpo
                );
            $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
            if(preg_match($regex, $socio->mail)){
                $this->db->insert('facturacion_mails',$email);
            }
        }
    }

    public function check_mails() {
	//log
	$file = './application/logs/checkmails.log';
	if( !file_exists($file) ){
		echo "creo";
		$log = fopen($file,'w');
	} else {
		echo "existe";
		$log = fopen($file,'a');
	}
	// Tomo cien direcciones de correo y las verifico con la rutina
        $this->load->model('socios_model');
	$grupo_check = $this->socios_model->getmails_check();
	if ( $grupo_check ) {
        	fwrite($log, "Comienzo de proceso de chequeo de direcciones de email".date('Y-m-d G:i:s'));            
		//ciclo los 100 socios a verificar
		foreach ( $grupo_check as $socio ) {
        		fwrite($log, "Chequeando socio $socio->sid - $socio->apynom con correo $socio->mail ----->");            
                	// Controlo validez del email
                	$dirmail=$socio->mail;
                        $this->load->library('VerifyEmail');
                        $vmail = new verifyEmail();
                        $vmail->setStreamTimeoutWait(30);
                        $vmail->Debug= FALSE;
	
                        $vmail->setEmailFrom('avisos_cvm@clubvillamitre.com');
                        if ($vmail->check($dirmail)) {
				$arrsoc=array( 'Id' => $socio->sid,
						'categoria' => $socio->categoria,
						'validmail_st' => '1',
						'validmail_ts' => date('Y-m-d G:i:s')
					);
				$this->socios_model->update_socio($socio->sid, $arrsoc);
        			fwrite($log, "Chequeo OK actualizado \n");            
                	} else {
				$arrsoc=array( 'Id' => $socio->sid,
						'categoria' => $socio->categoria,
						'validmail_st' => '2',
						'validmail_ts' => date('Y-m-d G:i:s')
					);
				$this->socios_model->update_socio($socio->sid, $arrsoc);
        			fwrite($log, "Chequeo fallido \n");            
			}
		}
	}
        fwrite($log, "FIN de proceso de check de direcciones de email".date('Y-m-d G:i:s'));            

    }

	public function genera_cupon() {
                /* COMENTO ESTA GENERACION PORQUE ME VOY A QUEDAR CON UN SOLO CUPON PARA CADA ASOCIADO*/
                $this->load->model('pagos_model');
                $socios = $this->pagos_model->get_cupones_sincodlink();
		foreach ( $socios as $socio ) {
			echo $socio->sid."--";
                        $cupon = $this->cuentadigital($socio->sid,$socio->apynom,100);
                        if($cupon && $socio->sid != 0){
                                $cupon_id = $this->pagos_model->generar_cupon($socio->sid,100,$cupon);
				echo "Id= ".$cupon_id."\n";
                                $data = base64_decode($cupon['image']);
                                $img = imagecreatefromstring($data);
                                if ($img !== false) {
                                        //@header('Content-Type: image/png');
                                        imagepng($img,'images/cupones/'.$cupon_id.'.png',0);
                                        imagedestroy($img);
                                        $cupon = base_url().'images/cupones/'.$cupon_id.'.png';
                                }else {
                                        echo 'Ocurrió un error.';
                                        $cupon = '';
                                }
                        }
		}
	}

    public function aviso_rechazo_debitos() {
	// Si viene fecha por parametro la tomo, sino el date
        if ($this->uri->segment(3)) {
                $xhoy = $this->uri->segment(3);
        } else {
                $xhoy=date('Y-m-d');
        }

        // Periodo y fechas del proceso.....
        $xanio=date('Y', strtotime($xhoy));
        $xmes=date('m', strtotime($xhoy));
        $xperiodo=date('Ym', strtotime($xhoy));
        $xahora=date('Y-m-d G:i:s', strtotime($xhoy));

        //log
        $file = './application/logs/aviso-rechdebito-'.$xanio.'-'.$xmes.'.log';
        if( !file_exists($file) ){
            echo "creo";
            $log = fopen($file,'w');
        }else{
            echo "existe";
            $log = fopen($file,'a');
        }
	
        fwrite($log, "Comienzo de proceso de envio de aviso de rechazo de debitos".date('Y-m-d G:i:s'));            

	// Busco los rechazados del periodo
        $this->load->model('debtarj_model');
        $this->load->model('tarjeta_model');
	
	// Busco las tarjetas
	$tarjetas = $this->tarjeta_model->get_tarjetas();
	foreach ( $tarjetas as $tarjeta ) {
		$id_marca = $tarjeta->id;
		echo $xperiodo." - ".$id_marca;
		$rechazados = $this->debtarj_model->get_contracargos($xperiodo, $id_marca);
		var_dump($rechazados);
		if ( $rechazados ) {
            		$this->load->library('email');
            		$enviados=0;
			$aviso_mail="";
			foreach ( $rechazados as $rechazado ) {
				echo $rechazado->mail;
 				if ( $rechazado->mail != '' ) { 
                                	$largo = strlen($rechazado->nro_tarjeta);
                                	if ( $largo > 8 ) {
                                        	$nrotarj = substr($rechazado->nro_tarjeta,0,4)."****".substr($rechazado->nro_tarjeta,$largo-4,$largo);
                                	} else {
                                        	$nrotarj = "MAL";
                                	}
	
					$txt = "Se envio aviso a $rechazado->sid - $rechazado->apynom al email $rechazado->mail porque se le rechazo el debito de su tarjeta $rechazado->descr_marca nro $nrotarj por un importe de $ $rechazado->importe\n";
					$aviso_mail = $aviso_mail . $txt;
        				fwrite($log, $txt);            
	
                			$this->email->from('avisos_cvm@clubvillamitre.com','Club Villa Mitre');
                			$this->email->to($rechazado->mail);
                			$this->email->bcc('cvm.agonzalez@gmail.com');
	
                			$asunto='Debito CVM rechazado';
                			$this->email->subject($asunto);
	
					// Armo cuerpo del email
					$txt_mail="";
	
                			// Armo encabezado con escudo y datos de cabecera
                			$txt_mail  = "<table class='table table-hover' style='font-family:verdana' width='100%' >";
                			$txt_mail .= "<th> <img src='http://clubvillamitre.com/images/cvm-encabezado-mail.jpg' alt='' ></th>";
                			$txt_mail .= "</table>";
			
	
                			// Datos del Titular
                			$txt_mail .= '<p style="font-family:verdana; color:black"><strong>Socio/a : '.$rechazado->sid.'-'.$rechazado->apynom.'</strong></p>';
		
		
					$txt_mail .= "<p style='color:red'><strong>AVISO DE DEBITO RECHAZADO</strong></p>";
					$txt_mail .= "<p style='color:black'><strong>Generado el ".date('d-m-Y')."</strong></p><br>";
					$txt_mail .= "<p style='color:black'>La tarjeta de credito $rechazado->descr_marca $nrotarj que ud. registro para el debito automatico del Club Villa Mitre, <strong> no pudo realizar el debito</strong></p>";
					$txt_mail .= '<p style="font-family:verdana; font-style:italic; color:black">Ponganse en contacto con la secretaria del Club para regularizar su situacion.</p>';
					$txt_mail .= '<p style="font-family:verdana; color:black">Recuerde que al no estar al dia con sus pagos ud. no puede aprovechar nuestra RED de Beneficios </p>';
					$txt_mail .= '<p style="font-family:verdana; color:black">Al club lo hacemos entre todos y es de suma importancia su aporte </p>';
					$txt_mail .= "<br>";
					$txt_mail .= '<p style="font-family:verdana; color:black">Muchas gracias</p>';
					$txt_mail .= "<br>";
		
                			$txt_mail .= "<table class='table table-hover' style='font-family:verdana' width='100%' >";
                			$txt_mail .= "<tr><th> <img src='http://clubvillamitre.com/images/cvm-zocalo-mail.jpg' alt='' ></th></tr>";
                			$txt_mail .= "</table>";
		
                			$this->email->message($txt_mail);
					// Fin armado del cuerpo
	
                			fwrite($log,  date('d/m/Y G:i:s').": Enviando: ".$rechazado->email);
	
                			if($this->email->send()){
                    				fwrite($log,  " ----> Enviado OK "." \n");
                    				$enviados++;
                			} else {
                    				$msg_error=$this->email->print_debugger();
                    				fwrite($log, " ----> Error de Envio:".$msg_error." \n");
                			}
				}
			}
		}
	}

        fwrite($log, "Se enviaron $enviados emails");            
        fwrite($log, "FIN de proceso de envio de aviso de rechazo de debitos".date('Y-m-d G:i:s'));            
        fclose($log);      

        mail('cvm.agonzalez@gmail.com', "El proceso de Aviso de rechazos de debitos termino correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de aviso de rechazos finalizó correctamente ".date('Y-m-d G:i:s')."\n".$aviso_mail,'From: avisos_cvm@clubvillamitre.com'."\r\n");

    }
    public function imputa_debitos_tarjetas() {
        if ($this->uri->segment(3)) {
                $xhoy = $this->uri->segment(3);
        } else {
                $xhoy=date('Y-m-d');
        }

        echo $xhoy;

        // Periodo y fechas del proceso.....
        $xanio=date('Y', strtotime($xhoy));
        $xmes=date('m', strtotime($xhoy));
        $xperiodo=date('Ym', strtotime($xhoy));
        $xahora=date('Y-m-d G:i:s', strtotime($xhoy));
        $xlim1=date('Y-m-',strtotime($xhoy)).'25';
        $xlim2=date('Y-m-t', strtotime($xhoy));

        //log
        $file = './application/logs/facturacion-'.$xanio.'-'.$xmes.'.log';
        $file_col = './application/logs/cobranza_col-'.$xanio.'-'.$xmes.'.csv';
        if( !file_exists($file) ){
            echo "creo";
            $log = fopen($file,'w');
            $col = fopen($file_col,'w');
        }else{
            echo "existe";
            $log = fopen($file,'a');
            $col = fopen($file_col,'a');
        }

    	$this->load->model("pagos_model");
        $this->load->model("socios_model");
        $this->load->model("debtarj_model");
        $this->load->model("tarjeta_model");


	// Paso estado a trabajar por excepcion (10) para reimputaciones puntuales
	$debitos=$this->debitos_tarjetas($xperiodo, $log, 10); 
    }

    public function debitos_tarjetas($xperiodo, $log, $estado=1) {

		$anio=substr($xperiodo,0,4);
        	$mes=substr($xperiodo,4,2);
        	$xhoy=date('Y-m-d', strtotime($anio.'-'.$mes.'-01'));
		
		$this->load->model("debtarj_model");
		$debitos=$this->debtarj_model->get_debitos_by_periodo($xperiodo, $estado);

		$cant=0;
		$totdeb=0;

		foreach ( $debitos as $debito ) {


			$id_debito = $debito->id_debito;
			$fecha_debito = $debito->fecha_debito;
			$fecha_acreditacion = $debito->fecha_acreditacion;
			$importe = $debito->importe;
			$estado = $debito->estado;
			$nro_renglon = $debito->nro_renglon;
			$id_socio = $debito->sid;
			$ult_periodo = $debito->ult_periodo_generado;
			$ult_fecha = $debito->ult_fecha_generacion;
			$id_marca = $debito->id_marca;
		

			// Busco el saldo actual del socio
			$total = $this->pagos_model->get_socio_total($id_socio);
                        $saldo_cc = $total + $importe;

                        // Le resta el pago debitado a la tarjeta al saldo 
                        $tarjeta=$this->tarjeta_model->get($id_marca);
                        $descripcion = "Pago por Debito en Tarjeta $tarjeta->descripcion";
                        $data = array(
				"sid" => $id_socio,
				"date" => $xhoy,
				"descripcion" => $descripcion,
				"debe" => '0',
				"haber" => $importe,
				"total" => $saldo_cc,
				"origen" => 5
			);


                        $this->pagos_model->insert_facturacion($data);
                        $this->pagos_model->registrar_pago2($id_socio, $importe);

			$cant=$cant+1;
			$totdeb=$totdeb+$importe;

                        $socio = $this->socios_model->get_socio($id_socio);
			if ( $ult_periodo == $xperiodo ) {
				if ( $fecha_debito == $ult_fecha ) {
	
            				$txt = date('H:i:s')." Registre debito tarjeta para el asociado $id_socio - $socio->apellido, $socio->nombre por un monto de $importe \n";
            				fwrite($log, $txt);            
				} else {
            				$txt = date('H:i:s')." Registre debito pero el asociado $id_socio - $socio->apellido, $socio->nombre tiene la fecha de ultimo debito no coincide con el movimiento \n";
            				fwrite($log, $txt);            
				}
			} else {
            			$txt = date('H:i:s')." El asociado $id_socio - $socio->apellido, $socio->nombre tiene debito en tarjeta pero no coincide el ultimo periodo generado \n";
            			fwrite($log, $txt);            
			
			}
		}
		
	$totales = array( "cant" => $cant, "importe" => $totdeb );
	return $totales;

    }

    public function regulariza_vitalicios() {

        $this->load->model('socios_model');
        $this->load->model('pagos_model');
	$query="DROP TEMPORARY TABLE IF EXISTS tmp_vitalicios; ";
	$this->db->query($query);

	$query="CREATE TEMPORARY TABLE tmp_vitalicios
		SELECT s.Id sid, s.dni, s.apellido, s.nombre, s.suspendido, SUM(p.pagado-p.monto) saldo
		FROM socios s
        		JOIN pagos p ON s.Id = p.tutor_id
		WHERE s.categoria=5 AND suspendido = 0 AND p.tipo = 1
		GROUP BY 1; ";
	$this->db->query($query);

	$query="CREATE TEMPORARY TABLE tmp_ultfac
		SELECT v.sid, MAX(f.Id) max_id
		FROM tmp_vitalicios v
			JOIN facturacion f ON v.sid = f.sid  
		GROUP BY 1;";
	$this->db->query($query);

	$query="INSERT INTO facturacion
		SELECT 0, v.sid, NOW(), 'Regularizacion Saldos Vitalicios', 0, -v.saldo, f.total-v.saldo, 0
		FROM tmp_vitalicios v
			LEFT JOIN tmp_ultfac u USING ( sid )
			LEFT JOIN facturacion f ON u.max_id = f.Id
		WHERE v.saldo < 0; ";
	$this->db->query($query);

	$query="UPDATE pagos p JOIN tmp_vitalicios t ON p.tutor_id = t.sid AND t.saldo < 0 SET estado = 0, pagado = monto, pagadoel=NOW() WHERE p.tipo = 1 AND p.estado = 1; ";
	$this->db->query($query);
    }

    public function suspender($log)
    {
echo "suspender";
        $this->load->model('socios_model');
	$this->load->model('pagos_model');
        $socios = $this->socios_model->get_socios_pagan();
	$cant = 0 ;
        foreach ($socios as $socio) {
            // Excluyo del analisis a los vitalicios
	    if ( $socio->categoria != 5 ) {
		$this->db->where('tutor_id', $socio->Id);
            	$this->db->where('tipo', 1);
            	$this->db->where('estado', 1);
            	$query = $this->db->get('pagos');
            	if( $query->num_rows() >= 5 ){ 
			$meses_atraso=$query->num_rows();
            		$this->db->where('tutor_id', $socio->Id);
            		$this->db->where('tipo', 1);
            		$this->db->where('pagadoel is not NULL');
            		$this->db->select('tutor_id, MAX(pagadoel) maxfch, DATEDIFF(MAX(pagadoel),CURDATE()) dias_ultpago');
    			$this->db->group_by('tutor_id');
            		$query = $this->db->get('pagos');
			$isusp=0;
			if ( $query->num_rows() > 0 ) {
                		$ult_pago = $query->row();
				$ds_ult = $ult_pago->dias_ultpago;
                		$query->free_result();                
				// FIX solicitado por Simon para que suspenda con 3 meses de morosidad
				// estaba con -150 (5 meses)
				if ( $ds_ult < -90 ) {
					$isusp=1;
				}
			} else {	
				$isusp=1;
			}
			if ( $isusp == 1 ) {
                		$this->db->where('Id',$socio->Id);
                		$this->db->update('socios', array('suspendido'=>1));

                        	// Verifico si tiene Debito Automatico
                        	$this->load->model('debtarj_model');
                        	$debtarj = $this->debtarj_model->get_debtarj_by_sid($socio->Id);
                        	if ( $debtarj ) {
                                	$this->pagos_model->registrar_pago('debe',$socio->Id,0.00,'Pongo Stop DEBIT del debito de tarjeta',0,0);
                                	$this->debtarj_model->stopdebit($debtarj->id,true);
					$txt_debito=" Hice STOP DEBIT id=$debtarj->id ";
                        	} else {	
					$txt_debito="";
				}
	
	
                		$txt = date('H:i:s').": Socio Suspendido #".$socio->Id." ".TRIM($socio->apellido).", ".TRIM($socio->nombre)." DNI= ".$socio->dni." atraso de ".$meses_atraso." ultimo pago ".$ds_ult.$txt_debito. " \n";
                		fwrite($log, $txt);   
	
        			$this->pagos_model->registrar_pago('debe',$socio->Id,0.00,'Suspension Proceso Facturacion por atraso de'.$meses_atraso.' con ultimo pago hace '.$ds_ult.' dias',0,0);
	
				$cant++;
			}
            	}
	     }
        }        
	return $cant;
    }

    function aviso_deuda(){ // esta funcion genera emails de aviso a todos los deudores
        //log
	$fecha=date('Ymd');
        $file = './application/logs/avisodeuda-'.$fecha.'.log';
        if( !file_exists($file) ){
            echo "creo log";
            $log = fopen($file,'w');
        }else{
            echo "existe log";
            $log = fopen($file,'a');
        }

        $this->load->model('general_model');
	$this->load->model("pagos_model");
	$this->load->model("debtarj_model");

	// busco los socios con deuda
	$deudores=$this->pagos_model->get_deuda_aviso();
	if ( $deudores ) {

		// vacio la tabla de envios detallados de facturacion
		$this->db->truncate('facturacion_mails'); 
                $txt = "Truncate de mails \n";
                fwrite($log, $txt);

            // Meto una cabecera para identificar los envios de mails de facturacion del mes
            $envio = array(
                    'titulo' => "Aviso Deuda Mes $fecha",
                    'grupo' => "AvisoDeuda",
                    'data' => json_encode(array("total"=>0, "enviados"=>0, "errores"=>0))
                    );
            $this->general_model->insert_envio($envio);


		// ciclo cada deudor y armo/grabo los emails en envios
		foreach ( $deudores as $deudor ) {
			// si tiene debito automatico activo no lo mando
			$debito=$this->debtarj_model->get_debtarj_by_sid($deudor->sid);
			if ( !$debito ) {
				$txt_mail="";

                		// Armo encabezado con escudo y datos de cabecera
                		$txt_mail  = "<table class='table table-hover' style='font-family:verdana' width='100%' >";
                		$txt_mail .= "<thead>";
                		$txt_mail .= "<tr style='background-color: #105401 ;'>";
                		$txt_mail .= "<th> <img src='http://clubvillamitre.com/images/cvm-encabezado-mail.jpg' alt='' ></th>";
                		$txt_mail .= "<th style='font-size:30; background-color: #105401; color:#FFF' align='center'>CLUB VILLA MITRE</th>";
                		$txt_mail .= "</tr>";
                		$txt_mail .= "</thead>";
                		$txt_mail .= "</table>";
		
                		// Datos del Titular
                		$txt_mail .= '<h3 style="font-family:verdana"><strong>Titular:</strong> '.$deudor->sid.'-'.$deudor->nombre.', '.$deudor->apellido.'</h3>';
	
	
				$txt_mail .= "<h1>AVISO DE DEUDA</h1>";
				$txt_mail .= "<h2>Generado el ".date('d-m-Y')."</h2>";
				$txt_mail .= "<br>";
				$txt_mail .= "<h1>Al dia de hoy ud. tiene una deuda de $ ".$deudor->deuda."</h1>";
				$txt_mail .= "<br>";
				$txt_mail .= '<p style="font-family:verdana; ">Si ud. realizo algun pago en el dia de ayer puede que no este reflejado en este resumen </p>';
				$txt_mail .= "<br>";
				$txt_mail .= '<p style="font-family:verdana; font-style:italic;">Ponganse en contacto con la secretaria del Club para regularizar su situacion. Existen diferentes formas para financiar su deuda </p>';
				$txt_mail .= "<br>";
				$txt_mail .= '<p style="font-family:verdana; ">Recuerde que al no estar al dia con sus pagos ud. no puede aprovechar nuestra RED de Beneficios </p>';
				$txt_mail .= "<br>";
				$txt_mail .= '<p style="font-family:verdana; ">Al club lo hacemos entre todos y es de suma importancia su aporte </p>';
				$txt_mail .= "<br>";
				$txt_mail .= '<p style="font-family:verdana; ">Mas informacion en <a href="https://www.villamitre.com.ar/"> www.villamitre.com.ar</a></p>';
				$txt_mail .= "<br>";
	
                		$txt_mail .= "<th> <img src='http://clubvillamitre.com/images/cvm-zocalo-mail.jpg' alt='' ></th>";
                		$txt_mail .= "<br> <br>";
	
                		$txt_mail .= "<img src='http://clubvillamitre.com/images/2doZocalo3.png' alt=''>";
	
	
				// grabo el detalle del email
	                	$email = array(
                    			'email' => $deudor->mail,
                    			'body' => $txt_mail
                		);
                		$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
                		if(preg_match($regex, $deudor->mail)){
                        		$this->db->insert('facturacion_mails',$email);
					// Logueo datos registrados de aviso de deuda
					if ( $deudor->sid != $deudor->tutoreado ) {
                				$txt = "El socio $deudor->sid es TUTOR y tiene Deuda de $deudor->deuda y se lo mandamos al email $deudor->mail \n";
					} else {
                				$txt = "El socio $deudor->sid tiene Deuda de $deudor->deuda y se lo mandamos al email $deudor->mail \n";
					}
                			fwrite($log, $txt);
          	      		} else {
					// Logueo datos descartados por no tener email registrado
                			$txt = "El socio $deudor->sid tiene Deuda de $deudor->deuda y no se lo podemos mandar al email $deudor->mail \n";
                			fwrite($log, $txt);
				}
			} else {
                		$txt = "El socio $deudor->sid tiene Deuda de $deudor->deuda y no lo mandamos porque tiene Debito Automatico \n";
                		fwrite($log, $txt);
			}

		}

	}
    }

	function control_acred_tarj(){
        	$this->load->database('default');
		$txt_ctrl="CONTROL DE CARGA DE ACREDITACIONES DE TARJETAS DEL ".date('Y-m-d H:i:s')."\n";
                $qry = "SELECT sdg.id, sdg.id_marca, t.descripcion, sdg.periodo, sdg.fecha_debito, sdg.fecha_acreditacion, sdg.cant_generada, sdg.total_generado, sdg.cant_acreditada, sdg.total_acreditado,
        			SUM(IF(sd.estado=0,1,0)) cant_rechazado, SUM(IF(sd.estado=1,1,0)) cant_ok, SUM(IF(sd.estado=9,1,0)) cant_sinproc,
        			SUM(IF(sd.estado=0,sd.importe,0)) impo_rechazado, SUM(IF(sd.estado=0,sd.importe,0)) impo_acreditado, SUM(IF(sd.estado=9,sd.importe,0)) impo_sinproc,
        			IF(sdg.cant_acreditada>0,'Contracargos procesados','Faltan Procesar Contracargos') mensaje
			FROM socios_debitos_gen sdg
        			JOIN tarj_marca t ON sdg.id_marca = t.id
        			JOIN socios_debitos sd ON sdg.id = sd.id_cabecera
			WHERE periodo > DATE_FORMAT(CURDATE(), '%Y%m') AND
        			sdg.estado = 1
			GROUP BY 1; ";
                $resultado=$this->db->query($qry);
                if ( $resultado->num_rows() == 0 ) {
                        $txt_ctrl=$txt_ctrl."No hay archivos generados para el próximo mes \n";
                } else {
                        $txt_ctrl=$txt_ctrl. "CABECERA \t \t \t \t \t \t \t \t \t \t DETALLES \t \t \t \t \t Observacion \n";
                        $txt_ctrl=$txt_ctrl. "ID \t idMarca \t Tarjeta \t Periodo \t FechaDebito \t FechaAcred \t Cant Generada \t Total Generado \t Cant Acreditada \t Total Acreditado \t Cant Rechazado \t Cant OK \t Cant Sin Procesar \t Total Rechazado \t Total OK \t Total Sin Procesar \t Observacion  \n";
                        foreach ( $resultado->result() as $fila ) {
                                $txt_ctrl=$txt_ctrl.$fila->id."\t".$fila->id_marca."\t".$fila->descripcion."\t".$fila->periodo."\t".$fila->fecha_debito."\t".$fila->fecha_acreditacion."\t".$fila->cant_generada."\t".$fila->total_generado."\t".$fila->cant_acreditada."\t".$fila->total_acreditado."\t".$fila->cant_rechazado."\t".$fila->cant_ok."\t".$fila->cant_sinproc."\t".$fila->impo_rechazado."\t".$fila->impo_acreditado."\t".$fila->impo_sinproc."\t".$fila->mensaje."\n";
                        }
                }

                // Me mando email de aviso que el proceso termino OK
                mail('secretaria@villamitre.com.ar', "El proceso de Control de Acreditación de tarjetas finalizó correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de imputacion de pagos finalizó correctamente ".date('Y-m-d H:i:s')."\n".$txt_ctrl,'From: avisos_cvm@clubvillamitre.com'."\r\n");
                mail('cvm.agonzalez@gmail.com', "El proceso de Control de Acreditación de tarjetas finalizó correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de imputacion de pagos finalizó correctamente ".date('Y-m-d H:i:s')."\n".$txt_ctrl,'From: avisos_cvm@clubvillamitre.com'."\r\n");




	}

	function controles(){

        $this->load->database('default');

		$txt_ctrl="CONTROLES CORRIDOS EL ".date('Y-m-d H:i:s')."\n";

/* Control de que el saldo de facturacion sea igual al de pagos */
		$txt_ctrl=$txt_ctrl."CONTROL DE SALDOS DE FACTURACION VS PAGOS \n";
		$qry = "DROP TEMPORARY TABLE IF EXISTS tmp_saldo_fact;";
        	$this->db->query($qry);
		$qry = "CREATE TEMPORARY TABLE tmp_saldo_fact
			SELECT sid, SUM( debe - haber ) saldo, sum(debe) debe, sum(haber) haber
			FROM facturacion 
			GROUP BY 1;";
        	$this->db->query($qry);

		$qry = "DROP TEMPORARY TABLE IF EXISTS tmp_saldo_pago;";
        	$this->db->query($qry);
		$qry = "CREATE TEMPORARY TABLE tmp_saldo_pago
			SELECT tutor_id sid, SUM(monto-pagado) saldo, sum(if(tipo<>5,monto,0)) generado, sum(if(tipo=5,monto,0)) afavor, sum(pagado) pagado, SUM(if(tipo<>5 AND estado=1,1,0)) sin_imputar
			FROM pagos
			GROUP BY 1;";
        	$this->db->query($qry);
			
		$qry = "SELECT s.Id sid, s.dni, s.nombre, s.apellido, f.saldo saldo_fact, f.debe, f.haber, p.saldo saldo_pago, p.generado, p.afavor, p.pagado, p.sin_imputar, sdt.id_marca
			FROM tmp_saldo_fact f
        			LEFT JOIN socios s ON ( f.sid = s.id )
        			LEFT JOIN tmp_saldo_pago p ON ( f.sid = p.sid )
        			LEFT JOIN socios_debito_tarj sdt ON ( f.sid = sdt.sid )
			WHERE f.saldo <> p.saldo; ";
        	$resultado = $this->db->query($qry);

		if ( $resultado->num_rows() == 0 ) {
			"Los saldos de facturacion y pagos COINCIDEN \n";
		} else {
			$txt_ctrl=$txt_ctrl. "SID \t DNI \t Nombre \t Apellido \t Saldo Fact \t Debe \t Haber \t Saldo Pago \t Generado \t A Favor \t Pagado \t Sin Imputar \t IdMarca \n";
			foreach ( $resultado->result() as $fila ) {
				$txt_ctrl=$txt_ctrl.$fila->sid."\t".$fila->dni."\t".$fila->nombre."\t".$fila->apellido."\t".$fila->saldo_fact."\t".$fila->debe."\t".$fila->haber."\t".$fila->saldo_pago."\t".$fila->generado."\t".$fila->afavor."\t".$fila->pagado."\t".$fila->sin_imputar."\t".$fila->id_marca."\n";
        		}
		}

/* Control de que el saldo del ultimo renglon de facturacion sea igual a la suma de movimientos */
		$txt_ctrl=$txt_ctrl."CONTROL DE SALDOS DE FACTURACION VS ULTIMA FILA DE FACTURACION \n";
		$qry = "DROP TEMPORARY TABLE IF EXISTS tmp_ultid; ";
        	$this->db->query($qry);
		$qry = "CREATE TEMPORARY TABLE tmp_ultid
			SELECT sid, MAX(id) max_id
			FROM facturacion
			GROUP BY 1; ";
        	$this->db->query($qry);

		$qry = "SELECT t.sid, s.dni, s.nombre, s.apellido, t.saldo saldo_fact, t.debe, t.haber, f.total ult_fila
			FROM tmp_saldo_fact t
        			JOIN socios s ON ( t.sid = s.Id )
        			JOIN tmp_ultid u USING (sid)
        			JOIN facturacion f ON ( f.id = u.max_id )
			WHERE t.saldo <> -f.total; ";
        	$resultado = $this->db->query($qry);

		if ( $resultado->num_rows() == 0 ) {
			"Los saldos de facturacion y el ultimo renglon COINCIDEN \n";
		} else {
			$txt_ctrl=$txt_ctrl. "SID \t DNI \t Nombre \t Apellido \t Saldo Fact \t Debe \t Haber \t Ultima Fila \n";
			foreach ( $resultado->result() as $fila ) {
				$txt_ctrl=$txt_ctrl.$fila->sid."\t".$fila->dni."\t".$fila->nombre."\t".$fila->apellido."\t".$fila->saldo_fact."\t".$fila->debe."\t".$fila->haber."\t".$fila->ult_fila."\n";
        		}
		}

/* Control de que no haya socios con registros impagos y saldo a favor */
		$txt_ctrl=$txt_ctrl."CONTROL DE SALDOS A FAVOR Y REGISTROS IMPAGOS \n";
		$qry = "DROP TEMPORARY TABLE IF EXISTS tmp_afavor; ";
        	$this->db->query($qry);
		$qry = "CREATE TEMPORARY TABLE tmp_afavor
			SELECT p.tutor_id , p.monto
			FROM pagos p
			WHERE p.tipo = 5 AND p.monto < 0; ";
        	$this->db->query($qry);

		$qry = "SELECT s.Id tutor_id, s.dni, s.nombre, s.apellido, p.id id_pago, p.sid, p.monto, p.generadoel, p.pagado, p.pagadoel, p.estado
			FROM pagos p
        			JOIN tmp_afavor a USING ( tutor_id )
        			JOIN socios s ON ( p.tutor_id = s.Id )
			WHERE p.estado = 1 AND p.tipo <> 5; ";
        	$resultado = $this->db->query($qry);

		if ( $resultado->num_rows() == 0 ) {
			"No existen socios con saldo a favor y pagos pendientes \n";
		} else {
			$txt_ctrl=$txt_ctrl. "SID \t DNI \t Nombre \t Apellido \t Id_Pago \t Tutor \t Socio \t Monto \t GeneradoEl \t Pagado \t PagadoEl \t Estado \n";
			foreach ( $resultado->result() as $fila ) {
				$txt_ctrl=$txt_ctrl.$fila->tutor_id."\t".$fila->dni."\t".$fila->nombre."\t".$fila->apellido."\t".$fila->id_pago."\t".$fila->sid."\t".$fila->monto."\t".$fila->generadoel."\t".$fila->pagado."\t".$fila->pagadoel."\t".$fila->estado."\n";
        		}
		}

/* Control de que no haya socios con registros estado=1 y todo pagado */
		$txt_ctrl=$txt_ctrl."CONTROL DE PAGOS PENDIENTES Y TODO PAGADO \n";
		$qry = "SELECT s.Id tutor_id, s.dni, s.nombre, s.apellido, p.id id_pago, p.sid, p.monto, p.generadoel, p.pagado, p.pagadoel, p.estado
			FROM pagos p
				JOIN socios s ON ( p.tutor_id = s.Id )
			WHERE p.estado = 1 AND p.pagado >= p.monto AND p.tipo <> 5 AND p.monto > 0; ";
        	$resultado = $this->db->query($qry);

		if ( $resultado->num_rows() == 0 ) {
			"No existen pagos pendientes de socios con todo pagado \n";
		} else {
			$txt_ctrl=$txt_ctrl. "SID \t DNI \t Nombre \t Apellido \t Id_Pago \t Tutor \t Socio \t Monto \t GeneradoEl \t Pagado \t PagadoEl \t Estado \n";
			foreach ( $resultado->result() as $fila ) {
				$txt_ctrl=$txt_ctrl.$fila->tutor_id."\t".$fila->dni."\t".$fila->nombre."\t".$fila->apellido."\t".$fila->id_pago."\t".$fila->sid."\t".$fila->monto."\t".$fila->generadoel."\t".$fila->pagado."\t".$fila->pagadoel."\t".$fila->estado."\n";
        		}
		}

/* Control de que no haya socios con registros estado=0 y sin todo pagado */
		$txt_ctrl=$txt_ctrl."CONTROL DE PAGOS con ESTADO=0 Y SIN TODO PAGADO \n";
		$qry = "SELECT s.Id tutor_id, s.dni, s.nombre, s.apellido, p.id id_pago, p.sid, p.monto, p.generadoel, p.pagado, p.pagadoel, p.estado
			FROM pagos p
				JOIN socios s ON ( p.tutor_id = s.Id )
			WHERE p.estado = 0 AND p.pagado < p.monto AND p.tipo <> 5 AND p.monto > 0; ";
        	$resultado = $this->db->query($qry);

		if ( $resultado->num_rows() == 0 ) {
			"No existen pagos con estado=0 y sin todo pagado \n";
		} else {
			$txt_ctrl=$txt_ctrl. "SID \t DNI \t Nombre \t Apellido \t Id_Pago \t Tutor \t Socio \t Monto \t GeneradoEl \t Pagado \t PagadoEl \t Estado \n";
			foreach ( $resultado->result() as $fila ) {
				$txt_ctrl=$txt_ctrl.$fila->tutor_id."\t".$fila->dni."\t".$fila->nombre."\t".$fila->apellido."\t".$fila->id_pago."\t".$fila->sid."\t".$fila->monto."\t".$fila->generadoel."\t".$fila->pagado."\t".$fila->pagadoel."\t".$fila->estado."\n";
        		}
		}


/* Control de que no haya socios con pagado > monto */
		$txt_ctrl=$txt_ctrl."CONTROL DE PAGOS MAYORES AL MONTO \n";
		$qry = "SELECT s.Id tutor_id, s.dni, s.nombre, s.apellido, p.id id_pago, p.sid, p.monto, p.generadoel, p.pagado, p.pagadoel, p.estado
			FROM pagos p
				JOIN socios s ON ( p.tutor_id = s.Id )
			WHERE p.estado = 0 AND pagado > monto;";
        	$resultado = $this->db->query($qry);

		if ( $resultado->num_rows() == 0 ) {
			"No existen pagos con mayor pagado que el monto \n";
		} else {
			$txt_ctrl=$txt_ctrl. "SID \t DNI \t Nombre \t Apellido \t Id_Pago \t Tutor \t Socio \t Monto \t GeneradoEl \t Pagado \t PagadoEl \t Estado \n";
			foreach ( $resultado->result() as $fila ) {
				$txt_ctrl=$txt_ctrl.$fila->tutor_id."\t".$fila->dni."\t".$fila->nombre."\t".$fila->apellido."\t".$fila->id_pago."\t".$fila->sid."\t".$fila->monto."\t".$fila->generadoel."\t".$fila->pagado."\t".$fila->pagadoel."\t".$fila->estado."\n";
        		}
		}

/* Control de que no haya registros estado=1 y monto=pagado=0 */
		$txt_ctrl=$txt_ctrl."CONTROL DE PAGOS PENDIENTES PERO QUE TIENEN TODO PAGADO \n";
		$qry = "SELECT s.Id tutor_id, s.dni, s.nombre, s.apellido, p.id id_pago, p.sid, p.monto, p.generadoel, p.pagado, p.pagadoel, p.estado
			FROM pagos p
        			JOIN socios s ON ( p.tutor_id = s.id )
			WHERE p.estado = 1 AND p.pagado = p.monto AND p.tipo <> 5; ";
        	$resultado = $this->db->query($qry);

		if ( $resultado->num_rows() == 0 ) {
			"No existen pagos pendientes con todo pagado \n";
		} else {
			$txt_ctrl=$txt_ctrl. "SID \t DNI \t Nombre \t Apellido \t Id_Pago \t Tutor \t Socio \t Monto \t GeneradoEl \t Pagado \t PagadoEl \t Estado \n";
			foreach ( $resultado->result() as $fila ) {
				$txt_ctrl=$txt_ctrl.$fila->tutor_id."\t".$fila->dni."\t".$fila->nombre."\t".$fila->apellido."\t".$fila->id_pago."\t".$fila->sid."\t".$fila->monto."\t".$fila->generadoel."\t".$fila->pagado."\t".$fila->pagadoel."\t".$fila->estado."\n";
        		}
		}


		// Me mando email de aviso que el proceso termino OK
        	mail('cvm.agonzalez@gmail.com', "El proceso de Controles Diario finalizó correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de imputacion de pagos finalizó correctamente ".date('Y-m-d H:i:s')."\n".$txt_ctrl,'From: avisos_cvm@clubvillamitre.com'."\r\n");

	}
       
	function pagos(){


		$this->load->model("pagos_model");
		$this->load->model("socios_model");

		$xahora=date('Y-m-d G:i:s');
                $path_col = './application/logs/pagocron-'.date('Ymd').'.csv';
		if( !file_exists($path_col) ){
			$log = fopen($path_col,'w');
		} else {
			$log = fopen($fl,'a');
		}
		fwrite($log, "Comienzo a procesar cron de pagos $xahora \n");

		// Si me vino una fecha en el URL fuerzo la generacion de esa fecha en particular sin controlar cron
        	if ($this->uri->segment(3)) {
			echo "asigno fecha de parametro \n";
			$ayer = $this->uri->segment(3);
			echo "ayer = $ayer \n";
			$si_act_col="N";
			fwrite($log, "Puse fecha de parametro URI $ayer \n");
		} else {
			echo "tomo el date\n";
			$ayer = date('Ymd',strtotime("-1 day"));
			$fecha = date('Y-m-d');
			if($this->pagos_model->check_cron_pagos()){exit('Esta tarea ya fue ejecutada hoy.');}	 
			$si_act_col="S";
			fwrite($log, "Puse fecha de DATE $ayer \n");
		}


		// Veo si tiene algun condicional enviado en la URL para hacer o no generacion
		// Sino viene segmento 4 (default) genera todo
		// Si viene en segmento 4 CD o TODO genero Cuenta Digital
		$ctrl_gen="";
		if ( $this->uri->segment(4) ) {
			$ctrl_gen=$this->uri->segment(4);
			echo "Controlo generacion vino -> $ctrl_gen";
			fwrite($log, "Vino URI 4 $ctrl_gen \n");
			if ( !($ctrl_gen == "TODO" || $ctrl_gen == "CD" || $ctrl_gen == "COL" || $ctrl_gen == "NADA" || $ctrl_gen == "COBROD" ) ) {
				echo "EL PARAMETRO PARA GENERAR ES INCORRECTO";
				exit;
			}
		} else {
			echo "Generacion default busca TODO\n";
			$ctrl_gen="TODO";
		}
		
		$reactivados=array();
		$cant_react=0;
		$cant_cd = 0;
		$total_cd = 0;
		$cant_col = 0;
		$total_col = 0;
		$cant_cobrod = 0;
		$total_cobrod = 0;

		if ( $ctrl_gen == "TODO" || $ctrl_gen == "CD" ) {
			echo "genero CD";
			fwrite($log, "Genero CD \n");
			// Busco los pagos del sitio de Cuenta Digital
			$pagos = $this->get_pagos($ayer);

			// Si bajo algo del sitio
			if($pagos) {
				// Ciclo los pagos encontrados
				foreach ($pagos as $pago) {
					$data = $this->pagos_model->insert_pago($pago);
					$this->pagos_model->registrar_pago2($pago['sid'],$pago['monto']);

					// Me fijo si esta suspendido y con el pago se queda con saldo a favor para reactivar
					$saldo=$this->pagos_model->get_saldo($pago['sid']);
					$socio=$this->socios_model->get_socio($pago['sid']);
					if ( $socio->suspendido == 1 && $saldo < 0 ) {
						$this->socios_model->suspender($pago['sid'],'no');
						$reactivados[]=$socio->Id."-".$socio->apellido.", ".$socio->nombre."\n";
						$cant_react++;
					}

					// Acumulo para email
					$cant_cd++;
					$total_cd=$total_cd+$pago['monto'];
				}
				fwrite($log, "Procese $cant_cd pagos de CD por un total de $total_cd \n");
			}
		}

		if ( $ctrl_gen == "TODO" || $ctrl_gen == "COBROD" ) {
                        echo "genero CobroDigital";
                        fwrite($log, "Genero CobroDigital \n");
                        // Busco los pagos del sitio de Cobro Digital
			// comentado para que no corte el proceso de esta noche 28dic22
                        $pagos = $this->get_pagos_COBROD($ayer);

                        // Si bajo algo del sitio
                        if($pagos) {
                                // Ciclo los pagos encontrados
                                foreach ($pagos as $pago) {
                                        $data = $this->pagos_model->insert_pago_cobrod($pago);
                                        $this->pagos_model->registrar_pago2($pago['sid'],$pago['monto']);

                                        // Me fijo si esta suspendido y con el pago se queda con saldo a favor para reactivar
                                        $saldo=$this->pagos_model->get_saldo($pago['sid']);
                                        $socio=$this->socios_model->get_socio($pago['sid']);
                                        if ( $socio->suspendido == 1 && $saldo < 0 ) {
                                                $this->socios_model->suspender($pago['sid'],'no');
                                                $reactivados[]=$socio->Id."-".$socio->apellido.", ".$socio->nombre."\n";
                                                $cant_react++;
                                        }

                                        // Acumulo para email
                                        $cant_cobrod++;
                                        $total_cobrod=$total_cobrod+$pago['monto'];
                                }
                                fwrite($log, "Procese $cant_cobrod pagos de CD por un total de $total_cobrod \n");
                        }
                }

		
		if ( $ctrl_gen == "TODO" || $ctrl_gen == "COL" ) {
			echo "genero COL";
			fwrite($log, "Genero COL \n");
			if ( $this->uri->segment(5) ) {
				echo "vino parametro 5 = ".$this->uri->segment(5)." \n";
				$suc_filtro=$this->uri->segment(5);
			} else {
				$suc_filtro=0;
			}
			// Busco los pagos registrados en COL
			$pagos_COL = $this->get_pagos_COL($ayer,$suc_filtro);

			// Separo los dos arrays que devuelve
			$pagosCOL = $pagos_COL[1];
			$descartadoCOL = $pagos_COL[0];

			// Si bajo algo del sitio
			if($pagosCOL) {
				// Ciclo los pagos encontrados
				foreach ($pagosCOL as $pago) {
					// Si vino en la URL que genera solo un local descarto el resto
					$data = $this->pagos_model->insert_pago_col($pago);
					$this->pagos_model->registrar_pago2($pago['sid'],$pago['monto']);

					// Me fijo si esta suspendido y con el pago se queda con saldo a favor para reactivar
					$saldo=$this->pagos_model->get_saldo($pago['sid']);
					$socio=$this->socios_model->get_socio($pago['sid']);
					if ( $socio->suspendido == 1 && $saldo < 0 ) {
						$this->socios_model->suspender($pago['sid'],'no');
						$reactivados[]=$socio->Id."-".$socio->apellido.", ".$socio->nombre."\n";
						$cant_react++;
					}

					// Acumulo para email
					$cant_col++;
					$total_col=$total_col+$pago['monto'];
				}
				fwrite($log, "Procese $cant_col pagos de COL por un total de $total_col \n");
			}
		}

		if (!$this->uri->segment(3)) {
			$this->pagos_model->insert_pagos_cron($fecha); 
			fwrite($log, "Actualizo pagos_cron \n");
		}

	// Actualizo en la base de la Coope los que tuvieron algun cambio
/*
	if ( $si_act_col = "S" ) {
		$actualizados = $this->_actualiza_COL($ayer);
	} else {
		$actualizados = 0;
	}
*/
		$actualizados = 0;

        // Me mando email de aviso que el proceso termino OK
	$qdesc = $descartadoCOL['descartados'];
	$pdesc = $descartadoCOL['tot_descartado'];
	$info_total="Procese fecha de cobro = $ayer \n Procese $cant_cd pagos de CuentaDigital por un total de $ $total_cd \n Procese $cant_col pagos de LaCoope por un total de $ $total_col. Descarte pagos de COL anteriores $qdesc por $ $pdesc y actualice saldos de $actualizados socios. \n Reactive $cant_react socios. \n";
	foreach ( $reactivados as $r ) {
		$info_total.=$r."\n";
	}
	$xahora=date('Y-m-d G:i:s');
        mail('cvm.agonzalez@gmail.com', "El proceso de Imputación de Pagos finalizó correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de imputacion de pagos finalizó correctamente ".$xahora."\n".$info_total,'From: avisos_cvm@clubvillamitre.com'."\r\n");

	}

	function get_pagos($fecha) {           
  
        	$this->config->load('cuentadigital');    
        	$url = 'https://www.cuentadigital.com/exportacion.php?control='.$this->config->item('cd_control');
        	$url .= '&fecha='.$fecha;	    
		if($a = file_get_contents($url)){
			$data = explode("\n",$a);
			$pago = array();
			foreach ($data as $d) {		   	  		 
				if($d){
					$entrantes = explode('/', $d);
					$dia = substr($entrantes[0], 0,2);
					$mes = substr($entrantes[0], 2,2);
					$anio = substr($entrantes[0], 4,4);
					$hora = substr($entrantes[1], 0,2);
					$min = substr($entrantes[1], 2,2);
					$pago[] = array(
			   			"fecha" => date('d-m-Y',strtotime($entrantes[0])),
			   			"hora" => $hora.':'.$min,
			   			"monto" => $entrantes[2],
			   			"sid" => $entrantes[3],
			   			"pid" => $entrantes[4]
			   		);
                    			$p = array(
                            			"fecha" => date('Y-m-d',strtotime($entrantes[0])),
                            			"hora" => $hora.':'.$min,
                            			"monto" => $entrantes[2],
                            			"sid" => $entrantes[3],
                            			"pid" => $entrantes[4]
                        			);
                    			$this->pagos_model->insert_cuentadigital($p);
				}
			}
			return $pago;
		} else {
			if($a === FALSE) {
                		mail("cvm.agonzalez@gmail.com","Fallo en Cron VM no pudo acceder CD",date('Y-m-d H:i:s'),'From: avisos_cvm@clubvillamitre.com'."\r\n");
                		exit();
			}
			return false;
		}
	}

	function get_pagos_COBROD($fecha) {

		$this->config->load('cobrodigital');
		$url = "https://www.cobrodigital.com/ws3";

		$SID = $this->config->item('cd_sid');
    		$id_comercio = $this->config->item('cd_idcomercio');
    		$headers= array("Content-Type: application/json");
		$post = array('idComercio' => $id_comercio, 'sid' => $SID, 'metodo_webservice'=> 'consultar_transacciones', 'desde' => "$fecha", 'hasta' => "$fecha" 	);
    		$post = json_encode($post);
    		//echo 'Post array: '.print_r($post, true);

    		$ch = curl_init($url);
    		curl_setopt($ch, CURLOPT_POST, true);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 'yes');

    		$resultado = curl_exec($ch);
    		$status = curl_getinfo($ch);
    		$errno  = curl_errno($ch);
    		$error  = curl_error($ch);

    		curl_close($ch);
		$data = $resultado;
		$pagos_cd = json_decode($data)->datos[0];
		$cont=0;
		$arr_ret = array();
		// ------------------------------------------------------------------------------------------
		// COMENTARIO DE COMO TOMO LA INFO DE LO QUE DEVUELVE EL SERVICIO
		// Como los nombres de columnas tienen espacios y tildes tomo por posicion de los valores
		// Ejemplo de un array de respuesta
		// (00) ["id_transaccion"]=> string(24) "BFUb4QNtJDtXDHdJW8h/Bg==" 
		// (01) ["Fecha"]=> string(10) "27/12/2022" 
		// (02) ["Código de barras"]=> string(29) "73858850140000115123200000008" 
		// (03) ["Nro Boleta"]=> string(1) "1" 
		// (04) ["Identificación"]=> string(0) "" 
		// (05) ["Nombre"]=> string(0) "" 
		// (06) ["Info"]=> string(12) "Provincianet" 
		// (07) ["Concepto"]=> string(19) "tarjeta de cobranza" 
		// (08) ["Bruto"]=> string(6) "100,00" 
		// (09) ["Comisión"]=> string(5) "-6,66" 
		// (10) ["Neto"]=> string(5) "93,34" 
		// (11) ["Saldo acumulado"]=> string(5) "93,34" 
		// (12) ["sumaresta"]=> string(1) "1" 
		// (13) ["Fecha_pago"]=> string(10) "26/12/2022"
		// ------------------------------------------------------------------------------------------
		foreach ($pagos_cd as $d) {
			$d=(array)$d;
			$valores = array_values($d);
			$cant_col = count($valores);
			$col=0;
			$hora_pago="11:11";
			while ( $col < $cant_col ) {
				switch ( $col ) {
					case  0: $id_trx = $valores[$col]; break;
					case  2: 
						$cod_barra = $valores[$col]; 
						$cupon = $this->pagos_model->get_cupon_cobrod($cod_barra); 
						if ( $cupon ) {
							$sid_pago = $cupon->sid;
						} else {
							$sid_pago = 0;
						}
						break;
					case  6: 
						$sistema = $valores[$col]; 
						switch ( $sistema ) {
							case 'Provincianet': $nsist = 1; break;
							case 'Rapipago': $nsist = 2; break;
							case 'Pagofacil': $nsist = 3; break;
							case 'Ripsa': $nsist = 4; break;
							default: $nsist = 99;
						}
						$sist_cobro = $nsist; 
						break;
					case  8: 
						$monto_pago = str_replace('.','',$valores[$col]);
						$monto_pago = str_replace(',','.',$monto_pago); 
						break;
					case  9: 
						$comision = str_replace('.','',$valores[$col]); 
						$comision = str_replace(',','.',$comision); 
						break;
					case 13: 
						$fecha = $valores[$col]; 
						$dia = substr($fecha, 0,2); 
						$mes = substr($fecha, 3,2);
						$anio = substr($fecha, 6,4);
						$fecha_pago = $anio."-".$mes."-".$dia; break;
				}
				$col++;
			}

			// Proceso el registro
			// Armo el array
			$cd_fila = array (
				"fecha" => $fecha_pago,
				"hora" => $hora_pago,
				"monto" => $monto_pago,
				"sid" => $sid_pago,
				"pid" => $id_trx
				);
			// Lo agrego al array de pagos a procesar, si es de un SID identificado
			// Los que son solo comisiones sin identificar SID no los devuelvo para procesar
			if ( $sid_pago != 0 ) {
				$arr_ret[] = $cd_fila;
			}
			// Lo inserto en la tabla de la BD
			$cobrodigital = array (
					"id" => 0,
					"fecha_pago" => $fecha_pago,
					"monto" => $monto_pago,
					"sid" => $sid_pago,
					"id_trx" => $id_trx,
					"id_cron" => 9999,
					"sist_cobranza" => $sist_cobro,
					"comision" => $comision
			);
			$this->pagos_model->insert_cobrodigital($cobrodigital);
		}
		return $arr_ret;
        }


	function get_pagos_COL($fecha,$suc_filtro) {           
		
        	$this->load->model('pagos_model');
		$cupones = $this->_newCOL($fecha,$suc_filtro);
		//echo $cupones->estado."---->>>><<<<<----\n";
		//echo $cupones->msg."---->>>><<<<<----\n";
		//echo "%&/(/((/#$#(/$(#/$(/#$(\n";
		//var_dump($cupones->result->cupones_cobrados);
		$descartados=0;
		$tot_descartado=0;
		$pago = array();
		foreach ($cupones->result->cupones_cobrados as $cupon) {
			//echo "cupon ... ";
			//var_dump($cupon);
			$nro_socio = $cupon->nro_socio;
			$suc = $cupon->sucursal;
			$xfecha = $cupon->fecha_cobro;
			$fecha_ctrl = date("Ymd",strtotime($cupon->fecha_cobro));
			$fecha_pago = date("Y-m-d",strtotime($cupon->fecha_cobro));
			$hora_pago = date("H:i:s",strtotime($cupon->fecha_cobro));
			$importe = $cupon->importe_cobrado;
			$periodo=substr($xfecha,0,4).substr($xfecha,5,2);
			$nro_cupon = ( date("ymdHi", strtotime($cupon->fecha_cobro)) * 1000 ) + $suc;

			// Si viene una sucursal de filtro salteo las sucursales distintas
			if ( $suc_filtro > 0 ) {
				if ( $suc != $suc_filtro ) {
					continue;
				}
			}
	
			// Verifico si el pago no fue procesado
			$cobro_col =  $this->pagos_model->get_cobcol_id($nro_socio, $periodo, $nro_cupon);

			if ( !$cobro_col && $fecha_ctrl > 20200914 ) {
				$pago[] = array(
					"fecha" => date('d-m-Y',strtotime($fecha_pago)),
					"hora" => $hora_pago,
					"monto" => $importe,
					"sid" => $nro_socio,
					"pid" => $nro_cupon
					);
				$p = array(
					"sid" => $nro_socio,
					"periodo" => $periodo,
					"fecha_pago" => date('Y-m-d',strtotime($fecha_pago)),
					"suc_pago" => $suc,
					"nro_cupon" => $nro_cupon,
					"importe" => $importe
					);
	
				$this->pagos_model->insert_cobranza_col($p);
			} else {
				$descartados++;
				$tot_descartado = $tot_descartado + $importe;
			}
                };
		$arr_result = array ();
		$arr_descartados = array( 'descartados' => $descartados, 'tot_descartado' => $tot_descartado);
		$arr_result[0] = $arr_descartados;
		$arr_result[1] = $pago;
		return $arr_result;
	}

    function _actualiza_COL($ayer) {           

	// Actualiza todos los dias salvo el 1 que va por el proceso de facturacion
	$actualizados=0;
	if ( date('d') > 1 ) {
		$hfecha=date('Y-m-t');
		$periodo=date('Ym');
        	$path_col = './application/logs/actcol-'.$hoy.'.csv';
        	$file_col = fopen($path_col,'w');
        	$this->load->model('pagos_model');
		$novedades = $this->pagos_model->get_novcol($ayer);
	
		if ( $novedades ) {
			foreach ( $novedades as $socio ) {
				
				$col_socio = $socio->sid;
				$col_dni = $socio->dni;
				$col_apynom = $socio->apynom;
				if ( $socio->saldo < 0 ) {
					$col_importe = 0;
				} else {
					$col_importe = $socio->saldo;
				}
				$col_recargo=0;
				$txt = '"'.$periodo.'","'.$col_socio.'","'.$col_dni.'","'.$col_apynom.'","'.$col_importe.'","'.$hfecha.'","'.$col_recargo.'","'.$hfecha.'"'."\r\n";
				fwrite($file_col, $txt);
				$actualizados++;
			}
		}
		fclose($file_col);
        	$md5 = md5_file($path_col);
        	$file_col_new = './application/logs/asociados_'.$md5.'.csv';
        	rename($path_col,$file_col_new);

        	// Luego llamo a la rutina que lo sube con el WS
        	$this->_sube_facturacion_COL($file_col_new);
	}

	return $actualizados;
    }

    function _sube_facturacion_COL($file_col_new) {           
    	$url = "https://extranet.cooperativaobrera.coop/proveedores/Importa_Socios_Mes/procesaArchivo";

    	//Prueba villa mitre
    	$login = "agonzalez.lacoope";
    	$token = "ewrewry23k5bc1436lnlahbg23218g12g1h3g1vm"; 
    	$file_name_with_full_path = $file_col_new;

    	$headers= array("Content-Type: multipart/form-data","Authorization: Token $token");

    	if (function_exists('curl_file_create')) { // php 5.5+
      		$cFile = curl_file_create($file_name_with_full_path);
    	} else {  
      		$cFile = '@' . realpath($file_name_with_full_path);
    	}

    	$post = array('login' => $login, 'userfile'=> $cFile);

    	$ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    	$resultado = curl_exec($ch);
    	$errno  = curl_errno($ch);
    	$error  = curl_error($ch);

    	echo 'Resultado: '.$resultado;
    	curl_close($ch);

    	if($errno !== 0) {
        	throw new Exception($error, $errno);
    	}

    	$obj_resultado = json_decode($resultado);

    	//estado = 1 -----> OK
    	//estado >= 100 --> ERROR
    	echo "<br /><br />Estado: ".$obj_resultado->estado;
    	echo "<br />Mensaje: ".$obj_resultado->msg;
    }

    function _newCOL($fecha,$suc_filtro) {           
    	$url = "https://extranet.cooperativaobrera.coop/proveedores/Importa_Socios_Mes/consultaCobros";
    
    	//Prueba villa mitre
    	$login = "agonzalez.lacoope";
    	$token = "ewrewry23k5bc1436lnlahbg23218g12g1h3g1vm";

    	$headers= array("Content-Type: multipart/form-data","Authorization: Token $token");

		$anio=substr($fecha,0,4);
		$mes=substr($fecha,4,2);
		$hfecha = date("Y-m-t", strtotime($fecha));
		$dfecha = date("Y-m-d", strtotime($anio."-".$mes."-01"));


    	$post = array('login' => $login, 'fecha_inicial' => "$dfecha", 'fecha_final'=> "$hfecha");

    	$ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
    	$resultado = curl_exec($ch);
    	$errno  = curl_errno($ch);
    	$error  = curl_error($ch);

    	//var_dump($resultado);
    	//echo 'status: '.print_r($status, true);

    	//echo 'Resultado: '.$resultado;
    	curl_close($ch);

    	if($errno !== 0) {
        	throw new Exception($error, $errno);
    	}

    	$obj_resultado = json_decode($resultado);

	return $obj_resultado;
    	//estado = 1 -----> OK
    	//estado >= 100 --> ERROR
    	//echo "<br /><br />Estado: ".$obj_resultado->estado;
    	//echo "<br />Mensaje: ".$obj_resultado->msg;
    }
    function debito_nuevacard() {
	$exitoso=FALSE;
        $this->config->load("nuevacard");
        $this->load->model('debtarj_model');
        $this->load->model('socios_model');
	$nro_comercio=$this->config->item('nc_negocio');

	$cont=0;
	$total=0;
	$fecha = date('d/m/Y');
        $mes = date('m');
        $ano = date('y');

        $fl = './application/logs/nuevacard-'.date('Y').'-'.date('m').'.log';
        if( !file_exists($fl) ){
            $log = fopen($fl,'w');
        }else{
            $log = fopen($fl,'a');
        }
        $file_tot = '/tmp/CVMCOOP'.$mes.$ano.'TOT.TXT';
        $ft=fopen($file_tot,'w');
        $file = '/tmp/CVMCOOP'.$mes.$ano.'.TXT';
        $f=fopen($file,'w');

//TODO generar facturacion del mes siguiente para los que tienen debito...
        $debtarjs = $this->debtarj_model->get_debtarjs();
	foreach ( $debtarjs AS $debtarj ) {
		$id_marca=$debtarj->id_marca;
		if ( $id_marca == 2 || $id_marca == 3 ) {
			$socio=$this->socios_model->get_socio($debtarj->sid);
// TODO tomar el importe de la facturacion que le corresponde
			$importe="100.00";
			$linea=$nro_comercio.",".$debtarj->nro_tarjeta.",".$socio->apellido.", ".$socio->nombre.",0,".$fecha.",".$importe.",DAU\n";
			fwrite($f,$linea);
			$cont++;
			$total=$total+$importe;
			fwrite($log,$socio->Id." ".$socio->apellido.", ".$socio->nombre." monto :".$importe."\n");
		}
	}
	$linea="FECHA :".$fecha."\n";
	fwrite($ft,$linea);
	$linea="CANTIDAD DE REGISTROS :".$cont."\n";
	fwrite($ft,$linea);
	$linea="TOTAL($) :".$total."\n";
	fwrite($ft,$linea);

	fwrite($log,"Se genero un archivo con ".$cont." debitos por un total de $ ".$total."\n");

	}

    function debito_visa() {
	$exitoso=FALSE;
        $this->load->model('tarjeta_model');
        $this->load->model('debtarj_model');
        $this->load->model('socios_model');
	// Visa esta grabada con id=1
	$nro_comercio=$this->tarjeta_model->get(1);

	$cont=0;
	$total=0;
	$fecha = date('d/m/Y');
        $mes = date('m');
        $ano = date('y');

        $fl = './application/logs/visa-'.date('Y').'-'.date('m').'.log';
        if( !file_exists($fl) ){
            $log = fopen($fl,'w');
        }else{
            $log = fopen($fl,'a');
        }
        $file_tot = '/tmp/CVMVISA'.$mes.$ano.'TOT.TXT';
        $ft=fopen($file_tot,'w');
        $file = '/tmp/CVMCOOP'.$mes.$ano.'.TXT';
        $f=fopen($file,'w');

//TODO generar facturacion del mes siguiente para los que tienen debito...
        $debtarjs = $this->debtarj_model->get_debtarjs();
	foreach ( $debtarjs AS $debtarj ) {
		$id_marca=$debtarj->id_marca;
		if ( $id_marca == 2 || $id_marca == 3 ) {
			$socio=$this->socios_model->get_socio($debtarj->sid);
// TODO tomar el importe de la facturacion que le corresponde
			$importe="100.00";
			$linea=$nro_comercio.",".$debtarj->nro_tarjeta.",".$socio->apellido.", ".$socio->nombre.",0,".$fecha.",".$importe.",DAU\n";
			fwrite($f,$linea);
			$cont++;
			$total=$total+$importe;
			fwrite($log,$socio->Id." ".$socio->apellido.", ".$socio->nombre." monto :".$importe."\n");
		}
	}
	$linea="FECHA :".$fecha."\n";
	fwrite($ft,$linea);
	$linea="CANTIDAD DE REGISTROS :".$cont."\n";
	fwrite($ft,$linea);
	$linea="TOTAL($) :".$total."\n";
	fwrite($ft,$linea);

	fwrite($log,"Se genero un archivo con ".$cont." debitos por un total de $ ".$total."\n");

	}

    function prueba_ahg() {

    	$cupon =  $this->cuentadigital(29219, "GONZALEZADRIAN", 123, null, 'agonzalez.lacoope@gmail.com') ;
	var_dump($cupon);
    }
 
    function cuentadigital($sid, $nombre, $precio, $venc=null, $mail=null) 
    {
        $this->config->load("cuentadigital");
        $cuenta_id = $this->config->item('cd_id');
        $nombre = substr($nombre,0,40);
        $concepto  = $nombre.' ('.$sid.')';
        $repetir = true;
        $count = 0;
        $result = false;
        if(!$venc){
	    if ( !$mail ) {
            	$url = 'https://www.cuentadigital.com/api.php?id='.$cuenta_id.'&codigo='.urlencode($sid).'&precio='.urlencode($precio).'&concepto='.urlencode($concepto).'&xml=1';
	    } else {
            	$url = 'https://www.cuentadigital.com/api.php?id='.$cuenta_id.'&codigo='.urlencode($sid).'&precio='.urlencode($precio).'&hacia='.urlencode($mail).'&m2=1&concepto='.urlencode($concepto).'&xml=1';
		echo $url;
	    }
        }else{
            $url = 'https://www.cuentadigital.com/api.php?id='.$cuenta_id.'&venc='.$venc.'&codigo='.urlencode($sid).'&precio='.urlencode($precio).'&concepto='.urlencode($concepto).'&xml=1';    
        }
        
        do{
            $count++;
            $a = file_get_contents($url);
            $a = trim($a);
            $xml = simplexml_load_string($a);
            // $xml = simplexml_import_dom($xml->REQUEST);
            if (($xml->ACTION) != 'INVOICE_GENERATED') {
                $repetir = true;
                echo('Error al generarlo: ');
                sleep(1);
                //echo '<a href="'.$url.'" target="_blank"><strong>Reenviar</strong></a>';
            } else {
                $repetir = false;
                //echo('<p>El cupon de aviso se ha enviado correctamente</p>');
                $result = array();
                $result['image'] = $xml->INVOICE->BARCODEBASE64;
                $result['barcode'] = $xml->INVOICE->PAYMENTCODE1;
                $result['codlink'] = substr($xml->INVOICE->PAYMENTCODE2,-10);
                //$result = $xml->INVOICE->INVOICEURL;

            }        
            if ($count > 5) { $repetir = false; };

        } while ( $repetir );    
            return $result;
    }

    public function intereses_jardin() {
        if(date('d') < 15){ die(); }
        if(date('d') > 25){ die(); }
	$dia = date('d');
        $this->load->model('general_model');
	// Busco el ID=2 del Jardin
        $config = $this->general_model->get_config(2);
        $recargo = $config->interes_mora;
        $this->load->model('actividades_model');
        $this->load->model('pagos_model');
	// Busco la comision=15 que es la comision de jardin para tener los socios 
	// que tienen actividades de esa comision
        $socact = $this->actividades_model->get_actsoc_comision(15);
	$aviso = "";
	$recargos=0;
	$revierte=0;
	foreach ( $socact as $socio ) {
		echo "Procesando $socio->sid ---";
		$sid = $socio->sid;
		$apynom = $socio->apynom;
		$descr_actividad = $socio->descr_actividad;
		$aid = $socio->aid;
        	switch ( $dia ) {
			case 15:
				$deuda = $this->pagos_model->get_deuda_jardin($sid);
				if ( $deuda ) {
                			$total = $this->pagos_model->get_socio_total($sid);
					$mes_deuda = $deuda[0]->mes;
					$monto_deuda = $deuda[0]->monto;
					// Meto recargo porque estamos a 15 y no pago el mes
					$total_act = $total - $recargo;

					$facturacion = array(
						'sid' => $sid,
						'descripcion'=>'Recargo '.$descr_actividad.' por atraso en el pago',
						'debe'=>$recargo,
						'haber'=>0,
						'total'=>$total_act
						);
					$this->pagos_model->insert_facturacion($facturacion);

					$pago = array(
						'sid' => $sid,
						'tutor_id' => $sid,
						'aid' => $aid,
						'generadoel' => date('Y-m-d'),
						'descripcion' => "Recargo '.$descr_actividad.' por atraso en el pago",
						'monto' => $recargo,
						'tipo' => 10,
						'ajuste' => 1
						);
					$this->pagos_model->insert_pago_nuevo($pago);
					$recargos++;
	
					$aviso .= "Facturo recargo al socio $sid - $apynom recargo de $ $recargo por atraso en el pago de la cuota de $descr_actividad por $ $monto_deuda del mes de $mes_deuda  \n";
				}

				break;
			default:
				// Controlo si se imputo un pago con fecha anterior a los limites
				$recjardin = $this->pagos_model->revierte_recargo_jardin($sid);
				if ( $recjardin ) {
                                       	// Anulo recargo porque imputamos despues un pago anterior al recargo
                			$total = $this->pagos_model->get_socio_total($sid);
                                       	$total_act = $total + $recargo;
					$id_recargo = $recjardin['id_recargo'];
					$imp_recargo = $recjardin['imp_recargo'];
					$fch_pago = $recjardin['fecha_pago'];

                                       	$facturacion = array(
                                               	'sid' => $sid,
                                               	'descripcion'=>'Anulo recargo '.$descr_actividad.' porque el pago llego posterior',
                                               	'debe'=>0,
                                               	'haber'=>$recargo,
                                               	'total'=>$total_act
                                               	);
                                       	$this->pagos_model->insert_facturacion($facturacion);

					$this->db->where('id',$id_recargo); 
					$this->db->update('pagos',array('pagadoel'=>$fch_pago, 'pagado'=>$imp_recargo, 'estado'=>'0'));
					$revierte++;

                                       	$aviso .= "Anulo recargo al socio $sid - $apynom de $ $imp_recargo por imputacion posterior del pago de la cuota de $descr_actividad  \n";
				} else {
					// Aviso que ese socio sigue sin pagar el mes
					$aviso .= "El socio $sid - $apynom sigue sin abonar la cuota de $descr_actividad \n";
				}
				
				break;
		}
	}
	$xahora=date('Y-m-d G:i:s');

        // Me mando email de aviso que el proceso termino OK
        mail('jardinlaciudad@villamitre.com.ar', "El proceso de Control de Jardin finalizo correctamente.", "Este es un mensaje automático generado por el sistema. \n El proceso termino con $recargos recargos y $revierte reversiones, segun el siguiente detalle \n $aviso ....".$xahora."\n",'From: avisos_cvm@clubvillamitre.com'."\r\n");
        mail('cvm.agonzalez@gmail.com', "El proceso de Control de Jardin finalizo correctamente.", "Este es un mensaje automático generado por el sistema. \n El proceso termino con $recargos recargos y $revierte reversiones, segun el siguiente detalle \n $aviso ....".$xahora."\n",'From: avisos_cvm@clubvillamitre.com'."\r\n");
    }

    public function intereses()
    {
        if(date('d') != 20){ die(); }
        $this->load->model('general_model');
	// Busco el ID=1 del interes general
        $config = $this->general_model->get_config(1);
        if($config->interes_mora > 0){
            $this->load->model("socios_model");            
            $this->load->model('pagos_model');
            $socios = $this->socios_model->get_socios_pagan();
            foreach ($socios as $socio) {
                $cuota = $this->pagos_model->get_monto_socio($socio->Id);
                $total = $this->pagos_model->get_socio_total($socio->Id);
                if($total*-1 > $cuota['total']){
                    $debe = $cuota['total'] * $config->interes_mora /100;
                    
                    $total = $total - $debe;
                    $facturacion = array(
                        'sid' => $socio->Id,
                        'descripcion'=>'Intereses por Mora',
                        'debe'=>$debe,
                        'haber'=>0,
                        'total'=>$total
                    );
                    $this->pagos_model->insert_facturacion($facturacion);

                    $pago = array(
                        'sid' => $socio->Id, 
                        'tutor_id' => $socio->Id,
                        'aid' => 0, 
                        'generadoel' => date('Y-m-d'),
                        'descripcion' => "Intereses por Mora",
                        'monto' => $debe,
                        'tipo' => 2,
                        );                    
                    $this->pagos_model->insert_pago_nuevo($pago);
                }
            }            
        }
    }

    public function envios_cron() 
	{
		echo "Comienzo Cron Envios ".date('Y-m-d H:i:s');
		$this->load->model('general_model');

		$pend_factu = $this->general_model->get_pend_envfact();
		$pend_masivo = $this->general_model->get_pend_envios();

		// tipo 1 facturacion mensual
		// Si existen emails para enviar
		echo "Facturacion ".$pend_factu->pendientes."\n";
		if ( $pend_factu->pendientes > 0 ) {
			// Busco el ultimo cron de envio de facturacion
			$ult_envio = $this->general_model->get_ult_cron(1);
			// Si existe verifico el estado
			if ( $ult_envio ) {
				// Si esta corriendo verifico la anticuacion y si es mas de 30 minutos considero un envio nuevo
				if ( $ult_envio->estado == 1 ) {
					if ( $ult_envio->anticuacion > 30 ) {
						// Si tiene mas de 30m de antiguedad lo marco con error y creo uno nuevo y largo un envio de lote
                                                $this->general_model->upd_ult_cron(1,1);
						$this->facturacion_mails();
					}
				} else {
				// Sino esta corriendo creo un nuevo cron y largo lote
                                        $this->general_model->upd_ult_cron(1,1);
					$this->facturacion_mails();
				}
			// Sino existe lo creo y largo el envio de un lote
			} else {
				$this->general_model->insert_ult_cron(1);
				$this->facturacion_mails();
			}
			
		}
		echo "Masivos ".$pend_masivo->pendientes."\n";
		// tipo 2 envios masivos
                if ( $pend_masivo->pendientes > 0 ) {
                        // Busco el ultimo cron de envio de facturacion
                        $ult_envio = $this->general_model->get_ult_cron(2);
                        // Si existe verifico el estado
                        if ( $ult_envio ) {
                                // Si esta corriendo verifico la anticuacion y si es mas de 30 minutos considero un envio nuevo
                                if ( $ult_envio->estado == 1 ) {
                                        if ( $ult_envio->anticuacion > 30 ) {
                                                // Si tiene mas de 30m de antiguedad lo marco con error y creo uno nuevo y largo un envio de lote
                                                $this->general_model->upd_ult_cron(2,1);
                                                $this->masivo_mails();
                                        }
                                } else {
                                // Sino esta corriendo creo un nuevo cron y largo lote
                                        $this->general_model->upd_ult_cron(2,1);
                                        $this->masivo_mails();
                                }
                        // Sino existe lo creo y largo el envio de un lote
                        } else {
                                $this->general_model->insert_ult_cron(2);
                                $this->masivo_mails();
                        }

                }
		echo "Termine Cron Envios ".date('Y-m-d H:i:s');
	}

    public function masivo_mails()
    {
                $this->load->model('general_model');
                $envio_info = $this->general_model->get_prox_envios();


		if ( $envio_info ) {
        		$path_log = './application/logs/envios-masivos-'.date('Ymd').'.log';
			if( !file_exists($path_log) ){
				$log = fopen($path_log,'w');
			} else {
				$log = fopen($path_log,'a');
			}
			$eid = 0;
			if ( count($envio_info) < 10 ) {
                        	if ( $envio_info[0]->estado == 99 ) {
                                	$ultima_tanda = 9;
                                	$id_envio_test = $envio_info[0]->eid;
                                	fwrite($log,date('d-m-Y H:i:s')."Se encontraron ".count($envio_info)." correos. Enviando Testing a direcciones fijas...n");
                        	} else {
                                	$ultima_tanda = 1;
                                	fwrite($log,date('d-m-Y H:i:s')."Se encontraron ".count($envio_info)." correos. Enviando Ultimo lote...n");
                        	}
			} else {
				$ultima_tanda = 0;
		    		fwrite($log,date('d-m-Y H:i:s')."Enviando lote\n");
			}
	
			$enviados=0;
			foreach ( $envio_info as $envio ) {
                    		$this->load->library('email');
                    		$this->email->from('avisos_cvm@clubvillamitre.com', 'Club Villa Mitre');
                    		$this->email->to($envio->email);
		    		if ( $ultima_tanda == 9 ) {
                    			$this->email->subject($envio->titulo."***TESTING***");
		    		} else {
                    			$this->email->subject($envio->titulo);
 		    		}
		    		if ( $ultima_tanda == 9 ) {
                    			$this->email->message("---testing---\n".$envio->body."---testing---\n");
		    		} else {
                    			$this->email->message($envio->body);
		    		}
                    		$st = $this->email->send();
		    		$eid = $envio->eid;
		
                    		//echo $this->email->print_debugger();
		    		if ( $st ) {
		    			fwrite($log,date('d-m-Y H:i:s')."Envie email a $envio->email \n");
                    			$this->general_model->enviado($envio->Id);
		    		} else {
		    			fwrite($log,date('d-m-Y H:i:s')."Fallo envio email a $envio->email \n");
					if ( $ultima_tanda == 9 ) {
                    				$this->general_model->enviado_error($envio->Id,1);
					} else {
                    				$this->general_model->enviado_error($envio->Id,0);
					}
                    		}
			
		    		// Agrego demora para que no considere al envio un SPAM / BULK
		    		sleep(2);
                	}
	        	$this->general_model->upd_ult_cron(2);
			if ( $ultima_tanda == 1 ) {
                		fwrite($log, date('d/m/Y G:i:s').": Envio Finalizado \n");
                		$resumen = $this->general_model->get_resumen_envios($eid);
                		$env_ok = $resumen->estado1;
                		$env_err = $resumen->estado9;
				$xahora = date('Y-m-d H:i:s');
                		mail('cvm.agonzalez@gmail.com', "El proceso de Envio de Emails finalizo correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de envios de email finalizó correctamente y se enviaron $env_ok emails bien y hubo $env_err emails con error.....".$xahora."\n",'From: avisos_cvm@clubvillamitre.com'."\r\n");
			} else {
                		if ( $ultima_tanda == 9 ) {
                        		fwrite($log, date('d/m/Y G:i:s').": Envio Testing Finalizado \n");
                        		$this->general_model->marca_envio_test($id_envio_test);
                        		$xahora = date('Y-m-d H:i:s');
                        		mail('cvm.agonzalez@gmail.com', "El proceso de Envio de TESTING Emails finalizo correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de envios de email finalizó correctamente y se enviaron $enviados emails ...".$xahora."\n",'From: avisos_cvm@clubvillamitre.com'."\r\n");
                		}
	
        		}
		}
    }

    public function facturacion_mails()
    {
	$this->load->model('general_model');
        $path_log = './application/logs/envios-fact-'.date('Ymd').'.log';

	if( !file_exists($path_log) ){
		$log = fopen($path_log,'w');
	} else {
		$log = fopen($path_log,'a');
	}

	fwrite($log,date('d-m-Y H:i:s')."Buscando correos para enviar ...\n");

        $envios = $this->general_model->get_prox_envfact();
        if (count($envios) == 0){ 
            	return false;
        }else{
		if ( count($envios) < 10 ) { 
			$ultima_tanda = 1;
			fwrite($log,date('d-m-Y H:i:s')."Se encontraron ".count($envios)." correos. Enviando Ultimo lote...n");
		} else {
			$ultima_tanda = 0;
			fwrite($log,date('d-m-Y H:i:s')."Se encontraron ".count($envios)." correos. Enviando Lote ...\n");
		}
            	$this->load->library('email');
	    	$enviados=0;
            	foreach ($envios as $email) {
			$this->email->from('avisos_cvm@clubvillamitre.com', 'Club Villa Mitre');
                	$this->email->to($email->email);                 
	
                	$asunto='Resumen de Cuenta al '.date('d/m/Y');
                	$this->email->subject($asunto);                
                	$this->email->message($email->body); 
			$ahora = date('d/m/Y G:i:s');
			fwrite($log,date('d-m-Y H:i:s').": Enviando: ".$email->email);
			
			// comento el envio para hacer la prueba sin envio
                	$st = $this->email->send();

			if ($st) {
				fwrite($log,"----> Enviado OK \n ");
				$this->db->where('Id',$email->Id);
				$this->db->update('facturacion_mails',array('estado'=>1));
			} else {
				$this->db->where('Id',$email->Id);
				$this->db->update('facturacion_mails',array('estado'=>9));
				$msg_error=substr($this->email->print_debugger(),0,20);
				fwrite($log,"----> Error de Envio: ".$msg_error." \n ");
			}
			$enviados++;
			sleep(2);
            }
	    $this->general_model->upd_ult_cron(1);
	    $this->general_model->upd_env_fact();
	    if ( $ultima_tanda == 1 ) {
            	fwrite($log, date('d/m/Y G:i:s').": Envio Finalizado \n");
		$resumen = $this->general_model->get_resumen_fact();
		$env_ok = $resumen->estado1;
		$env_err = $resumen->estado9;
		$xahora = date('Y-m-d H:i:s');
            	mail('cvm.agonzalez@gmail.com', "El proceso de Envio de Emails finalizo correctamente.", "Este es un mensaje automático generado por el sistema para confirmar que el proceso de envios de email finalizó correctamente y se enviaron $env_ok emails bien y hubo $env_err emails con error.....".$xahora."\n",'From: avisos_cvm@clubvillamitre.com'."\r\n");
	    }
            
	}
    }

    public function pagos_nuevos($value='')
    {
        return false; die;
        $this->load->database();
        $this->load->model('pagos_model');
        $this->db->where('estado',1);        
        $query = $this->db->get('socios');
        $socios = $query->result();
        foreach ($socios as $socio) {            
            $total = $this->pagos_model->get_socio_total($socio->Id);            
            $pago = array(
                'sid' => $socio->Id, 
                'tutor_id' => $sid,
                'aid' => 0, 
                'generadoel' => date('Y-m-d'),
                'descripcion' => "Deuda Anterior",
                'monto' => $total*-1,
                'tipo' => 1,
                );
            if($total*-1 < 0){
                $pago['descripcion'] = 'A favor';
                $pago['tipo'] = 5;
            }
            $this->pagos_model->insert_pago_nuevo($pago);
            var_dump($pago);
            echo '<hr>';
        }
    }

    public function correccion()
    {
        return false; die();
        $this->load->database();
        $this->load->model('pagos_model');
        $this->db->where('estado',1);
        $this->db->where('actual >',1);
        $query = $this->db->get('financiacion');
        $f = $query->result();
        foreach ($f as $ff) {
            $haber = ($ff->actual - 1) * ($ff->monto/$ff->cuotas);
            $total = $this->pagos_model->get_socio_total($ff->sid);
            $total = $total + $haber;
            $facturacion = array(
                'sid' => $ff->sid, 
                'descripcion' => 'Corrección de Financiación de Deuda', 
                'debe'=>0,
                'haber'=>$haber,
                'total'=>$total
                );

            $this->db->insert('facturacion',$facturacion);
            var_dump($facturacion);
            echo '<hr>';
        }
    }  

    public function control()
    {
        $this->load->model("pagos_model");
        $this->load->model("socios_model");
        $socios = $this->socios_model->listar(); //listamos todos los socios activos
        foreach ($socios as $socio) {    
            $total = $this->pagos_model->get_socio_total($socio['datos']->Id);
            $total2 = $this->pagos_model->get_socio_total2($socio['datos']->Id);
            if($total + $total2 != 0 && $total <= 0){
                echo $socio['datos']->Id.' | '.$total.' | '.$total2.'<br>';            
            }
        }
    }  
}

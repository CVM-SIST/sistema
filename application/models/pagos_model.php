<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 */
class Pagos_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database('default');
    }

    public function get_cupon($sid){
        $this->load->model("socios_model");
        $socio = $this->socios_model->get_socio($sid);
        if($socio->tutor != 0){
            $grupo_familiar = true;
            //si el socio pertenece a un grupo familiar buscamos el cupon del tutor
            return $this->get_cupon($socio->tutor);
        }

        $query = $this->db->get_where('cupones',array('sid'=>$sid,'estado'=>'1'));
        if($query->num_rows() == 0){
            $cupon = new stdClass();
            $cupon->monto = '0';
            return $cupon;
        }else{
            return $query->row();
        }
    }

    public function get_cupones_old() 
    {
        $this->db->where('estado',0);
        $this->db->where('DATE(date) < DATE_SUB(CURDATE(), INTERVAL 360 DAY)');
        $query = $this->db->get('cupones');
        if($query->num_rows() == 0){return false;}
        $cupones = $query->result();
        return $cupones;
    }

    public function get_cobrod_libres()
    {
	$qry = "SELECT COUNT(*) libres FROM cupones_cobrod WHERE sid = 0 AND date = '0000-00-00 00:00:00'; ";
        $libres = $this->db->query($qry)->result();
	if ( $libres ) {
		return $libres[0]->libres;
	} else {
		return 0;
	}
    }

    public function get_cupon_libre_cobrod($socio)
    {
	// Busco el primer cupon libre
        $this->db->where('sid',"0");
        $this->db->where('date',"0000-00-00 00:00:00");
        $this->db->order_by('id','asc');
        $query = $this->db->get('cupones_cobrod');
        if($query->num_rows() == 0){return false;}
        $cupon = $query->row();
        $query->free_result();

	$sid = $socio->Id;

	// Actualizo los datos con el sid a asignarle y el ts de este momento
	$qry = "UPDATE cupones_cobrod SET sid = $sid, date=NOW() WHERE id = $cupon->id; ";
        $this->db->query($qry);
	
	// Doy de alta el pagador en el sistema de Cobro Digital
	$cupon_cobrod = $this->get_cupon_cobrod_by_sid($sid);
	$this->_altaPagadorCD($socio, $cupon_cobrod);

	// Meto la imagen del cupon en el directorio correspondiente
	$img = file_get_contents("https://www.cobrodigital.com/wse/bccd/".$cupon->barcode."h.png");
	file_put_contents('images/cupones/cobrod/'.$sid.'.png', $img);

        return $cupon_cobrod;
    }

    private function _altaPagadorCD($socio, $cupon_cobrod)
    {
		$this->config->load('cobrodigital');
                $url = "https://www.cobrodigital.com/ws3";

                $SID = $this->config->item('cd_sid');
                $id_comercio = $this->config->item('cd_idcomercio');
                $headers= array("Content-Type: application/json");
                $post = array('idComercio' => $id_comercio, 'sid' => $SID, 'metodo_webservice'=> 'consultar_transacciones', 'desde' => "$fecha", 'hasta' => "$fecha"    );
                $post = json_encode($post);

		$apynom = $socio->apellido.", ".$socio->nombre;
		$pagador = array("id" => $socio->Id, "Apellido y nombres" => "$apynom", "Direccion del cliente" => "$socio->domicilio", "E-mail" => "$socio->email" , "tarjeta" => "$cupon_cobrod->barcode" , "COD ELECTRONICO" => "$cupon_cobrod->codlink" );

    		$post = array('idComercio' => $id_comercio, 'sid' => $SID, 'metodo_webservice'=> 'crear_pagador', 'pagador' => $pagador );
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

    }

    public function get_cupon_cobrod_by_sid($sid)
    {
        $this->db->where('sid',$sid);
        $query = $this->db->get('cupones_cobrod');
        if($query->num_rows() == 0){return false;}
        $cupon = $query->row();
        $query->free_result();
        return $cupon;
    }

    public function get_cupon_cobrod($barcode)
    {
        $this->db->where('barcode',$barcode);
        $query = $this->db->get('cupones_cobrod');
        if($query->num_rows() == 0){return false;}
        $cupon = $query->row();
        $query->free_result();
        return $cupon;
    }

    public function get_cupon_by_id($id='')
    {
        $this->db->where('Id',$id);
        $query = $this->db->get('cupones');
        if($query->num_rows() == 0){return false;}
        $cupon = $query->row();
        $query->free_result();
        return $cupon;
    }

    public function pasa_fact_tutor($id, $tutor)
    {
        $this->load->model("pagos_model");

        $qry = "SELECT SUM(monto-pagado) saldo FROM pagos WHERE tutor_id = $id; ";
        $saldo = $this->db->query($qry)->result();
        $monto_saldo = $saldo[0]->saldo;
        
        $this->load->model("socios_model");
        $soc_tutor = $this->socios_model->get_socio($tutor);
	$apynom_tutor = $soc_tutor->apellido.", ".$soc_tutor->nombre;
        $soc_socio = $this->socios_model->get_socio($id);
	$apynom_socio = $soc_socio->apellido.", ".$soc_socio->nombre;
        
        if ( $monto_saldo > 0 ) {
            $this->pagos_model->registrar_pago('haber', $id, $monto_saldo,'Transfiere saldo a su tutor: '.$tutor."-".$apynom_tutor);
            $this->pagos_model->registrar_pago('debe', $tutor, $monto_saldo,'Recibe saldo de: '.$id."-".$apynom_socio);
        } else {
            $this->pagos_model->registrar_pago('debe', $id, $monto_saldo,'Transfiere saldo a su tutor: '.$tutor."-".$apynom_tutor);
            $this->pagos_model->registrar_pago('haber', $tutor, $monto_saldo,'Recibe saldo de: '.$id."-".$apynom_socio);
        }
    }


    public function get_monto_socio($sid){ // devuelve el importe que deberá pagar un socio o su tutor, en caso de pertenecer a un grupo familiar
        $grupo_familiar = $tutor = false;
        $monto = 0;
        //obtenemos el precio de cada categoria
        $this->load->model("general_model");

	$categ_grupo_fam = $this->general_model->get_cat(4);
	$precio_excedente = $categ_grupo_fam->precio_unit;

        //buscamos si el socio pertenece a un grupo familiar
        $this->load->model("socios_model");
        $socio = $this->socios_model->get_socio($sid);

        if($socio->tutor != 0){
            $grupo_familiar = true;
            //si el usuario pertenece a un grupo familiar buscamos el monto del tutor
            return $this->get_monto_socio($socio->tutor);
        }

        //buscamos si el usuario es tutor de grupo familiar
        $query = $this->db->get_where('socios',array('tutor'=>$sid,'estado'=>'1'));
        if($query->num_rows() != 0){
            $tutor = true;
            $familiares_a_cargo = $query->result();
            $fam_actividades = array();
            $total_familiares = (count($familiares_a_cargo) + 1);  //la cantidad de familiares a cargo mas el tutor
            $familiares = array();
            foreach ($familiares_a_cargo as $familiar) { // buscamos las actividades de cada familiar
                $fam_actividades = $this->get_actividades_socio($familiar->Id);
                $familiares[] = array('datos' => $familiar, 'actividades' => $fam_actividades);
            }

            //buscamos las actividades del socio titular
            $socio_actividades =  $this->get_actividades_socio($sid);
            //buscamos los familiares excedentes
            $excedente = $total_familiares - 4; // 4 = total del grupo familiar
            $monto_excedente = 0;
            for ($i=0; $i < $excedente; $i++) {
                $monto_excedente = $monto_excedente + $precio_excedente;
            }

            $monto = $categ_grupo_fam->precio - ($categ_grupo_fam->precio * $socio->descuento / 100); //valor de la cuota de grupo familiar
            $total = $monto + ( $monto_excedente - ($monto_excedente * $socio->descuento / 100) ); //cuota mensual mas el excedente en caso de ser mas socios de lo permitido en el girpo fliar

            foreach ($socio_actividades['actividad'] as $actividad) {
		/// Si tiene seguro y no es federado lo considero dentro del total de la cuota
		if ( $actividad->federado == 0 && $actividad->seguro > 0 ) {
			$valor_act=$actividad->precio+$actividad->seguro;
		} else {
			$valor_act=$actividad->precio;
		}
		// actividades del titular del grupo familiar
		if ( $actividad->descuento > 0 ) {
			$tb = $actividad->monto_porcentaje;
			if ( $tb == 0 || $tb == 2 || $tb == 4 || $tb == 6 ) {
				if ( $actividad->precio > 0 ) {
                		 	$total = $total + ( $valor_act - $actividad->descuento );
				}
			} else {
                		$total = $total + ( $valor_act - ( $valor_act * $actividad->descuento / 100 ) );
			}
		} else {
			$total=$total+$valor_act;
		}
            }

            foreach ($familiares as $familiar) {
                foreach($familiar['actividades']['actividad'] as $actividad){
			/// Si tiene seguro y no es federado lo considero dentro del total de la cuota
			if ( $actividad->federado == 0 && $actividad->seguro > 0 ) {
				$valor_act=$actividad->precio+$actividad->seguro;
			} else {
				$valor_act=$actividad->precio;
			}
			if ( $actividad->descuento > 0 ) {
				//actividades del los socios del grupo famlilar
		    		$tb = $actividad->monto_porcentaje;
		    		if ( $tb == 0 || $tb == 2 || $tb == 4 || $tb == 6 ) {
					if ( $actividad->precio > 0 ) {
                    				$total = $total + ( $valor_act - $actividad->descuento );
					}
		    		} else {
                    			$total = $total + ( $valor_act - ($valor_act * $actividad->descuento /100) );
		    		}
			} else {
				$total=$total+$valor_act;
			}
                }
            }

            $financiacion = $this->get_financiado_mensual($socio->Id);
            $f_total = 0;
            if($financiacion){
                foreach ($financiacion as $plan) {
                    $f_total = $f_total + round($plan->monto/$plan->cuotas,2);
                }
            }

            $total = $total + $f_total;
            $cuota = array(
                "tid" => $sid,
                "titular" => $socio->apellido.' '.$socio->nombre,
                "total" => $total,
                "categoria" => 'Grupo Familiar',
                "cuota" => $monto,
                "familiares" => $familiares,
                "actividades" => $socio_actividades,
                "excedente" => $excedente,
                "monto_excedente" => $monto_excedente- ($monto_excedente * $socio->descuento / 100),
                "financiacion" => $financiacion,
                "descuento" => $socio->descuento,
                "cuota_neta"=>$categ_grupo_fam->precio
            );
            return $cuota;

        }else{ //si no esta en un grupo familiar
            $socio_actividades =  $this->get_actividades_socio($sid); //buscamos las actividades del socio
	    $categ_socio = $this->general_model->get_cat($socio->categoria);
            $socio_cuota = $categ_socio->precio - ($categ_socio->precio * $socio->descuento / 100); //precio de la cuota
            $total = $socio_cuota; //cuota mensual
            foreach ($socio_actividades['actividad'] as $actividad) {
		if ( $actividad->federado == 0 && $actividad->seguro > 0 ) {
			$valor_act=$actividad->precio+$actividad->seguro;
		} else {
			$valor_act=$actividad->precio;
		}
		//actividades del socio
		$tb = $actividad->monto_porcentaje;
		if ( $tb == 0 || $tb == 2 || $tb == 4 || $tb == 6 ) {
			if ( $actividad->precio > 0 ) {
                		$total = $total + ( $valor_act - $actividad->descuento );
			}
		} else {
                	$total = $total + ( $valor_act - ($valor_act * $actividad->descuento /100 ) );
		}
            }

            $financiacion = $this->get_financiado_mensual($socio->Id);
            $f_total = 0;
            if($financiacion){
                foreach ($financiacion as $plan) {
                    $f_total = $f_total + round($plan->monto/$plan->cuotas,2);
                }
            }


            $total = $total + $f_total;
            $cuota = array(
                "tid" => $sid,
                "titular" => $socio->apellido.' '.$socio->nombre,
                "total" => $total,
                "categoria" => $categ_socio->nomb,
                "cuota" => $socio_cuota,
                "familiares" => '0',
                "actividades" => $socio_actividades,
                "excedente" => '0',
                "monto_excedente" => '0',
                "financiacion" => $financiacion,
                "descuento" => $socio->descuento,
                "cuota_neta"=>$categ_socio->precio
            );
        return $cuota;
        }
    }

    function get_actividades_socio($sid){
        $this->load->model("socios_model");  //buscamos datos del socio
        $socio = $this->socios_model->get_socio($sid);
        $this->load->model("actividades_model"); //buscamos las actividades del socio
        $actividades = $this->actividades_model->get_act_asoc($sid);
        $act = array();
        foreach ($actividades as $actividad) {
                    if($actividad->estado == '1'){
                        $act[] = $actividad;
                    }
                }
        $actividades_socio = array('actividad' =>$act);
        return $actividades_socio; // devolvemos los datos de la actividad y el usuario correspondiente
    }

    function generar_cupon_cobrod($cupon)
    {
        $this->db->insert('cupones_cobrod',$cupon);
        return $this->db->insert_id();
    }	    

    function generar_cupon($sid, $monto,$cupon)
    {
        $this->db->where('sid',$sid); // ponemos en 0 todos los cupones de este socio
        $this->db->update('cupones',array('estado'=>'0'));
        $data = array(
                'sid' => $sid,
                'monto' => $monto,
                'estado' => '1',
                'barcode' => $cupon['barcode']
            );
        $this->db->insert('cupones',$data);
        return $this->db->insert_id();
    }
    public function get_socio_total($sid)
    {
        $this->db->where('sid',$sid);
        $this->db->order_by('Id','desc');
        $fact = $this->db->get('facturacion');
        if($fact->num_rows() == 0){return false;}
        $total = $fact->row()->total;
        return $total;
    }
    public function get_saldo_socio($sid) 
    {
	$qry = "SELECT SUM(p.pagado-p.monto) saldo FROM pagos p WHERE sid = $sid AND estado = 1; ";
	$total = $this->db->query($qry)->result();
        return $total[0]->saldo;
    }

    public function get_socio_total2($sid)
    {
        $this->db->where('tutor_id',$sid);
        $this->db->where('tipo !=',5);
        $this->db->where('estado',1);
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){return false;}
        $total = 0;
        foreach ($query->result() as $pago) {
            $total = $total + $pago->monto;
            $total = $total - $pago->pagado;
        }
        return $total;
    }


    public function insert_facturacion_col($data)
    {
        $this->db->insert('facturacion_col',$data);
    }

    public function insert_facturacion($data)
    {
        $this->db->insert('facturacion',$data);
    }

    function insert_cobranza_col($data)
    {
        $datos = array(
                'sid' => $data['sid'],
                'periodo' => $data['periodo'],
                'fecha_pago' => $data['fecha_pago'],
                'suc_pago' => $data['suc_pago'],
                'nro_cupon' => $data['nro_cupon'],
                'importe' => $data['importe'],
		'id' => 0
            );
        $this->db->insert('cobranza_col',$datos);
        return $this->db->insert_id();
    }

    function get_cobcols($periodo)
    {
        $query = $this->db->get_where('facturacion_col',array('periodo'=>$periodo));

	$registros=array();
        foreach ($query->result() as $fact) {

		$nro_socio=$fact->sid;
		$cobcol= get_cobcol($nro_socio,$periodo);
		$fecha_pago=0;
		$suc_pago=0;
		$nro_cupon=0;
		if ( $cobcol ) {
			$fecha_pago = $cobcol->fecha_pago;
			$suc_pago = $cobcol->suc_pago;
			$nro_cupon = $cobcol->nro_cupon;
		}
            	$registro = array(
                	'id' => $fact->id,
                	'sid' => $fact->sid,
                	'periodo' => $periodo,
                	'importe' => $fact->importe,
                	'cta_socio' => $fact->cta_socio,
                	'actividades' => $fact->actividades,
                	'fecha_pago' => $fecha_pago,
                	'suc_pago' => $suc_pago,
                	'nro_cupon' => $nro_cupon
                );
		$registros[]=$registro;
        }

	return $registros;
    }

    function get_cobcol($sid, $periodo) {
        $this->db->where('sid', $sid);
        $this->db->where('periodo', $periodo);
        $query = $this->db->get('cobranza_col');
        if($query->num_rows() == 0){
		return false;
	} else {
		return $query->row();
	}
    }

    function get_cobcol_id($nro_socio, $periodo, $nro_cupon) {
        $this->db->where('sid', $nro_socio);
        $this->db->where('periodo', $periodo);
        $this->db->where('nro_cupon', $nro_cupon);
        $query = $this->db->get('cobranza_col');
        if($query->num_rows() == 0){
                return false;
        } else {
                return $query->row();
        }


    }

    public function check_cron($periodo)
    { //comprueba si ya se ejecuto la tarea este mes o si esta en curso
	$anio=substr($periodo,0,4);
	$mes=substr($periodo,4,2);
	$ahora=date($anio.'-'.$mes.'-'.'01 00:00:00');
        $this->db->where('YEAR(date)' , $anio);
        $this->db->where('MONTH(date)' , $mes);

        $query = $this->db->get('facturacion_cron');
        if($query->num_rows() == 0){
            $this->db->insert( 'facturacion_cron',array('date'=>$ahora, 'des'=>'0','en_curso'=>1) );
            return 'iniciado';
        }else{
            $cron_state = $query->row();
            if($cron_state->en_curso == 1){
                return 'en_curso';
            }else{
                return false;
            }
        }
    }

    public function update_facturacion_cron($periodo, $tipo, $cant, $importe)
    {
	$anio=substr($periodo,0,4);
	$mes=substr($periodo,4,2);
        $this->db->where('YEAR(date)' , $anio);
        $this->db->where('MONTH(date)' , $mes);
	$query = $this->db->get('facturacion_cron');
	if ( $query->num_rows() == 0 ) {
		return false;
	} else {
		$cron_state = $query->row();
		if ( $cron_state->en_curso == 1 ) {
			$this->db->where( 'Id', $cron_state->Id );
			switch ( $tipo ) {
				// Suspendidos
				case 1:
					$this->db->update( 'facturacion_cron',array('socios_suspendidos'=>$cant) );
					break;
				// Cambio de categoria a Mayor
				case 2:
					$this->db->update( 'facturacion_cron',array('socios_cambio_mayor'=>$cant) );
					break;
				// Facturados
				case 3:
					$cant_act=$cron_state->socios_facturados+$cant;
					$total_act=$cron_state->total_facturado+$importe;
					$this->db->update( 'facturacion_cron',array('socios_facturados'=>$cant_act, 'total_facturado'=>$total_act) );
					break;
				// Debito
				case 4:
					$cant_act=$cron_state->socios_debito+$cant;
					$total_act=$cron_state->total_debito+$importe;
					$this->db->update( 'facturacion_cron',array('socios_debito'=>$cant_act, 'total_debito'=>$total_act) );
					break;
				// Archivo de cobranza COL
				case 5:
					$cant_act=$cron_state->socios_col+$cant;
					$total_act=$cron_state->total_col+$importe;
					$this->db->update( 'facturacion_cron',array('socios_col'=>$cant_act, 'total_col'=>$total_act) );
					break;
			}
			return true;
		} else {
			return false;
		}
	}
    }

    public function get_facturacion_cron($periodo)
    {
        $anio=substr($periodo,0,4);
        $mes=substr($periodo,4,2);
        $this->db->where('YEAR(date)' , $anio);
        $this->db->where('MONTH(date)' , $mes);
        $query = $this->db->get('facturacion_cron');
        if ( $query->num_rows() == 0 ) {
                return false;
        } else {
		return $query->row();
	}
     }


    public function insert_facturacion_cron()
    {
    }
    public function get_facturacion($sid)
    {
        $this->db->order_by('DATE(date)','desc');
        $this->db->order_by('Id','desc');
        $this->db->where("sid", $sid);
        $query = $this->db->get('facturacion');
        return $query->result();
    }
    public function check_cron_pagos()
    { //comprueba si ya se ejecuto la tarea hoy
        $this->db->where(date('Y'), 'YEAR(date)' , FALSE);
        $this->db->where(date('m'), 'MONTH(date)' , FALSE);
        $this->db->where(date('d'), 'DAY(date)' , FALSE);
        $query = $this->db->get('pagos_cron');
        if($query->num_rows() == 0){
            return false;
        }else{
            return true;
        }
    }
    public function insert_pagos_cron($fecha)
    {
        $this->db->insert('pagos_cron',array('date'=>$fecha,'des'=>'0'));
    }

    public function insert_pago_col($pago)
    {
        $total = $this->get_deuda($pago['sid']);
        $total = $total + $pago['monto'];
        $descripcion = "Pago acreditado desde: La Coope <br>Fecha: ".$pago['fecha'].' '.$pago['hora'];
        $this->db->insert('facturacion',array('sid'=>$pago['sid'],'haber'=>$pago['monto'],'total'=>$total,'descripcion'=>$descripcion, 'origen'=>'1'));

    }

    public function insert_pago($pago)
    {
        $total = $this->get_deuda($pago['sid']);
        $total = $total + $pago['monto'];
        $descripcion = "Pago acreditado desde: CuentaDigital <br>Fecha: ".$pago['fecha'].' '.$pago['hora'];
        $this->db->insert('facturacion',array('sid'=>$pago['sid'],'haber'=>$pago['monto'],'total'=>$total,'descripcion'=>$descripcion, 'origen'=>'2'));

    }

    public function insert_pago_cobrod($pago)
    {
        $total = $this->get_deuda($pago['sid']);
        $total = $total + $pago['monto'];
        $descripcion = "Pago acreditado desde: CobroDigital <br>Fecha: ".$pago['fecha'].' '.$pago['hora'];
        $this->db->insert('facturacion',array('sid'=>$pago['sid'],'haber'=>$pago['monto'],'total'=>$total,'descripcion'=>$descripcion, 'origen'=>'6'));

    }

    public function registrar_pago($tipo,$sid,$monto,$des,$actividad,$ajuste, $origen='0')
    {
        $total = $this->get_socio_total($sid);
        $this->load->model("socios_model");
        $reg_socio = $this->socios_model->get_socio($sid);
	if ( $reg_socio->tutor > 0 ) {
		$tutor = $reg_socio->tutor;
	} else {
		$tutor = $sid;
	}

        if($tipo == 'debe'){
            $debe = $monto;
            $haber = '0.00';
            $total = $total - $debe;
            if($actividad == 'cs'){
                $aid = 0;
                $tipo = 1;
            }else{
		// Si en la leyenda pongo que es un Seguro lo tipifico como 6 pero con el aid de la actividad
		if ( substr($des,0,6) == "Seguro" ) {
                	$aid = $actividad;
                	$tipo = 6;
		} else {
                	$aid = $actividad;
                	$tipo = 4;
		}
            }
            $pago = array(
                'sid' => $sid,
                'tutor_id' => $tutor,
                'aid' => $aid,
                'generadoel' => date('Y-m-d'),
                'descripcion' => $des,
                'monto' => $monto,
                'tipo' => $tipo,
                );
            $this->pagos_model->insert_pago_nuevo($pago);
            $this->registrar_pago2($tutor,0);
        }else{
            $haber = $monto;
            $debe = '0.00';
            $total = $total + $haber;
            $this->registrar_pago2($tutor,$monto,$ajuste);
        }
        if ( $ajuste == 1 ) {
                $orig=4;
        } else {
                $orig=3;
        }
        $data = array(
                "sid" => $tutor,
                "descripcion" => $des,
                "debe" => $debe,
                "haber" => $haber,
                "total" => $total,
		"origen" => $orig
            );
        $this->db->insert("facturacion",$data);
        $data['iid'] = $this->db->insert_id();
        $data['fecha'] = date('d/m/Y');
        $data = json_encode($data);
        return $data;
    }

    public function get_reinscripcion($sid){
      $this->db->where('sid',$sid);
      $query = $this->db->get('reinscripcion_2017');
      if($query->num_rows() == 0){ return false;}
      $reinscripcion = $query->row();
      return $reinscripcion;
    }

    public function busca_fact_mes($sid){
      $mes = date('Ym');
      $this->db->where('sid',$sid);
      $this->db->where('aid',0);
      $this->db->where('DATE_FORMAT(generadoel,"%Y%m")',$mes);
      $query = $this->db->get('pagos');
      if($query->num_rows() == 0){ return false;}
      $fact_mes = $query->row();
      return $fact_mes;
    }

    public function reinscripcion($sid){
        $now = date ( 'Y-m-d H:i:s' );

        $reins = array(
            'sid' => $sid,
            'ts' => $now
            );
        $this->db->insert('reinscripcion_2017',$reins);

    }

    public function get_deuda_monto($sid){
      $this->db->select('p.tutor_id sid, SUM(p.monto-p.pagado) deuda');
      $this->db->where('p.estado',1);
      $this->db->where('p.tutor_id',$sid);
      $this->db->where('DATE_FORMAT(p.generadoel,"%Y%m") <= DATE_FORMAT(CURDATE(),"%Y%m")');
      $this->db->group_by('p.tutor_id');
      $this->db->having('SUM(p.monto-p.pagado) > 0');
      $query = $this->db->get('pagos as p');
      if($query->num_rows() == 0){ return false;}
      $deuda = $query->row()->deuda;
      return $deuda;

    }

    public function get_deuda_aviso(){
      $this->db->select('p.tutor_id sid, p.sid tutoreado, s.dni, s.apellido, s.nombre, s.mail, SUM(p.monto-p.pagado) deuda');
      $this->db->where('p.estado',1);
      $this->db->where('s.suspendido',0);
      $this->db->where('DATE_ADD(s.alta, INTERVAL 35 DAY)< CURDATE()');
      $this->db->join('socios as s','s.Id = p.tutor_id');
      $this->db->group_by('p.tutor_id');
      $this->db->having('SUM(p.monto-p.pagado) > 260');
      $query = $this->db->get('pagos as p');
      if($query->num_rows() == 0){ return false;}
      $deudores = $query->result();
      $query->free_result();
      return $deudores;
    }

    public function get_deuda($sid){
        $this->db->where('sid',$sid);
        $this->db->order_by('Id','desc');
        $query = $this->db->get('facturacion');
        if($query->num_rows() == 0){ return 0;}
        $deuda = $query->row()->total;
        return $deuda;
    }

    public function get_deuda_sinhoy($sid){
        $this->db->where('sid',$sid);
        $this->db->where('DATE(date) < CURDATE()');
        $this->db->order_by('Id','desc');
        $query = $this->db->get('facturacion');
        if($query->num_rows() == 0){ return 0;}
        $deuda = $query->row()->total;
        return $deuda;
    }

    public function financiar_deuda($socio,$monto,$cuotas,$detalle){
        $inicio = date('Y-m-d');
        $fin = $inicio;
        $financiacion = array(
            'sid' => $socio,
            'cuotas' => $cuotas,
            'monto' => $monto,
            'inicio' => $inicio,
            'fin' => $fin,
            'detalle'=>$detalle
            );
        $this->db->insert('financiacion',$financiacion);

	$credito=$monto-($monto/$cuotas);
	$this->registrar_pago('haber',$socio,$credito,'Refinanciacion de $ '.$monto.' de deuda en '.$cuotas.' cuotas',0,1);
    }

    public function get_planes($sid){
        $this->db->where('sid',$sid);
        $query = $this->db->get('financiacion');
        $planes = $query->result();
        $query->free_result();
        return $planes;
    }

    public function cancelar_plan($id){
        $this->db->where('Id',$id);
        $this->db->update('financiacion',array('estado'=>2));
    }

    public function get_financiado_mensual($sid){
        $this->db->where('sid',$sid);
        $this->db->where('estado',1);
        $query = $this->db->get('financiacion');
        if($query->num_rows() == 0){return false;}
        $planes = $query->result();
        $query->free_result();
        return $planes;
    }

    public function update_cuota($id){
        $this->db->where('Id',$id);
        $this->db->where('estado',1);
        $query = $this->db->get('financiacion');
        if($query->num_rows() == 0){return false;}
        $plan = $query->row();

	$hoy = date('Y-m-d');
        $this->db->where('Id',$id);
        $this->db->where('estado',1);
	if ( $plan->actual+1 >= $plan->cuotas ) {
        	$this->db->set('actual','actual+1',false);
        	$this->db->set('fin',"'".$hoy."'",false);
        	$this->db->set('estado',2,false);
        	$this->db->update('financiacion');
	} else {
        	$this->db->set('actual','actual+1',false);
        	$this->db->set('fin',"'".$hoy."'",false);
        	$this->db->update('financiacion');
	}
    }


    public function get_morosos($id_act_com=null){

	$solo_cta_social = 0;
	// Cargo en la variables actividades el filtro en f() de lo que llego por parametros
	if ( $id_act_com == "cs" ) {
		$actividades = null;
		$solo_cta_social = 1;
	} else {
		if ( $id_act_com > 0 ) {
			$this->db->where('estado','1');
			$this->db->where('id',$id_act_com);
			$query = $this->db->get('actividades');
		} else {
			$this->db->where('estado','1');
			$this->db->where('comision',-$id_act_com);
			$query = $this->db->get('actividades');
		}
	}

	// Si vino algun parametro y el SQL no encontro nada salgo con false
	if ( $id_act_com == "cs" ) {
		// Sino vino parametros  vino -1 pongo null la variable p luego tomar TODOS LOS SOCIOS de la cuota social
		$actividades = null;
	} else {
		if($query->num_rows() == 0){return false;}
		$actividades = $query->result();
	}

	// Busco el conjunto de socios morosos (tanto p actividad como p cuota social)
	$hoy=date('Ym');
	$this->db->select('p.tutor_id sid, SUM(p.monto-p.pagado) deuda');
	$this->db->where('p.estado',1);
	$this->db->where('p.tipo !=',5);
	$this->db->where('DATE_FORMAT(p.generadoel, "%Y%m") <',$hoy);
    	if ( $actividades ) {
		$in=array();
        	foreach ( $actividades as $actividad ) {
            		$in[]=$actividad->Id;
        	}
        	$this->db->where_in('p.aid',$in);
    	} else {
		if ( $solo_cta_social == 1 ) {
        		$this->db->where_in('p.aid','0');
		}
	}
    	$this->db->group_by('p.tutor_id');
    	$this->db->having('SUM(p.monto-p.pagado) > 0');
    	$query = $this->db->get('pagos as p');
    	$morosos = $query->result();

	// Seteo un array vacio para meter toda la info
	// Ciclo los morosos para buscar la info especifica
	$result_morosos=array();
	foreach ( $morosos as $moroso ) {
		$sid=$moroso->sid;
		// Busco datos fijos del socio
        $this->db->where('id',$sid);
        $query = $this->db->get('socios',1);
        if ( $query->num_rows() == 0 ) {
            // Llenar el array....
			continue;
	} else {
			$socio = $query->row();
	}

	// Busco la deuda de cuotas sociales
	$hoy=date('Ym');
        $this->db->select('p.tutor_id sid, COUNT(*) meses, MIN(DATE(p.generadoel)) pago, SUM(p.monto-p.pagado) deuda ');
        $this->db->where('p.estado',1);
        $this->db->where('p.tipo',1);
        $this->db->where('p.tutor_id',$sid);
        $this->db->where('DATE_FORMAT(p.generadoel, "%Y%m") <',$hoy);
        $this->db->group_by('p.tutor_id');
        $query = $this->db->get('pagos as p');
        if ( $query->num_rows() == 0 ) {
		$deuda_cuotas = null;
            	$meses_cuota = 0;
            	$gen_cuota = 0;
            	$deuda_cuota = 0;
	} else {
		$dc = $query->row();
		$meses_cuota = $dc->meses;
		$gen_cuota = $dc->pago;
		$deuda_cuota = $dc->deuda;
	}

	// busco la deuda de actividades
        $this->db->select('p.tutor_id sid, p.aid, count(*) meses, min(date(p.generadoel)) pago, sum(p.monto-p.pagado) deuda ');
        $this->db->where('p.estado',1);
        $this->db->where('p.tipo',4);
        $this->db->where('p.tutor_id',$sid);
        $this->db->where('date_format(p.generadoel, "%Y%m") <',$hoy);
        $this->db->group_by('p.tutor_id, p.aid');
        $query = $this->db->get('pagos as p');
        if ( $query->num_rows() == 0 ) {
            $result=array (
				'dni' => $socio->dni,
				'sid' => $sid,
				'nro_socio' => $socio->socio_n,
				'apynom' => $socio->nombre.", ".$socio->apellido,
				'telefono' => "F: ".$socio->telefono." C: ".$socio->celular,
				'tutor' => $socio->tutor,
				'domicilio' => $socio->domicilio,
				'actividad' => "Solo Cuota Social",
				'estado' => $socio->suspendido,
				'meses_cuota' => $meses_cuota,
				'gen_cuota' => $gen_cuota,
				'deuda_cuota' => $deuda_cuota,
				'meses_activ' => 0,
				'gen_activ' => 0,
				'deuda_activ' => 0
            );
            $result_morosos[]=$result;
	} else {
            $deuda_activ = $query->result();
            $query->free_result();
			// Ciclo las actividades con deuda para llenar el array
            $cont=0;
            foreach ( $deuda_activ as $da ) {
                $aid = $da->aid;
		if ( $aid == 0 ) {
                	$descr_activ = "Cuota Social";
		} else {
                	$this->db->where('Id',$aid);
                	$query = $this->db->get('actividades',1);
			$activ = $query->row();
                	$descr_activ = $activ->nombre;
		}

                if ( $cont++ == 0 ) {
                    $mcuota=$meses_cuota;
                    $gcuota=$gen_cuota;
                    $dcuota=$deuda_cuota;
                } else {
                    $mcuota=0;
                    $gcuota=0;
                    $dcuota=0;
                }
				$result=array (
					'dni' => $socio->dni,
					'sid' => $sid,
					'apynom' => $socio->nombre.", ".$socio->apellido,
					'telefono' => "F: ".$socio->telefono." C: ".$socio->celular,
					'tutor' => $socio->tutor,
					'domicilio' => $socio->domicilio,
					'actividad' => $descr_activ,
					'estado' => $socio->suspendido,
					'meses_cuota' => $mcuota,
					'gen_cuota' => $gcuota,
					'deuda_cuota' => $dcuota,
					'meses_activ' => $da->meses,
					'gen_activ' => $da->pago,
					'deuda_activ' => $da->deuda
				);
				$result_morosos[]=$result;
			}
		}
	}

    	return $result_morosos;
    }
    public function get_pagos_actividad($act){
        $this->db->where('aid',$act);
        $this->db->where('estado',1);
        $query = $this->db->get('actividades_asociadas');
        $asoc = $query->result();
        $query->free_result();

        $this->load->model("socios_model");
        $this->load->model("debtarj_model");

        foreach ($asoc as $a) {
            $socio = $this->socios_model->get_socio($a->sid);
            $a->Id = $socio->Id;
            $a->tutor = $socio->tutor;
            $a->socio = @$socio->nombre.' '.@$socio->apellido;
            $a->telefono = @$socio->telefono;
            $a->fijocel = "F: ".@$socio->telefono." C: ".@$socio->celular;
            $a->nacimiento = @$socio->nacimiento;
            $a->alta = @$socio->alta;
	    
                $debito = $this->debtarj_model->get_debtarj_by_sid($socio->Id);
		if ( $debito ) {
			if ( $debito->estado == 1 ) {
				$a->debito = true;
			} else {
				$a->debito = false;
			}
		} else {
			$a->debito = false;
		}

            $a->dni = @$socio->dni;
            $a->suspendido = @$socio->suspendido;
            $a->observaciones = @$socio->observaciones;
            $a->act_nombre = $this->actividades_model->get_actividad($a->aid)->nombre;
            //@$a->deuda = $this->pagos_model->get_deuda($socio->Id);
            //@$a->deuda = $this->pagos_model->get_ultimo_pago_actividad($a->aid,$socio->Id);
            @$a->deuda = $this->pagos_model->get_deuda_actividad($a->aid,$socio->Id);
            /* Modificado AHG para manejo de array en PHP 5.3 que tengo en mi maquina */
	        $array_ahg = $this->pagos_model->get_monto_socio($socio->Id);
            @$a->cuota = $array_ahg['total'];
            /* Fin Modificacion AHG */
            @$a->monto_adeudado = $this->pagos_model->get_saldo_socio($socio->Id);
	    /* Segrego deuda por tipo */
	    $qry = "SELECT SUM(IF(aid=0,pagado-monto,0)) deuda_cs, SUM(IF(tipo=6 and aid>0,pagado-monto,0)) deuda_seguro,
	            SUM(IF(tipo!=6 and aid>0,pagado-monto,0)) deuda_actividad
			FROM pagos
			WHERE sid = $socio->Id AND estado = 1 ; ";
	    $deuda = $this->db->query($qry)->result();
            @$a->deuda_cs = $deuda[0]->deuda_cs;
            @$a->deuda_seg = $deuda[0]->deuda_seguro;
            @$a->deuda_act = $deuda[0]->deuda_actividad;
        }
        return $asoc;
    }

    public function get_pagos_comision($com){

	$qry = "SELECT DISTINCT aa.sid 
		FROM actividades_asociadas aa 
			JOIN actividades a ON aa.aid = a.id AND a.comision = $com
		WHERE aa.estado = 1; ";
	$asoc = $this->db->query($qry)->result();

        $this->load->model("socios_model");
        $this->load->model("debtarj_model");

        foreach ($asoc as $a) {
            $socio = $this->socios_model->get_socio($a->sid);
            $a->Id = $socio->Id;
            $a->tutor = $socio->tutor;
            $a->socio = @$socio->nombre.' '.@$socio->apellido;
            $a->telefono = @$socio->telefono;
            $a->fijocel = "F: ".@$socio->telefono." C: ".@$socio->celular;
            $a->nacimiento = @$socio->nacimiento;
            $a->alta = @$socio->alta;
	    
                $debito = $this->debtarj_model->get_debtarj_by_sid($socio->Id);
		if ( $debito ) {
                        if ( $debito->estado == 1 ) {
                                $a->debito = true;
                        } else {
                                $a->debito = false;
                        }
		} else {
			$a->debito = false;
		}

            $a->dni = @$socio->dni;
            $a->suspendido = @$socio->suspendido;
            $a->observaciones = @$socio->observaciones;
            $a->act_nombre = @$socio->nombre_act;

	    // Ciclo las actividades
	    $qry = "SELECT DISTINCT aa.aid, a.nombre nombre_act
                FROM actividades_asociadas aa
                        JOIN actividades a ON aa.aid = a.id 
                WHERE aa.estado = 1 AND aa.sid = $socio->Id; ";
	    $acts = $this->db->query($qry)->result();
	    	
            @$a->deuda = 0;
	    foreach ( $acts as $act )
	    {
            	$deuda = $this->pagos_model->get_deuda_actividad($act->aid,$socio->Id);
            	@$a->deuda = @$a->deuda + $deuda;
	    }

            /* Modificado AHG para manejo de array en PHP 5.3 que tengo en mi maquina */
	    $array_ahg = $this->pagos_model->get_monto_socio($socio->Id);
            @$a->cuota = $array_ahg['total'];
            /* Fin Modificacion AHG */
            @$a->monto_adeudado = $this->pagos_model->get_saldo_socio($socio->Id);
            /* Segrego deuda por tipo */
            $qry = "SELECT SUM(IF(aid=0,pagado-monto,0)) deuda_cs, SUM(IF(tipo=6 and aid>0,pagado-monto,0)) deuda_seguro,
                    SUM(IF(tipo!=6 and aid>0,pagado-monto,0)) deuda_actividad
                        FROM pagos
                        WHERE sid = $socio->Id AND estado = 1 ; ";
            $deuda = $this->db->query($qry)->result();
            @$a->deuda_cs = $deuda[0]->deuda_cs;
            @$a->deuda_seg = $deuda[0]->deuda_seguro;
            @$a->deuda_act = $deuda[0]->deuda_actividad;

        }
        return $asoc;
    }

    public function get_pagos_profesor($id)
    {
        $this->db->where('profesor',$id);
        $query = $this->db->get('actividades');
        $actividades = $query->result();
        $socios = array();
        foreach ($actividades as $actividad) {
            $socios[] = $this->get_pagos_actividad($actividad->Id);
        }
        return $socios;
    }

    public function get_usuarios_suspendidos()
    {
        $this->db->where('suspendido',1);
        $this->db->where('estado',1);
        $this->db->order_by('apellido','asc');
        $this->db->order_by('nombre','asc');
        $query = $this->db->get('socios');
        $socios = $query->result();
        foreach ($socios as $socio) {
            $socio->deuda_monto = $this->get_deuda($socio->Id);
        }
        $query->free_result();
        return $socios;
    }

    public function get_socios_activos($value='')
    {
        $this->db->where('suspendido',0);
        $this->db->where('estado',1);
        $this->db->order_by('apellido','asc');
        $this->db->order_by('nombre','asc');
        $query = $this->db->get('socios');
        $socios = $query->result();
        foreach ($socios as $socio) {
            $socio->fijocel = "F: ".$socio->telefono." C: ".$socio->celular;
            $socio->deuda_monto = $this->get_deuda($socio->Id);
        }
        $query->free_result();
        return $socios;
    }

    public function get_pagos_mensual($aid,$anio,$mes){
	$pagos = 0;
	$this->db->select_sum('monto');
	$this->db->where($mes, 'MONTH(generadoel)' , FALSE);
	$this->db->where($anio, 'YEAR(generadoel)' , FALSE);
	if ( $aid == 0 ) {
		$this->db->where('aid' , 0);
	} else {
		$this->db->where('aid' , '> 0');
	}
	$query = $this->db->get('pagos');
	if($query->num_rows() != 0){
		$pagos = $query->row()->monto;
	} else {
		$pagos = 0;
	}

	return $pagos;
    }

    public function get_pagos_categorias($id){
        $this->db->where('estado',1);
        $this->db->where('categoria',$id);
        $query = $this->db->get('socios');
        $socios = $query->result();

        foreach ($socios as $socio) {
            $socio->fijocel = "F: ".$socio->telefono." C: ".$socio->celular;
            $socio->deuda_monto = $this->get_deuda($socio->Id);
            $socio->deuda = $this->pagos_model->get_ultimo_pago_socio($socio->Id);
            /* Modificado AHG para manejo de array en PHP 5.3 que tengo en mi maquina */
	        $array_ahg = $this->pagos_model->get_monto_socio($socio->Id);
            $socio->cuota = $array_ahg['total'];
            /* Fin Modificacion AHG */
        }
        return $socios;

    }

    public function get_ingresos($fecha1='',$fecha2='')
    {
        /*$this->db->order_by('facturacion.date','asc');
        $this->db->where('facturacion.date >=',$fecha1.' 0:00:00');
        $this->db->where('facturacion.date <=',$fecha2.' 23:59:59');
        $this->db->where('facturacion.haber >',-1);
        $this->db->join('socios','socios.Id = facturacion.sid');
        $query = $this->db->get('facturacion');
        if($query->num_rows() == 0){return false;}
        $ingresos = $query->result();
        foreach ($ingresos as $ingreso) {
            $cuota = $acts = array();
            $this->db->where('estado',0);
            $this->db->where('sid',$ingreso->sid);
            $this->db->where('pagadoel >=',$fecha1.' 0:00:00');
            $this->db->where('pagadoel <=',$fecha2.' 23:59:59');
            $query = $this->db->get('pagos');
            if($query->num_rows() != 0){
                $pagos = $query->result();
                foreach ($pagos as $pago) {
                    if($pago->tipo == 1 || $pago->tipo == 2 || $pago->tipo == 3){
                        $cuota[] = $pago;
                    }else if($pago->tipo == 4){
                        $acts[] = $pago;
                    }
                }
                $ingreso->cuota = $cuota;
                $ingreso->acts = $acts;
            }
        }
        $query->free_result();*/

        //$this->db->where('estado',0);
        //$this->db->where('sid',$ingreso->sid);
        $this->load->model('socios_model');
        $this->db->where('pagadoel >=',$fecha1.' 0:00:00');
        $this->db->where('pagadoel <=',$fecha2.' 23:59:59');
        $this->db->where('estado',0);
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){ return false; }
        $pagos = $query->result();
        foreach ($pagos as $pago) {
            $pago->socio = $this->socios_model->get_socio($pago->tutor_id);
        }
        return $pagos;
    }

     public function get_ingresos_cuentadigital($fecha1='',$fecha2='')
    {
        $this->load->model('socios_model');
        $this->db->where('fecha >=',$fecha1.' 0:00:00');
        $this->db->where('fecha <=',$fecha2.' 23:59:59');
        $query = $this->db->get('cuentadigital');
        if($query->num_rows() == 0){ return false; }
        $pagos = $query->result();
        foreach ($pagos as $pago) {
            $pago->socio = $this->socios_model->get_socio($pago->sid);
        }
        return $pagos;
    }

     public function get_ingresos_cobrodigital($fecha1='',$fecha2='')
    {
        $this->load->model('socios_model');
        $this->db->where('fecha_pago >=',$fecha1);
        $this->db->where('fecha_pago <=',$fecha2);
        $query = $this->db->get('cobrodigital');
        if($query->num_rows() == 0){ return false; }
        $pagos = $query->result();
        foreach ($pagos as $pago) {
            $pago->socio = $this->socios_model->get_socio($pago->sid);
        }
        return $pagos;
    }


    public function get_cobros_actividad($fecha1='',$fecha2='',$actividad=false,$categoria=false)
    {
        $this->load->model('actividades_model');
        $this->load->model('socios_model');
        $actividad = $this->actividades_model->get_actividad($actividad);
        //$this->db->select('sid');
        //$this->db->distinct();
        $this->db->where('estado',0);
        $this->db->where('aid',$actividad->Id);
        $this->db->where('pagadoel >=',$fecha1.' 0:00:00');
        $this->db->where('pagadoel <=',$fecha2.' 23:59:59');
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){return false;}
        $pagos = $query->result();
        $res = array();
        foreach ($pagos as $pago) {
            $pago->socio = $this->socios_model->get_socio($pago->sid);
            $pago->deuda = $this->get_deuda_actividad($actividad->Id,$pago->sid);
            if($categoria != ''){
                if(date('Y',strtotime($pago->socio->nacimiento)) != $categoria){
                    continue;
                }
            }
            $res[] = $pago;
        }
        return $res;
    }

    public function get_cobros_cuota($fecha1='',$fecha2='',$categoria=false)
    {
        $this->load->model('socios_model');
        //$this->db->select('sid');
        //$this->db->distinct();
        $this->db->where('estado',0);
        $this->db->where('tipo',1);
        $this->db->where('pagadoel >=',$fecha1.' 0:00:00');
        $this->db->where('pagadoel <=',$fecha2.' 23:59:59');
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){return false;}
        $pagos = $query->result();
        $res = array();
        foreach ($pagos as $pago) {
            $pago->socio = $this->socios_model->get_socio($pago->sid);
            $pago->deuda = $this->get_deuda_cuota($pago->sid);
            if($categoria != ''){
                if(date('Y',strtotime($pago->socio->nacimiento)) != $categoria){
                    continue;
                }
            }
            $res[] = $pago;
        }
        return $res;
    }

    public function insert_pago_nuevo($pago)
    {
        if ( $pago['monto'] == 0 AND $pago['tipo'] != 5 ) {
             $pago['pagadoel'] = $pago['generadoel'];
             $pago['estado'] = 0;
        }
        $this->db->insert('pagos',$pago);
    }


    public function registrar_pago2($sid=false,$monto='0',$ajuste='0')
    {
        if(!$sid){return false;}
        //$this->load->model('pagos_model');
        $this->db->where('tipo',5);
        $this->db->where('tutor_id ',$sid);
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){
            $pago = array(
                'sid' => $sid,
                'tutor_id' => $sid,
                'aid' => 0,
                'generadoel' => date('Y-m-d'),
                'descripcion' => "A favor",
                'monto' => 0,
		'ajuste' => 0,
                'tipo' => 5
            );
            $this->insert_pago_nuevo($pago);
            $a_favor = 0;
        }else{
            $a_favor = $query->row()->monto;
        }
        $monto = $monto + $a_favor*-1;


        $this->db->order_by('generadoel','asc');
        $this->db->order_by('tipo','asc');
        $this->db->where('tipo !=',5);
        $this->db->where('monto >',0);
        $this->db->where('tutor_id',$sid);
        $this->db->where('estado',1);
        $query = $this->db->get('pagos');
        foreach ($query->result() as $pago) {
            if($monto > 0){
                if( ($pago->monto - $pago->pagado) <= $monto){
                    if($pago->pagado == 0){
                        $pagado = $pago->monto;
                        $monto = $monto - $pagado;
                    }else{
                        $pagado = $pago->monto;
                        $monto = $monto - ($pago->monto - $pago->pagado);
                    }
                    $this->db->where('Id',$pago->Id);
                    $this->db->update('pagos',array('pagado'=>$pagado,'estado'=>0,'pagadoel'=>date('Y-m-d H:i:s'),'ajuste'=>$ajuste));
                }else{
                    if($pago->pagado == 0){
                        $pagado = $monto;
                    }else{
                        $pagado = $pago->pagado+$monto;
                    }
                    $this->db->where('Id',$pago->Id);
                    $this->db->update('pagos',array('pagado'=>$pagado,'pagadoel'=>date('Y-m-d H:i:s'),'ajuste'=>$ajuste));
                    $monto = 0;
                }
            }
        }

        $this->db->where('tutor_id',$sid);
        $this->db->where('tipo',5);
        $this->db->update('pagos',array('monto'=>$monto*-1));

    }

    public function get_ultimo_pago_actividad($aid,$sid)
    {
        $this->db->order_by('pagadoel', 'desc');
        $this->db->where('aid',$aid);
        $this->db->where('tutor_id',$sid);
        $this->db->where('estado',0);
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){return false;}
        $ultimo_pago = $query->row();
        $query->free_result();
        return $ultimo_pago;
    }

    public function revierte_recargo_jardin($sid)  
    {
	$dia = date('d');
	$mes = date('m');
	$fch_where = date('Y-m-');
	// Busco si tuvo recargo si no devuelvo false
	if ( $dia <= 25 ) {
		$qry = "SELECT p.id id_recargo, p.monto imp_recargo, p.monto-p.pagado neto FROM pagos p WHERE p.tutor_id = $sid AND DATE(p.generadoel) = '$fch_where"."15' AND p.tipo = 10; ";
		$recargo = $this->db->query($qry);
        	if ( $recargo->num_rows() == 0 ) { return false; }
        	$neto_recargo = $recargo->row();
		if ( $neto_recargo->neto == 0 ) { return false; }

		$qry = "SELECT SUM(p.monto-p.pagado) saldo FROM pagos p WHERE p.tutor_id = $sid;" ;
		$reg_saldo = $this->db->query($qry)->row();
        	$saldo = $reg_saldo->saldo;

		$qry = "SELECT f.* FROM facturacion f WHERE f.sid = $sid AND DATE(f.date) > '$fch_where"."15' ; ";
		
        	$movimientos = $this->db->query($qry)->result();
		$ret_valor = false;
		foreach ( $movimientos as $movimiento ) {
			$descr = $movimiento->descripcion;
			$fecha = $movimiento->date;
			$pos1 = strpos($descr , "go acreditado desde:");
			if ( $pos1 ) {
				$pos2 = strpos($descr,"Fecha:");

				$diap=substr($descr,$pos2+7,2);
				$mesp=substr($descr,$pos2+10,2);
				$anop=substr($descr,$pos2+13,4);
				$fch_pago=$anop."-".$mesp."-".$diap;

				if ( $diap <= 15 ) {

					// Si tiene saldo 0 , a favor o menor que el recargo facturacdo entonces si anulo
					if ( $saldo <= $neto_recargo->imp_recargo ) {
						$ret_valor = array ( 'fecha_pago' => $fch_pago, 'id_recargo' => $neto_recargo->id_recargo, 'imp_recargo' => $neto_recargo->imp_recargo);
				 		break;
					}
				}
			
			}
	   	}
		return $ret_valor;
	}
	
    }

    public function get_deuda_jardin($sid)  
    {
	$mes = date('Ym');
	// Busco los movimientos del mes de la comision 15 JARDIN
        $qry = "SELECT p.tutor_id sid, $mes mes, p.aid, a.nombre descr_actividad, a.precio, a.seguro, p.monto
                FROM pagos p
			JOIN actividades a ON p.aid = a.Id AND a.comision = 15
                WHERE 
                        p.tutor_id = $sid AND
			DATE_FORMAT(p.generadoel, '%Y%m') = $mes AND
			p.monto > 0 AND
			p.pagado = 0 AND
			p.tipo != 10
		LIMIT 1; ";
        $deuda = $this->db->query($qry);
        if($deuda->num_rows() == 0){return false;}
        return $deuda->result();
    }

    public function get_deuda_actividad($aid,$sid)
    {
        $this->db->order_by('generadoel', 'asc');
        $this->db->where('aid',$aid);
        $this->db->where('sid',$sid);
        $this->db->where('estado',1);
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){return false;}
        $ultimo_pago = $query->row();
        $query->free_result();
        return $ultimo_pago;
    }

    public function get_saldo($sid)
    {
        $this->db->where('tutor_id',$sid);
        $this->db->where('estado',1);
        $this->db->select_sum('monto');
        $this->db->select_sum('pagado');
        $query = $this->db->get('pagos');
	$saldo=$query->row()->monto-$query->row()->pagado;
	return $saldo;
    }

    public function get_deuda_cuota($sid)
    {
        $this->db->order_by('generadoel', 'asc');
        $this->db->where('tipo',1);
        $this->db->where('sid',$sid);
        $this->db->where('estado',1);
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){return false;}
        $ultimo_pago = $query->row();
        $query->free_result();
        return $ultimo_pago;
    }

    public function get_ultimo_pago_cuota($sid='')
    {
        $this->db->order_by('pagadoel', 'desc');
        $this->db->where('tipo',1);
        $this->db->where('tutor_id',$sid);
        $this->db->where('estado',0);
        $query = $this->db->get('pagos');
        if($query->num_rows() == 0){return false;}
        $ultimo_pago = $query->row();
        $query->free_result();
        return $ultimo_pago;
    }

    public function get_ultimo_pago_socio($sid)
    {
        //$this->db->where('aid',$aid);
        $this->db->order_by('generadoel','asc');
        $this->db->where('tutor_id',$sid);
        $this->db->where('tipo !=',5);
        $this->db->where('tipo !=',4);
        $this->db->where('tipo !=',2);
        $this->db->where('estado',1);
        $query = $this->db->get('pagos',1);
        if($query->num_rows() == 0){return false;}
        $ultimo_pago = $query->row();
        $query->free_result();
        return $ultimo_pago;
    }

    public function get_pagos_actividad_anterior($act){
        $this->db->where('aid',$act);
        $this->db->where('estado',1);
        $query = $this->db->get('actividades_asociadas');
        $asoc = $query->result();
        $query->free_result();

        $this->load->model("socios_model");

        foreach ($asoc as $a) {
            $socio = $this->socios_model->get_socio($a->sid);
            $a->Id = $socio->Id;
            $a->socio = @$socio->nombre.' '.@$socio->apellido;
            $a->telefono = @$socio->telefono;
            $a->nacimiento = @$socio->nacimiento;
            $a->dni = @$socio->dni;
            $a->suspendido = @$socio->suspendido;
            $a->act_nombre = $this->actividades_model->get_actividad($a->aid)->nombre;
            //@$a->deuda = $this->pagos_model->get_deuda($socio->Id);
            $a->deuda = 0;
            $this->db->where('sid',$a->Id);
            $this->db->where('tipo',1);
            $this->db->where('monto >',0);
            $this->db->where('descripcion','Deuda Anterior');
            //$this->db->where('generadoel <','2015-05-01 00:00:00');
            $query = $this->db->get('pagos');
            if($query->num_rows != 0){
                $a->deuda = $query->row()->estado;
            }
        }
        return $asoc;
    }

    public function get_socios_financiados()
    {
        $this->db->where('fn.estado',1);
        $this->db->join('socios','socios.Id = fn.sid');
        $query = $this->db->get('financiacion as fn');
        if($query->num_rows() == 0){ return false; }
        $socios = $query->result();
        $query->free_result();
        return $socios;
    }

    public function get_becas()
    {
            $this->db->select('aa.*, socios.*, a.nombre descr_actividad, aa.descuento as descuento, aa.monto_porcentaje as monto_porcentaje, socios.Id as Id');
            $this->db->where('aa.descuento >',0);
            $this->db->where('aa.estado',1);
            $this->db->join('socios', 'socios.Id = aa.sid', 'left');
            $this->db->join('actividades as a', 'a.Id = aa.aid', 'left');
            $query = $this->db->get('actividades_asociadas as aa');
            if($query->num_rows() == 0){ return false; }
            $socios = $query->result();
	    foreach ($socios as $socio) {
		$socio->fijocel = "F: ".$socio->telefono." C: ".$socio->celular;
	    }
            $query->free_result();
	    foreach ( $socios as $socio ) {
		/* Segrego deuda por tipo */
            	$qry = "SELECT SUM(IF(aid=0,pagado-monto,0)) deuda_cs, SUM(IF(tipo=6 and aid>0,pagado-monto,0)) deuda_seguro,
                    	SUM(IF(tipo!=6 and aid>0,pagado-monto,0)) deuda_actividad
                        FROM pagos
                        WHERE sid = $socio->Id AND estado = 1 ; ";
            	$deuda = $this->db->query($qry)->result();
            	@$socio->deuda_cs = $deuda[0]->deuda_cs;
            	@$socio->deuda_seg = $deuda[0]->deuda_seguro;
            	@$socio->deuda_act = $deuda[0]->deuda_actividad;
	    }
            return $socios;
    }

    public function get_sin_actividades()
    {
        $this->load->model('socios_model');
        $socios = $this->socios_model->get_socios();
        $sin_actividades = array();
        foreach ($socios as $socio) {
            $this->db->where('sid', $socio->Id);
            $this->db->where('estado',1);
            $query  = $this->db->get('actividades_asociadas');
            if( $query->num_rows() == 0 ){
		$socio->fijocel = "F: ".$socio->telefono." C: ".$socio->celular;
                $sin_actividades[] = $socio;
            }
        }
        return $sin_actividades;
    }

    public function insert_cuentadigital($pago='')
    {
        $this->db->insert('cuentadigital',$pago);
    }

    public function insert_cobrodigital($pago='')
    {
        $this->db->insert('cobrodigital',$pago);
    }

    public function get_pagos_edit($socio_id)
    {
        $this->db->select('pagos.*,actividades.nombre');
        $this->db->where('pagos.tutor_id', $socio_id);
        $this->db->where('pagos.generadoel >=',date('Y-m').'-01');
        $this->db->join('actividades', 'actividades.Id = pagos.aid', 'left');
        $query = $this->db->get('pagos');
        if( $query->num_rows() == 0 ){ return false; }
        $pagos = $query->result();
        $query->free_result();
        return $pagos;
    }

    public function revertir_fact($sid, $aid, $periodo) {
	// Busco el registro metido en la tabla pagos
        $this->db->where('pagos.aid',$aid);
        $this->db->where('pagos.sid',$sid);
        $this->db->where('DATE_FORMAT(pagos.generadoel,"%Y%m")',$periodo);
        $query = $this->db->get('pagos');
        if( $query->num_rows() == 0 ){ return false; }
        $pago = $query->row();
	
	$monto = $pago->monto;
	
	if ( $pago->pagado == 0 ) {
		$this->db->where('Id',$pago->Id);
		$this->db->update('pagos',array('pagado'=>$monto,'estado'=>0,'pagadoel'=>date('Y-m-d H:i:s'),'ajuste'=>1));
	} else {
		$pagado = $pago->pagado;
		$this->db->where('Id',$pago->Id);
		$this->db->update('pagos',array('pagado'=>$monto,'estado'=>0,'pagadoel'=>date('Y-m-d H:i:s'),'ajuste'=>1));
		// Pongo a favor lo que tenia pagado
		$this->db->where('tutor_id', $pago->tutor_id);
		$this->db->where('tipo', 5);
		$this->db->set('monto', 'monto-'.$pagado, FALSE);
		$this->db->update('pagos');
	}
	
	// Meto un ajuste del monto de lo generado
	$total = $this->get_socio_total($pago->tutor_id);
	$facturacion = array(
		'sid' => $pago->tutor_id,
		'descripcion'=> "REVERSION FACTURACION",
		'debe'=>0,
		'haber'=>$monto,
		'total'=>$total+$monto
	);
	$this->db->insert('facturacion', $facturacion);

    }

    public function eliminar_pago($id)
    {
        $this->db->where('pagos.Id',$id);
        $this->db->join('actividades', 'actividades.Id = pagos.aid', 'left');
        $query = $this->db->get('pagos');
        if( $query->num_rows() == 0 ){ return false; }
        $pago = $query->row();

        //actualizamos saldo a favor
        $a_favor = $pago->pagado;
        if($a_favor > 0){
            $this->db->where('tutor_id', $pago->tutor_id);
            $this->db->where('tipo', 5);
            $this->db->set('monto', 'monto-'.$a_favor, FALSE);
            $this->db->update('pagos');
        }

        //actualizamos facturacion
        $facturacion = $pago->monto;
        if($facturacion > 0){
            switch ($pago->tipo) {
                case 1:
                    $descripcion = 'Cuota Social - '.date('d/m/Y',strtotime($pago->generadoel));
                    break;

                case 2:
                    $descripcion = 'Recargo por Mora - '.date('d/m/Y',strtotime($pago->generadoel));
                    break;

                case 3:
                    $descripcion = 'Financiación de deuda - '.date('d/m/Y',strtotime($pago->generadoel));
                    break;

                case 4:
                    $descripcion = 'Actividad: '.$pago->nombre.' - '.date('d/m/Y',strtotime($pago->generadoel));
                    break;
            }
            $total = $this->get_socio_total($pago->tutor_id);
            $facturacion = array(
                'sid' => $pago->tutor_id,
                'descripcion'=> "CORRECCIÓN MANUAL DE PAGOS: ".$descripcion,
                'debe'=>0,
                'haber'=>$facturacion,
                'total'=>$total+$facturacion
            );
            $this->db->insert('facturacion', $facturacion);
        }

        $this->db->where('Id', $id);
        $this->db->delete('pagos');
        $query->free_result();
        return $pago->tutor_id;
    }

    public function get_meses_ingresos() {
	$qry="DROP TEMPORARY TABLE IF EXISTS tmp_meses; ";
        $this->db->query($qry);
	$qry="CREATE TEMPORARY TABLE tmp_meses ( mes integer, descr_mes char(30), INDEX(mes) );  ";
        $this->db->query($qry);
	$qry=" INSERT INTO tmp_meses VALUES (  1, 'Enero' ); ";
        $this->db->query($qry);
	$qry=" INSERT INTO tmp_meses VALUES (  2, 'Febrero' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES (  3, 'Marzo' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES (  4, 'Abril' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES (  5, 'Mayo' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES (  6, 'Junio' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES (  7, 'Julio' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES (  8, 'Agosto' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES (  9, 'Setiembre' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES ( 10, 'Octubre' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES ( 11, 'Noviembre' );";
        $this->db->query($qry);
	$qry="INSERT INTO tmp_meses VALUES ( 12, 'Diciembre' ); ";
        $this->db->query($qry);
	$qry="SELECT DATE_FORMAT(f.date,'%Y%m') mes, CONCAT(m.descr_mes,'-',DATE_FORMAT(f.date,'%Y')) descr_mes, COUNT(*) movimientos 
		FROM facturacion f
			JOIN tmp_meses m ON DATE_FORMAT(f.date, '%m') = m.mes
		WHERE f.date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
		GROUP BY 1; ";
        $meses = $this->db->query($qry)->result();

        return $meses;
    }

    public function get_facturacion_all()
    {
        $qry = "SELECT s.apellido, s.nombre, s.dni, f.*
                FROM facturacion f
                        JOIN socios s ON f.sid = s.Id 
                WHERE 
			f.date > DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND
                        s.suspendido = 0
		ORDER BY f.sid, f.date, f.id; ";
        $facturacion = $this->db->query($qry);
        if ( $facturacion->num_rows() == 0 ) { return false; }
        $foo = $facturacion->result();
        $facturacion->free_result();
        return $foo;
    }
}

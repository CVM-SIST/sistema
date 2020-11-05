<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 */
	class Socios_model extends CI_Model {
	    
	    public function __construct() {
		parent::__construct();
		$this->load->database('default');
	    }
	    
	    public function register($datos)
	    {
		$this->db->insert('socios', $datos);
		return $this->db->insert_id();   
	    }

	    public function checkDNI($dni,$id=null)
	    {
		if($id){
		    $query = $this->db->get_where('socios', array('dni' => $dni, 'Id !=' =>$id, 'estado' =>'1'),1);
		}else{
		    $query = $this->db->get_where('socios', array('dni' => $dni, 'estado' =>'1'),1);
		}
		if($query->num_rows() == 0){
		    return false;
		}else{
		    return $query->result();
		}
	    }

	    public function getmails_check() {
		$qry="SELECT Id sid, categoria, CONCAT(nombre,', ',apellido) apynom, mail FROM socios WHERE suspendido = 0 AND validmail_st IN (0, 2) AND mail != '' LIMIT 100";
		$resultado = $this->db->query($qry)->result();
		if ( $resultado ) {
			return $resultado;
		} else {
			return false;
		}
	    }

	    public function listar(){
	/*	Cambiado por el SQL con JOIN para optimizar lecturas Ago2017 */
		$this->load->model('pagos_model');
		$qry="SELECT s.*, GROUP_CONCAT(DISTINCT a.nombre) actividades, SUM(p.monto-p.pagado) deuda
			FROM socios s
				LEFT JOIN actividades_asociadas aa ON ( s.Id = aa.sid AND aa.estado = 1 )
				LEFT JOIN actividades a ON ( aa.aid = a.Id )
				LEFT JOIN pagos p ON ( s.Id = p.tutor_id AND p.aid = 0 AND p.estado = 1 AND DATE_FORMAT(p.generadoel,'%Y%m') < DATE_FORMAT(CURDATE(),'%Y%m') )
			WHERE s.estado = 1 
			GROUP BY s.Id; ";
		$resultado = $this->db->query($qry)->result();
		$socios = array();
		foreach ( $resultado as $socio ) {
		    $socios[$socio->Id]['datos'] = $socio;
		    $cuota = $this->pagos_model->get_monto_socio($socio->Id);
		    if ( !$cuota ) { $cuota = 0; };
		    $socios[$socio->Id]['cuota'] = $cuota;
		}        

		return $socios;
	    }
	    public function get_socio($id)
	    {
		if ( !$id || $id == '0' ) {
			$socio = new stdClass();
			$socio->Id=0;
			$socio->nombre=0;
			$socio->apellido=0;
			$socio->dni=0;
			return $socio;
		} else {
		    $query = $this->db->get_where('socios',array('Id' => $id,'estado'=> '1'),1);
		    if($query->num_rows() == 0){return false;}
		    return $query->row();
		}
	    }

	    public function get_socios_comision($comision, $activ)
	    {
		$query="SELECT s.* 
			FROM socios s 
				JOIN actividades_asociadas aa ON ( s.Id = aa.sid AND aa.estado = 1 ) 
				JOIN actividades a ON ( a.Id = aa.aid ) 
			WHERE a.comision IN ( ";
		
		$cont = 0;
		foreach ( $comision as $id_comision ) {
			if ( $cont > 0 ) { $query .= ", "; };
			$query .= ' '.$id_comision.' ';
			$cont++;
		}
		$query .= " ) "; 
		if ( $activ == 1 ) {
			$query .= " AND s.suspendido = 0; ";
		} else {
			$query .= " ; ";
		}
		$result = $this->db->query($query)->result(); 
		return $result;
	    }

	    public function get_socios_titu_comision($comision, $activ)
	    {
		$query="SELECT s.* 
			FROM socios s 
				JOIN profesores p ON ( s.Id = p.sid )
			WHERE p.comision IN ( ";
		
		$cont = 0;
		foreach ( $comision as $id_comision ) {
			if ( $cont > 0 ) { $query .= ", "; };
			$query .= ' '.$id_comision.' ';
			$cont++;
		}
		$query .= " ) ";
		if ( $activ == 1 ) {
			$query .= " AND s.suspendido = 0; ";
		} else {
			$query .= " ; ";
		}

		$result = $this->db->query($query)->result(); 
		return $result;
	    }

	    public function get_socios_conact($activ)
	    {
		$query="SELECT s.* FROM socios s LEFT JOIN actividades_asociadas aa ON ( s.Id = aa.sid AND aa.estado = 1 ) WHERE aa.sid = s.Id "; 
		if ( $activ == 1 ) {
			$query .= " AND s.suspendido = 0; ";
		} else {
			$query .= " ; ";
		}
		$result = $this->db->query($query)->result(); 
		return $result;
	    }

	    public function get_socios_sinact($activ)
	    {
		$query="SELECT s.* FROM socios s LEFT JOIN actividades_asociadas aa ON ( s.Id = aa.sid AND aa.estado = 1 ) WHERE aa.sid is NULL "; 
		if ( $activ == 1 ) {
			$query .= " AND s.suspendido = 0; ";
		} else {
			$query .= " ; ";
		}
		$result = $this->db->query($query)->result(); 
		return $result;
	    }

	    public function get_socios_actdatos($filtro_act, $filtro_mail, $filtro_tele)
	    {
		// Calculo totales para devolver en primer instancia
		$query  = "SELECT SUM(IF(suspendido=0,1,0)) activos, COUNT(*) total FROM socios ; ";
		$result = $this->db->query($query)->row();
		$cant_socios = $result->total;
		$cant_socios_act = $result->activos;

		$query  = "SELECT COUNT(*) cant_filtro FROM socios s ";
		$query .= "WHERE s.suspendido = 0 AND DATE_SUB(CURDATE(), INTERVAL $filtro_act MONTH) > s.update_ts  ";
		if ( $filtro_mail == "sin" ) {
			$query .= "AND s.mail = '' ";
		} else {
			if ( $filtro_mail == "cone" ) {
				$query .= "AND validmail_st = 2 ";
			} else {
				$query .= "AND s.mail != '' ";
			}
		}
		if ( $filtro_tele == "sin" ) {
			$query .= "AND ( s.telefono = '' AND s.celular = '' )";
		} else {
			$query .= "AND ( s.telefono != '' OR s.celular != '' )";
		}

		$query .= ";";
		$result = $this->db->query($query)->row();
		$cant_socios_filtro = $result->cant_filtro;

		// Saco los primeros 30 para devolver en segundo elemento del array
		$query  = "SELECT s.* FROM socios s ";
		$query .= "WHERE s.suspendido = 0 AND DATE_SUB(CURDATE(), INTERVAL $filtro_act MONTH) > s.update_ts  ";
		if ( $filtro_mail == "sin" ) {
			$query .= "AND s.mail = '' ";
		} else {
			if ( $filtro_mail == "cone" ) {
				$query .= "AND validmail_st = 2 ";
			} else {
				$query .= "AND s.mail != '' ";
			}
		}
		if ( $filtro_tele == "sin" ) {
			$query .= "AND ( s.telefono = '' AND s.celular = '' ) ";
		} else {
			$query .= "AND ( s.telefono != '' OR s.celular != '' ) ";
		}

		$query .= "ORDER BY s.update_ts ASC ";
		$query .= "LIMIT 100; ";
		$result = $this->db->query($query)->result();
		
		$salida = array();

		$salida[0] = array ( 'cant_socios'=>$cant_socios, 'cant_socios_act'=>$cant_socios_act, 'cant_socios_filtro'=>$cant_socios_filtro );
		$salida[1] = $result;

		return $salida;
	    }

	    public function get_socios_ahg()
	    {
		$query="SELECT s.* FROM socios s WHERE s.Id IN ( 28896, 29219 ); ";
		$result = $this->db->query($query)->result();
		return $result;

	    }

	    public function get_socios_activos()
	    {
		$query = $this->db->get_where('socios',array('suspendido'=>0));
		return $query->result();
	    }

	    public function get_socios()
	    {
		$query = $this->db->get_where('socios',array('estado'=>1));
		return $query->result();
	    }

	    public function get_socio_full($id)
	    {
		    $query = $this->db->get_where('socios',array('Id' => $id),1);
		    if($query->num_rows() == 0){return false;}
		    return $query->row();
	    }

	    public function get_socio_by($by)
	    {
		$by['estado'] = 1;
		$query = $this->db->get_where('socios',$by,1);
		if($query->num_rows() == 0){
		    return false;
		}else{
		    return $query->result();
		}
	    }

	    public function borrar_socio($id){
		$this->db->where('Id', $id); 
		$this->db->update('socios',array("estado"=>'0'));
	    }

	    public function update_socio($id,$data){
		$categ = $this->get_cat($id, $data);
		if ( $categ['cambio'] == 1 ) {
			$this->load->model('pagos_model');
			if ( $categ['precio_ant'] != $categ['precio_new'] ) {
				$diferencia = $categ['precio_new'] - $categ['precio_ant'];
				if ( $diferencia > 0 ) {
					$this->pagos_model->registrar_pago('debe',$id,$diferencia,'Cambio de Categoria de '.$categ['descr_ant'].' a '.$categ['descr_new'],0,0);
				} else {
					$this->pagos_model->registrar_pago('haber',$id,-$diferencia,'Cambio de Categoria de '.$categ['descr_ant'].' a '.$categ['descr_new'],0,0);
				}
			} else {
				$this->pagos_model->registrar_pago('debe',$id,0.00,'Cambio de Categoria de '.$categ['descr_ant'].' a '.$categ['descr_new'],0,0);
			}
		}
		$this->db->where('Id', $id);
		$this->db->update('socios', $data); 
	    }

	    public function act_datos($sid,$datos){
		$this->db->where('Id', $sid);
		$this->db->update('socios', $datos);
	    }


	    public function get_cat($id, $data){
		$cat_new=$data['categoria'];
		$query="SELECT s.categoria id_ant, c1.nomb descr_ant, c1.precio precio_ant, $cat_new id_new, c2.nomb descr_new, c2.precio precio_new, IF ( $cat_new != s.categoria , 1, 0) cambio
			FROM socios s
				LEFT JOIN categorias c1 ON ( s.categoria = c1.Id )
				LEFT JOIN categorias c2 ON ( $cat_new = c2.Id )
			WHERE s.Id = $id; ";
		$result = $this->db->query($query)->row(); 
		if ( $result ) {
		    $cat = array( 'id_ant' => $result->id_ant,
				'descr_ant' => $result->descr_ant,
				'precio_ant' => $result->precio_ant,
				'id_new' => $result->id_new,
				'descr_new' => $result->descr_new,
				'precio_new' => $result->precio_new,
				'cambio' => $result->cambio
			);
		    return $cat;
		}else{
		    return false;
		}
	    }

	    public function update_lCon()
	    {   
		$uid = $this->session->userdata('id_usuario');
		$fecha = date("Y-m-d G:i:s");
		$data = array('lCon'=>$fecha);
		$this->db->where('Id',$uid);
		$this->db->update('admin',$data);
	    }

	    public function es_tutor($sid){    
		$query = $this->db->get_where('socios', array('estado' =>'1','tutor =' => $sid));
		if($query->num_rows() == 0){
		    return false;
		}else{
		    return true;
		}
	    }
	    public function listar_tutores(){    
		$this->db->select('tutor');
		$this->db->distinct('tutor');
		$query = $this->db->get_where('socios', array('estado' =>'1','tutor !=' => '0'));
		$tutores = $query->result();
		return $tutores;
	    }
	    public function get_cumpleanios(){ // esta funcion devuelve los socios que cumplen 18 años entre el 21 del mes pasado y el 20 de este mes
		
		$mes = date('m'); //mes actual
		$mes_ant = date('m')-1; //mes anterior
		if(count($mes_ant) == 1){ 
		    $mes_ant = '0'.$mes_ant;
		}
		$anio = date('Y')-18; //18 años antes

		$this->db->where($anio, 'YEAR(nacimiento)' , FALSE);
		$this->db->where($mes_ant, 'MONTH(nacimiento)' , FALSE);
		$this->db->where('21 <=', 'DAY(nacimiento)' , FALSE);
		$this->db->get('socios');
		$query1 = $this->db->last_query(); //buscamos los que nacieron el mes anterior despues del 21 inclusive

		$this->db->where($anio, 'YEAR(nacimiento)' , FALSE);
		$this->db->where($mes, 'MONTH(nacimiento)' , FALSE);
		$this->db->where('20 >=', 'DAY(nacimiento)' , FALSE);
		$this->db->get('socios');
		$query2 = $this->db->last_query(); // buscamos los que nacieron este mes antes del 20 inclusive

		$query = $this->db->query($query1." UNION ".$query2);
		return $query->result();
	    }
	    public function actualizar_menor($id_menor){
		$this->db->where('Id',$id_menor);
		$this->db->update('socios',array('tutor'=>'0','categoria'=>'2'));
		$this->load->model('pagos_model');
		$this->pagos_model->registrar_pago('debe',$id_menor,0.00,'Cambio de Categoria de Menor a Mayor - Proceso Facturacion',0,0);

	    }
	    public function get_socios_pagan($facturado=false){
		if($facturado){
		    $this->db->where('facturado', 0);
		}
		$query = $this->db->get_where('socios',array('tutor'=>'0','estado'=>'1','suspendido'=>0));
		return $query->result();
	    }
	    public function check_u_n($sn){
		$query = $this->db->get_where('socios',array('socio_n'=>$sn,'estado'=>'1'));
		if($query->num_rows() == 0){
		    return false;
		}else{
		    return true;
		}
	    }

	    public function get_resumen_mail($sid){
		$this->load->model('pagos_model');
		$this->load->model('debtarj_model');
		$resumen = $this->pagos_model->get_monto_socio($sid);
		$res['resumen'] = $resumen;
		$socio = $this->get_socio($sid);
		$res['mail'] = $socio->mail;
		$res['deuda'] = $this->pagos_model->get_deuda_sinhoy($sid);
		$res['sid'] = $sid;
		$res['socio_n'] = $socio->socio_n;
		$debtarj = $this->debtarj_model->get_debtarj_by_sid($sid);
		if ( $debtarj ) {
			$res['debtarj'] = $debtarj;
		} else {
			$res['debtarj'] = null;
		}
		
		return $res;
	    }

	    public function insert_deuda($sid,$deuda){
		$this->load->model('pagos_model');
		$total = $this->pagos_model->get_socio_total($sid);
		$total = $total - $deuda;
		$insert = array(
		    'sid'=> $sid,
		    'descripcion'=>'Ingreso Manual',
		    'debe'=>$deuda,
		    'haber' => 0,
		    'total' => $total
		    );
		$this->db->insert('facturacion',$insert);
	    }

	    public function suspender($id,$si='si'){
		if($si == 'si'){
		    $hoy = date("Y-m-d");
		    $this->db->where('Id',$id);
		    $this->db->update('socios',array('suspendido'=>1, 'fecha_baja'=>$hoy));
		}else{
		    $this->db->where('Id',$id);
		    $this->db->update('socios',array('suspendido'=>0, 'fecha_baja'=>null));
		}
	    }

	    public function get_socio_by_dni($dni)
	    {
		$this->db->where('dni', $dni);
		$query = $this->db->get('socios');
		if( $query->num_rows() == 0 ){ return false; }
		$socio = $query->row();
		$query->free_result();
		return $socio;
	    }

	    public function get_socio_by_barcode($barcode)
	    {
		$this->db->where('barcode', $barcode);
		$query = $this->db->get('cupones');
		if( $query->num_rows() == 0 ){ return false; }
		$cupon = $query->row();    
		$socio = $this->get_socio($cupon->sid);
		if(!$socio){ return false; }
		return $socio;
	    }

	    public function get_padron_app()
	    {
		$query="DROP TEMPORARY TABLE IF EXISTS tmp_saldos; ";
		$this->db->query($query);
		$query="CREATE TEMPORARY TABLE tmp_saldos ( INDEX ( sid ) )
			SELECT tutor_id sid, MAX(IF(estado = 1,DATE_FORMAT(generadoel,'%Y%m'),0)) ult_impago, SUM(pagado-monto) saldo, IF(SUM(pagado-monto)>=0,1,0) aldia
			FROM pagos 
			GROUP BY 1; ";
		$this->db->query($query);
		$query="SELECT s.dni, s.id sid, CONCAT(s.apellido,', ',s.nombre) apynom, c.barcode, p.saldo, 
				CASE WHEN aldia=0 AND p.ult_impago < DATE_FORMAT(CURDATE(), '%Y%m') THEN 99
				     WHEN aldia=0 AND p.ult_impago = DATE_FORMAT(CURDATE(), '%Y%m') THEN 10
				     WHEN aldia=1 THEN 1
				END semaforo
			FROM socios s 
				LEFT JOIN cupones c ON ( s.id = c.sid AND c.estado = 1 ) 
				LEFT JOIN tmp_saldos p ON ( s.id = p.sid ) 
			WHERE s.suspendido = 0 
			GROUP BY s.id; ";
		$result = $this->db->query($query)->result();
		return $result;
	    }

	    public function get_status_by_dni($dni)
	    {
		$query="DROP TEMPORARY TABLE IF EXISTS tmp_saldos; ";
		$this->db->query($query);
		$query="CREATE TEMPORARY TABLE tmp_saldos ( INDEX ( sid ) )
                        SELECT p.tutor_id sid, MIN(IF(p.estado = 1,IF (p.tipo != 5 ,DATE_FORMAT(p.generadoel,'%Y%m'),'2100-01-01'),0)) ult_impago, SUM(p.pagado-p.monto) saldo, IF(SUM(p.pagado-p.monto)>=0,1,0) aldia
			FROM pagos p
				JOIN socios s ON s.dni = $dni AND s.Id = p.tutor_id
			GROUP BY 1; ";
		$this->db->query($query);
		$query="SELECT s.dni, s.Id sid, CONCAT(s.apellido,', ',s.nombre) apynom, c.barcode, p.saldo, s.suspendido,
				CASE WHEN aldia=0 AND p.ult_impago < DATE_FORMAT(CURDATE(), '%Y%m') THEN 99
				     WHEN aldia=0 AND p.ult_impago = DATE_FORMAT(CURDATE(), '%Y%m') THEN 10
				     WHEN aldia=1 THEN 1
				END semaforo
			FROM socios s 
				LEFT JOIN cupones c ON ( s.Id = c.sid AND c.estado = 1 ) 
				LEFT JOIN tmp_saldos p ON ( s.Id = p.sid ) 
			WHERE s.dni = $dni 
			GROUP BY s.Id; ";
		$result = $this->db->query($query)->row();
		return $result;
	    }

	}  
?>

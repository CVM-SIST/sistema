<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 */
class Debtarj_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database('default');
    }

/* Funciones de la tabla socios_debito_tarj */
    
    public function grabar($datos)
    {
        $this->db->insert('socios_debito_tarj', $datos);
        return $this->db->insert_id();   
    }

    public function stopdebit($id, $pone){
        $this->db->where('Id', $id); 
	$query=$this->db->get('socios_debito_tarj');
	$debtarj=$query->row();
	if ( $pone ) {
        	$this->db->where('Id', $id); 
        	$this->db->update('socios_debito_tarj',array("estado"=>'2'));
	} else {
        	$this->db->where('Id', $id); 
        	$this->db->update('socios_debito_tarj',array("estado"=>'1'));
	}
    }

    public function borrar($id){
        $this->db->where('Id', $id); 
        $this->db->update('socios_debito_tarj',array("estado"=>'0',"nro_tarjeta"=>'0'));
    }

    public function actualizar($id, $datos){
        $this->db->where('Id', $id);
        $this->db->update('socios_debito_tarj', $datos); 
    }

    public function get($id)
    {
        if (!$id || $id == '0'){
            $debtarj = new stdClass();
            $debtarj->sid=0;
            $debtarj->id_marca=0;
            $debtarj->nro_tarjeta=0;
            $debtarj->fecha_adhesion=0;
            $debtarj->ult_periodo_generado=0;
            $debtarj->ult_fecha_generacion=0;
            $debtarj->estado=0;
            $debtarj->id=0;
            return $debtarj;
        } else {
            $query = $this->db->get_where('socios_debito_tarj',array('Id' => $id),1);
            if($query->num_rows() == 0) {return false;}
            return $query->row();
        }
    }

    public function get_debtarjs()
    {
        $this->db->where('estado >','0');
        $query = $this->db->get('socios_debito_tarj');
        return $query->result();
    }

    public function get_debitos_gen()
    {
        $this->db->where('estado >','0');
        $this->db->limit(6);
        $this->db->order_by('periodo','desc');
        $query = $this->db->get('socios_debitos_gen');
        return $query->result();
    }

    public function inicializa_contra($periodo, $id_marca)
    {
        $qry="UPDATE socios_debitos sd 
				JOIN socios_debitos_gen sdg ON sdg.id_marca = $id_marca AND sdg.periodo = $periodo AND sdg.estado = 1
                        	JOIN socios_debito_tarj sdt ON sd.id_debito = sdt.id AND sdt.id_marca = sdg.id_marca 
			SET sd.estado = 1 
		WHERE sd.estado = 9; ";
        $this->db->query($qry);

    }

    public function get_periodo_marca($periodo, $id_marca)
    {
        $query = $this->db->get_where('socios_debitos_gen',array('periodo'=>$periodo, 'id_marca'=>$id_marca, 'estado'=>1));
   	if ($query->num_rows() > 0) {
        	return $query->row();
    	} else {
    		return FALSE;
	}
    }


    public function cierre_contracargo($id_cabecera)
    {
	$qry="UPDATE socios_debitos_gen sdg 
        		JOIN ( SELECT COUNT(*) cantidad, SUM(d.importe) total FROM socios_debitos d WHERE d.id_cabecera = $id_cabecera AND d.estado = 0 ) sd
		SET sdg.cant_acreditada=sdg.cant_generada-sd.cantidad, sdg.total_acreditado=total_generado-sd.total
			WHERE sdg.id = $id_cabecera; ";
        $this->db->query($qry);

    }

    public function mete_contracargo($id_cabecera, $nrotarjeta, $nrorenglon, $importe)
    {
	if ( $id_cabecera && $nrotarjeta && $nrorenglon && $importe ) {
        	$qry="SELECT sd.id
			FROM socios_debitos sd 
                        	JOIN socios_debitos_gen sdg ON sdg.id = $id_cabecera AND sdg.estado = 1
                        	JOIN socios_debito_tarj sdt ON sd.id_debito = sdt.id AND sdt.id_marca = sdg.id_marca AND MOD(sdt.nro_tarjeta,10000) = $nrotarjeta 
                	WHERE sd.importe = $importe AND sd.nro_renglon = $nrorenglon; ";
        	$contras = $this->db->query($qry)->result();
        	if ($contras) {
        		$qry="UPDATE socios_debitos sd 
					JOIN socios_debitos_gen sdg ON sdg.id = $id_cabecera AND sdg.estado = 1
                        		JOIN socios_debito_tarj sdt ON sd.id_debito = sdt.id AND sdt.id_marca = sdg.id_marca AND MOD(sdt.nro_tarjeta,10000) = $nrotarjeta
				SET sd.estado = 0
                		WHERE sd.importe = $importe AND
					sd.nro_renglon = $nrorenglon ";
        		$this->db->query($qry);
                	return TRUE;
        	} else {
                	return FALSE;
		}
	} else {
			return FALSE;
	}


    }

    public function get_contracargos($periodo, $id_marca)
    {
	$qry="SELECT sdt.id_marca, sdt.sid, sdt.nro_tarjeta, CONCAT(s.apellido,', ',s.nombre) apynom, sd.id_debito, sdg.fecha_debito, sdg.fecha_acreditacion, sd.nro_renglon, sd.importe 
		FROM socios_debitos_gen sdg 
			JOIN socios_debitos sd ON sdg.id = sd.id_cabecera AND sd.estado = 0
			JOIN socios_debito_tarj sdt ON sd.id_debito = sdt.id AND sdt.id_marca = sdg.id_marca 
			JOIN socios s ON sdt.sid = s.Id 
		WHERE sdg.periodo = $periodo AND sdg.id_marca = $id_marca AND sdg.estado = 1; ";
        $contras = $this->db->query($qry)->result();
        if ($contras) {
                return $contras;
        } else {
                return FALSE;
        }
    }


    public function exist_periodo_marca($periodo, $id_marca)
    {
        $query = $this->db->get_where('socios_debitos_gen',array('periodo'=>$periodo, 'id_marca'=>$id_marca, 'estado'=>'1'));
   	if ($query->num_rows() > 0) {
        	return TRUE;
    	} else {
    		return FALSE;
	}
    }


    public function get_debgen($periodo, $id_marca)
    {
        $this->db->where('periodo', $periodo);
        $this->db->where('id_marca', $id_marca);
        $this->db->where('estado', 1);
        $query = $this->db->get('socios_debitos_gen');
        $cabecera = $query->row();
	return $cabecera;
    }

    public function insert_periodo_marca($datos)
    {
        $this->db->insert('socios_debitos_gen', $datos);
        return $this->db->insert_id();
    }

    public function anula_periodo_marca($periodo, $id_marca)
    {
        $this->db->where('periodo', $periodo);
        $this->db->where('id_marca', $id_marca);
        $query = $this->db->get('socios_debitos_gen');
	$cabecera = $query->row();

	$this->db->where('id', $cabecera->id);
        $this->db->delete('socios_debitos_gen'); 

	$this->db->where('id_cabecera', $cabecera->id);
        $this->db->delete('socios_debitos'); 
    }

    public function get_debtarjs_anul($sid)
    {
        $query = $this->db->get_where('socios_debito_tarj',array('estado'=>'0', 'sid'=>$sid));
        return $query->result();
    }

    public function get_debtarj_by($by)
    {
        $by['estado'] = 1;
        $query = $this->db->where($by);
        $query = $this->db->where('estado in (1,2)');
        $query = $this->db->get('socios_debito_tarj');
        if($query->num_rows() == 0){
            return false;
        }else{
            return $query->result();
        }
    }

    public function get_debtarj($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('socios_debito_tarj');
        if( $query->num_rows() == 0 ){ return false; }
        $debtarj = $query->row();
        $query->free_result();
        return $debtarj;
    }

    public function get_debtarj_by_cambio($nro_renglon, $periodo, $id_marca)
    {
        $qry="SELECT sdt.*
                FROM socios_debitos_gen sdg
                        JOIN socios_debitos sd ON sdg.id = sd.id_cabecera AND sd.nro_renglon = $nro_renglon
                        JOIN socios_debito_tarj sdt ON sd.id_debito = sdt.id 
                        JOIN socios s ON sdt.sid = s.Id 
                WHERE sdg.id_marca = $id_marca AND sdg.estado  = 1 AND sdg.periodo = $periodo; ";
        $debtarj = $this->db->query($qry)->result();
        if ($debtarj) {
                return $debtarj;
        } else {
                return FALSE;
        }

    }

    public function get_debtarj_by_sid_tarjeta($sid, $id_marca, $nro_tarjeta)
    {
        $this->db->where('sid', $sid);
        $this->db->where('id_marca', $id_marca);
        $this->db->where('nro_tarjeta', $nro_tarjeta);
        $query = $this->db->get('socios_debito_tarj');
        if( $query->num_rows() == 0 ){ return false; }
        $debtarj = $query->row();
        $query->free_result();
        return $debtarj;
    }

    public function get_debtarj_by_sid($sid)
    {
        $this->db->where('sid', $sid);
        $this->db->where('estado in (1,2)');
        $query = $this->db->get('socios_debito_tarj');
        if( $query->num_rows() == 0 ){ return false; }
        $debtarj = $query->row();
        $query->free_result();
        return $debtarj;
    }

/* Funciones de la tabla socios_debitos */

    public function insert_debito($id,$id_cabecera,$importe,$renglon=0){
        $insert = array(
            'id_debito'=> $id,
            'id_cabecera'=>$id_cabecera,
            'importe' => $importe,
            'estado' => 9,
	    'nro_renglon' => $renglon,
	    'id' => 0 
            );
        $this->db->insert('socios_debitos',$insert);
    }

    public function upd_gen($id_cabecera, $datos) {
        $this->db->where('id', $id_cabecera);
        $this->db->update('socios_debitos_gen',$datos);
    }

    public function upd_noacred($id_cabecera) {
	$qry="UPDATE socios_debitos SET estado = 0 WHERE id_cabecera = $id_cabecera AND estado = 9; ";
	$this->db->query($qry);
    }

    public function upd_acred($id_debito){
        $this->db->where('Id', $id_debito);
        $this->db->update('socios_debitos',array("estado"=>"1"));
    }

    public function upd_debito_rng($id_debito,$nro_renglon){
        $this->db->where('Id', $id_debito);
        $this->db->update('socios_debitos',array("nro_renglon"=>$nro_renglon));
    }

    public function get_debito_rng($id_marca, $fecha_debito, $nro_renglon){

        $this->db->select('sd.*, socios_debito_tarj.id_marca as id_marca, socios_debito_tarj.nro_tarjeta as nro_tarjeta');
        $this->db->where('fecha_debito', $fecha_debito);
        $this->db->where('nro_renglon', $nro_renglon);
        $this->db->join('socios_debito_tarj', 'socios_debito_tarj.id = sd.id_debito AND socios_debito_tarj.id_marca = '.$id_marca, 'left');
        $query=$this->db->get('socios_debitos as sd');
        if( $query->num_rows() == 0 ){ return false; }
        $debito = $query->row();
        return $debito;
    }


    public function get_fchult_debito()
    {
        $this->db->where('estado', 1);
        $this->db->order_by('fecha_debito','desc');
        $query = $this->db->get('socios_debitos',1);
        if( $query->num_rows() == 0 ){ return false; }
        $debitos = $query->row();
        $query->free_result();
        return $debitos->fecha_debito;
    }

    public function get_debito_by_marca_nrotarj($id_marca, $fecha, $nro_tarjeta) {
        $this->db->where('id_marca', $id_marca);
        //$this->db->where('ult_fecha_generacion', $fecha);
        $this->db->where('nro_tarjeta-TRUNCATE(nro_tarjeta,-4)', $nro_tarjeta);
        //$this->db->where('estado', 1);
        $query = $this->db->get('socios_debito_tarj');
        if( $query->num_rows() == 0 ){ return false; }
	    $debtarjs=$query->row();
        $id_debito=$debtarjs->id;
        $ult_fecha=$debtarjs->ult_fecha_generacion;
        $debito=$this->get_debito_by_id($id_debito, $ult_fecha);
        return $debito;
    }

    public function get_debitos_by_cabecera($id_cabecera) {
        $qry="SELECT sdt.id_marca, sdt.sid, sdt.nro_tarjeta, sd.id_debito, sdt.ult_fecha_generacion, sdt.ult_periodo_generado, sd.nro_renglon, sd.importe 
                FROM socios_debitos sd 
                        JOIN socios_debito_tarj sdt ON sd.id_debito = sdt.id 
                WHERE sd.id_cabecera = $id_cabecera AND sd.estado  = 9
		ORDER BY sd.nro_renglon; ";
        $debitos = $this->db->query($qry)->result();
        if ($debitos) {
                return $debitos;
        } else {
                return FALSE;
        }
    }

    public function get_debitos_by_marca_periodo($id_marca, $periodo) {
        $this->db->where('id_marca', $id_marca);
        $this->db->where('periodo', $periodo);
        $this->db->where('estado', 1);
        $query = $this->db->get('socios_debitos_gen');
        if( $query->num_rows() == 0 ){ return false; }
	$cabecera = $query->row();
	
	$debtarjs=$this->get_debitos_by_cabecera($cabecera->id);
	if ($debtarjs) { 
		return $debtarjs;
	} else {
		return false;
	}
    }

    public function get_deberr_by_marca_periodo($id_marca, $periodo) {
        $this->db->where('id_marca', $id_marca);
        $this->db->where('periodo', $periodo);
        $this->db->where('estado','1');
        $query = $this->db->get('socios_debitos_gen',1);
	$row=$query->row();
	$fecha_deb=$row->fecha_debito;

        $this->db->where('sd.id_cabecera', $row->id);
        $this->db->where('sd.estado',0);
        $this->db->select('s.id sid, TRIM(s.apellido) apellido, TRIM(s.nombre) nombre, t.descripcion marca, sd.nro_renglon, sdt.nro_tarjeta, sd.importe');
        $this->db->join('socios_debito_tarj sdt','sdt.id = sd.id_debito AND sdt.id_marca = '.$id_marca);
        $this->db->join('socios s','sdt.sid = s.id');
        $this->db->join('tarj_marca t','sdt.id_marca = t.id');
        $query = $this->db->get('socios_debitos sd');
	$debitos=$query->result();

        return $debitos;

    }

    public function get_debito_by_id($id_debito, $id_cabecera, $estado='')
    {
        $this->db->where('id_debito', $id_debito);
        $this->db->where('id_cabecera', $id_cabecera);
	if ( $estado ) {
        	$this->db->where('estado', $estado);
	} else {
        	$this->db->where('estado', 1);
	}
        $query = $this->db->get('socios_debitos',1);
        if( $query->num_rows() == 0 ){ return false; }
        $debitos = $query->row();
        $query->free_result();
        return $debitos;
    }

    public function get_debitos_by_socio($id_debito)
    {
        $this->db->where('id_debito', $id_debito);
        $this->db->where('estado >', 0);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('socios_debitos',1);
        if( $query->num_rows() == 0 ){ return false; }
        $debitos = $query->row();
        $query->free_result();
        return $debitos;
    }

    public function get_debitos_by_periodo($periodo, $estado=1)
    {
        $qry="SELECT sdg.fecha_debito, sdg.fecha_acreditacion, sdt.id_marca, sdt.sid, sdt.nro_tarjeta, sdt.ult_periodo_generado, sdt.ult_fecha_generacion, sd.*
                FROM socios_debitos_gen sdg	
			JOIN socios_debitos sd ON sdg.id = sd.id_cabecera AND sd.estado = $estado
                        JOIN socios_debito_tarj sdt ON sd.id_debito = sdt.id 
                WHERE sdg.periodo = $periodo AND sdg.estado = 1; ";
        $debitos = $this->db->query($qry)->result();
        return $debitos;
    }


}  
?>

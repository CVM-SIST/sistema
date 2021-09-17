<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 */
class General_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database('default');
    }

    public function write_log($log)
    {
        $this->db->insert('log_cambios',$log);
    }

    public function get_cats(){
        $this->db->where("estado > 0");
        $query = $this->db->get("categorias");
        return $query->result();
    }

    public function get_cat($id){
        $this->db->where('Id',$id);
        $query = $this->db->get("categorias");
        return $query->row();
    }
    public function update_cat($idcateg,$categ='')
    {
        $this->db->where('Id',$idcateg);
        $this->db->update('categorias',$categ);
    }
    public function insert_cat($categ='')
    {
        $this->db->insert('categorias',$categ);
        return $this->db->insert_id();
    }
    public function delete_cat($idcateg)
    {
    	$this->db->where('Id',$idcateg);
	$this->db->update('categorias',array('estado'=>0));
    }

    /*public function get_ciudades(){
    	$this->db->order_by("ciudad_nombre", "asc"); 
        $query = $this->db->get("ciudades");
        return $query->result();
    }*/

    public function get_config($id)
    {
        $query = $this->db->where('id', $id);
        $query = $this->db->get('configuracion');
        $config = $query->row();
        $query->free_result();
        return $config;
    }

    public function update_config($config='')
    {
        $this->db->update('configuracion',$config);
    }

    public function save_cat_config($precios,$fam){
        for ($i=1; $i < count($precios)+1; $i++) { 
            $this->db->where('Id',$i);
            $this->db->update('categorias',array('precio'=>$precios[$i-1]));
	
        }
        $this->db->where('Id','4');
        $this->db->update('categorias',array('precio_unit'=>$fam));
    }
/**
ENVIOS
**/
    public function insert_envio($envio='')
    {
        $this->db->insert('envios',$envio);
        return $this->db->insert_id();
    }

    public function get_socios_by($grupo,$data='',$activ)
    {
        if($grupo == 1){
            $this->db->where('estado',1);            
	    if ( $activ == 1 ) {
		$this->db->where('suspendido',0);
	    } 
            $query = $this->db->get('socios');
            if($query->num_rows() == 0){return false;}
            $socios = $query->result();
        }else{
            switch ($grupo) {
                case 'categorias':
                    foreach ($data as $id) {
                        $this->db->or_where('categoria',$id);
                    }
                    $this->db->where('estado',1);            
		    if ( $activ == 1 ) {
			$this->db->where('suspendido',0);
	    	    } 
                    $query = $this->db->get('socios');
                    if($query->num_rows() == 0){return false;}
                    $socios = $query->result();
                    break;
                
                case 'actividades':
                    foreach ($data as $id) {        
                        $this->db->or_where('aid',$id);
                    }
                    $this->db->where('estado',1);
                    $query = $this->db->get('actividades_asociadas');
                    if($query->num_rows() == 0){return false;}
                    $actividades = $query->result();
                    $socios = array();
                    $this->load->model('socios_model');
                    foreach ($actividades as $actividad) {
                        $soc = $this->socios_model->get_socio($actividad->sid);
			if ( $activ == 1 ) {
				if ( $soc->suspendido == 0 ) {
                        		$socios[] = $soc;
				}
			} else {
                        		$socios[] = $soc;
			}
                    }
                    break;

                case 'socconactiv':
                    $this->load->model('socios_model');
		    $socios=$this->socios_model->get_socios_conact($activ);
                    break;

                case 'socsinactiv':
                    $this->load->model('socios_model');
		    $socios=$this->socios_model->get_socios_sinact($activ);
                    break;

                case 'soccomision':
                    $this->load->model('socios_model');
		    $socios=$this->socios_model->get_socios_comision($data, $activ);
                    break;

                case 'titcomision':
                    $this->load->model('socios_model');
		    $socios=$this->socios_model->get_socios_titu_comision($data, $activ);
                    break;

                case 'ahg':
                    $this->load->model('socios_model');
		    $socios=$this->socios_model->get_socios_ahg();
                    break;

            }
        }
        return $socios;
        
    }

    public function insert_envios_data($envio_data)
    {
        $this->db->insert('envios_data',$envio_data);
    }

    public function update_envio($id='',$envio)
    {
        $this->db->where('Id',$id);
        $this->db->update('envios',$envio);
    }

    public function get_envios()
    {
	$limite = 20;
        $this->db->where('estado',1);
        $this->db->order_by('Id','desc');
        $query = $this->db->get('envios');
        if($query->num_rows() == 0){ return false; }
        $envios_orig = $query->result();
	$envios=array();
	$i = 0;
        foreach ($envios_orig as $envio) {
            $this->db->where('eid',$envio->Id);
            $query = $this->db->get('envios_data');
            $envio->total = $query->num_rows();

            $this->db->where('eid',$envio->Id);
            $this->db->where('estado', 1);
            $query = $this->db->get('envios_data');
            $envio->enviados = $query->num_rows();

            $this->db->where('eid',$envio->Id);
            $this->db->where('estado', 9);
            $query = $this->db->get('envios_data');
            $envio->errores = $query->num_rows();
	
	    $envios[]=$envio;
	    $i++;
	    if ( $i > $limite ) {
		break;
	    }
        }
        $query->free_result();
        return $envios;
    }

    public function get_envio($id='')
    {
        $this->db->where('Id',$id);
        $query = $this->db->get('envios');
        if($query->num_rows() == 0){ return false; }
        $envio = $query->row();

        $this->db->where('eid',$envio->Id);
        $query = $this->db->get('envios_data');
        $envio->total = $query->num_rows();

        $this->db->where('eid',$envio->Id);
        $this->db->where('estado',1);
        $query = $this->db->get('envios_data');
        $envio->enviados = $query->num_rows();
        $query->free_result();

        return $envio;
    }

    public function get_envios_data($id)
    {
        $this->db->where('eid',$id);
        $query = $this->db->get('envios_data');        
        if($query->num_rows() == 0){ return false; }
        $envios_data = $query->result();
        $query->free_result();
        return $envios_data;
    }

    public function clear_envio_data($id)
    {
        $this->db->where('eid',$id);
        $this->db->delete('envios_data');
    }

    public function get_envio_data($id='')
    {
        $this->db->where('eid',$id);
        $this->db->where('estado',0);
        $query = $this->db->get('envios_data',1);
        if($query->num_rows() == 0){ return false; }
        $envio = $query->row();
        $query->free_result();
        return $envio;
    }

    public function enviado($id='')
    {
        $this->db->where('Id',$id);
        $this->db->update('envios_data',array('estado'=>1));
    }

    public function enviado_error($id='')
    {
        $this->db->where('Id',$id);
        $this->db->update('envios_data',array('estado'=>9));
    }

    public function get_enviados($id='')
    {
        $this->db->where('eid',$id);
        $this->db->where('estado',1);
        $query = $this->db->get('envios_data');
        $enviados = $query->num_rows();
        $query->free_result();
        return $enviados;
    }

    public function get_pend_envfact() {
	$query="SELECT COUNT(*) pendientes FROM facturacion_mails WHERE estado = 0; ";
	$envios = $this->db->query($query)->result()[0];
	return $envios;
    }

    public function get_resumen_fact() {
	$query="SELECT SUM(IF(estado=0,1,0)) estado0, SUM(IF(estado=1,1,0)) estado1, SUM(IF(estado=9,1,0)) estado9 FROM facturacion_mails ; ";
	$envios = $this->db->query($query)->result()[0];
	return $envios;
    }

    public function get_prox_envfact() {
	$query="SELECT * FROM facturacion_mails WHERE estado = 0 LIMIT 3; ";
	$envios = $this->db->query($query)->result();
	return $envios;
    }

    public function get_pend_envios() {
	$query="SELECT COUNT(*) pendientes FROM envios e JOIN envios_data d ON e.Id = d.eid AND d.estado = 0 WHERE e.estado = 1; ";
	$envios = $this->db->query($query)->result()[0];
	return $envios;
    }

    public function get_prox_envios() {
	$query="SELECT e.titulo, e.body, d.* FROM envios_data d JOIN envios e ON e.Id = d.eid AND e.estado = 1 WHERE d.estado = 0 LIMIT 3; ";
	$envios = $this->db->query($query)->result();
	return $envios;
    }

    public function get_resumen_envios($eid) {
        $query="SELECT SUM(IF(estado=0,1,0)) estado0, SUM(IF(estado=1,1,0)) estado1, SUM(IF(estado=9,1,0)) estado9 FROM envios_data WHERE eid = $eid ; ";
        $envios = $this->db->query($query)->result()[0];
        return $envios;
    }

/**
COMISIONES
**/

    public function get_actividades_comision()
    {
        $this->db->where('Id',$this->session->userdata('Id'));
        $query = $this->db->get('profesores');
        if($query->num_rows() == 0){return false;}
        $profesor = $query->row();

        $this->db->where('comision',$profesor->comision);
        $query = $this->db->get('actividades');
        if($query->num_rows() == 0){return false;}
        $actividades = $query->result();
        $query->free_result();
        return $actividades;

    }

    public function get_reporte($aid='')
    {
        $this->load->model('actividades_model');
        $this->load->model('socios_model');
        $this->load->model('pagos_model');
        $reporte = new STDClass();
        $reporte->actividad = $this->actividades_model->get_actividad($aid);

        $this->db->select('sid');
        $this->db->distinct();
        $this->db->where('estado',1);
        $this->db->where('aid',$aid);
        $query = $this->db->get('actividades_asociadas');
        if($query->num_rows() == 0){ $socios = false; }
        $socios = $query->result();
        if($socios){
            foreach ($socios as $socio) {
                $socio->info = $this->socios_model->get_socio($socio->sid);
                $socio->deuda = $this->pagos_model->get_deuda_monto($socio->sid);
                
                $this->db->order_by('date','desc');
                $this->db->where('haber >',0);
                $this->db->where('sid',$socio->sid);
                $query = $this->db->get('facturacion',1);
                if($query->num_rows() == 0){
                    $socio->ultimo_pago = 'No se registran pagos.';
                }else{
                    $socio->ultimo_pago = $query->row()->date;
                }
            }
        }
        $reporte->socios = $socios;        
        return $reporte;
    }

/* FUnciones para la nueva tabla de registro de ejecucion de crones */
// Tipos -> 1-Facturacion Mensual 2-Aviso Deuda 3-Envio Masivo
	function get_ult_cron($tipo) {
		$fecha=date('Y-m-d');
		$query = "SELECT * FROM crones WHERE tipo = $tipo AND fecha = '$fecha'; ";
                $cron = $this->db->query($query)->row();
		if ( $cron ) {
			$query = "SELECT c.*, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(ts_inicio) anticuacion 
					FROM crones c 
					WHERE c.tipo = $tipo AND c.fecha = '$fecha'; ";
                	$cron = $this->db->query($query)->row();
			return $cron;
		} else {
			return false;
		}
	}
		
	function insert_ult_cron($tipo) {
		$fecha=date('Y-m-d');
		$query = "SELECT * FROM crones WHERE tipo = $tipo AND fecha = '$fecha' AND estado = 1 ;";
                $cron = $this->db->query($query)->row();
		if ( $cron ) {
			return false;
		} else {
			$query = "INSERT INTO crones VALUES ( $tipo, 1, '$fecha', 1, NOW(), null, null, null ); ";
                	$this->db->query($query);
			return true;
		}
	}

	function upd_ult_cron($tipo,$status=null,$datos1=null,$datos2=null) {
		$fecha=date('Y-m-d');
                $query = "SELECT * FROM crones WHERE tipo = $tipo AND fecha = '$fecha'; ";
                $cron = $this->db->query($query)->row();
                if ( $cron ) {
			if ( $status ) {
				$estado = $status;
			} else {
				$estado = 0;
			}
                        $query="UPDATE crones c 
					SET c.ts_fin = NOW(), veces = veces + 1, estado = $estado, datos1 = '$datos1', datos2 = '$datos2'
				WHERE c.tipo = $tipo AND c.fecha = '$fecha'; ";
                        $this->db->query($query);
                        return true;
                } else {
                        return false;
                }
	}

/* Fin de funciones CRONES */
}
?>

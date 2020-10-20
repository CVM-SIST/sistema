<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class ws_api extends CI_Controller {

	protected $login = null;
	protected $nivel = null;
	protected $token = null;
	protected $token_ok = FALSE;
	protected $dni = null;

	public function __construct()
    	{
		parent::__construct();
        	$this->load->helper(array('url', 'form', 'html', 'string'));

        	//Controlo si el pedido tiene un token y es valido
        	$authorization_str = explode(' ', $this->input->get_request_header('Authorization'));
        	$login = $this->input->get_post('login');
        	$dni = $this->input->get_post('dni');

echo "--------------------------------------\n";
var_dump($dni);
echo "--------------------------------------\n";

		//echo "\n\nLogin: ".$login." Authorization token: ".$authorization_str[0]." \n" ;

		//Login del usuario con token
		if (count($authorization_str) > 0 && $authorization_str[0] != null) {
            		$token = $authorization_str[0];

            		if ($this->token_login($login, $token)) {
                		$this->token_ok = TRUE;
                		$this->token = $token;
                		$this->dni = $dni;
            		} else {
                		//No existe la combinacion token - usuario
				echo "No tiene permiso para esta funcion";
                		return $this->jsonResultStr(100, "No tiene permiso para acceder a esta pagina");
            		}
        	}  else {
				echo "No tiene permiso para esta funcion2";
                		return $this->jsonResultStr(100, "No tiene permiso para acceder a esta pagina2");
		}
            
    	}

	function token_login($login, $token)
	{
		if ( $login == "agonzalez.lacoope" && $token == "ewrewry23k5bc1436lnlahbg23218g12g1h3g1vm" ) {
			// Aca va luego el chequeo correcto - hardcodeado o contra la BD
			echo "OK los permisos chequeados";
			$this->nivel = 0;
			$this->login = $login;
			return true;
		} else {
			return false;
		}
	}

	function valid_user() { // esta funcion Valida el usuario y devuelve nivel del mismo
		if ( $this->token_ok ) {
			$arr_ret = array ( 'nivel' => $this->nivel, 'login' => $this->login );
			echo "Usuario Validado : " . $this->login . " - Token: " . $this->token . " - Nivel : " . $this->nivel ."\n";
			return json_encode($arr_ret);
		} else {
			echo "No tiene permiso para esta funcion2";
                	return $this->jsonResultStr(100, "No tiene permiso para acceder a esta pagina2");
		}
	}

	function get_padron() { // esta funcion devuelve el padron para chequeo OFF en base a la entidad correspondiente al usuario logueado
echo "Padron CVM!!!!";
		$padron=array();
		$this->load->model('socios_model');
		$padron = $this->socios_model->get_padron_app();
		return json_encode($padron);
	}

	function get_socio() { // esta funcion devuelve los datos de un socio puntual a partir de su DNI y en base a la entidad correspondiente al usuario logueado
echo "Socio CVM!!!!".$this->dni;
		$this->load->model('socios_model');
		$socio = $this->socios_model->get_socio_by_dni($this->dni);
var_dump($socio);
		return json_encode($socio);
	}

}

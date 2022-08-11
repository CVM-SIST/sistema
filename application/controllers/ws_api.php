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
        	$ya_validado = $this->input->get_post('ya_validado');


		//echo "\n\nLogin: ".$login." Authorization token: ".$authorization_str[0]." DNI: ".$dni." Ya validado".$ya_validado." \n" ;

		// Si el proceso previo ya cargo el post de usuario validado tomo esos datos como buenos
		if ( $ya_validado ) {
            			$token = $authorization_str[0];
                		$this->login = $login;
                		$this->nivel = 0;
                		$this->token_ok = TRUE;
                		$this->token = $token;
                		$this->dni = $dni;
		// sino valido
		} else {
			//Login del usuario con token
			if (count($authorization_str) > 0 && $authorization_str[0] != null) {
            			$token = $authorization_str[0];
	
            			if ($this->token_login($login, $token)) {
                			$this->token_ok = TRUE;
                			$this->token = $token;
                			$this->dni = $dni;
            			} else {
                			//No existe la combinacion token - usuario
					//echo "No tiene permiso para esta funcion";
					$result = json_encode($this->array_to_utf8(array("estado" => "100", "result" => null, "msg" => "No tiene permiso para acceder a esta pagina1")));
                			return $result;
            			}
        		}  else {
					$result = json_encode($this->array_to_utf8(array("estado" => "100", "result" => null, "msg" => "No tiene permiso para acceder a esta pagina2")));
                			return $result;
			}
		}
            
    	}

	function token_login($login, $token)
	{
		if ( $login == "agonzalez.lacoope" && $token == "6bb55159e45c90ff1200f4579b8a748051354bca" ) {
			// Aca va luego el chequeo correcto - hardcodeado o contra la BD
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
			$result = json_encode($this->array_to_utf8(array("estado" => "100", "result" => (object) $arr_ret, "msg" => "No tiene permiso para acceder a esta pagina3")));
			return $result;
		} else {
			$result = json_encode($this->array_to_utf8(array("estado" => "100", "result" => null, "msg" => "No tiene permiso para acceder a esta pagina3")));
                	return $result;
		}
	}

	function get_padron() { // esta funcion devuelve el padron para chequeo OFF en base a la entidad correspondiente al usuario logueado
                // Si no esta validado el usuario-token devuelvo false
                if ( $this->token_ok ) {
                    $padron=array();
                    $this->load->model('socios_model');
                    $padron = $this->socios_model->get_padron_app();
                    if ( $padron ) {
                        $result = json_encode($this->array_to_utf8(array("estado" => "0", "result" => $padron, "msg" => "Proceso OK")));
                    } else {
                        $result = json_encode($this->array_to_utf8(array("estado" => "100", "result" => null, "msg" => "No se puede procesar padron")));
                    }
                    echo $result;
                }
	}

	function get_socio() { // esta funcion devuelve los datos de un socio puntual a partir de su DNI y en base a la entidad correspondiente al usuario logueado
                // Si no esta validado el usuario-token devuelvo false
                if ( $this->token_ok ) {
                    $this->load->model('socios_model');
                    $socio = $this->socios_model->get_socio_by_dni($this->dni);
                    $semaforo = $this->socios_model->get_status_by_dni($this->dni);
		    $socio->barcode = $semaforo->barcode;
		    $socio->saldo = $semaforo->saldo;
		    $socio->semaforo = $semaforo->semaforo;
                    if ( $socio ) {
                        $result = json_encode($this->array_to_utf8(array("estado" => "0", "result" => (object) $socio, "msg" => "Proceso OK")));
                    } else {
                        $result = json_encode($this->array_to_utf8(array("estado" => "100", "result" => null, "msg" => "No se puede procesar padron")));
                    }
                    echo $result;
                }
	}

        function check_estado() { // esta funcion devuelve el status de un socio puntual a partir de su DNI y en base a la entidad correspondiente al usuario logueado
                // Si no esta validado el usuario-token devuelvo false
                if ( $this->token_ok ) {
                    $this->load->model('socios_model');
                    $socio = $this->socios_model->get_status_by_dni($this->dni);
                    if ( $socio ) {
                        $result = json_encode($this->array_to_utf8(array("estado" => "0", "result" => (object) $socio, "msg" => "Proceso OK")));
                    } else {
                        $result = json_encode($this->array_to_utf8(array("estado" => "100", "result" => null, "msg" => "No se puede procesar status")));
                    }
                    echo $result;
                }
        }

function array_to_utf8($array) {
    $result = array();
    if (is_array($array)) {
        foreach ($array as $nro => $fields) {
            $field_aux = array();
            // Si es un arreglo llamo de forma recursiva
            if (is_array($fields)) {
                $field_aux = $this->array_to_utf8($fields);
            } else {
                // Si no es un arreglo chequeo la codificación
                if (is_string($fields) ) {
                    //Si no es utf-8 lo codifico
                    $field_aux = utf8_encode($fields);
                } else {
                    $field_aux = $fields;
                }
            }
            /* Se agrega el campo al resultado */
            $result[utf8_encode($nro)] = $field_aux;
        }
    } else {
        if (is_string($fields) ) {
            //Si no es utf-8 lo codifico
            $result = utf8_encode($array);
        } else {
            $result = $array;
        }
    }
    return $result;
}


}

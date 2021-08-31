<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Imprimir extends CI_Controller {


    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('session'));
        $this->load->helper(array('url'));        
        include(BASEPATH."../application/libraries/phpqrcode/qrlib.php");
        if(!$this->session->userdata('is_logued_in')){          
            redirect(base_url().'admin');
        }              
    }
    function index()
    {
        $data['listado'] = false;
        $this->load->view('imprimir/index',$data);
        $this->load->view('imprimir/foot');
    }

    public function exportar($action='')
    {
        $data['listado'] = false;
        switch ($action) {
            case 'socios':
                $this->load->model('socios_model');                
                $this->load->model('general_model');                
                $this->load->model('pagos_model');                
                $clientes = $this->socios_model->get_socios_activos();
                    
                $titulo = "CVM_Socios_".date('d-m-Y');
                
                $this->load->library('PHPExcel');                
                $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                             ->setLastModifiedBy("Club Villa Mitre")
                                             ->setTitle("Listado")
                                             ->setSubject("Listado de Socios");
                
                $this->phpexcel->getActiveSheet()->getStyle('A1:R1')->getFill()->applyFromArray(
                    array(
                        'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array('rgb' => 'E9E9E9'),
                    )
                );

                // agregamos información a las celdas
                $this->phpexcel->setActiveSheetIndex(0)
                            ->setCellValue('A1', '#')
                            ->setCellValue('B1', 'Apellido')
                            ->setCellValue('C1', 'Nombre')
                            ->setCellValue('D1', 'DNI')
                            ->setCellValue('E1', 'Domicilio')
                            ->setCellValue('F1', 'Localidad')
                            ->setCellValue('G1', 'Nacionalidad')
                            ->setCellValue('H1', 'Fecha de Nacimiento')
                            ->setCellValue('I1', 'Teléfono')
                            ->setCellValue('J1', 'Email')
                            ->setCellValue('K1', 'Celular')
                            ->setCellValue('L1', 'Tutor de grupo Familiar')
                            ->setCellValue('M1', 'Categoría de Socio')
                            ->setCellValue('N1', 'Descuento')
                            ->setCellValue('O1', 'Fecha de Ingreso')                            
                            ->setCellValue('P1', 'Estado')
                            ->setCellValue('Q1', 'Observaciones')
                            ->setCellValue('R1', 'Saldo en Cuenta Corriente');                 

                
                $cont = 2;
                foreach ($clientes as $cliente) {
                    //tutor
                    if($cliente->tutor != 0){
                        $tutor = $this->socios_model->get_socio($cliente->tutor);
                        $tutor->Id = '#'.$tutor->Id;
                    }else{
                        $tutor = new STDClass();
                        $tutor->Id = '';
                        $tutor->nombre = '';
                        $tutor->apellido = '';
                    }
                    //categoria
                    $categoria = $this->general_model->get_cat($cliente->categoria);
                    //estado
                    if($cliente->suspendido == 0){
                        $estado = 'Activo';
                    }else{
                        $estado = 'Suspendido';
                    }
                    //saldo
                    $saldo = $this->pagos_model->get_socio_total($cliente->Id);

                    $this->phpexcel->setActiveSheetIndex(0)
                                ->setCellValue('A'.$cont, $cliente->Id)
                                ->setCellValue('B'.$cont, trim($cliente->apellido))
                                ->setCellValue('C'.$cont, trim($cliente->nombre))  
                                ->setCellValue('D'.$cont, $cliente->dni)  
                                ->setCellValue('E'.$cont, $cliente->domicilio)  
                                ->setCellValue('F'.$cont, $cliente->localidad)  
                                ->setCellValue('G'.$cont, $cliente->nacionalidad)  
                                ->setCellValue('H'.$cont, $cliente->nacimiento)
                                ->setCellValue('I'.$cont, trim($cliente->telefono))
                                ->setCellValue('J'.$cont, trim($cliente->mail))
                                ->setCellValue('K'.$cont, trim($cliente->celular))
                                ->setCellValue('L'.$cont, $tutor->Id.' - '.trim($tutor->apellido).' '.trim($tutor->nombre))
                                ->setCellValue('M'.$cont, $categoria->nomb)
                                ->setCellValue('N'.$cont, $cliente->descuento)
                                ->setCellValue('O'.$cont, $cliente->alta)
                                ->setCellValue('P'.$cont, $estado)
                                ->setCellValue('Q'.$cont, trim($cliente->observaciones))
                                ->setCellValue('R'.$cont, $saldo);                               
                                $cont ++;
                } 

                // Renombramos la hoja de trabajo
                  $this->phpexcel->getActiveSheet()->setTitle('Clientes');
                 
                foreach(range('A','R') as $columnID) {
                    $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }
                // configuramos el documento para que la hoja
                // de trabajo número 0 sera la primera en mostrarse
                // al abrir el documento
                $this->phpexcel->setActiveSheetIndex(0);
                 
                 
                // redireccionamos la salida al navegador del cliente (Excel2007)
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
                header('Cache-Control: max-age=0');
                 
                $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                $objWriter->save('php://output');
                break;

            case 'actividades':
                $this->load->model('actividades_model');
                $this->load->model('socios_model');
                $actividades = $this->actividades_model->get_act_asoc_all();
                    
                $titulo = "CVM - Actividades - ".date('d-m-Y');
                
                
                
                $this->load->library('PHPExcel');                
                $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                             ->setLastModifiedBy("Club Villa Mitre")
                                             ->setTitle("Listado")
                                             ->setSubject("Listado de Socios");
                
                $this->phpexcel->getActiveSheet()->getStyle('A1:F1')->getFill()->applyFromArray(
                    array(
                        'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array('rgb' => 'E9E9E9'),
                    )
                );
                 

                // agregamos información a las celdas
                $this->phpexcel->setActiveSheetIndex(0)
                            ->setCellValue('A1', 'Socio #')
                            ->setCellValue('B1', 'Apellido')
                            ->setCellValue('C1', 'Nombre')
                            ->setCellValue('D1', 'Actividad #')
                            ->setCellValue('E1', 'Actividad')
                            ->setCellValue('F1', 'Descuento');

                $cont = 2;
                foreach ($actividades as $actividad) {                    

                    $this->phpexcel->setActiveSheetIndex(0)
                                ->setCellValue('A'.$cont, $actividad->sid)
                                ->setCellValue('B'.$cont, $actividad->socio_apellido)  
                                ->setCellValue('C'.$cont, trim($actividad->socio_nombre))  
                                ->setCellValue('D'.$cont, $actividad->aid)  
                                ->setCellValue('E'.$cont, $actividad->actividad_nombre)  
                                ->setCellValue('F'.$cont, $actividad->descuento);                                                        
                                $cont ++;
                } 
                // Renombramos la hoja de trabajo
                $this->phpexcel->getActiveSheet()->setTitle('Actividades');
                 
                foreach(range('A','F') as $columnID) {
                    $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }
                // configuramos el documento para que la hoja
                // de trabajo número 0 sera la primera en mostrarse
                // al abrir el documento
                $this->phpexcel->setActiveSheetIndex(0);
                 
                 
                // redireccionamos la salida al navegador del cliente (Excel2007)
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
                header('Cache-Control: max-age=0');
                 
                $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                $objWriter->save('php://output');
                break;

            case 'cuenta_corriente':
                $this->load->model('pagos_model');                
                $facturaciones = $this->pagos_model->get_facturacion_all();
                    

                $titulo = "CVM - Cuentas Corrientes - ".date('d-m-Y');
                
                
                
                $this->load->library('PHPExcel');                
                $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                             ->setLastModifiedBy("Club Villa Mitre")
                                             ->setTitle("Listado")
                                             ->setSubject("Listado de Socios");
                
                $this->phpexcel->getActiveSheet()->getStyle('A1:F1')->getFill()->applyFromArray(
                    array(
                        'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array('rgb' => 'E9E9E9'),
                    )
                );
                 

                // agregamos información a las celdas
                $this->phpexcel->setActiveSheetIndex(0)
                            ->setCellValue('A1', 'Socio #')
                            // ->setCellValue('B1', 'Apellido')
                            // ->setCellValue('C1', 'Nombre')
                            ->setCellValue('B1', 'Facturación #')
                            ->setCellValue('C1', 'Fecha')
                            ->setCellValue('D1', 'Descripcion')
                            ->setCellValue('E1', 'Tipo (D/H)')
                            ->setCellValue('F1', 'Importe');

                $cont = 2;
                foreach ($facturaciones as $facturacion) {                    
                    if($facturacion->debe == 0){
                        $tipo = "H";
                        $importe = $facturacion->haber;
                    }else if($facturacion->haber == 0){
                        $tipo = "D";
                        $importe = -$facturacion->debe;
                    }

                    $this->phpexcel->setActiveSheetIndex(0)
                                ->setCellValue('A'.$cont, $facturacion->sid)
                                // ->setCellValue('B'.$cont, $facturacion->apellido)  
                                // ->setCellValue('C'.$cont, trim($facturacion->nombre))  
                                ->setCellValue('B'.$cont, $facturacion->Id)  
                                ->setCellValue('C'.$cont, $facturacion->date)  
                                ->setCellValue('D'.$cont, str_replace('Detalle',' Detalle',strip_tags($facturacion->descripcion)))
                                ->setCellValue('E'.$cont, $tipo)  
                                ->setCellValue('F'.$cont, $importe);                                                        
                                $cont ++;
                } 
                // Renombramos la hoja de trabajo
                $this->phpexcel->getActiveSheet()->setTitle('Cuentas Corrientes');
                 
                foreach(range('A','F') as $columnID) {
                    $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }
                // configuramos el documento para que la hoja
                // de trabajo número 0 sera la primera en mostrarse
                // al abrir el documento
                $this->phpexcel->setActiveSheetIndex(0);
                 
                 
                // redireccionamos la salida al navegador del cliente (Excel2007)
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
                header('Cache-Control: max-age=0');
                 
                $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                $objWriter->save('php://output');
                break;
            
            default:                
                $this->load->view('imprimir/index',$data);
                $this->load->view('imprimir/exportar',$data);
                $this->load->view('imprimir/foot');
                break;
        }
    }

    public function carnets($sid=0, $carnet=0){
	// TODO Sacar los parametros de entrada y dejar solo por POST
        if ( $_POST ) {
		$sid = $_POST['sid'];
       		$carnet = $_POST['tipo_carnet'];
	}

        if(!$sid){die;}

        $this->_imprime_papel( $sid, $carnet );
    }
    
    function _imprime_papel( $sid, $carnet ) {

        $this->load->model('socios_model');
        $this->load->model('pagos_model');

        $socio = $this->socios_model->get_socio($sid);
        $cupon = $this->pagos_model->get_cupon($sid);
        $monto = $this->pagos_model->get_monto_socio($sid)['total'];

        $soc_carnets[] = array('socio'=>$socio, 'cupon'=>$cupon, 'monto'=>$monto);
        $data['socios'] = $soc_carnets;
        $data['carnet'] = $carnet;

	$this->load->view("imprimir-carnets-lote",$data);

    }


    public function listado($listado)
    {
        $data['listado'] = $listado;
        $this->load->view('imprimir/index',$data);
        switch ($listado) {
            case 'actividades':
                $this->load->model('actividades_model');
                $data['actividades'] = $this->actividades_model->get_actividades();
                $data['comisiones'] = $this->actividades_model->get_comisiones();
                $data['comision_sel'] = '';
                $data['actividad_sel'] = '';
                $data['render_cat'] = false;
                $this->load->view('imprimir/actividades',$data);
                break;

            case 'profesores':
                $this->load->model('actividades_model');
                $data['profesores'] = $this->actividades_model->get_profesores();
                $this->load->view('imprimir/profesores',$data);
                break;

            case 'usuarios_suspendidos':
                $this->load->model('pagos_model');
                $data['socios'] = $this->pagos_model->get_usuarios_suspendidos();
                $this->load->view('imprimir/usuarios_suspendidos',$data);
                break;

            case 'socios':
                $this->load->view('imprimir/socios',$data);
                break;

            case 'seguro':
                $this->load->model('actividades_model');
                $data['socios'] = $this->actividades_model->get_socios_seguro();
                $this->load->view('imprimir/seguro',$data);
                break;

            case 'categorias':
                $this->load->model('general_model');
                $data['actividades'] = $this->general_model->get_cats();                
                $this->load->view('imprimir/categorias',$data);
                break;

            case 'morosos':                
                $data['baseurl'] = base_url();                
                $this->load->model('pagos_model');
                $data['morosos'] = false;
                $this->load->model('actividades_model');
                $data['comision_sel'] = '';
                $data['actividad_sel'] = '';
                $data['comisiones'] = $this->actividades_model->get_comisiones();
                $data['actividades'] = $this->actividades_model->get_actividades();
                $this->load->view('imprimir/morosos',$data);
                break;

            case 'financiacion':
                $this->load->model('pagos_model');
                $data['socios'] = $this->pagos_model->get_socios_financiados();
                $this->load->view('imprimir/financiacion',$data);                
                break;

             case 'becas':
                $this->load->model('actividades_model');
                $this->load->model('pagos_model');
                $actividad = false;                
                $actividad = $this->uri->segment(4);
                $data['socios'] = false;
                if($actividad){
                    $data['socios'] = $this->pagos_model->get_becas($actividad);
                }
                $data['a_actual'] = $actividad;
                $data['actividades'] = $this->actividades_model->get_actividades();
                $this->load->view('imprimir/becas',$data);             
                break;
            
            case 'sin_actividades':
                $this->load->model('pagos_model');
                $data['socios'] = $this->pagos_model->get_sin_actividades();
                $this->load->view('imprimir/sin_actividades',$data);
                break;

            default:

                break;
        }
        $this->load->view('imprimir/foot');
    }

    public function cobros($action='',$fecha1=false,$fecha2=false)
    {        
        $data = array();
        $this->load->model('pagos_model');
        $this->load->view('imprimir/index',$data);
        switch ($action) {
            case 'ingresos':
                $data['ingresos'] = false;
                if($fecha1 && $fecha2){
                    $data['ingresos'] = $this->pagos_model->get_ingresos($fecha1,$fecha2);
                }
                $data['fecha1'] = $fecha1;
                $data['fecha2'] = $fecha2;
                $this->load->view('imprimir/ingresos', $data, FALSE);
                break;

            case 'actividades':
                $data['cobros'] = false;
                $data['actividad_s'] = false;
                $fechas = $this->input->post('daterange');
                $actividad = $this->input->post('actividad');                
                $categoria = $this->input->post('categoria'); 
                $data['categoria'] = $categoria;

                if($fechas){
                    $fecha = explode(' - ', $fechas);
                    $fecha1 = $fecha[0];
                    $fecha2 = $fecha[1];
                }
                if($fecha1 && $fecha2){
                    if($actividad != '-1'){
                        $data['cobros'] = $this->pagos_model->get_cobros_actividad($fecha1,$fecha2,$actividad,$categoria);
                        $data['actividad_s'] = $actividad;                        
                    }else{
                        $data['cobros'] = $this->pagos_model->get_cobros_cuota($fecha1,$fecha2,$categoria);
                        $data['actividad_s'] = '-1';
                    }
                }                
                $data['fecha1'] = $fecha1;
                $data['fecha2'] = $fecha2;
                $this->load->model('actividades_model');
                $data['actividades'] = $this->actividades_model->get_actividades();
                $data['actividad_info'] = $this->actividades_model->get_actividad($actividad);
                $this->load->view('imprimir/actividades-cobros', $data, FALSE);
                break;

            case 'anterior':
                $this->load->model('actividades_model');
                $id = $this->uri->segment(4);
                $data['actividad'] = new STDClass();
                $data['actividad']->Id = false;
                if($id){
                    $data['socios'] = $this->pagos_model->get_pagos_actividad_anterior($id);
                    $data['actividad'] = $this->actividades_model->get_actividad($id);
                }else{
                    $data['socios'] = false;                    
                }
                $data['actividades'] = $this->actividades_model->get_actividades();
                $this->load->view('imprimir/anterior',$data);
                break;

            case 'cuentadigital':
                $data['ingresos'] = false;
                if($fecha1 && $fecha2){
                    $data['ingresos'] = $this->pagos_model->get_ingresos_cuentadigital($fecha1,$fecha2);
                }
                $data['fecha1'] = $fecha1;
                $data['fecha2'] = $fecha2;
                $this->load->view('imprimir/cuentadigital', $data, FALSE);
                break;
            
            default:
                # code...
                break;
        }
        $this->load->view('imprimir/foot');

    }

    function generar($listado,$id){
        switch ($listado) {           
            case 'actividades':
                $this->load->model('pagos_model');
                $this->load->model('actividades_model');
		if ( $id > 0 ) {
                	$data['actividad'] = $this->actividades_model->get_actividad($id);
			$data['comision'] = '';
			$data['comision_sel'] = '';
			$data['actividad_sel'] = $id;
                	$data['socios'] = $this->pagos_model->get_pagos_actividad($id);
		} else {
                	$data['actividad'] = '';
			$data['comision'] = $this->actividades_model->get_comision(-$id);
			$data['comision_sel'] = -$id;
			$data['actividad_sel'] = '';
                	$data['socios'] = $this->pagos_model->get_pagos_comision(-$id);
		}
                $this->load->view('imprimir/actividades_listado',$data);
                break;

            case 'morosos':
                $this->load->model('pagos_model');
                $this->load->model('actividades_model');
		$data['filtro'] = $id;
		if ( $id == "cs" ) {
                        $data['actividad'] = array("id"=>"cs", "nombre"=>"Cuota Social");
                        $data['comision'] = '';
                        $data['comision_sel'] = '';
                        $data['actividad_sel'] = $id;
                        $data['morosos'] = $this->pagos_model->get_morosos($id);
		} else {
                	if ( $id > 0 ) {
                        	$data['actividad'] = $this->actividades_model->get_actividad($id);
                        	$data['comision'] = '';
                        	$data['comision_sel'] = '';
                        	$data['actividad_sel'] = $id;
                        	$data['morosos'] = $this->pagos_model->get_morosos($id);
                	} else {
                        	$data['actividad'] = '';
                        	$data['comision'] = $this->actividades_model->get_comision(-$id);
                        	$data['comision_sel'] = -$id;
                        	$data['actividad_sel'] = '';
                        	$data['morosos'] = $this->pagos_model->get_morosos($id);
                	}
		}
                $this->load->view('imprimir/morosos_listado',$data);
                break;


            case 'profesores':
                $this->load->model('pagos_model');
                $this->load->model('actividades_model');
                $data['profesor'] = $this->actividades_model->get_profesor($id);
                $data['socios'] = $this->pagos_model->get_pagos_profesor($id);
                $this->load->view('imprimir/profesores_listado',$data);
                break;

            case 'socios':
                $this->load->model('pagos_model');
                if($id == 'activos'){
                    $data['id'] = $id;
                    $data['titulo'] = "Socios Activos";
                    $data['socios'] = $socios = $this->pagos_model->get_socios_activos();
                    foreach ($socios as $socio) {
			$socio->fijocel = "F: ".$socio->telefono." C:".$socio->celular;
                        $socio->deuda = $this->pagos_model->get_ultimo_pago_socio($socio->Id);
                        /* Modificado AHG para manejo de array en PHP 5.3 que tengo en mi maquina */
			            $array_ahg = $this->pagos_model->get_monto_socio($socio->Id);
                        $socio->cuota = $array_ahg['total'];
                        /* Fin Modificacion AHG */
                    }
                    $this->load->view('imprimir/socios_listado',$data); 
                }else if($id == 'suspendidos'){
                    $data['id'] = $id;
                    $data['titulo'] = "Socios Suspendidos";                    
                    $data['socios'] = $socios = $this->pagos_model->get_usuarios_suspendidos();
                    foreach ($socios as $socio) {
			$socio->fijocel = "F: ".$socio->telefono." C:".$socio->celular;
                        $socio->deuda = $this->pagos_model->get_ultimo_pago_socio($socio->Id);
                        /* Modificado AHG para manejo de array en PHP 5.3 que tengo en mi maquina */
			            $array_ahg = $this->pagos_model->get_monto_socio($socio->Id);
                        $socio->cuota = $array_ahg['total'];
                        /* Fin Modificacion AHG */
                    }
                    $this->load->view('imprimir/socios_listado',$data); 
                }                
                break;

            case 'categorias':
                $this->load->model('pagos_model');
                $this->load->model('general_model');
                $data['categoria'] = $this->general_model->get_cat($id);               
                $data['socios'] = $this->pagos_model->get_pagos_categorias($id);
                $this->load->view('imprimir/categorias_listado',$data);
                break;

            case 'socios':

                break;
            
            default:

                break;
        }
    }

/*
AHG Comentado 20170105 porque no se usa..... creo
    function morosos(){
        $this->load->model('pagos_model');
        $meses = $this->uri->segment(3);
        $act = $this->uri->segment(4) ?: null;
        if(!$meses){ $meses = 6; }
        if($meses){
        $data['morosos'] = $this->pagos_model->get_morosos($meses, $act);
            if($act){
                $this->load->model('actividades_model');
                $actividad = $this->actividades_model->get_actividad($this->uri->segment(4))->nombre;                        
            }else{
                $actividad = "Todas";
            }
        $data['meses'] = $meses;
        $data['actividad'] = $actividad;
        $this->load->view('imprimir-morosos',$data);
        }

    }
*/

    public function actividades($id=false){
        /*$this->load->model('pagos_model');
        $this->load->model('actividades_model');
        $act = $this->uri->segment(3) ?: null;        
        if(!$act){die;}
        $actividad = $this->actividades_model->get_actividad($this->uri->segment(3))->nombre;
        $data['actividad'] = $actividad;
        $data['socios'] = $this->pagos_model->get_pagos_actividad($act);
        $this->load->view('imprimir-actividades',$data);        */
        if(!$id){ return false; }
        $this->load->model('pagos_model');
        $this->load->model('actividades_model');
        $data['actividad'] = $this->actividades_model->get_actividad($id);
        $data['profesor'] = $this->actividades_model->get_profesor($data['actividad']->profesor);
        $data['socios'] = $this->pagos_model->get_pagos_actividad($id);
        $this->load->view('imprimir/actividades_listado',$data);    
    }

    public function carnet(){
        $this->load->model('socios_model');        
        $this->load->model('pagos_model');        
        $id = $this->uri->segment(3) ?: null;        
        $id = $this->uri->segment(3) ?: null;        
        if(!$id){die;}
        $socio = $data['socio'] = $this->socios_model->get_socio($id);
        $data['cupon'] = $this->pagos_model->get_cupon($id);
        $monto = $this->pagos_model->get_monto_socio($id);
        $data['monto'] = $monto = $monto['total'];
// TODO en mi PC no funciona el buscar cupon y ver bien con categorias $0 (prensa)
	if ( $socio->categoria != 11 ) {
        	if($data['cupon']->monto == 0 || $data['cupon']->monto != $monto){
            	//$this->load->model('socios_model');
            	//$socio = $this->socios_model->get_socio($_POST['id']);
            	$cupon = $this->cuentadigital($id,$socio->nombre.' '.$socio->apellido,$monto);

            	if($cupon){                                    
                	$this->load->model('pagos_model');
                	$cupon_id = $this->pagos_model->generar_cupon($id,$monto,$cupon);
                	$data = base64_decode($cupon['image']);
                	$img = imagecreatefromstring($data);
                    	if ($img !== false) {
                        	@header('Content-Type: image/png');
                        	imagepng($img,'images/cupones/'.$cupon_id.'.png',0);
                        	imagedestroy($img);
                        	redirect(base_url().'imprimir/carnet/'.$id);
                    	} else {
                        	echo 'Ocurrió un error.';
                    	}                
            	}
        	$data['cupon'] = $this->pagos_model->get_cupon($id);
        	}
	}

        $fmto = $this->uri->segment(4);
	if ( !$fmto ) {
        	$this->load->view('imprimir-carnet',$data);
	} else {
        	$this->load->view('imprimir-carnet2',$data);
	}
    }
    public function platea(){
        $this->load->model('actividades_model');        
        $id = $this->uri->segment(3) ?: null;        
        if(!$id){die;}
	$platea = $this->actividades_model->get_platea($id);
	$data['platea'] = $platea;
        $this->load->view('imprimir-platea',$data);
    }

    public function carnet_plastico_frente(){
        $tipo_carnet = $_POST['tipo_carnet'];
        $data['tipo_carnet'] = $tipo_carnet;
        $this->load->view('imprimir-plastico-frente',$data);

 	$this -> load -> library("FPDF/fpdf_card");

 	$card = new FPDF_Card();

 	// IMAGEN
	switch ( $tipo_carnet ) {

                        case 1:
                                $logo=BASEPATH."../images/carnet-frente-new.png";
                                break;
                        // Prensa
                        case 2:
                                $logo=BASEPATH."../images/Prensa_Dorso_300.jpg";
                                break;
                        // Comercio
                        case 3:
                                $logo=BASEPATH."../images/Comercio_Dorso.jpg";
                                break;
                        // VM Racing
                        case 4:
                                $logo=BASEPATH."../images/VMRacing_Dorso.jpg";
                                break;
                        // Credencial Plastico
                        case 5:
                                $logo=BASEPATH."../images/Plastico_2021_Dorso.jpg";
                                break;
                        // Mutual 14 Agosto
                        case 6:
                                $logo=BASEPATH."../images/Mutual_14ago_Dorso.jpg";
                                break;
                        // Clasico Papel
                        default:
                                $logo=BASEPATH."../images/carnet-frente-new.png";
                                break;
	}
 	$card->Image($logo, 0, 0, 86, 54);

	// OUTPUT.
	$card->Output("I", "card.pdf", true);
	exit;
    }

    public function carnets_n(){
        $sids = $_POST['sid'];
        $carnet = $_POST['tipo_carnet'];
        $socios = explode(',', $sids);
        foreach ( $socios as $socio ) {
		$this->_imprime_papel($sid, $carnet);
        }
	exit;
    }

    public function carnet_plastico_n(){
        $sids = $_POST['sid'];
        $carnet = $_POST['tipo_carnet'];

        $this -> load -> library("FPDF/fpdf_card");
        $card = new FPDF_Card();

	$socios = explode(',', $sids);
	$salto=0;
	foreach ( $socios as $socio ) {
        	$this->_imprime_plastico($card, $socio, $carnet, $salto);
		$salto=1;
	}

        // OUTPUT.
        $card->Output("I", "card.pdf", true);

	exit;
    }

    public function carnet_plastico(){
        $sid = $_POST['sid'];
        $carnet = $_POST['tipo_carnet'];

        if(!$sid){die;}

        $this -> load -> library("FPDF/fpdf_card");
        $card = new FPDF_Card();

	$this->_imprime_plastico($card, $sid, $carnet, 0);

	// OUTPUT.
	$card->Output("I", "card.pdf", true);
	exit;
    }

     function _imprime_plastico($card, $sid, $carnet, $salto=0) {

        $this->load->model('general_model');
        $this->load->model('socios_model');
        $this->load->model('pagos_model');
	$socio = $this->socios_model->get_socio($sid);
	$cupon = $this->pagos_model->get_cupon($sid);
	$monto = $this->pagos_model->get_monto_socio($sid)['total'];


	// SALTO si viene el parametro seteado para impresion de "N" paginas
        if ( $salto > 0 ) { $card->addPage(); };

 	// DATOS
	$apynom = utf8_decode(trim($socio->nombre).' '.trim($socio->apellido));
	$linea0 = 20;
	if ( strlen($apynom) <= 24 ) {
		$card->addText( 28, $linea0, $apynom, 'B', 10);
		$card->addText( 28, $linea0+5, 'Nro Socio: '.number_format($socio->Id, 0, '', '.'), 'B', 8);
		//$card->addText( 43, $linea0+5, number_format($socio->Id, 0, '', '.'),  'B', 8);
		$card->addText( 28, $linea0+10, 'DNI: '.number_format($socio->dni, 0, '', '.'), 'B', 8);
		//$card->addText( 43, $linea0+10, number_format($socio->dni, 0, '', '.'), 'B', 8);
	} else {
		$card->addText( 28, $linea0, utf8_decode($socio->nombre), 'B', 10);
		$card->addText( 28, $linea0+5, utf8_decode($socio->apellido), 'B', 10);
		$card->addText( 28, $linea0+10, 'Nro Socio: '.number_format($socio->Id, 0, '', '.'), 'B', 9);
		//$card->addText( 50, $linea0+10, number_format($socio->Id, 0, '', '.'),  'B', 9);
		$card->addText( 28, $linea0+15, 'DNI: '.number_format($socio->dni, 0, '', '.'), 'B', 9);
		//$card->addText( 50, $linea0+15, number_format($socio->dni, 0, '', '.'), 'B', 9);
	}

 	// FOTO
	if(file_exists( BASEPATH."../images/socios/".$socio->Id.".jpg" )){
		$imagen = BASEPATH."../images/socios/".$socio->Id.".jpg";
 		$card->Image($imagen, 2, $linea0-4, 24, 18);
	}

	// PIE CARNET
	$cupon = $this->pagos_model->get_cupon($sid);
	if ( $cupon ) {
		$card->addBarcode($cupon->barcode, array("x" => 29, "y" => 37, "w" => 35, "h" => 13));
	}

/*
	if( file_exists(BASEPATH."../images/cupones/".$cupon->Id.".png") ){
		$barra = BASEPATH."../images/cupones/".$cupon->Id.".png";
 		$card->Image($barra, 24, 35, 40, 15);
	}
*/

	$socio->dni;
        QRcode::png($socio->dni,BASEPATH."../images/temp/QR_".$socio->dni.".png",QR_ECLEVEL_L,10,2);
        $qr=BASEPATH."../images/temp/QR_".$socio->dni.".png";

	if( file_exists($qr) ){
 		$card->Image($qr, 68, 35, 15, 15);
	}

	// Meto registro en carnets
	$carnet = $this->socios_model->busco_carnet($sid);
	if ( !$carnet || $carnet->dias > 30 ) {
		$this->socios_model->imprimo_carnet($sid);

		// Facturo costo de la credencial
		// BUsco valor de la credencial en el ID=3 del config
        	$config = $this->general_model->get_config(3);
        	$valor_cred = $config->interes_mora;

		if ( $socio->tutor == 0 ) {
			$tutor = $sid;
		} else {
			$tutor = $socio->tutor;
		}

		$this->pagos_model->registrar_pago('debe',$tutor,$valor_cred,'Costo por impresión de credencial plástica','cs',0,0);

	}

    }

    function cuentadigital($sid, $nombre, $precio, $venc=null) 
    {
        $this->config->load("cuentadigital");
        $cuenta_id = $this->config->item('cd_id');
        $nombre = substr($nombre,0,40);
        $concepto  = $nombre.' ('.$sid.')';
        $repetir = true;
        $count = 0;
        $result = false;
        if(!$venc){
            $url = 'https://www.cuentadigital.com/api.php?id='.$cuenta_id.'&codigo='.urlencode($sid).'&precio='.urlencode($precio).'&concepto='.urlencode($concepto).'&xml=1';
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

    public function socios_excel($type=''){
        $this->load->model('pagos_model'); 
        if($type=='suspendidos'){    
            $clientes = $this->pagos_model->get_usuarios_suspendidos();
            $titulo = "CVM - Socios Suspendidos - ".date('d-m-Y');
        }else{
            $clientes = $this->pagos_model->get_socios_activos();
            $titulo = "CVM - Socios Activos - ".date('d-m-Y');
        }
        foreach ($clientes as $cliente) {
            $cliente->deuda = $this->pagos_model->get_ultimo_pago_socio($cliente->Id);
            /* Modificado AHG para manejo de array en PHP 5.3 que tengo en mi maquina */
            $array_ahg = $this->pagos_model->get_monto_socio($cliente->Id);
            $cliente->cuota = $array_ahg['total'];
            /* Fin Modificacion AHG */
        }
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:H1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         
        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'SID')
                    ->setCellValue('B1', 'Nombre y Apellido')
                    ->setCellValue('C1', 'Teléfono')
                    ->setCellValue('D1', 'Domicilio')
                    ->setCellValue('E1', 'DNI')
                    ->setCellValue('F1', 'Fecha de Alta')                   
                    ->setCellValue('G1', 'Monto Adeudado')                   
                    ->setCellValue('H1', 'Meses Adeudados');




        $cont = 2;
        foreach ($clientes as $cliente) {        
            if($cliente->deuda){                      
                $hoy = new DateTime();
                $d2 = new DateTime($cliente->deuda->generadoel);                
                $interval = $d2->diff($hoy);
                $meses = $interval->format('%m');
                if($meses > 0){
                    $meses_a = "Debe ".$meses;
                    if($meses > 1){ 
                        $meses_a .= ' Meses';
                    }else{
                        $meses_a .= ' Mes';
                    }                    
                }else{
                    if( $hoy->format('%m') != $d2->format('%m') && $cliente->deuda->monto != '0.00' ){
                    $meses_a = "Saldo del mes anterior";
                    }else{                
                    $meses_a = "Cuota al Día";                
                    }
                }
            }else{            
                $meses_a = "Aún no se registró ningun pago";
            }                    
            

            if($cliente->deuda_monto < 0){
                $monto_a = "$ ".$cliente->deuda_monto*-1;        
            }else{    
                $monto_a = "Cuota al Día";        
            }
            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente->Id)
                        ->setCellValue('B'.$cont, trim($cliente->nombre).' '.trim($cliente->apellido))
                        ->setCellValue('C'.$cont, $cliente->telefono)
                        ->setCellValue('D'.$cont, $cliente->domicilio)
                        ->setCellValue('E'.$cont, $cliente->dni)
                        ->setCellValue('F'.$cont, date('d/m/Y',strtotime($cliente->alta)))
                        ->setCellValue('G'.$cont, $monto_a)
                        ->setCellValue('H'.$cont, $meses_a);
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','H') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }

    public function actividades_excel($id=''){
        
        $this->load->model('actividades_model');
        $this->load->model('pagos_model');

	if ( $id > 0 ) {
        	$actividad = $this->actividades_model->get_actividad($id);        
        	$clientes = $this->pagos_model->get_pagos_actividad($id);        
        	$titulo = "CVM - ".$actividad->nombre." - ".date('d-m-Y');
	} else {
        	$comision = $this->actividades_model->get_comision(-$id);        
        	$clientes = $this->pagos_model->get_pagos_comision(-$id);        
        	$titulo = "CVM - ".$comision->descripcion." - ".date('d-m-Y');
	}
        
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:I1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         
        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Socio')
                    ->setCellValue('B1', 'Apellido y Nombre')
                    ->setCellValue('C1', 'DNI')
                    ->setCellValue('D1', 'Fecha de Nacimiento')
                    ->setCellValue('E1', 'Teléfono')
                    ->setCellValue('F1', 'Fecha de Alta')                  
                    ->setCellValue('G1', 'Observaciones')                  
                    ->setCellValue('H1', 'Monto Adeudado')                  
                    ->setCellValue('I1', 'Meses Adeudados')
                    ->setCellValue('J1', 'Estado');                    
        
        $cont = 2;
        foreach ($clientes as $cliente) {

            if($cliente->deuda){                      
                    $hoy = new DateTime();
                    $d2 = new DateTime($cliente->deuda->generadoel);                
                    $interval = $d2->diff($hoy);
                    $meses = $interval->format('%m');
                    if($meses > 0){
                        $adeudados = "Debe ".$meses;
                        if($meses > 1){ 
                            $adeudados .= ' Meses';
                        }else{
                            $adeudados .= ' Mes';
                        }                    
                    }else{
                        if( $hoy->format('%m') == $d2->format('%m')){
                            $adeudados = "Mes Actual";     
                        }else{                    
                            $adeudados = "Cuota al Día";           
                        }                       
                    }
                }else{                    
                    $adeudados = "Cuota al Día";                    
                }
                if($cliente->suspendido == 1){ 
                    $estado = 'SUSPENDIDO'; 
                }else{ 
                    $estado = 'ACTIVO'; 
                }

            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente->Id)
                        ->setCellValue('B'.$cont, $cliente->socio)
                        ->setCellValue('C'.$cont, $cliente->dni)
                        ->setCellValue('D'.$cont, date('d/m/Y',strtotime($cliente->nacimiento)))
                        ->setCellValue('E'.$cont, $cliente->fijocel)
                        ->setCellValue('F'.$cont, date('d/m/Y',strtotime($cliente->alta)))                     
                        ->setCellValue('G'.$cont, $cliente->observaciones)
                        ->setCellValue('H'.$cont, $cliente->monto_adeudado*-1)
                        ->setCellValue('I'.$cont, $adeudados)                    
                        ->setCellValue('J'.$cont, $estado);                        
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','J') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }

    public function cuentadigital_excel($fecha1='',$fecha2=''){
        
        $this->load->model('pagos_model');
        if($fecha1 && $fecha2){
		$clientes = $this->pagos_model->get_ingresos_cuentadigital($fecha1,$fecha2);
        } else {
		die;
	}

        $titulo = "CVM - Ingresos Cta Digital entre ".$fecha1." y ".$fecha2." - ".date('d-m-Y');
        
        $this->load->library('PHPExcel');
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:F1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         
        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Fecha')
                    ->setCellValue('B1', 'SID')
                    ->setCellValue('C1', 'Nombre y Apellido')
                    ->setCellValue('D1', 'Importe');
        
        $cont = 2;
        foreach ($clientes as $cliente) {   

	    if ( $cliente->socio ) {
		$xsocio = trim($cliente->socio->nombre).", ".trim($cliente->socio->apellido);
	    } else {
		$xsocio = "Inexistente";
            }
            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, date('d/m/Y', strtotime($cliente->fecha)))
                        ->setCellValue('B'.$cont, $cliente->sid)
                        ->setCellValue('C'.$cont, $xsocio)
                        ->setCellValue('D'.$cont, $cliente->monto);
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','D') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }

    public function categorias_excel($id=''){
        
        $this->load->model('pagos_model');
        $this->load->model('general_model');
        $categoria = $this->general_model->get_cat($id);               
        $clientes = $this->pagos_model->get_pagos_categorias($id);
        
        $titulo = "CVM - ".$categoria->nomb." - ".date('d-m-Y');
        
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:F1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         
        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Socio')
                    ->setCellValue('B1', 'Nombre y Apellido')
                    ->setCellValue('C1', 'Teléfono')
                    ->setCellValue('D1', 'DNI')
                    ->setCellValue('E1', 'Fecha de Alta')                    
                    ->setCellValue('F1', 'Meses Adeudados');                    
        
        $cont = 2;
        foreach ($clientes as $cliente) {   

            $meses = @round($cliente->deuda/$cliente->cuota);
            if($meses < 0){
                $meses = $meses * -1;
                if($meses == 1){
                    $adeudados = "Debe ".$meses." mes";                        
                }else{
                    $adeudados = "Debe ".$meses." meses";                        
                }
            }else if($meses > 0){
                if($meses == 1){
                    $adeudados = $meses." mes pagado por adelantado";
                }else{
                    $adeudados = $meses." meses pagados por adelantado";                
                }
            }else if($meses == 0){
                $adeudados = "Socio sin Deuda";
            }

            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente->Id)
                        ->setCellValue('B'.$cont, trim($cliente->nombre).' '.trim($cliente->apellido))
                        ->setCellValue('C'.$cont, $cliente->telefono)
                        ->setCellValue('D'.$cont, $cliente->dni)
                        ->setCellValue('E'.$cont, date('d/m/Y',strtotime($cliente->alta)))
                        ->setCellValue('F'.$cont, $adeudados);
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','F') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }
    
    public function ingresos_excel($fecha1='',$fecha2=''){
                    
        $this->load->model('pagos_model');
        $clientes = $data['ingresos'] = $this->pagos_model->get_ingresos($fecha1,$fecha2);
        
        $titulo = "CVM - Ingresos del ".date('d-m-Y',strtotime($fecha1))." al ".date('d-m-Y',strtotime($fecha2))." - ".date('d-m-Y');
        
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:G1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         
        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Facturado El')
                    ->setCellValue('B1', 'Pagado El')                                   
                    ->setCellValue('C1', 'Descripción')
                    ->setCellValue('D1', 'Monto')
                    ->setCellValue('E1', 'Pagado')
                    ->setCellValue('F1', 'Socio')                                
                    ->setCellValue('G1', 'Nombre y Apellido')                                
                    ->setCellValue('H1', 'Observaciones');
        
        $cont = 2;
        foreach ($clientes as $cliente) {        
            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, date('d/m/Y',strtotime($cliente->generadoel)))
                        ->setCellValue('B'.$cont, date('d/m/Y',strtotime($cliente->pagadoel)))
                        ->setCellValue('C'.$cont, trim(strip_tags($cliente->descripcion)))
                        ->setCellValue('D'.$cont, $cliente->monto)
                        ->setCellValue('E'.$cont, $cliente->pagado)              
                        ->setCellValue('F'.$cont, $cliente->sid)             
                        ->setCellValue('G'.$cont, trim($cliente->socio->nombre).' '.trim($cliente->socio->apellido))             
                        ->setCellValue('H'.$cont, $cliente->socio->observaciones);
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','H') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }

    public function cobros_actividad_excel($fecha1='',$fecha2='',$actividad='',$categoria=''){
                    
        $this->load->model('pagos_model');
        $this->load->model('actividades_model');

        if($actividad != '-1'){
            $clientes = $data['ingresos'] = $this->pagos_model->get_cobros_actividad($fecha1,$fecha2,$actividad,$categoria);
            $data['actividad_s'] = $actividad;                        
	    $actividad = $this->actividades_model->get_actividad($actividad);
	    $xact = $actividad->nombre;
        }else{
            $clientes = $data['ingresos'] = $this->pagos_model->get_cobros_cuota($fecha1,$fecha2,$categoria);
            $data['actividad_s'] = '-1';
	    $actividad=null;
	    $xact = "Cuota Social";
        }
        //$clientes = $data['ingresos'] = $this->pagos_model->get_cobros_actividad($fecha1,$fecha2,$actividad,$categoria);
        
        $titulo = "CVM - Ingresos del ".date('d/m/Y',strtotime($fecha1))." al ".date('d/m/Y',strtotime($fecha2))." - ".$xact." - ".date('d/m/Y');
        
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:G1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         
        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Facturado El')
                    ->setCellValue('B1', 'Pagado El')                    
                    ->setCellValue('C1', 'Monto')
                    ->setCellValue('D1', 'Activ/Seguro')
                    ->setCellValue('E1', 'Socio')                    
                    ->setCellValue('F1', 'Socio')                    
                    ->setCellValue('G1', 'Fecha de Nacimiento')
                    ->setCellValue('H1', 'Observaciones')
                    ->setCellValue('I1', 'Deuda');
        
        $cont = 2;        

        foreach ($clientes as $cliente) {

            if($cliente->deuda){                      
                $hoy = new DateTime();
                $d2 = new DateTime($cliente->deuda->generadoel);                
                $interval = $d2->diff($hoy);
                $meses = $interval->format('%m');
                if($meses > 0){
                    $deuda = 'Debe '.$meses;
                    if($meses > 1){ 
                        $deuda .= 'Meses';
                    }else{
                        $deuda .= 'Mes';
                    }
                    $deuda .= ' - $ '.$meses*$cliente->deuda->monto;                
                }else{
                    if( $hoy->format('%m') != $d2->format('%m') && $cliente->deuda->monto != '0.00' ){                
                        $deuda = 'Saldo del mes anterior';                    
                    }else{                                        
                        $deuda = 'Cuota al Día';                    
                    }
                }
            }else{
                $deuda = 'Cuota al Día';
            }

            if($cliente->tipo == 6){                      
		$concepto = "Seguro";
	    } else {
		if ( $xact == "Cuota Social" ) {
			$concepto = "Cuota Social";
		} else {
			$concepto = "Actividad";
		}
	    }

            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, date('d/m/Y',strtotime($cliente->generadoel)))
                        ->setCellValue('B'.$cont, date('d/m/Y',strtotime($cliente->pagadoel)))
                        ->setCellValue('C'.$cont, $cliente->pagado)
                        ->setCellValue('D'.$cont, $concepto)              
                        ->setCellValue('E'.$cont, $cliente->sid)
                        ->setCellValue('F'.$cont, trim($cliente->socio->nombre).' '.trim($cliente->socio->apellido))
                        ->setCellValue('G'.$cont, date('d/m/Y',strtotime($cliente->socio->nacimiento)))
                        ->setCellValue('H'.$cont, $cliente->socio->observaciones)
                        ->setCellValue('I'.$cont, $deuda);                     
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','G') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }

    public function morosos_excel($actividad=''){
                    
        $this->load->model('pagos_model');
        $this->load->model('actividades_model');
                
	if($actividad){
		$clientes = $this->pagos_model->get_morosos($actividad);
            	$titulo = "CVM - Morosos - ".date('d-m-Y');
	} else {
		return false;
	}

        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:C1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         
        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'DNI')
                    ->setCellValue('B1', 'ID')
                    ->setCellValue('C1', 'Nombre')
                    ->setCellValue('D1', 'Teléfonos')
                    ->setCellValue('E1', 'Domicilio')
                    ->setCellValue('F1', 'Actividad')                   
                    ->setCellValue('G1', 'Estado')                   
                    ->setCellValue('H1', 'Deuda Cta Social')                   
                    ->setCellValue('I1', 'Último Pago Cta Social')
                    ->setCellValue('J1', 'Deuda Actividad')
                    ->setCellValue('K1', 'Último Pago Actividad');
        
        $cont = 2;
        foreach ($clientes as $cliente) {        

		switch ( $cliente['estado'] ) {
			case 1: $xestado="SUSP"; break;
			case 0: $xestado="ACTI"; break;
		}

            	$this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente['dni'])
                        ->setCellValue('B'.$cont, $cliente['sid'])
                        ->setCellValue('C'.$cont, $cliente['apynom'])
                        ->setCellValue('D'.$cont, $cliente['telefono'])
                        ->setCellValue('E'.$cont, $cliente['domicilio'])
                        ->setCellValue('F'.$cont, $cliente['actividad'])
                        ->setCellValue('G'.$cont, $xestado)
                        ->setCellValue('H'.$cont, $cliente['deuda_cuota']*-1)
                        ->setCellValue('I'.$cont, date('d/m/Y',strtotime($cliente['gen_cuota'])))
                        ->setCellValue('J'.$cont, $cliente['deuda_activ']*-1)
                        ->setCellValue('K'.$cont, date('d/m/Y',strtotime($cliente['gen_activ'])));                       
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','E') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }

    public function anterior_excel($id=''){
            
        $this->load->model('pagos_model');
        $this->load->model('actividades_model');
        if($id != ''){
            $clientes = $this->pagos_model->get_pagos_actividad_anterior($id);
            $actividad = $this->actividades_model->get_actividad($id);       
            $titulo = "CVM - Deuda Anterior - ".$actividad->nombre." - ".date('d-m-Y');
        }else{
            die();
        }
        
        
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:G1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         

        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Socio')
                    ->setCellValue('B1', 'Nombre y Apellido')
                    ->setCellValue('C1', 'Teléfono')
                    ->setCellValue('D1', 'DNI')
                    ->setCellValue('E1', 'Fecha de Nacimiento')
                    ->setCellValue('F1', 'Fecha de Alta')                 
                    ->setCellValue('G1', 'Sin deuda hasta el 30/04/2015');
        
        $cont = 2;
        foreach ($clientes as $cliente) {        
        if($cliente->deuda == 0){
            $deuda = "Si";
        }else{
            $deuda = "No";
        }
            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente->Id)  
                        ->setCellValue('B'.$cont, $cliente->socio)
                        ->setCellValue('C'.$cont, $cliente->telefono)  
                        ->setCellValue('D'.$cont, $cliente->dni)  
                        ->setCellValue('E'.$cont, date('d/m/Y',strtotime($cliente->nacimiento)))
                        ->setCellValue('F'.$cont, date('d/m/Y',strtotime($cliente->date)))
                        ->setCellValue('G'.$cont, $deuda);
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','G') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }
    public function financiacion_excel(){
            
        $this->load->model('pagos_model');
        $this->load->model('actividades_model');
        
            $clientes = $this->pagos_model->get_socios_financiados();            
            $titulo = "CVM - Financiación  - ".date('d-m-Y');
      
        
        
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:G1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         

        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Nombre y Apellido')
                    ->setCellValue('B1', 'Socio')
                    ->setCellValue('C1', 'Detalle')
                    ->setCellValue('D1', 'Cuotas')
                    ->setCellValue('E1', 'Cuota Actual')
                    ->setCellValue('F1', 'Monto')                 
                    ->setCellValue('G1', 'Inicio - Fin');
        
        $cont = 2;
        foreach ($clientes as $cliente) {            
            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente->nombre.' '.$cliente->apellido)
                        ->setCellValue('B'.$cont, $cliente->sid)  
                        ->setCellValue('C'.$cont, $cliente->detalle)  
                        ->setCellValue('D'.$cont, $cliente->cuotas)  
                        ->setCellValue('E'.$cont, $cliente->actual)  
                        ->setCellValue('F'.$cont, $cliente->monto)  
                        ->setCellValue('G'.$cont, $cliente->inicio.' | '.$cliente->fin);
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','G') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }

    public function becas_excel($actividad=false){       
        $this->load->model('pagos_model');
        $this->load->model('actividades_model');
        if($actividad){
            $clientes = $this->pagos_model->get_becas($actividad);
            if($actividad != '-1'){
                $a = $this->actividades_model->get_actividad($actividad);
            }else{
                $a = new STDClass();
                $a->nombre = 'Cuota Social';
            }
            $titulo = "CVM - Becados - ".$a->nombre." - ".date('d-m-Y');
        }else{
            die();
        }
        
        
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:G1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         

        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Socio')
                    ->setCellValue('B1', 'Nombre y Apellido')
                    ->setCellValue('C1', 'Teléfono')
                    ->setCellValue('D1', 'DNI')
                    ->setCellValue('E1', 'Fecha de Nacimiento')
                    ->setCellValue('F1', 'Fecha de Alta')                 
                    ->setCellValue('G1', 'Porcentaje Becado');
        
        $cont = 2;
        foreach ($clientes as $cliente) {            
            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente->Id)  
                        ->setCellValue('B'.$cont, trim($cliente->nombre).' '.trim($cliente->apellido))
                        ->setCellValue('C'.$cont, $cliente->telefono)  
                        ->setCellValue('D'.$cont, $cliente->dni)  
                        ->setCellValue('E'.$cont, date('d/m/Y',strtotime($cliente->nacimiento)))
                        ->setCellValue('F'.$cont, date('d/m/Y',strtotime($cliente->alta)))
                        ->setCellValue('G'.$cont, $cliente->descuento.'%');
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','G') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }

    public function seguro_excel(){       
        $this->load->model('actividades_model');
        $clientes = $this->actividades_model->get_socios_seguro();
        $titulo = "CVM - Listado para Seguro - ".date('d-m-Y');
        
        $this->load->library('PHPExcel');
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:D1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         

        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Actividad')
                    ->setCellValue('B1', 'Nombre y Apellido')
                    ->setCellValue('C1', 'DNI')
                    ->setCellValue('D1', 'Fecha de Nacimiento');
        
        $cont = 2;
        foreach ($clientes as $cliente) {            
            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente->descr_actividad)
                        ->setCellValue('B'.$cont, $cliente->apynom)  
                        ->setCellValue('C'.$cont, $cliente->dni)  
                        ->setCellValue('D'.$cont, $cliente->nacimiento);
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Socios_Seguro');
         
        foreach(range('A','D') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
    
    // end: setExcel
    }

    public function sin_actividad_excel(){       
        $this->load->model('pagos_model');                
        $clientes = $this->pagos_model->get_sin_actividades();
            
        $titulo = "CVM - Socios Sin Actividades Asociadas - ".date('d-m-Y');
        
        
        
        $this->load->library('PHPExcel');
        //$this->load->library('PHPExcel/IOFactory');
        // configuramos las propiedades del documento
        $this->phpexcel->getProperties()->setCreator("Club Villa Mitre")
                                     ->setLastModifiedBy("Club Villa Mitre")
                                     ->setTitle("Listado")
                                     ->setSubject("Listado de Socios");
        
        $this->phpexcel->getActiveSheet()->getStyle('A1:G1')->getFill()->applyFromArray(
            array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'E9E9E9'),
            )
        );
         

        // agregamos información a las celdas
        $this->phpexcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Socio')
                    ->setCellValue('B1', 'Nombre y Apellido')
                    ->setCellValue('C1', 'Teléfono')
                    ->setCellValue('D1', 'DNI')
                    ->setCellValue('E1', 'Fecha de Nacimiento')
                    ->setCellValue('F1', 'Fecha de Alta');                 
        
        $cont = 2;
        foreach ($clientes as $cliente) {            
            $this->phpexcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$cont, $cliente->Id)  
                        ->setCellValue('B'.$cont, trim($cliente->nombre).' '.trim($cliente->apellido))
                        ->setCellValue('C'.$cont, $cliente->telefono)  
                        ->setCellValue('D'.$cont, $cliente->dni)  
                        ->setCellValue('E'.$cont, date('d/m/Y',strtotime($cliente->nacimiento)))
                        ->setCellValue('F'.$cont, date('d/m/Y',strtotime($cliente->alta)));                        
                        $cont ++;
        } 
        // Renombramos la hoja de trabajo
        $this->phpexcel->getActiveSheet()->setTitle('Clientes');
         
        foreach(range('A','G') as $columnID) {
            $this->phpexcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        // configuramos el documento para que la hoja
        // de trabajo número 0 sera la primera en mostrarse
        // al abrir el documento
        $this->phpexcel->setActiveSheetIndex(0);
         
         
        // redireccionamos la salida al navegador del cliente (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo.'.xlsx"');
        header('Cache-Control: max-age=0');
         
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
        $objWriter->save('php://output');
         
    
    // end: setExcel
    }
}

?>

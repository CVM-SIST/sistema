<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Estado extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
	}

	public function index()
	{
		$data = array();
		$this->load->view('estado/index', $data, FALSE);
	}

	public function ver() {
		$this->load->model('socios_model');
		$this->load->model('pagos_model');
		$dni = $this->uri->segment(3);
		$socio = $this->socios_model->get_socio_by_dni($dni);
		$cupon_cobrod = $this->pagos_model->get_cupong_cobrod_by_sid($socio->Id);
		$data = array();
	        $data['socio'] = $socio;

		$this->load->view('estado/index2', $data, FALSE);

		if($socio){
			$socio->deuda = $this->pagos_model->get_ultimo_pago_socio($socio->Id);
			$socio->cuota = $this->pagos_model->get_monto_socio($socio->Id)['total'];			
			$socio->facturacion = $this->pagos_model->get_facturacion($socio->Id);
			if( $socio->socio_n == 0 ) {
				?> <h2>#<?=$socio_Id?> - <?=$socio->apellido?>, <?=$socio->nombre?></h2> <?
			}else{
				?><h2>#<?=$socio_Id?> - (nro. original <?=$socio->socio_n?>  <?=$socio->apellido?>, <?=$socio->nombre?></h2>	<?
			}
			if($socio->deuda){                      
	            $hoy = new DateTime();
	            $d2 = new DateTime($socio->deuda->generadoel);                
	            $interval = $d2->diff($hoy);
	            $meses = $interval->format('%m');
	            if($meses > 0){
	            ?>
	            	<div class="alert alert-danger">
	            		<i class="fa fa-warning"></i> SOCIO CON DEUDA | Debe <?=$meses?> <? if($meses > 1){ echo 'Meses';}else{echo 'Mes';} ?>
	            	</div>
	            <?
	            }else{
	                if( $hoy->format('%m') != $d2->format('%m') && $socio->deuda->monto != '0.00' ){
	                ?>
	                <div class="alert alert-warning"><i class="fa fa-info"></i> Saldo del mes anterior</div>
	                <?
	                }else{                    
	                ?>
	                <div class="alert alert-success"><i class="fa fa-check-square"></i> Cuota al Día</div>
	                <?                
	                }
	            }
	        }else{
	            ?>
	            <div class="alert alert-success"><i class="fa fa-check-square"></i> Sin Deuda</div>
	            <?
	        }
			if ( $cupon_cobrod ) {
				$url="https://cobrodigital.com/externo/landing_barcode.php?barcode=".$cupon_cobrod->barcode;
				?>
				<form id="formPago" method="post">
				<a href="<?=$url?>" id="btn_pago" class="btn btn-primary">Pagar Online</a> 
		    		</form>
				<?
			}
	        $data['facturacion'] = $socio->facturacion;
	        $this->load->view('estado/resumen2', $data, FALSE);
		}else{
			?>
			<div class="alert alert-danger">
				<i class="fa fa-exclamation-triangle"></i>
				No se encontró ningún socio.
			</div>
			<?		
		}
	}

	public function get_socio()
	{
		header('Access-Control-Allow-Origin: *'); 
		$this->load->model('socios_model');
		$this->load->model('pagos_model');
		$input = $this->input->post('socio_input');
		if (strlen($input) > 9) {			
			$socio = $this->socios_model->get_socio_by_barcode($input);
		} else {
			$socio = $this->socios_model->get_socio_by_dni($input);
		}
		$cupon_cobrod = $this->pagos_model->get_cupon_cobrod_by_sid($socio->Id);
		if($socio){
			$socio->deuda = $this->pagos_model->get_ultimo_pago_socio($socio->Id);
			$socio->cuota = $this->pagos_model->get_monto_socio($socio->Id)['total'];			
			$socio->facturacion = $this->pagos_model->get_facturacion($socio->Id);
                        if ( $socio->socio_n == 0 ) {
                                ?> <h2>#<?=$socio->Id?> - <?=$socio->apellido?>, <?=$socio->nombre?></h2> <?
                        } else {
                                ?><h2>#<?=$socio->Id?> - (nro. original <?=$socio->socio_n?>) ----  <?=$socio->apellido?>, <?=$socio->nombre?></h2>    <?
                        }

			if($socio->deuda){                      
	            		$hoy = new DateTime();
	            		$d2 = new DateTime($socio->deuda->generadoel);                
	            		$interval = $d2->diff($hoy);
	            		$meses = $interval->format('%m');
	            		if ($meses > 0) {
	            		?>
	            			<div class="alert alert-danger">
	            			<i class="fa fa-warning"></i> SOCIO CON DEUDA | Debe <?=$meses?> <? if($meses > 1){ echo 'Meses';}else{echo 'Mes';} ?>
	            			</div>
	            		<?
	            		} else {
	                		if ( $hoy->format('%m') != $d2->format('%m') && $socio->deuda->monto != '0.00' ) {
	                		?>
	                			<div class="alert alert-warning"><i class="fa fa-info"></i> Saldo del mes anterior</div>
	                		<?
	                		} else {                    
	                		?>
	                			<div class="alert alert-success"><i class="fa fa-check-square"></i> Cuota al Día</div>
	                		<?                
	                		}
	            		}
	        	} else {
	            		?>
	            		<div class="alert alert-success"><i class="fa fa-check-square"></i> Sin Deuda</div>
	            		<?
	        	}
			
			if ( $cupon_cobrod ) {
				$url="https://cobrodigital.com/externo/landing_barcode.php?barcode=".$cupon_cobrod->barcode;
				?>
				<form id="formPago" method="post">
				<a href="<?=$url?>" id="btn_pago" class="btn btn-primary">Pagar Online</a> 
		    		</form>
				<?
			}
			$data['facturacion'] = $socio->facturacion;
	        	$this->load->view('estado/resumen', $data, FALSE);
		} else {
			?>
			<div class="alert alert-danger">
				<i class="fa fa-exclamation-triangle"></i>
				No se encontró ningún socio.
			</div>
			<?		
		}
		//echo json_encode($socio);
	}

}

/* End of file estado.php */
/* Location: ./application/controllers/estado.php */

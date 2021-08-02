<?php
require_once('fpdf_barcode128.php');

class FPDF_Card extends FPDF_Barcode128 {
	public $text_font = 'Arial';
	public $text_size = 8;

	function FPDF_Card($size = array(86, 54)){
		parent::__construct('P', 'mm', $size);
		$this->AliasNbPages();
		$this->AddPage();
		$this->Rect(0, 0, $size[0], $size[1], 'D');
	}

	public function addText($w, $h, $text, $d = '', $size = false){
		$size = !$size ? $this -> text_size : $size;
		$this -> SetFont($this -> text_font, $d, $size);
		$this -> Text($w, $h, $text);
	}

	/**
	 * Inserta el cÃ³digo de barras
	 * @author Alejo E. Rojas
	 */
	public function addBarcode($cod, $pos = array("x" => 30, "y" => 42, "w" => 35, "h" => 9)){
		$cod = sprintf("%06u", $cod);
		$this -> Code128($pos['x'], $pos['y'], $cod, $pos['w'], $pos['h']-3);
		$this -> SetFont('Arial','B',10);
		$this -> SetFontSpacing(3);
		$this -> Text($pos['x'], $pos['y']+$pos['h'], " ".$cod);
	}


	function SetFontSpacing($size){
		$this->_out(sprintf('BT %.3F Tc ET',$size*$this->k));
	}

	/**
	 * Redefino el mÃ©todo _getpagesize para permitir pÃ¡ginas mÃ¡s anchas que altas.
	 * @author Alejo E. Rojas
	 */
	protected function _getpagesize($size)
	{
		if(is_string($size)){
			$size = strtolower($size);
			if(!isset($this->StdPageSizes[$size]))
				$this->Error('Unknown page size: '.$size);
			$a = $this->StdPageSizes[$size];
			return array($a[0]/$this->k, $a[1]/$this->k);
		}else{
			return $size;
		}
	}

}



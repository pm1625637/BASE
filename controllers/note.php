<?php
class Note extends Controller
{
	function __construct()
	{
		parent::__construct('data','php');
		// <HEAD>
		$this->data['title'] =' Notes';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
	}
 	function index()
	{
		parent::index();
	}
	function line($url)
	{
		$i_table = $this->Get->get_id_table('notes');
		$i_line = $url[2];
		$i_column =  $this->Get->get_id_column($i_table,'note');
		
		$this->data['i_table'] = $i_table;
		$this->data['i_line'] = $i_line;
		$this->data['i_column'] = $i_column;
		// Only the cell : note
		$this->data['coord'] = '['.$i_table.']['.$i_line.']['.$i_column.']';
		$this->data['cell'] =  $this->Get->get_cell($i_table,$i_line,$i_column); 
		//function get_record($strTable,$line)		
		$record = $this->Get->get_record('notes',$i_line);	
		// array(3) { ["id_note"]=> string(1) "1" ["note"]=> string(6) "note 1" ["user_id"]=> string(1) "1" }
		$this->data['obj'] = (json_decode(json_encode($record)));
		//in the view echo $obj->note;
		$this->data['json'] = json_encode($record);
		
		$this->data['content'] = $this->Template->load('note',$this->data,TRUE);
		// MAIN PAGE
		$this->Template->load('layout',$this->data);
	}
	function json($url)
	{
		$i_table = $this->Get->get_id_table('notes');
		$i_line = $url[2];
		$i_column =  $this->Get->get_id_column($i_table,'note');
		
		// Only the cell : note
		$this->data['i_table'] = $i_table;
		$this->data['i_line'] = $i_line;
		$this->data['i_column'] = $i_column;
		$this->data['coord'] = '['.$i_table.']['.$i_line.']['.$i_column.']';
		$this->data['cell'] =  $this->Get->get_cell($i_table,$i_line,$i_column); 
		//function get_record($strTable,$line)		
		$record = $this->Get->get_record('notes',$i_line);	
		// array(3) { ["id_note"]=> string(1) "1" ["note"]=> string(6) "note 1" ["user_id"]=> string(1) "1" }
		//$this->data['obj'] = (json_decode(json_encode($record)));
		$obj = (json_decode(json_encode($record)));
		//in the view echo $obj->note;
		$json = json_encode($record);
		
		//$this->data['content'] = $this->Template->load('note',$this->data,TRUE);
		// MAIN PAGE
		header('Content-Type:text/plain');
		header('Content-Disposition: attachment; filename="note'.$obj->id_note.'.json"');
		echo (isset($json))? $json:'$json'; 

		//$this->Template->load('json',$this->data);
	}
}
?>
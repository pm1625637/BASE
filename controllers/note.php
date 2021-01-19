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
		
		// Only the cell : note
		echo ($this->Get->get_cell($i_table,$i_line,$i_column));
	
		//function get_record($strTable,$line)		
		$record = $this->Get->get_record('notes',$i_line);	
		// array(3) { ["id_note"]=> string(1) "1" ["note"]=> string(6) "note 1" ["user_id"]=> string(1) "1" }
		$obj = (json_decode(json_encode($record)));
		echo $obj->note;
	}
}
?>
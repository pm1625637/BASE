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

		// The whole record as an array
		$note = $this->Get->get_line($i_table, $i_line);
		echo $note[$i_column];		
		$columns = $this->Get->get_columns_of('notes');
		$record = $this->Get->combine($note,$columns);
		
		file_put_contents(DATADIRECTORY.'note'.$i_line.'.json',json_encode($record));
		$obj = (json_decode(json_encode($record)));
		var_dump($obj);
		//without combine echo $obj->{"2"};
		echo $obj->note;
		// And many more functions
	}
}
?>
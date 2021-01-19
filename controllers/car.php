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
	function note($url)
	{
		$i_table = $this->Get->get_id_table('notes');
		$i_line = $url[2];
		$i_column =  $this->Get->get_id_column($i_table,'note');
		echo $this->Get->get_cell($i_table,$i_line,$i_column);
	}
}
?>
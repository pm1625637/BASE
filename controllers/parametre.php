<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Parametre
* @version:	7.2
* @author: pierre.martin@live.ca
* @php: 7.4
* @revision: 2021-01-16
* @licence MIT
*/
class Parametre extends Controller
{
	function __construct()
	{
		parent::__construct('parametres','php','parametre');
		// <HEAD>
		$this->data['title'] =' Parameters';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		
		if(!isset($_SESSION['loggedin']))
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}
	}
	function index()
	{
		parent::index();
	}
	function add_table()
	{
		$this->denied('add a table ');
	}	
	function edit_table($url)
	{
		$this->denied('edit a table');
	}
	function delete_table($url)
	{		
		$this->denied('delete table');
	}
	function add_field($url)
	{
		$this->denied('add a field');
	}
	function edit_field($url)
	{
		$this->denied('edit a field');
	}
	function delete_field($url)
	{
		$this->denied('delete a field');
	}
	/*function add_record($url)
	{
		$this->denied('add a record');
	}
	function edit_record($url)
	{
		$this->denied('edit a record');
	}
	function delete_record($url)
	{
		$this->denied('delete a record');
	}*/
	function denied($string)
	{
		$this->Msg->set_msg("You don't have the right to $string in this module.");
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
	
	function ini()
	{
		$this->load_model('Struct');
		$this->Struct->connect(DATADIRECTORY,'schemas','php');
		$tables = $this->Struct->get_tables();
		$realID = $this->Param->get_id_table('tables');
		$this->Param->empty_table($realID);
		$post['table'] = $realID;
		foreach($tables as $strTable)
		{
			$post['id_table'] = $this->Param->get_last_number('tables','id_table') + 1;
			$post['strtable'] = $strTable;
			$this->Param->add_line($post,'id_table');
		}
		//$this->Param->create_demo();
		$this->Msg->set_msg("You have initialized ");
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
	function repair()
	{
		$this->Param->repair();
	}
	
}
?>
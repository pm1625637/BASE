<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Schema
* @version:	7.2
* @author: pierre.martin@live.ca
* @php: 7.4
* @revision: 2021-01-16
* @licence MIT
*/
class Schema extends Controller
{
	function __construct()
	{
		parent::__construct('schemas','php','schema');
		// <HEAD>
		$this->data['title'] =' Schemas';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		
		if(!isset($_SESSION['loggedin']))
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}

		$this->data['dbmysql_import']=$this->Sys->get_field_value_where_unique('server','id_server',1,'dbmysql');
		$this->data['dbmysql_export']=$this->Sys->get_field_value_where_unique('server','id_server',2,'dbmysql');
		// LEFT
		$this->data['left'] = $this->Template->load('left',$this->data,TRUE);
	}
	function index()
	{
		parent::index();
	}
	
	function import()
	{
		$server = $this->Sys->get_record('server',1);
		try
		{
			if($server['passmysql']=='-')
			{
				unset($server['passmysql']);
			}
			$this->Get->from_mysql($server['ipmysql'],$server['usermysql'],@$server['passmysql'],$server['dbmysql']);
			$this->Msg->set_msg('You have imported '.$server['dbmysql']);
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
	}
	function export($url)
	{
		$server = $this->Sys->get_record('server',2);
		try
		{
			if($server['passmysql']=='-' || $server['passmysql']=='')
			{
				unset($server['passmysql']);
			}
			$export_results[] = $this->Get->to_mysql($server['ipmysql'],$server['usermysql'],@$server['passmysql'],$server['dbmysql'],$url[TABLE]);
			$this->Msg->set_msg('You have imported '.$server['dbmysql']);
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->data['export_results'] = $export_results;
		$this->data['content'] = $this->Template->load('export_table_results',$this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	
	function export_all()
	{
		$export_results=array();
		try
		{
			$server = $this->Sys->get_record('server',2);
			if($server['passmysql']=='-')
			{
				unset($server['passmysql']);
			}
			$tables = $this->Get->get_tables();
			foreach($tables as $tab)
			{
				//var_dump($table); exit;
				try
				{
					$id_table = $this->Get->get_id_table($tab);
					if($this->Get->count_lines($id_table) > 0)
					{
						//$export_results .= $id_table.'<br>';
						$export_results[] = $this->Get->to_mysql($server['ipmysql'],$server['usermysql'],@$server['passmysql'],$server['dbmysql'],$tab);
						$this->Msg->set_msg('You have exported '.$tab);
						//$this->Msg->set_msg($export_results);
					}
					else
					{
						$export_results[] = $tab.' is empty!';
					}
				}
				catch(Throwable $t)
				{
					$this->Msg->set_msg($t->getMessage());
				}
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->data['export_results'] = $export_results;
		$this->data['content'] = $this->Template->load('export_table_results',$this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	
	function ini()
	{
		//$this->load_model('Struct');
		//$this->Struct->connect(DATADIRECTORY,'structures','php');
		//$this->Struct->create_demo();
		$this->import();
		$this->Msg->set_msg("You have initialized schema.");
		header('Location:'.WEBROOT.strtolower(get_class($this)));
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
	}*/
	/*function edit_record($url)
	{
		$this->denied('edit a record');
	}*/
	/*function delete_record($url)
	{
		$this->denied('delete a record');
	}*/
	function denied($string)
	{
		$this->Msg->set_msg('<span style="color:red">You don\'t have the right to '.$string.' in this module.</span>');
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
}
?>
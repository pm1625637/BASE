<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Main
* @version:	1.1 (index.php)
* @author: pierre.martin@live.ca
* @php: 7.4
* @revision: 2021-01-20
* @licence MIT
*/
class Main extends Controller
{	
	function __construct()
	{
		parent::__construct(DEFAULTDATABASE,'php');

		$this->data['title'] =' Main';
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
	
	function get_json()
	{
		header("Content-Type: text/plain");
		echo $this->Get->get_cell(5,1,2);
	}
	/*function langa()
	{
		include(DATADIRECTORY.'f_lang.php');
		$post['table'] = $this->Get->get_id_table('langues');
		$this->Get->get_id_table('langues');
		foreach($lang as $k=>$value)
		{
			$post['key'] = $k;
			$post['value'] = $value;
			$this->Get->add_line($post,'id_langue');		
		}
	}*/
}
?>
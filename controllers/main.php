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

		/*$this->data['title'] =' Main';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);*/
		
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
	
	//TEST de la fonction is_unique
	function test()
	{
		$result = $this->Get->is_unique('users','user','user 1');
		var_dump($result);
	}
	function classes()
	{
		$class_methods = get_class_methods('Model');
		foreach ($class_methods as $method_name)
		{
			$post['table'] = $this->Get->get_id_table('functions');
			$post['function'] = $method_name;
			$m= new ReflectionMethod('Model',$method_name);
			$params = $m->getParameters();
			$post['parameters']='';
			foreach($params as $name=>$value)
			{
				$post['parameters'] .= $value;
			}
			unset($m);
			$this->Get->add_line($post,'id_function');
		}
		$this->index();
	}
}
?>
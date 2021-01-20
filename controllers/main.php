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
		// <HEAD>
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
		// CHANGE THE DEFAULT TITLE BANNER
		//$this->data['title'] = '<a href="'.DEFAULTCONTROLLER.'" target="_blank">'.ucfirst(DEFAULTCONTROLLER).'</a>';
		//$this->data['banner']= $this->Template->load('banner', $this->data,TRUE);
		parent::index();
	}

}
?>
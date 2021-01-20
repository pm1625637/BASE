<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class Login extends Controller
{
	function __construct()
	{
		//parent::__construct(DEFAULTCONTROLLER,'php');
		parent::__construct(DEFAULTDATABASE,'php');
	}
	function index()
	{
		$post = @$_POST;
		try
		{
			$user = $this->Sys->get_where_unique('users','username',@$post['username']);
			if($user)
			{
				$idTable = $this->Sys->get_id_table('users');
				$colId = $this->Sys->get_id_column($idTable,'id_user');
				$colPass = $this->Sys->get_id_column($idTable,'password');
				$colUser = $this->Sys->get_id_column($idTable,'username');
				$colJumbo = $this->Sys->get_id_column($idTable,'jumbo');
				
				if($user[$colPass] == trim(md5($post['password'])) && $user[$colUser] == trim($post['username']))
				{
					$post['loggedin'] = TRUE;
					$post['table'] = $idTable;
					$post['line'] = $this->Sys->get_real_id($idTable,'username',$user[$colUser]);
					$post['password'] = $user[$colPass];
					$post['id_user'] = $user[$colId];
					$post['jumbo'] = $user[$colJumbo];

					$this->Sys->set_line($post);
					$_SESSION = $post;
					$this->Get->save(TRUE);
					$this->Msg->set_msg('You are logged in!');
					header('Location:'.WEBROOT.DEFAULTCONTROLLER);
					//header('Location:'.WEBROOT.'main');
					exit;
				}
				else
				{
					$this->Msg->set_msg('Username not found!');
					$this->data['action'] = WEBROOT.strtolower(get_class($this));
					$this->Template->load('login',$this->data);
				}
			}
			else
			{
				$this->Msg->set_msg('Username not found!');
				$this->data['action'] = WEBROOT.strtolower(get_class($this));
				$this->Template->load('login',$this->data);
			}
		}
		catch(Exception $e)
		{
			$this->Msg->set_msg($e->getMessage());
			$this->data['action'] = WEBROOT.strtolower(get_class($this));
			$this->Template->load('login',$this->data);
		}
	}
	
	function logout()
	{
		if(isset($_SESSION['loggedin']))
		{
			$_SESSION['loggedin']='';
			$this->Sys->set_cell(2,1,4,"0");
			//$this->Sys->set_line($_SESSION);	
		}		
		// remove all session variables
		session_unset(); 
		// destroy the session 
		session_destroy();
		$this->Msg->set_msg('You have logged out!');
		header('Location:'.WEBROOT.strtolower(get_class($this)));
	}
}
?>
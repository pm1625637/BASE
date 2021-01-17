<?php  
class Msg extends Model
{
	/*function __construct()
	{
		parent::__construct();
	}
	*/
	function get_msg($unescape=FALSE)
	{
		$int = $this->count_lines(1);
		$msg = $this->get_cell(1,$int,1);
		if($unescape)
		{
			$this->unescape($msg);	
		}
		return $msg;
	}
	
	function set_msg($string)
	{
		$int = $this->count_lines(1)+1;
		$post['table'] = 1;
		$post['line'] = $int;
		$post['message'] = $string;
		$post['datetime'] =  date("Y-m-d H:i:s",time());
		$this->set_line($post);
		//$this->set_cell(1,$int,1,$string);
	}
	
}
?>
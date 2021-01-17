<?php  
class Param extends Model
{
	function __construct()
	{
		
	}
	public function create_demo()
	{
		unset($this->data);
		$this->data[0][0][1]='tables';
		$this->data[1][0][1]='id_table';
		$this->data[1][0][2]='table';
		$this->data[1][0][3]='skippy';;
		$this->save();
	}
	
	public function repair()
	{
		foreach($this->data[1] as $i=>$tab)
		{
			if($i==0) continue;
			$this->data[1][$i][3] ='';
		}
		$this->save();
	}
}
?>
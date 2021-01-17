<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class System extends Controller
{
	function __construct()
	{
		parent::__construct('system','php','system');
		// <HEAD>
		$this->data['title'] =' System';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		if(!isset($_SESSION['loggedin']) || $_SESSION['id_user']!=1)
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}
		//Delete doublon
		$table = $this->Sys->get_id_table('bigfiles');
		$column = $this->Sys->get_id_column($table,'bigfile');
		$this->Sys->del_doublon($table,$column);
	}
	function index()
	{
		if(isset($_SESSION['line'])>1 || empty($_SESSION))
		exit('No direct script access allowed');
		parent::index();
	}
	function renumber_column($url)
	{
		$strTable=$url[TABLE];		
		$this->properties('left',$strTable);
		$post = @$_POST;
		try
		{
			if(!$this->Get->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			@$this->Get->renumber($strTable,$post['strfield'],$post['value']);
			$this->Msg->set_msg('You have renumbered column '.$post['strfield'].' from '.$post['value'].' in the table '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Renumber a column of the table: $strTable" ;
		$this->data['placeholder'] = 'Renumber a column';
		
		//$this->data['columns'] = $this->Get->get_columns_of('actions');
		$this->data['columns'] = array(1=>'strfield',2=>'value');
		
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,NULL,'column',' : Column to be renumbered');	
		$this->data['divvalue'] = $this->Template->makediv('value','start',' : Beginning value');	
	
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/renumber_column/'.$strTable;
		$this->data['content'] = $this->Template->load('renumber-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function show_table($url)
	{
		$debut = microtime(true)*1000;

		if(isset($url[TABLE]) && $this->Get->table_exists($url[TABLE]))
		{
			$strTable = $url[TABLE];
		}
		else
		{
			$this->Msg->set_msg("Record not found in: $url[TABLE]");
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit();
		}

		//$this->Get->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
		
		//LEFT
		$this->properties('left',$strTable);
		
		//CONTENTS
		$this->data['columns'] = $this->Get->get_columns_of($strTable);

		if(isset($url[FIELD]))
		{
			$this->Get->order_by($strTable,$url[FIELD]);
		}

		$records = $this->Get->all();

		if(isset($records))
		{
			$tbody ='';
			foreach($records as $key=>$t)
			{
				$tbody .= '<tr id="tr'.$key.'">';
				$i = 0;
				foreach($t as $k=>$value)
				{
					$table = $this->Get->get_id_table($strTable);
					$col = $this->Get->get_column_name($table,$k);
					if(substr($col, -3, 1)=="_")
					{
						$strForeignTable = stristr($col, '_', true).'s';
						$col = stristr($col, '_', true);

						$rec = $this->Get->get_where_unique($strForeignTable,'id_'.$col,$value);
						$intForeignTable = $this->Get->get_id_table($strForeignTable);
						$tbody .= '<td>';
						if($rec)
						{
							$tbody .= $rec[1];
						}
						$tbody .= '</td>';
					}
					elseif(substr($col, 2, 1)=="_")
					{
						$arr=null;
						if(strstr($col, '_'))
						{
							$arr=explode('_',$col);
						}
						if($col=='id_'.$arr[1] && isset($arr))
						{
							try
							{
								if($strTable=='blocks')
								{
									$tbody .= '<script>
									$(document).ready(function(){
									$("#td'.$key.'").editable("'.WEBROOT.'system/set_cell/'.$table.'/'.$key.'/'.$k.'",{name: \'value\'});
									});
									</script>';
									$tbody .= '<td id="td'.$key.'" style="text-decoration:underline;">'.$value.'</td>';
								}
								else
								{
									$tbody .= '<td id="td'.$key.'">'.$value.'</td>';
								}
							}
							catch (Throwable $t)
							{
								$tbody .= '<td id="td'.$key.'">'.$value.'</td>';
							}
						}
						elseif($strTable=='blocks' && $col=='block')
						{
							//<a href=" '.WEBROOT.DEFAULTCONTROLLER'/load_script/'">'.$value.'</a>
							//$_SESSION['sblock'] = $value;
							//get_field_value_where_unique($strTable,$strColumn,$unique,$strField)
							$id_block = $this->Sys->get_field_value_where_unique($strTable,'block',$value,'id_block');
							$tbody .= '<td><a href="'.WEBROOT.DEFAULTCONTROLLER.'/load_script_get/'.$id_block.'">'.$value.'</a></td>';
						}
						else
						{
							$tbody .= '<td>'.$value.'</td>';
						}
					}
					else
					{
						$tbody .= '<td>'.$value.'</td>';
					}
					$i++;
				}
				while($i < $this->data['nbrcolonne'] )
				{
					$tbody .= '<td>-</td>';
					$i++;
				}

				switch($this->data['thead'])
				{
				case 'actions':
					$tbody .='<td><a title="Edit this action ?"  href=" '.WEBROOT.$this->data['controller'].'/edit_action/'.$this->data['thead'].'/'.$key.' ">edit</a></td>';
				break;
				default:
					//$tbody .='<td><a title="Edit this record ?"  href=" '.WEBROOT.$this->data['controller'].'/edit_record/'.$this->data['thead'].'/'.$R.' ">edit</a></td>';
					$tbody .='<td><a title="Edit this record ?"  href=" '.WEBROOT.$this->data['controller'].'/edit_record/'.$this->data['thead'].'/'.$key.' ">edit</a></td>';
				}
				$tbody .= '<td><a title="Are you sure you want to delete this record ?"  href=" '.WEBROOT.$this->data['controller'].'/delete_record/'.$this->data['thead'].'/'.$key.' ">delete</a></td>';

				$tbody .= '</tr>';
			}
			$this->data['tbody'] = $tbody;
		}
		$fin = microtime(true)*1000;
		$this->data['performance'] = $fin-$debut;
		$this->data['content'] = $this->Template->load('tables', $this->data,TRUE);
		//LAYOUT
		$this->Template->load('layout',$this->data);
	}
	function set_cell($url)
	{
		//var_dump($url); exit;
		// define('CONTROLLER',0); define('ACTION',1); define('TABLE',2); define('FIELD',3); define('INDEX',3); define('VALUE',4);
		//array(5) {  [0]=>  string(4) "main"  [1]=>  string(8) "set_cell"  [2]=>  string(1) "2"  [3]=>  string(1) "1"  [4]=>  string(1) "1"}
		// sleep for a while so we can see the indicator in demo
		/*if ($_POST['slow']) {
		usleep(500000);
		}*/
		if (is_array($_POST['value'])) {
		echo implode(', ', $_POST['value']);
		} else {
		echo $_POST['value'];
		}
		//$("#td1").editable("/conversion/main/set_cell/2/1/1",{ name : 'value', id  : 'id', type : 'text'});
		//var_dump($url); exit;
		$this->Get->set_cell($url[TABLE],$url[INDEX],$url[VALUE],$_POST['value']);
	}
	/*function add_table()
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
	function add_record($url)
	{
		//var_dump($_SESSION); exit;
		if( ($url[TABLE]=='users' || $url[TABLE]=='scripts' || $url[TABLE]=='operators'  || $url[TABLE]=='rwords' || $url[TABLE]=='configs') && $_SESSION['id_user'] !== "1" )
		{
			$this->denied('add a record');
		}
		else
		{
			parent::add_record($url);
		}
	}
	function edit_record($url)
	{
			if( ($url[TABLE]=='users' || $url[TABLE]=='scripts' || $url[TABLE]=='operators'  || $url[TABLE]=='rwords' || $url[TABLE]=='configs') && $_SESSION['id_user'] !== "1" )
		{
			$this->denied('edit a record');
		}
		else
		{
			parent::edit_record($url);
		}
	}*/
	function delete_record($url)
	{
		if( ($url[TABLE]=='users' || $url[TABLE]=='scripts' || $url[TABLE]=='operators'  || $url[TABLE]=='rwords' || $url[TABLE]=='configs') && $_SESSION['id_user'] !== "1" )
		{
			$this->denied('delete a record');
		}
		else
		{
			if($url[TABLE]=='blocks')
			{
				$rec = $this->Get->get_record($url[TABLE],$url[INDEX]);
				unlink(BLOCKDIRECTORY.$rec['block'].'.php');
			}
			elseif($url[TABLE]=='files')
			{
				$rec = $this->Get->get_record($url[TABLE],$url[INDEX]);
				unlink(DATADIRECTORY.$rec['file'].'.php');
			}
			parent::delete_record($url);
		}
	}
	function denied($string)
	{
		$this->Msg->set_msg("You don't have the right to $string in this module.");
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
	
	function list_files()
	{
		foreach (glob("*.php") as $filename)
		{
			echo "$filename size " . filesize($filename) . "\n";
		}
	}
}
?>
<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Controller
* @version:	8.3
* @author: pierre.martin@live.ca
* @php: 7.4
* @revision: 2021-03-18 16:25
* @note : controle des labels
* @licence MIT
*/
class Controller
{
	public static $version = '8.3';
	protected $data = array();
	
	function __construct($file,$ext,$path=NULL)
	{
		$this->path = $path;		
		$this->load_model('Sys');
		$this->load_model('Msg');
		$this->load_model('Get');
		$this->load_class('Template');
		
		$this->Sys->connect(DATADIRECTORY,'system','php');
		$this->Msg->connect(DATADIRECTORY,'messages','php');
		$this->Get->connect(DATADIRECTORY,$file,$ext);
			
		//////
		$this->load_model('Struct');
		$this->Struct->connect(DATADIRECTORY,'schemas','php');
		
		$this->load_model('Param');
		$this->Param->connect(DATADIRECTORY,'parametres','php');
		
		//Delete doublon in sys files table
		$table = $this->Sys->get_id_table('files');
		$column = $this->Sys->get_id_column($table,'file');
		$this->Sys->del_doublon($table,$column);
		
		//Delete doublon in param tables table
		$table = $this->Param->get_id_table('tables');
		$column = $this->Param->get_id_column($table,'strtable');
		$this->Param->del_doublon($table,$column);
		//////
		$configs=$this->Sys->table('configs');
		//model public function get_id_table($table,$strColumn)
		$table=$this->Sys->get_id_table('configs'); 
		//model  public function get_id_column($table,$strColumn)
		$key=$this->Sys->get_id_column($table,'key'); 
		$value=$this->Sys->get_id_column($table,'value'); 
		// $rec[2] == key $rec[3]== value
		foreach($configs as $i=>$rec)
		{
			$this->data[$rec[$key]] = $rec[$value];
		}
		//PATH
		$this->data['path'] = $path;
		//LINK
		$this->data['link'] = strtolower(get_class($this));
		//<HEAD>
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		// BANNER
		// title from configs
		if(!$this->data['title'])
		{
			$this->data['title'] = '<a href="'.DEFAULTCONTROLLER.'" target="_blank">'.ucfirst(DEFAULTCONTROLLER).'</a>';	
		}
		$this->data['banner']= $this->Template->load('banner', $this->data,TRUE);
		// NAVIGATION
		$this->data['nav'] = $this->Template->load('nav',$this->data,TRUE);
		// MESSAGE
		$this->get_message();
		// LEFT
		$this->data['tables'] = $this->Get->get_tables();
		$this->data['left'] = $this->Template->load('left',$this->data,TRUE);
		// FOOTER
		$this->data['footer'] = $this->Template->load('footer', $this->data,TRUE);
		// CHECK SYSTEM
		try
		{
			$this->Get->check_system();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
	}
	function index()
	{
		// CONTENU
		$this->data['file'] = $this->Get->filename;
		$this->data['ffilesize'] = $this->Get->ffilesize;
		$this->data['numtables'] = $this->Get->count_tables();
		$this->data['maxlines'] = $this->Get->count_max_lines();
		$this->data['maxcols'] = $this->Get->count_max_columns();
		//$this->data['path'] = NULL;
		$this->data['content'] = $this->Template->load('details',$this->data,TRUE);
		// MAIN PAGE
		$this->Template->load('layout',$this->data);
	}	
	
	function add_table()
	{
		try
		{
			$strTable = @$_POST['table'];
			if($this->Get->add_table($strTable))
			{
				$strTable = $this->Get->remove_accents($strTable);
				//For tables list
				$last = $this->Param->get_last('tables');
				$idtab = $this->Param->get_id_table('tables');
				$post['table'] = $idtab;
				$post['id_table'] = $last+1;
				$post['strtable'] = $strTable;
				$this->Param->add_line($post,'id_table');
				//
				$this->Msg->set_msg('You have added the table : '.$strTable);
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
				exit;
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Add a table';
		$this->data['tip'] = 'When you add or rename a table, it will be added to the table parameters/tables. Keep in mind that the table parameters is used exclusively for the import and for the drop-down list in the actions form.';
		$this->data['placeholder'] = 'New table name';
		$this->data['name'] = 'table';
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/add_table';
		$this->data['content'] = $this->Template->load('add', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function edit_table($url)
	{
		//LEFT
		//$this->properties('left',$url[TABLE]);		
		try
		{
			$id_table = $this->Get->get_id_table($url[TABLE]);
			$strTableName = $this->Get->get_table_name($id_table);
			$strTable = @$_POST['newname']; 
			if($this->Get->edit_table($id_table,$strTable))
			{
				//For parameters
				$post['table'] = $this->Param->get_id_table('tables');
				$post['line'] = $this->Param->get_real_id($post['table'],'strtable',$strTableName);
 				$rec = $this->Param->get_line($post['table'],$post['line']);
				$post['id_table'] = $rec[1];
				$post['strtable'] = strtolower($strTable);
				$this->Param->set_line($post);
				//
				$this->Msg->set_msg('You renamed the table: '.$strTableName.' for: '.$strTable);
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
				//header('Location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Rename the table '.$url[TABLE] ;
		//data-toggle="tooltip"
		$this->data['tip'] = 'When you add or rename a table, it will be added to the table [tables] from parameters.
		Rename table are automatically lowercase.';
		$this->data['placeholder'] = 'New name for table'; 
		$this->data['name'] = 'newname';
		//$this->data['value'] = $strTable;
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/edit_table/'.$url[TABLE];
		$this->data['content'] = $this->Template->load('edit', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function delete_table($url)
	{
		$strTable=$url[TABLE];
		if($this->Get->table_exists($strTable))
		{
			try
			{
				$answer = @$_POST['inlineRadioOptions'];
				if(!isset($answer) && isset($strTable))
				{
					$tab = $this->Get->get_id_table($strTable);
					$refaction = WEBROOT.strtolower(get_class($this)).'/delete_table/'.$url[TABLE];
					$this->question('Are you sure you want to delete table '.$this->colorize($url[TABLE],'red').' ?',$refaction,$tab);
					exit;
				}
				elseif($answer == 'yes')
				{
					$this->Get->delete_table($this->Get->get_id_table($strTable));
					$this->Msg->set_msg("You have deleted the table: $strTable");
				}
			}
			catch (Throwable $t)
			{
				$this->Msg->set_msg($t->getMessage());
			}
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
	}
	function empty_table($url)
	{
		$strTable=$url[TABLE];
		if($this->Get->table_exists($strTable))
		{
			try
			{
				$answer = @$_POST['inlineRadioOptions'];
				if(!isset($answer) && isset($strTable))
				{
					$tab = $this->Get->get_id_table($strTable);
					$refaction = WEBROOT.strtolower(get_class($this)).'/empty_table/'.$url[TABLE];
					$this->question('Are you sure you want to empty table '.$this->colorize($url[TABLE],'red').' ?',$refaction,$tab);
					exit;
				}
				elseif($answer == 'yes')
				{
					$this->Get->empty_table($this->Get->get_id_table($strTable));
					$this->Msg->set_msg("You empty the table: $strTable");
				}
			}
			catch (Throwable $t)
			{
				$this->Msg->set_msg($t->getMessage());
			}
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
	}
	function show_table($url)
	{
		$debut = microtime(true)*1000;
		
		if(isset($url[TABLE]) && $this->Get->table_exists($url[TABLE]))
		{
			$strTable = $url[TABLE];
			$this->data['strTable'] = $strTable;
		}
		else
		{
			$this->Msg->set_msg('Record not found in: '.$url[TABLE]);
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit();
		}
		
		if($strTable == 'actions')
		{
			$block = isset($_SESSION['sblock'])?$_SESSION['sblock']:'';
			$this->data['listblocks'] = $this->Template->dropdown($this->Sys,'blocks','block',2,$block);
			//LEFT
			$this->properties('left',$strTable,'properties-action');
		}
		else
		{
			if($this->Sys->get_where_multiple('files','file','==',$strTable))
			{
				$this->data['listbigfiles'] = $this->Template->dropdown_where($this->Sys,'files','file',2,$strTable);	
				$_SESSION['sbigfile']=$strTable;
			}
			else
			{
				/*$this->Msg->set_msg('No php file attached to table: '.$url[TABLE]);
				$this->get_message();*/
				$_SESSION['sbigfile']='No php file attached';
			}
			//LEFT
			$this->properties('left',$strTable);
		}
		//CONTENTS
		$this->data['columns'] = $this->Get->get_columns_of($strTable);

		if(isset($url[FIELD]))
		{
			$this->Get->order_by($strTable,$url[FIELD]);
		}
		
		$records = $this->Get->all(false,SHOWLIMIT);
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
					if(substr($col, -3, 3)=="_id")
					{
						$strForeignTable = stristr($col, '_', true).'s';
						$col = stristr($col, '_', true);
						try
						{
							$rec = $this->Get->get_where_unique($strForeignTable,'id_'.$col,$value);
							
							$tbody .= '<td>';
							if($rec)
							{
								foreach($rec as $r=>$val)
								{
									if(($r <=> 2) !== 0) continue;
									$tbody .=  '<a href="'.WEBROOT.strtolower(get_class($this)).'/show/'.$strForeignTable.'/id_'.$col.'/'.$rec[1].'">'.$rec[2].'</a>';
								}
							}
							$tbody .= '</td>';
						}
						catch (Throwable $t)
						{
							$this->Msg->set_msg($t->getMessage());
						}
					}
					elseif(substr($col, 0, 3)=="id_")
					{
						$arr = explode('_',$col);
						if(isset($arr[1]))
						{
							$str = $arr[1].'s';
							try
							{
								$records =$this->Get->get_where('rules','master','==',$str);
								if($records)
								{
									$a = '<span>'.$value.' </span>';
									foreach($records as $r=>$rule)
									{
										$a .= '<a href="'.WEBROOT.strtolower(get_class($this)).'/show/'.$rule[3].'/'.$arr[1].'_'.$arr[0].'/'.$value.'" title="Slave: '.$rule[3].'">['.$rule[3].']</a>';
									}
									$tbody .= '<td>'.$a.'</td>';
								}
								else
								{
									if($strTable=='actions')
									{
										$tbody .= '<script>
										$(document).ready(function(){
										$("#td'.$key.'").editable("'.WEBROOT.'main/set_cell/'.$table.'/'.$key.'/'.$k.'",{name: \'value\'});
										});
										</script>';
										$tbody .= '<td id="td'.$key.'" style="text-decoration:underline;">'.$value.'</td>';
									}
									else
									{
										$tbody .= '<td id="td'.$key.'">'.$value.'</td>';
									}
								}
							}
							catch (Throwable $t)
							{
								$tbody .= '<td id="td'.$key.'">'.$value.'</td>';
							}
						}
						else
						{
							$tbody .= '<td>'.$value.'</td>';
						}
					}
					elseif($col == 'action')
					{
						$value = $this->Sys->get_field_value_where_unique('scripts','id_script',$value,'script');
						$tbody .= '<td>'.$value.'</td>';
					}
					elseif($col == 'operator')
					{
						$tbody .= '<td style="text-align:center">'.$value.'</td>';
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
	function add_field($url)
	{
		$strTable=$url[TABLE];
		if(!$this->Get->table_exists($strTable))
		{
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit;
		}
		//LEFT
		/*$this->Get->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
		$this->data['thead'] = $this->Get->table;
		$this->data['nbrligne'] = $this->Get->table_nbrlines;
		$this->data['nbrcolonne'] = $this->Get->table_nbrcolumns;
		$this->data['controller'] = strtolower(get_class($this));
		$this->data['left'] = $this->Template->load('properties', $this->data,TRUE);*/
		$this->properties('left',$strTable);
		
		$id_table = $this->Get->get_id_table($strTable);
		$strColonne = @$_POST['field'];
		try
		{
			if($this->Get->add_column($id_table,$strColonne))
			{
				$this->Msg->set_msg("You have added the field: $strColonne to the table: $strTable");
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
				exit;
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Add a field to the table: $strTable" ;
		$this->data['tip'] ='';
		$this->data['placeholder'] = 'Name of the field';
		$this->data['name'] = 'field';
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/add_field/'.$strTable;
		$this->data['content'] = $this->Template->load('add', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function edit_field($url)
	{
		$strTable=$url[TABLE];
		$id_colonne = $url[FIELD];
		$id_table = $this->Get->get_id_table($strTable);
		$column_name = $this->Get->get_column_name($id_table,$id_colonne);
		if(!$this->Get->table_exists($strTable) || !$this->Get->column_exists($id_table, $column_name))
		{
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit;
		}
		//LEFT
		$this->properties('left',$strTable);
		
		$strColonne = @$_POST['field'];
		try
		{
			if($this->Get->edit_column($id_table,$id_colonne,$strColonne))
			{
				$this->Msg->set_msg("You renamed the field: $column_name for $strColonne.");
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
				exit;
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Change the name of the field $column_name to the table: $strTable" ;
		$this->data['tip'] ='';
		$this->data['placeholder'] = 'Rename the field';
		$this->data['name'] = 'field';
		$this->data['value'] = $column_name;
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/edit_field/'.$url[TABLE].'/'.$url[FIELD];
		$this->data['content'] = $this->Template->load('edit', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function delete_field($url)
	{
		//var_dump($url); exit;
		if(isset($url[TABLE]) && isset($url[FIELD]))
		{
			$strTable = $url[TABLE];
			$idTable = $this->Get->get_id_table($url[TABLE]);
			$nbrColonne = $this->Get->count_columns($idTable);
			try
			{
				$column_name = $this->Get->get_column_name($idTable,$url[FIELD]);
				$this->Get->delete_column($idTable,$url[FIELD]);
				if(--$nbrColonne == 0)
				{
					$this->Msg->set_msg("Since there is no more field, you deleted the table: $strTable");
					header('Location:'.WEBROOT.$url[CONTROLLER]);
					exit;
				}
				else
				{
					$this->Msg->set_msg("You removed the field $column_name from the table $strTable.");
					if(isset($url[VALUE]))
					{
						header('Location:'.WEBROOT.$url[CONTROLLER].'/show_fields/'.$url[TABLE]);
						exit;
					}
					else
					{
						header('Location:'.WEBROOT.$url[CONTROLLER].'/show_table/'.$url[TABLE]);
						exit;
					}
				}
			}
			catch (Throwable $t)
			{
				$this->Msg->set_msg($t->getMessage());
				header('Location:'.WEBROOT.$url[CONTROLLER].'/show_table/'.$url[TABLE]);
			}
		}
		else
		{
			header('Location:'.WEBROOT.$url[CONTROLLER]);
		}
	}
	function show_fields($url)
	{
		//LEFT
		/*$this->Get->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
		$this->data['thead'] = $this->Get->table;
		$this->data['nbrligne'] = $this->Get->table_nbrlines;
		$this->data['nbrcolonne'] = $this->Get->table_nbrcolumns;
		$this->data['controller'] = strtolower(get_class($this));
		$this->data['left'] = $this->Template->load('properties', $this->data,TRUE);*/
		$this->properties('left',$url[TABLE]);
		$id = $this->Get->get_id_table($url[TABLE]);
		$this->data['idtable'] = $id;
		$this->data['columns'] = $this->Get->get_columns($id);
		$this->data['content'] = $this->Template->load('fields',$this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function add_record($url)
	{
		$strTable=$url[TABLE];
		//LEFT
		$this->properties('left',$strTable);
		$post = @$_POST;
		try
		{
			if(!$this->Get->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			$last = $this->Get->get_last($this->Get->table);
			$post[$this->Get->primary] = ++$last;
			if($strTable=='users' && strtolower(get_class($this))=='system' && isset($post['password']))
			{
				$post['password'] = trim(md5($post['password'])); 
			}
			$this->Get->add_line($post,$this->Get->primary);
			$this->Msg->set_msg("You have added a record to table: $strTable");
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Add a record to the table: $strTable";
		$this->data['placeholder'] = 'Add a record';
		$this->data['columns'] = $this->Get->get_columns_of($strTable);
		//var_dump($this->data['columns']); exit;
		foreach($this->data['columns'] as $key=>$col)
		{
			if(substr($col, -3, 1)=="_")
			{
				$tblList = stristr($col, '_', true).'s';
				$strListColumns = $this->Get->get_columns_of($tblList);
				//dropdown($cols,$strTable,$selectName,$value=null)
				$this->data['tblList'][$key] = $this->dropdown($strListColumns,$tblList,$col);
			}
		}
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/add_record/'.$strTable;
		$this->data['content'] = $this->Template->load('add-rec', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function edit_record($url)
	{
		$strTable=$url[TABLE];
		try
		{
			if(!$this->Get->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)),'refresh');
				exit;
			}
			//LEFT
			$this->properties('left',$strTable);
			$post = @$_POST;
			$this->Get->set_line($post);
			$this->Msg->set_msg("You have changed a record at the table: $strTable");
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit;
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Edit a record in the table: $strTable" ;
		$this->data['placeholder'] = 'Edit a record';
		$this->data['columns'] = $this->Get->get_columns_of($strTable);
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['line'] = $url[INDEX];
		$this->data['record'] = $this->Get->get_line($this->data['table'],$url[INDEX]);
		foreach($this->data['columns'] as $key=>$col)
		{
			if(substr($col, -3, 1)=="_")
			{
				$tblList = stristr($col, '_', true).'s';
				$strListColumns = $this->Get->get_columns_of($tblList);
				//dropdown($cols,$strTable,$selectName,$value=null)
				$value = $this->data['record'][$key];
				$this->data['tblList'][$key] = $this->dropdown($strListColumns,$tblList,$col,$value);
			}
		}
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/edit_record/'.$strTable.'/'.$url[INDEX];
		$this->data['content'] = $this->Template->load('edit-rec', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function delete_record($url)
	{
		if(isset($url[TABLE]) && isset($url[FIELD]))
		{
			$strTable = $url[TABLE];
			$idRec = $url[FIELD];
			$idTable = $this->Get->get_id_table($strTable);
			$this->Get->check_rule($strTable,$idRec);
			$this->Get->del_line($idTable,$idRec);
			$this->Msg->set_msg("You deleted a record from the table: $strTable");
		}
		else
		{
			header('Location:'.WEBROOT.$url[CONTROLLER]);
			exit;
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}

	function show($url)
	{
		$debut = microtime(true)*1000;
		try
		{	
			//$records = $this->Get->get_where($url[TABLE],$url[FIELD],'==',$url[VALUE]);
			$columns = $this->Get->get_columns_of($url[TABLE]);
			$records = $this->Get->select_where($columns,$url[TABLE],$url[FIELD],'==',$url[VALUE]);
		}
		catch(Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		if($records)
		{
			$strTable = $url[TABLE];
			$this->Get->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
			$tbody ='';
			foreach($records as $key=>$t)
			{
				$tbody .= '<tr>';
				$i = 0;
				foreach($t as $k=>$value)
				{
					$table = $this->Get->get_id_table($strTable);
					$col = $this->Get->get_column_name($table,$k);
					if(substr($col, -3, 3)=="_id")
					{
						$strForeignTable = stristr($col, '_', true).'s';
						$col = stristr($col, '_', true);
						$rec = $this->Get->get_where_unique($strForeignTable,'id_'.$col,$value);
						$tbody .= '<td>';
						if($rec)
						{
							foreach($rec as $r=>$value)
							{
								if(($r <=> 2) !== 0) continue;
								$value = '<a href="'.WEBROOT.strtolower(get_class($this)).'/show/'.$strForeignTable.'/id_'.$col.'/'.$rec[1].'">'.$rec[2].'</a>';
								$tbody .= $value;
							}
						}
						$tbody .= '</td>';
					}
					elseif(substr($col, 0, 3)=="id_")
					{
						$arr=explode('_',$col);
						if($col=='id_'.$arr[1])
						{
							$str = $arr[1].'s';
							try
							{
								$records =$this->Get->get_where('rules','master','==',$str);
								if($records)
								{
									$a = '<span>'.$value.' </span>';
									foreach($records as $i=>$rule)
									{
										$a .= '<a href="'.WEBROOT.strtolower(get_class($this)).'/show/'.$rule[3].'/'.$arr[1].'_'.$arr[0].'/'.$value.'" title="Slave: '.$rule[3].'">['.$rule[3].']</a>';
									}
									$tbody .= '<td>'.$a.'</td>';
								}
								else
								{
									$tbody .= '<td>'.$value.'</td>';
								}
							}
							catch (Throwable $t)
							{
								$tbody .= '<td>'.$value.'</td>';
							}
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
				while($i < $this->Get->table_nbrcolumns)
				{
					$tbody .= '<td>-</td>';
					$i++;
				}
				$tbody .='<td><a title="Edit this record ?"  href="'.WEBROOT.strtolower(get_class($this)).'/edit_record/'.$strTable.'/'.$key.' ">edit</a></td>';
				$tbody .= '<td><a title="Are you sure you want to delete this record ?"  href=" '.WEBROOT.strtolower(get_class($this)).'/delete_record/'.$strTable.'/'.$key.' ">delete</a></td>';
				$tbody .= '</tr>';
			}
			$this->data['tbody'] = $tbody;
		}
		else
		{
			$this->Msg->set_msg("Record not found in: $url[TABLE]");
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit();
		}
		$fin = microtime(true)*1000;
		$this->data['thead'] = strtolower(get_class($this));
		$this->data['controller'] = strtolower(get_class($this));
		$this->data['performance'] = $fin-$debut;
		$this->data['columns'] = $columns;
		$this->data['nbrcolonne'] = $this->Get->table_nbrcolumns;
		$this->data['content'] = $this->Template->load('tables', $this->data,TRUE);
		//LAYOUT
		$this->Template->load('layout',$this->data);
	}
	function printrec($url)
	{
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
		$this->Get->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
		$this->data['records'] = $this->Get->all(TRUE);
		$this->Template->load('print',$this->data);
	}
	
	/*function get($url)
	{
		$cols = $this->Get->get_columns_of($url[TABLE]);
		$rec = $this->Get->get_where_unique($url[TABLE],$url[FIELD],$url[VALUE]);
		$this->Get->unescape($rec);
		$record = $this->Get->combine($cols,$rec);
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($record);
		return json_encode($record);
	}*/
	
	function get_message()
	{
		$this->data['msg'] = $this->Msg->get_msg(TRUE);
		$this->data['msg'] = $this->Template->load('msg',$this->data,TRUE);
	}
	function load_model($name)
	{
		require_once(ROOT.'models/'.strtolower($name).'.php');
		$this->$name = new $name();
	}
	function load_class($name)
	{
		require_once(ROOT.'classes/'.strtolower($name).'.php');
		$this->$name = new $name();
	}
	function demo()
	{
		$this->Get->save(TRUE);
		$this->Get->create_demo();
		$this->Msg->set_msg('You have reinitialized the database.');
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
	function bkp()
	{
		$this->Get->save(TRUE);
		$this->Msg->set_msg('Your back-up is complete.');
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
	function preprint($res)
	{
		echo '<pre>';
		print_r($res);
		echo '</pre>';
	}
	function dropdown($cols,$strTable,$selectName,$value=null,$label=null)
	{
		$rec = $this->Get->select($cols,$strTable);
		//Désactive la ligne des noms de colonnes
		//unset($array[$i][0]);
		$colkeys = array_keys($cols);
		$html  = '<div class="form-group">';
		$label = (isset($label))?$label:$selectName;
		$html .= '<label for="'.$selectName.'">'.$label.'</label>';
		$html .= '<select class="form-control input-sm" name="'.$selectName.'">';
		$str='';
		$selected='';

			foreach($rec as $row)
			{
				for($i=1;$i<count($colkeys);$i++)
				{
					$str .= ' * '.$row[$colkeys[$i]];
				}
				if($row[$colkeys[0]]===$value)
				{
					$selected = 'selected="selected"';
				}
				$html .= '<option value="'.$row[$colkeys[0]].'"' .$selected. '>'.$str.'</option>';
				$str ='';
				$selected='';
			}
		//}
		$html .= '</select>';
		$html .= '</div>';
		return $html;
	}
	function dropdown_where($cols,$strTable,$selectName,$value=null,$strColumn,$op,$val)
	{
		//select_where(array $columns,$strTable,$strColumn,$op='==',$value)
		$rec = $this->Get->select_where($cols,$strTable,$strColumn,$op,$val);
		//Désactive la ligne des noms de colonnes
		//unset($array[$i][0]);
		$colkeys = array_keys($cols);
		$html  = '<div class="form-group">';
		$html .= '<label for="'.$selectName.'">'.$selectName.'</label>';
		$html .= '<select class="form-control input-sm" name="'.$selectName.'">';
		$str='';
		$selected='';

			foreach($rec as $row)
			{
				for($i=1;$i<count($colkeys);$i++)
				{
					$str .= ' * '.$row[$colkeys[$i]];
				}
				if($row[$colkeys[0]]===$value)
				{
					$selected = 'selected="selected"';
				}
				$html .= '<option value="'.$row[$colkeys[0]].'"' .$selected. '>'.$str.'</option>';
				$str ='';
				$selected='';
			}
		//}
		$html .= '</select>';
		$html .= '</div>';
		return $html;
	}
	function search($url)
	{
		$file = ROOT.'data/'.$this->Get->filename;
		$searchfor = $url[2];

		// the following line prevents the browser from parsing this as HTML.
		//header('Content-Type: text/plain');

		// get the file contents, assuming the file to be readable (and exist)
		$contents = file_get_contents($file);
		// escape special characters in the query
		$pattern = preg_quote($searchfor, '/');
		// finalise the regular expression, matching the whole line
		$pattern = "/^.*$pattern.*\$/m";
		// search, and store all matching occurences in $matches
		if(preg_match_all($pattern, $contents, $matches))
		{
		$this->Msg->set_msg('Matches found !');
		$c=count($matches[0]);
		$found='';
		for($i=0; $i<$c; $i++)
		{
			$found .= $matches[0][$i].'<br>';
		}
		$this->data['content'] = $found;
		 // echo implode("\n", $matches[0]);
		}
		else
		{
			$this->Msg->set_msg('No matches found');
		}
		$this->get_message();
		$this->Template->load('layout',$this->data);
	}
	
	function properties($view,$strTable,$properties='properties')
	{
		try
		{
			$this->Get->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
			$this->data['id'] = $this->Get->id_table;
			$this->data['thead'] = $this->Get->table;
			$this->data['nbrligne'] = $this->Get->table_nbrlines;
			$this->data['nbrcolonne'] = $this->Get->table_nbrcolumns;
			$this->data['controller'] = strtolower(get_class($this));
			//No need to set path. __construct doing it.
			//$this->data['path'] = $this->path;
			$this->data['sys'] = $this->Sys;
			$this->data[$view] = $this->Template->load($properties, $this->data,TRUE);
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
	}
	
	function question($strQuestion=null,$action=null,$table=null,$post=null)
	{
		$this->data['question'] = $strQuestion;
		$this->data['action'] = $action;
		$this->data['table'] = $table;
		$this->data['post'] = $post;
		$this->data['content'] = $this->Template->load('yesno',$this->data,TRUE);
		// MAIN PAGE
		$this->Template->load('layout',$this->data);
	}
	
	function colorize($string,$color)
	{
		return '<span style="color:'.$color.';"> '.$string.' </span>';
	}
	
	public function cleanup() 
	{
		foreach ($this as $key => $value) 
		{
            unset($this->$key);
        }
	}
	
	function console_log($data)
	{
	  echo '<script>';
	  echo 'console.log('. json_encode( $data ) .')';
	  echo '</script>';
	}
	
	function jumbo($bool)
	{
		$_SESSION['jumbo']=$bool;
	}
	
	function mobile()
	{
		$mobile_browser = '0';

		if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$mobile_browser++;
		}

		if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			$mobile_browser++;
		}    

		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
		$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda ','xda-');

		if (in_array($mobile_ua,$mobile_agents)) {
			$mobile_browser++;
		}

		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0) {
			$mobile_browser = 0;
		}

		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'mac') > 0) {
				$mobile_browser = 0;
		}

		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'ios') > 0) {
				$mobile_browser = 1;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'android') > 0) {
				$mobile_browser = 1;
		}

		if($mobile_browser == 0)
		{
			//its not a mobile browser
			//echo"You are not a mobile browser";
			return 0;
		} else {
			//its a mobile browser
			//echo"You are a mobile browser!";
			return 1;
		}
	}
		function delete_where($url)
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
			//del_lines_where($strTable,$strColumn,$op='==',$multiple,$strKeyCol)
			//$this->Get->del_lines_where('Carrier','EFID','==','-','CarrierNumber');
			@$this->Get->del_lines_where($strTable,$post['strfield'],$post['operator'],$post['value'],$post['unique']);
			$this->Msg->set_msg("You have deleted selection from table: $strTable");
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Delete a selection in the table: '.$strTable ;
		$this->data['placeholder'] = 'Delete a selection';
		
		$this->data['columns'] = $this->Get->get_columns_of('actions');
		
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,NULL,'column',' : Where column *operator value. Operator could be anything in the list');	
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2);
		$this->data['divvalue'] = $this->Template->makediv('value','value',' : The value that will be use by the operator for comparison');
		$this->data['listuniques'] = $this->Template->cdropdown($this->Get,$strTable,'unique',NULL,NULL,'unique',' : A field name that contains only unique value. Usually begin with id_');	
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/delete_where/'.$strTable;
		$this->data['content'] = $this->Template->load('del-rec-where', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function import_table()
	{
		try
		{	
			if(isset($_POST['table']))
			{
				$strTable = $_POST['table'];
				$strKey = 'id_'.substr($strTable, 0, -1);
				$server = $this->Sys->get_record('server',1); 
				$link = mysqli_connect($server['ipmysql'],$server['usermysql'],$server['passmysql'],$server['dbmysql']);
				//mysqli_set_charset($link,"utf8");
				$sql = "SELECT $strKey FROM $strTable;";	
				$res = mysqli_query($link,$sql);
				$rows = mysqli_num_rows($res);
				mysqli_free_result($res);
				
				$answer = @$_POST['inlineRadioOptions'];
				if(!$answer)
				{
					$refaction = WEBROOT.strtolower(get_class($this)).'/import_table';
					$this->question('Do you want to import fields only ?',$refaction,$strTable,$_POST);
					exit;
				}
				if($answer == 'yes')
				{
					if($this->import_fields($strTable))
					{
						$this->Msg->set_msg("You imported fields of the table: $strTable");
					}
					header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
					exit('redirection');
				}
				elseif($rows > 0)
				{
					if($this->import_mysql($strTable))
					{
						$this->Msg->set_msg("You imported table: $strTable");
					}
					header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
					exit('redirection');
				}
				else
				{
					if($this->import_fields($strTable))
					{
						$this->Msg->set_msg("You imported fields of the table: $strTable");
					}
					header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
					exit('redirection');
				}
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Import a table';
		$this->data['placeholder'] = 'Name of the table';
		$this->data['name'] = 'table';
		$this->data['list'] = $this->Template->dropdown($this->Param,'tables','table',2);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/import_table';
		$this->data['content'] = $this->Template->load('import_table', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	
	function import_odbc($strTable)
	{
		$return = FALSE;
		$server = $this->Sys->get_record('server',1); 
		
		$link = odbc_connect($server['odbc'],"",""); 
		if (!$link) 
		{ 
			$this->Msg->set_msg('ODBC failure or DentalMate is not running!');
			die("ODBC failure or DentalMate is not running!"); 
		} 
		
		//SET OWNER
		$sql ="set owner='M5150Dc+'";
		$res = @odbc_exec($link,$sql);
		unset($res);
		try
		{
			//TRY ADD THE TABLE IF EXISTS IT WILL EXIT
			$this->Get->add_from_odbc($strTable);
			$table = $this->Get->get_id_table($strTable); 
			//SKIPPY
			//get_where_unique($strTable,$strColumn,$unique)
			if($skippy = $this->Param->get_where_unique('tables','table',$strTable))
			{
				//var_dump($skippy); exit;
				$skippy = explode(',',$skippy[3]);
				//var_dump($skippy); exit;
				$field = trim($skippy[0]);
			}
			//SELECT EVERYTHING FROM THE TABLE
			$sql = 'select * from '.$strTable;		
			if($r = @odbc_exec($link,$sql))
			{
				try
				{
					$max = LOADLIMIT;
					$line = 0;
					$batch = TRUE;
					while($row = odbc_fetch_array($r))
					{
						if( $batch!=TRUE && isset($row[$field]) && $this->validation($row[$field],$skippy[1],$skippy[2]) )
						{
							continue;
						}
	
						if($line == 0 || $batch == TRUE)
						{
							$column = 1;
							foreach($row as $col=>$value)
							{
								$col = $this->check_rwords($col);
								$col = str_replace('_','~', $col);
								$data[$table][0][$column++] = $col;
							}
							$batch=FALSE;
							if($line == 0)
							{
								$line++;
							}
						}
						$column = 1;
						foreach($row as $col=>$value)
						{	
							if(isset($row[$field]) && $this->validation($row[$field],$skippy[1],$skippy[2]))
							{
								$line--;
								continue 2;
							}
							
							$this->Get->escape($value);
							$value = strval($value);
							
							if( $value || $value == 0 || $value == "0" )
							{
								$data[$table][$line][$column] = $value;
							}
							else
							{
								$data[$table][$line][$column] = $value;
							}
							$column++; 
						}
						
						if($line == $max)
						{
							$this->add_big_data($data,$strTable.$max.'.php',$strTable); 
							$max = $max + LOADLIMIT;
							$data[$table]=array();
							$batch=TRUE;
						}
						$line++;
					}	
					$line--;
					$this->Msg->set_msg("The table $strTable was saved.");
					$this->add_big_data($data,$strTable.$line.'.php',$strTable);			
				}
				catch (Throwable $t)
				{
					$this->Msg->set_msg($t->getMessage());
				}
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		odbc_close($link); 
		sleep(1);
		if(file_exists(DATADIRECTORY.$strTable.LOADLIMIT.'.php'))
		{
			$this->load_big_data($strTable,LOADLIMIT);
		}
		else
		{
			$this->load_big_data($strTable,$line);
		}
	}
	function import_odbc_fields($strTable)
	{
		$server = $this->Sys->get_record('server',1); 
		$link = odbc_connect($server['odbc'],"",""); 
		if (!$link) 
		{ 
			$this->Msg->set_msg('ODBC failure or DentalMate is not running!');
			die("ODBC failure or DentalMate is not running!"); 
		} 
		//SET OWNER
		//launch DM use UN: Rhonda / PW: future64
		$sql ="set owner='M5150Dc+'";
		$res = @odbc_exec($link,$sql);
		unset($res);
		try
		{
			//TRY ADD THE TABLE IF EXISTS IT WILL EXIT
			$this->Get->add_from_odbc($strTable);
			$table = $this->Get->get_id_table($strTable); 
			//SELECT EVERYTHING FROM THE TABLE
			$sql = 'select * from '.$strTable;		
			if($r = @odbc_exec($link,$sql))
			{
				try
				{
					$max = LOADLIMIT;
					$line = 0;
					$batch = TRUE;
					while($row = odbc_fetch_array($r))
					{
						$column = 1;
						foreach($row as $col=>$value)
						{
							$col = $this->check_rwords($col);
							$col = str_replace('_','~', $col);
							$this->Get->data[$table][0][$column++] = $col;
							//$column++;
						}
						break;
					}	
				}
				catch (Throwable $t)
				{
					$this->Msg->set_msg($t->getMessage());
				}
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->Get->save();
		odbc_close($link); 
	}
	function import_mysql($strTable)
	{
		$server = $this->Sys->get_record('server',1); 
		$link = mysqli_connect($server['ipmysql'],$server['usermysql'],$server['passmysql'],$server['dbmysql']);
		try
		{
			$this->Get->add_table_only($strTable);
			//$this->Get->add_table($strTable);
			$table = $this->Get->get_id_table($strTable); 
			//SKIPPY
			if($skippy = $this->Param->get_where_unique('tables','strtable',$strTable))
			{
				$skippy = explode(',',$skippy[3]);
				if(count($skippy) > 1)
				{
					$field = trim($skippy[0]);
					$op = trim($skippy[1]);
					$value = trim($skippy[2]);
				}
			}
			//SELECT EVERYTHING FROM THE TABLE
			$sql = 'select * from `'.$strTable.'`';	
		
			if($result = mysqli_query($link,$sql))
			{	
				try
				{
					$i=0;
					while($row = mysqli_fetch_array($result,MYSQLI_ASSOC))
					{
						if(isset($field) && $this->validation($row[$field],$op,$value)) continue;
						$c=1;
						foreach($row as $col=>$val)
						{
							if($this->Get->valid_foreign_key($col))
							{
								$this->Get->delete_table($table);
								$this->Msg->set_msg("A foreigh key constraint in the rules table does not allow the importation. Please import the master table before.");
								header('Location:'.WEBROOT.strtolower(get_class($this)));
								exit();
							}
							$this->Get->data[$table][0][$c] = $this->check_rwords($col);
							$this->Get->data[$table][$i+1][$c] = utf8_encode($val);
							$c++;
						}
						$i++;
					}				
				}
				catch (Throwable $t)
				{
					$this->Msg->set_msg($t->getMessage());
				}
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		return $this->Get->save();
	}
	function import_fields($strTable)
	{
		//SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='paie' AND `TABLE_NAME`='paies'
		$server = $this->Sys->get_record('server',1); 
		$link = mysqli_connect($server['ipmysql'],$server['usermysql'],$server['passmysql'],$server['dbmysql']);
		if (!$link) 
		{ 
			$this->Msg->set_msg('MYSQL is not running!');
			die("MYSQL is not running!"); 
		} 
		try
		{
			$this->Get->add_table_only($strTable);
			$table = $this->Get->get_id_table($strTable); 
			$db = $server['dbmysql'];
			$sql = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='$db' AND `TABLE_NAME`='$strTable'";
			if($result = mysqli_query($link,$sql))			
			{
				$column = 1;
				while($row = mysqli_fetch_array($result,MYSQLI_ASSOC))
				{
					foreach($row as $col=>$value)
					{
						//echo $value;
						$value = $this->check_rwords($value);
						//$col = str_replace('_','~', $col);
						$this->Get->data[$table][0][$column] = $value;
					}
					$column++;
				}
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->Get->save(); 
	}
	
	function check_rwords($str)
	{
		$rwords = $this->Sys->table('rwords');
		foreach($rwords as $i=>$rec)
		{
			foreach($rec as $r=>$col)
			{
				if($col == strtoupper($str))
				{
					$str = 'rw'.$str;
				}
			}
		}
		return $str;
	}
	function transfert($url)
	{
		$records = $this->Get->table($url[TABLE]);
		$cols = $this->Get->get_columns_of($url[TABLE]);	
		$tab = $this->Struct->get_id_table($url[TABLE]);
		$count = $this->Struct->count_lines($tab);
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/transfert/'.$url[TABLE];
			$this->question('Do you want to empty the table '.$url[TABLE].' before transfer ?',$refaction,$tab);
			exit;
		}
		if ($answer == 'yes')
		{
			$this->Struct->empty_table($tab);
		}
		if($tab == 0)
		{
			$this->Msg->set_msg("Table $url[TABLE] not exists in schema(php) or table name is not lowercase!");
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		foreach($records as $c=>$r)
		{
			//$this->preprint($post); exit;
			$post = $this->Get->combine($cols,$records[$c]);
			//$post['table'] = $tab; 
			//$this->preprint($post); exit;
			foreach($post as $col => $value)
			{
				$idcol = $this->Struct->get_id_column($tab,$col);
				if($idcol==0 || !$idcol)
				{
					continue;
				}

				if($answer !== 'yes')
				{
					$this->Struct->data[$tab][$count + $c][$idcol] = $value;
				}
				else
				{
					$this->Struct->data[$tab][$c][$idcol] = $value;
				}
			}
			if($answer !== 'yes')
			{
				ksort($this->Struct->data[$tab][$count + $c],SORT_NUMERIC);
			}
			else
			{
				ksort($this->Struct->data[$tab][$c],SORT_NUMERIC);
			}
		}
		
		foreach($this->Struct->data[$tab] as $i=>$rec)
		{
			if($i==0)
			{
				$columns = $this->Struct->data[$tab][0];
				continue;
			}
			foreach($rec as $col=>$value)
			{
				foreach($columns as $c=>$column)
				{
					if(array_key_exists($c,$rec))
					{
						$this->Struct->data[$tab][$i][$col] = $value;
					} 
					else
					{
						$this->Struct->data[$tab][$i][$c] = '-';
					}
				}
			}
			ksort($this->Struct->data[$tab][$i],SORT_NUMERIC);
		}
		//$this->preprint($this->Struct->data[$tab]); exit;
		$this->Struct->save();
		$this->Msg->set_msg("The table: $url[TABLE] has been transfered to schema");
		header('Location:'.WEBROOT.'schema/show_table/'.$url[TABLE]);
	}
	function export_to_mysql($url)
	{
		$server = $this->Sys->get_record('server',2);
		try
		{
			if($server['passmysql']=='-' || $server['passmysql']=='')
			{
				unset($server['passmysql']);
			}
			$export_results[] = $this->Get->to_mysql($server['ipmysql'],$server['usermysql'],@$server['passmysql'],$server['dbmysql'],$url[TABLE]);
			$this->Msg->set_msg('You export table '.$url[TABLE].' to MySQL.');
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['export_results'] = $export_results;
		$this->data['content'] = $this->Template->load('export_table_results',$this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	//Move to core controller
	/**function question($strQuestion=NULL,$action=NULL,$table=NULL,$post=NULL)
	{
		$this->data['question'] = $strQuestion;
		$this->data['action'] = $action;
		$this->data['table'] = $table;
		$this->data['post'] = $post;
		$this->data['content'] = $this->Template->load('yesno',$this->data,TRUE);
		// MAIN PAGE
		$this->Template->load('layout',$this->data);
	}*/
	function copy_column($url)
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
			@$this->Get->copy_column($strTable,$post['strfield'],$post['string']);
			$this->Msg->set_msg('You have duplicate column '.$post['strfield'].' to '.$post['string'].' in the table  '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Duplicate a column of the table: '.$strTable ;
		$this->data['placeholder'] = 'Duplicate a column';
		
		$this->data['columns'] = $this->Get->get_columns_of('actions');
		
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,TRUE,'column');
		$this->data['divstring'] = $this->Template->makediv('string','new column',' : New name for the field');
	
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/copy_column/'.$strTable;
		$this->data['content'] = $this->Template->load('copy-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function split_column($url)
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

			@$this->Get->split_column($strTable,$post['strfield'],$post['string'],$post['left'],$post['right']);
			$this->Msg->set_msg('You have splitted column '.$post['strfield'].' to '.$post['string'].' in the table  '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Split a column of the table: '.$strTable ;
		$this->data['placeholder'] = 'Split a column';

		$this->data['columns'] = $this->Get->get_columns_of('actions');

		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,FALSE,'column');
		$this->data['divleft'] = $this->Template->makediv('left','left',' : A number representing the length you want to keep from the left');
		$this->data['divright'] = $this->Template->makediv('right','right',' : A number representing the length you want to keep from the right');			
		$this->data['divstring'] = $this->Template->makediv('string','newcolumn',' : Enter a name for the new column');
		
		if(isset($post['strfield']))
		{
			$this->data['sample'] = $this->Get->get($strTable,1,$post['strfield']);
			$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',$post['strfield']);	
		}
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/split_column/'.$strTable;
		$this->data['content'] = $this->Template->load('split-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function split_column_needle($url)
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

			@$this->Get->split_column_needle($strTable,$post['strfield'],$post['string'],$post['value']);
			$this->Msg->set_msg('You have splitted column '.$post['strfield'].' to '.$post['string'].' in the table  '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Split a column with needle of the table: '.$strTable ;
		$this->data['placeholder'] = 'Split a column with a needle';

		$this->data['columns'] = $this->Get->get_columns_of('actions');

		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,FALSE,'column');
		$this->data['divstring'] = $this->Template->makediv('string','newcolumn',' : Enter a name for the new column');
		$this->data['divvalue'] = $this->Template->makediv('value','needle',' : The research string.');

		if(isset($post['strfield']))
		{
			$this->data['sample'] = $this->Get->get($strTable,1,$post['strfield']);
			$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',$post['strfield']);	
		}
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/split_column_needle/'.$strTable;
		$this->data['content'] = $this->Template->load('split-column-needle', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function move_column($url)
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
			@$this->Get->move_column($strTable,$post['strfield'],$post['totable']);
			$this->Msg->set_msg('You have move column '.$post['strfield'].' to table  '.$post['totable']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$post['totable']);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Move a column from table '.$strTable.' to another table.' ;
		$this->data['placeholder'] = 'Move a column';

		$this->data['columns'] = $this->Get->get_columns_of('actions');

		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,NULL,'column');	
		$this->data['listtotables'] = $this->Template->dropdown($this->Param,'tables','totable',2);
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/move_column/'.$strTable;
		$this->data['content'] = $this->Template->load('move-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function copy_column_keys($url)
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
			@$this->Get->copy_column_keys($strTable,$post['strfield'],$post['totable'],$post['tofield'],$post['string'],$post['operator'],$post['value']);
			$this->Msg->set_msg('You copied column '.$post['strfield'].' to  '.$post['tofield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$post['totable']);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Copy a column from table '.$strTable.' to another column by matching condition.';
		$this->data['placeholder'] = 'Copy a column to another';
		
		$this->data['columns'] = $this->Get->get_columns_of('actions');

		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,FALSE,NULL,' : This column to be copy to another');
		$this->data['listtotables'] = $this->Template->dropdown($this->Param,'tables','totable',2,NULL,FALSE,NULL,' : The table that will receive the column. It can be the same table which is '.$strTable);
		$this->data['listtofields'] = $this->Template->cdropdown($this->Get,NULL,'tofield',NULL,FALSE,NULL,' : The column that will receive the copy. It should already be created.');
		$this->data['divstring'] = $this->Template->cdropdown($this->Get,$strTable,'string',NULL,FALSE,'where',' : The field that will serve for matching condition');
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,NULL,FALSE);
		$this->data['divvalue'] = $this->Template->makediv('value','value',' : The value that will serve for matching condition');		
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/copy_column_keys/'.$strTable;
		$this->data['content'] = $this->Template->load('copy-column-keys', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function copy_data_keys($url)
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
			//copy_data_keys($strTable,$strColumn,$strToTable,$strToField,$left,$right,$string,$op='==',$value=null)
			@$this->Get->copy_data_keys($strTable,$post['strfield'],$post['totable'],$post['tofield'],$post['left'],$post['right'],$post['string'],$post['operator'],$post['value']);
			$this->Msg->set_msg('You copied data from '.$post['strfield'].' to  '.$post['tofield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$post['totable']);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Copy data from table '.$strTable.' [left] to another table [right] column by matching keys.';
		$this->data['placeholder'] = 'Copy data column to another table';

		$this->data['columns'] = $this->Get->get_columns_of('actions');

		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,FALSE,NULL,' : This column to be copy to another');
		$this->data['listtotables'] = $this->Template->dropdown($this->Param,'tables','totable',2,NULL,FALSE,NULL,' : The table that will receive the column. It can be the same table which is '.$strTable);
		$this->data['listtofields'] = $this->Template->cdropdown($this->Get,NULL,'tofield',NULL,FALSE,NULL,' : The column that will receive the copy. It should already be created.');
		$this->data['divleft'] = $this->Template->makediv('left','left',' : Left keyname field to match');
		$this->data['divright'] = $this->Template->makediv('right','right',' : Right keyname field to match');
		$this->data['divstring'] = $this->Template->cdropdown($this->Get,$strTable,'string',NULL,FALSE,'where',' : The field that will serve for matching condition');
		//dropdown($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL,$offset=0)
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,NULL,FALSE,NULL,' : You can use the "LIKE" operator to search a string into the field.');
		$this->data['divvalue'] = $this->Template->makediv('value','value',' : The value that will serve for matching condition');		
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/copy_data_keys/'.$strTable;
		$this->data['content'] = $this->Template->load('copy-data-keys', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function copy_text_where($url)
	{
		$strTable=$url[TABLE];
		$this->properties('left',$strTable);
		$post = @$_POST;
		//var_dump($post); exit;
		try
		{
			if(!$this->Get->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			//copy_text_where($strTable,$strColumn,$strLeft,$string,$op='==',$value)
			@$this->Get->copy_text_where($strTable,$post['strfield'],$post['left'],$post['string'],$post['operator'],$post['value']);
			$this->Msg->set_msg('You copied text '.$post['left'].' to  '.$post['strfield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Table '.$strTable.' : copy text to column by matching condition.';
		$this->data['placeholder'] = 'Copy text by mathching condition ';
		
		$this->data['columns'] = $this->Get->get_columns_of('actions');

		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,FALSE,'column',' : This column will receive the text');
		$this->data['divleft'] =  $this->Template->makediv('left','text',' : The text that will be copied'); 
		$this->data['divstring'] = $this->Template->cdropdown($this->Get,$strTable,'string',NULL,FALSE,'where',' : The field that will serve for matching condition');
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,NULL,FALSE);
		$this->data['divvalue'] = $this->Template->makediv('value','value',' : The value that will serve for matching condition');		
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/copy_text_where/'.$strTable;
		$this->data['content'] = $this->Template->load('copy-text-where', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function merge_rows($url)
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
			$answer = @$_POST['inlineRadioOptions'];
			if(!isset($answer) && isset($post['table']) && isset($post['strfield']) && isset($post['tofield']) && isset($post['unique']))
			{
				$tab = $this->Get->get_id_table($strTable);
				$refaction = WEBROOT.strtolower(get_class($this)).'/merge_rows/'.$url[TABLE];
				$this->question('Are you sure you want to merge rows of table '.$url[TABLE].' into '.$this->colorize($post['unique'],'red').' ?',$refaction,$tab,$post);
				exit;
			}
			elseif($answer=='yes')
			{
				//merge_rows($strTable,$strColKey,$strColOrder,$strColResult)
				@$this->Get->merge_rows($strTable,$post['strfield'],$post['tofield'],$post['unique']);
				$this->Msg->set_msg('You merge column '.$post['unique'].' using '.$post['strfield'].' order by '.$post['tofield']);
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
				exit;
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Merge rows from table '.$strTable.' to a column in the first row by matching keys.';
		$this->data['placeholder'] = 'Merge rows to a single column';
		
		$this->data['columns'] = $this->Get->get_columns_of('actions');
		//$this->Template->cdropdown($db,$strTable,$selectName,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,FALSE,'multiple',' : Multiple keys matching rows');
		$this->data['listtofields'] = $this->Template->cdropdown($this->Get,$strTable,'tofield',NULL,FALSE,'line',' : The field that will serve for sorting');
		$this->data['listuniques'] = $this->Template->cdropdown($this->Get,$strTable,'unique',NULL,FALSE,'concatenation',' : The column that will receive the concat text. First row of all. Other rows will be deleted.');	
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/merge_rows/'.$strTable;
		$this->data['content'] = $this->Template->load('merge-rows', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function move_one_to_many($url)
	{
		$strTable=$url[TABLE];	
		$this->properties('left',$strTable);
		$post = @$_POST;
		//var_dump($post); exit;
		try
		{
			if(!$this->Get->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			//move_column_keys($strTable,$strColumn,$strToTable,$strTableKey,$strToTableKey)
			//$this->preprint($post); exit;
			@$this->Get->move_one_to_many($strTable,$post['column'],$post['totable'],$post['tofield'],$post['unique']);
			$this->Msg->set_msg('You have move column '.$post['column'].' to table '.$post['totable']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$post['totable']);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Move a column from table '.$strTable.' to any table by matching keys. (one to many)';
		$this->data['placeholder'] = 'Move a column';
	
		$this->data['columns'] = $this->Get->get_columns_of('actions');
		
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'column',NULL,' : This column to be move');
		$this->data['listtotables'] = $this->Template->dropdown($this->Param,'tables','totable',2,NULL,TRUE,NULL,' : The table that will receive the column.');
		$this->data['listtofields'] = $this->Template->cdropdown($this->Get,'empty','tofield',NULL,' : Match keys of the table that will receive the column');
		$this->data['listuniques'] = $this->Template->cdropdown($this->Get,$strTable,'unique',NULL,' : Unique key of the table that has the column you want to move');	
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/move_one_to_many/'.$strTable;
		$this->data['content'] = $this->Template->load('move-one-to-many', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
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
		$this->data['legend'] = 'Renumber a column of the table: '.$strTable ;
		$this->data['placeholder'] = 'Renumber a column';
		
		$this->data['columns'] = $this->Get->get_columns_of('actions');

		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,NULL,'column',' : Column to be renumbered');	
		$this->data['divvalue'] = $this->Template->makediv('value','start',' : Beginning value');	
				
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/renumber_column/'.$strTable;
		$this->data['content'] = $this->Template->load('renumber-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function match_column($url)
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
			//matches($strMaster,$strMasterOldColumn,$strSlave,$strSlaveOldColumn,$strMasterNewNumbersColumn)
			@$this->Get->matches($strTable,$post['strfield'],$post['totable'],$post['tofield'],$post['unique']);
			$this->Msg->set_msg('You have reassigned column '.$post['unique'].' from '.$strTable.' to the table '.$post['totable'].' field : '.$post['tofield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Reassign key values from the master table : '.$strTable ;
		$this->data['placeholder'] = 'Reassign a key column';
		$this->data['columns'] = $this->Get->get_columns_of('actions');

		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,NULL,'Master old key',' : This column contains original keys');
		$this->data['listtotables'] = $this->Template->dropdown($this->Param,'tables','totable',2,NULL,FALSE,'Slave',' : Slave table that will have multiple key value from master table','Slave');
		$this->data['listtofields'] = $this->Template->cdropdown($this->Get,NULL,'tofield',NULL,NULL,'Slave key',' : Column to match the master old key and then change it for new key.');
		$this->data['listuniques'] = $this->Template->cdropdown($this->Get,$strTable,'unique',NULL,NULL,'Master new key',' : This column contains new keys');
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/match_column/'.$strTable;
		$this->data['content'] = $this->Template->load('match-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}	
	function concat_columns($url)
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
			//$this->Get->concat_columns($strTable,$filter,$strToColumn,$sep=',')
			@$this->Get->concat_columns($strTable,$post['string'],$post['column'],$post['value']);
			$this->Msg->set_msg('You have concated columns '.$post['string'].' to '.$post['column'].' in the table '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Concat two or more columns of the table: '.$strTable ;
		$this->data['placeholder'] = 'Concat columns';
		
		$this->data['columns'] = $this->Get->get_columns_of('actions');
		
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'column',NULL,TRUE,NULL,' : This column to receive the concatening');
		$this->data['divstring'] = $this->Template->makediv('string','filter',' : Separate wanted fields with a comma ex: Addr1,City,State');
		$this->data['divvalue'] = $this->Template->makediv('value','delimiter',' : Set a result delimiter, if empty it will be a space by default');	
			
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/concat_columns/'.$strTable;
		$this->data['design'] = (object)$this->Template;
		$this->data['content'] = $this->Template->load('concat-columns', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function date_corrector($url)
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
			//$this->Get->concat_columns($strTable,$filter,$strToColumn,$sep=',')
			@$this->Get->date_corrector($strTable,$post['strfield'],$post['operator']);
			$this->Msg->set_msg('You fixed date column '.$post['strfield'].' in the table '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Fix dates in the table: '.$strTable ;
		$this->data['placeholder'] = 'Fix dates';

		$this->data['columns'] = $this->Get->get_columns_of('actions');
		//$this->Template->dropdown($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL,$offset=0);
		//$this->Template->cdropdown($db,$strTable,$selectName,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		//$this->Template->datalist($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		//$this->Template->makediv($colonne,$label=NULL,$help=NULL,$value=NULL);
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,TRUE,'column',' : This date column to be corrected');
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,NULL,TRUE,NULL,' : Identify the current format of the date you want to change','format');
				
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/date_corrector/'.$strTable;
		$this->data['content'] = $this->Template->load('date-corrector', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	
	function time_corrector($url)
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
			@$this->Get->time_corrector($strTable,$post['strfield'],$post['operator']);
			$this->Msg->set_msg('You fixed time column '.$post['strfield'].' in the table '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Fix time field in the table: '.$strTable ;
		$this->data['placeholder'] = 'Fix time field';

		$this->data['columns'] = $this->Get->get_columns_of('actions');
		//$this->Template->dropdown($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL,$offset=0);
		//$this->Template->cdropdown($db,$strTable,$selectName,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		//$this->Template->datalist($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		//$this->Template->makediv($colonne,$label=NULL,$help=NULL,$value=NULL);
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,TRUE,'column',' : This time column to be corrected');
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,NULL,TRUE,NULL,' : Identify the current format of the time field you want to change','format');
				
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/time_corrector/'.$strTable;
		$this->data['content'] = $this->Template->load('time-corrector', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	
	function copy_table($url)
	{
		$copy = $this->Get->copy_table($url[TABLE]);
		$lastdm = $this->Param->get_last('tables');
		$idtabdm = $this->Param->get_id_table('tables');
		$post['table'] = $idtabdm;
		$post['id_table'] = ++$lastdm;
		$post['strtable'] = $copy;
		$this->Param->add_line($post,'id_table');
		
		$this->Msg->set_msg('You have duplicated table : '.$url[TABLE]);
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.'copy'.strtolower($url[TABLE]));	
	}
	function add_action()
	{
		$this->data['title'] = $this->Sys->get_cell(1,1,3);
		$this->data['head'] = $this->Template->load('head-add-action',$this->data,TRUE);
		
		$strTable='actions';
		$block = isset($_SESSION['sblock'])?$_SESSION['sblock']:'';
		//$this->Template->dropdown($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		$this->data['listblocks'] = $this->Template->dropdown($this->Sys,'blocks','block',2,$block);
		//LEFT
		$this->properties('left',$strTable,'properties-action');
		
		$post = @$_POST;
		//var_dump($post); exit;
		try
		{
			if(!$this->Get->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			$last = $this->Get->get_last($this->Get->table);
			$post[$this->Get->primary] = ++$last;
			if(isset($post['action']))
			{
				$this->clean_post_action($post);
			}
			$this->Get->add_line($post,$this->Get->primary);
			$this->Msg->set_msg('You have added an action : '.$post['action']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Add an action';
		$this->data['placeholder'] = 'Add an action';
		$this->data['columns'] = $this->Get->get_columns_of($strTable);
		//$this->Template->dropdown($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		$this->data['listactions'] = $this->Template->dropdown($this->Sys,'scripts','action',1,NULL,FALSE,NULL,NULL,1);
		$this->data['liststrtables'] = $this->Template->dropdown($this->Param,'tables','strtable',2);
		//$this->Template->cdropdown($db,$strTable,$selectName,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,NULL,'strfield');
		$this->data['listtotables'] = $this->Template->dropdown($this->Param,'tables','totable',2);
		$this->data['listtofields'] = $this->Template->cdropdown($this->Get,NULL,'tofield');
		$this->data['divleft'] = $this->Template->makediv('left','left');
		$this->data['divright'] = $this->Template->makediv('right','right');			
		$this->data['divstring'] = $this->Template->datalist($this->Get,NULL,'string',2);
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2);
		$this->data['divvalue'] = $this->Template->makediv('value','value');
		$this->data['listuniques'] = $this->Template->cdropdown($this->Get,NULL,'unique');
		//$this->data['design'] = (object)$this->Template;
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/add_action/';
		$this->data['content'] = $this->Template->load('add-action', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function edit_action($url)
	{
		$this->data['title'] = $this->Sys->get_cell(1,1,3);
		$this->data['head'] = $this->Template->load('head-edit-action',$this->data,TRUE);
		$strTable=$url[TABLE];
		try
		{
			if(!$this->Get->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			$block = isset($_SESSION['sblock'])?$_SESSION['sblock']:'';
			$this->data['listblocks'] = $this->Template->dropdown($this->Sys,'blocks','block',2,$block);
			$this->properties('left',$strTable,'properties-action');
			$post = @$_POST;
			if(isset($post['action']))
			{
				$this->clean_post_action($post);
			}
			$this->Get->set_line($post);
			$this->Msg->set_msg('You have changed a record at the table : '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit;
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Edit an action' ;
		$this->data['placeholder'] = 'Edit an action';
		$this->data['columns'] = $this->Get->get_columns_of($strTable);
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['line'] = $url[INDEX];
		$this->data['record'] = $this->Get->get_line($this->data['table'],$url[INDEX]);	
	
		$this->data['listactions'] = $this->Template->dropdown($this->Sys,'scripts','action',1,$this->data['record'][2],TRUE,NULL,NULL,1);
		$this->data['liststrtables'] = $this->Template->dropdown($this->Param,'tables','strtable',2,$this->data['record'][3],$header=TRUE);
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$this->data['record'][3],'strfield',$this->data['record'][4]);
		$this->data['listtotables'] = $this->Template->dropdown($this->Param,'tables','totable',2,$this->data['record'][5],$header=TRUE);
		$this->data['listtofields'] = $this->Template->cdropdown($this->Get,$this->data['record'][5],'tofield',$this->data['record'][6]);
		$this->data['divleft'] = $this->Template->makediv('left','left',NULL,$this->data['record'][7]);
		$this->data['divright'] = $this->Template->makediv('right','right',NULL,$this->data['record'][8]);		
		$this->data['divstring'] = $this->Template->datalist($this->Get,$this->data['record'][3],'string',2,$this->data['record'][9]);
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,$this->data['record'][10],$header=FALSE);
		$this->data['divvalue'] = $this->Template->makediv('value','value',NULL,$this->data['record'][11]);
		$this->data['listuniques'] = $this->Template->cdropdown($this->Get,$this->data['record'][3],'unique',$this->data['record'][12]);
		
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/edit_action/'.$strTable.'/'.$url[INDEX];
		$this->data['content'] = $this->Template->load('edit-action', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	
	function get_fields()
	{
		$cols = $this->Get->get_columns_of($_POST['strtable']);
		foreach($cols as $id=>$col)
		{
			$fields_arr[] = array("id" => $id, "col" => $col);	
		}
		echo json_encode($fields_arr);
	}
	
	function get($url)
	{
		$cols = $this->Get->get_columns_of($url[TABLE]);
		$rec = $this->Get->get_where_unique($url[TABLE],$url[FIELD],$url[VALUE]);
		$this->Get->unescape($rec);
		$record = $this->Get->combine($cols,$rec);
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($record,JSON_UNESCAPED_UNICODE);
		return json_encode($record);
	}
	function get_sfields()
	{
		$cols = $this->Get->get_columns_of($_POST['totable']);
		foreach($cols as $id=>$col)
		{
			$fields_arr[] = array("id" => $id, "col" => $col);	
		}
		echo json_encode($fields_arr);
	}
	function get_count()
	{
		$count = $this->Get->count($_POST['strtable']);
		echo json_encode($count);
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
	function execute_all_actions()
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(empty($answer))
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/execute_all_actions';
			$this->question('Are you sure you want to execute all actions scripts blocks ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			$table = $this->Sys->get_id_table('blocks');
			$blocks = $this->Sys->get_table($table);
			foreach($blocks as $id=>$block)
			{
				if($id==0)continue;
				$this->Get->load_script($block[2]);
				//sleep(1);
				$this->execute_actions();
				//sleep(1);
				/*if($id == 8 )
				{
					break;
				}*/
			}
		}
		else
		{
			header('Location:'.WEBROOT.strtolower(get_class($this)));
		}
	}
	function execute_actions()
	{		
		/*		
		1	import table	
		2	rename table	
		3	empty table	
		4	delete table	
		5	duplicate table
		6	add a field	
		7	rename a field	
		8	delete a column	
		9	duplicate a column	
		10	split a column	
		11	move a column to another table	
		12	delete records selection
		13	transfert table to schema.php	
		14	add a table	
		15	renumber a column	
		16	column number reassignment
		17  move a column match one to many
		18  copy a column match keys
		19  concat columns
		20  date corrector
		21  find and replace
		22  merge rows
		23  split column needle
		24  copy text where
		25  copy data keys
		26  load big data
		27  direct export to mysql
		28  save big data
		29  time corrector
		30  save as csv
		31  load a csv
		*/
		
		/*Fields of table: actions
		1 id_action
		2 action
		3 strtable
		4 strfield
		5 totable
		6 tofield
		7 left
		8 right
		9 string
		10 operator
		11 value
		12 unique */
		
		$acts = $this->Get->table('actions');
		//$this->preprint($acts); exit;
		//$act[2] is the action
		foreach($acts as $i=>$act)
		{
			$this->Get->unescape($act[10]);
			switch($act[2])
			{
				// import table
				case 1:
					try
					{
						$strTable = $act[3];
						$strKey = 'id_'.substr($strTable, 0, -1);
						$server = $this->Sys->get_record('server',1); 
						$link = mysqli_connect($server['ipmysql'],$server['usermysql'],$server['passmysql'],$server['dbmysql']);
						$sql = "SELECT $strKey FROM $strTable;";	
						$res = mysqli_query($link,$sql);
						$rows = mysqli_num_rows($res);
						mysqli_free_result($res);
						if($rows > 0)
						{
							$this->import_mysql($act[3]);
							$this->Msg->set_msg('You have imported table '.$act[3]);							
						}
						else
						{
							$this->import_fields($act[3]);
							$this->Msg->set_msg("You have imported table $act[3] but it is empty !");
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// rename table
				case 2:
					try
					{
						$id_table = $this->Get->get_id_table($act[3]);
						$strTableName = $this->Get->get_table_name($id_table);
						$strTable = $act[9];
						if($this->Get->table_exists($strTableName))
						{
							$this->Get->edit_table($id_table,$strTable);
							//For parameters
							$post['table'] = $this->Param->get_id_table('tables');
							$post['line'] = $this->Param->get_real_id($post['table'],'strtable',$strTableName);
							$rec = $this->Param->get_line($post['table'],$post['line']);
							$post['id_table'] = $rec[1];
							$post['strtable'] = strtolower($strTable);
							$this->Param->set_line($post);
							//
							$this->Msg->set_msg('You renamed the table: '.$strTableName.' for: '.$strTable);
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// empty table
				case 3:
					try
					{
						if($this->Get->table_exists($act[3]))
						{
							$this->Get->empty_table($this->Get->get_id_table($act[3]));
							$this->Msg->set_msg('You empty the table : '.$act[3]);
						}
						else
						{
							$this->Msg->set_msg('Table : '.$act[3].' does not exists!');
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// delete table
				case 4:
					try
					{
						if($this->Get->table_exists($act[3]))
						{
							$this->Get->delete_table($this->Get->get_id_table($act[3]));
							$this->Msg->set_msg('You deleted the table : '.$act[3]);
						}
						else
						{
							$this->Msg->set_msg('Table : '.$act[3].' does not exists!');
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// duplicate table
				case 5:
					try
					{
						if($this->Get->table_exists($act[3]))
						{
							$this->Get->copy_table($act[3]);
							$this->Msg->set_msg('You have duplicated the table : '.$act[3]);
						}
						else
						{
							$this->Msg->set_msg('Table : '.$act[3].' does not exists!');
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// add a field
				case 6:
					try
					{
						if($this->Get->add_column($this->Get->get_id_table($act[3]),$act[9]))
						{
							$this->Msg->set_msg('You have added the field: '.$act[9].' to the table: '.$act[3]);
						}
						else
						{
							$this->Msg->set_msg('Cannot add the field: '.$act[9].' to the table: '.$act[3]);
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// rename a field
				case 7:
					try
					{
						$table = $this->Get->get_id_table($act[3]);
						//edit_column($table,$column,$strColumn)
						if($this->Get->edit_column($table,$this->Get->get_id_column($table,$act[4]),$act[9]))
						{
							$this->Msg->set_msg('You renamed the field: '.$act[4].' for '.$act[9].' to the table: '.$act[3]);
						}
						else
						{
							$this->Msg->set_msg('Cannot rename the field: '.$act[4].' for '.$act[9].' to the table: '.$act[3]);
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// delete a column
				case 8:
					try
					{
						$table = $this->Get->get_id_table($act[3]);
						$column = $this->Get->get_id_column($table,$act[4]);
						$nbrColonne = $this->Get->count_columns($table);
						$this->Get->delete_column($table,$column);
						if(--$nbrColonne == 0)
						{
							$this->Msg->set_msg('Since there was no more field, you deleted the table: '.$act[3]);
						}
						else
						{
							$this->Msg->set_msg('You removed the field: '.$act[4].' from the table  '.$act[3]);
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// duplicate a column
				case 9:
					try
					{
						$this->Get->copy_column($act[3],$act[4],$act[9]);
						$this->Msg->set_msg('You have duplicate column '.$act[4].' to '.$act[9].' in the table  '.$act[3]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// split a column
				case 10:
					try
					{
						//$this->Get->split_column($strTable,$post['column'],$post['newcolumn'],$post['left'],$post['right']);
						$this->Get->split_column($act[3],$act[4],$act[9],$act[7],$act[8]);
						$this->Msg->set_msg('You have splitted column '.$act[4].' to '.$act[9].' in the table  '.$act[3]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// move a column to another table
				case 11:
					try
					{
						//move_column($strTable,$post['column'],$post['totable']);
						$this->Get->move_column($act[3],$act[4],$act[5]);
						$this->Msg->set_msg('You have move column '.$act[4].' to table '.$act[5]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// delete records selection
				case 12:
					try
					{
						//$this->Get->del_lines_where($strTable,$post['field'],$post['operator'],$post['value'],$post['unique']);
						$this->Get->del_lines_where($act[3],$act[4],$act[10],$act[11],$act[12]);
						$this->Msg->set_msg('You deleted selection from table: '.$act[3].' where '.$act[4].' '.$act[10].' '.$act[11]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// transfer table to structure(php)
				case 13:
					try
					{
						$act[3] = strtolower($act[3]);
						$records = $this->Get->table($act[3]);
						$cols = $this->Get->get_columns_of($act[3]);	
						$tab = $this->Struct->get_id_table($act[3]); 
						$answer ='no';
						$count = $this->Struct->count_lines($tab);
						if($tab == 0)
						{
							$this->Msg->set_msg('['.$act[3].'] Table name does not match any table in schema. Tables and fields are case-sensitive.');
						}
						else
						{
							foreach($records as $c=>$r)
							{
								$post = $this->Get->combine($cols,$records[$c]);
								//$post['table'] = $tab; 
								//$this->preprint($post); exit;
								foreach($post as $col => $value)
								{
									$idcol = $this->Struct->get_id_column($tab,$col);
									if($idcol==0 || !$idcol)
									{
										continue;
									}

									if($answer !== 'yes')
									{
										$this->Struct->data[$tab][$count + $c][$idcol] = $value;
									}
									else
									{
										$this->Struct->data[$tab][$c][$idcol] = $value;
									}
								}
								if($answer !== 'yes')
								{
									ksort($this->Struct->data[$tab][$count + $c],SORT_NUMERIC);
								}
								else
								{
									ksort($this->Struct->data[$tab][$c],SORT_NUMERIC);
								}
							}
							
							foreach($this->Struct->data[$tab] as $i=>$rec)
							{
								if($i==0)
								{
									$columns = $this->Struct->data[$tab][0];
									continue;
								}
								foreach($rec as $col=>$value)
								{
									foreach($columns as $c=>$column)
									{
										if(array_key_exists($c,$rec))
										{
											$this->Struct->data[$tab][$i][$col] = $value;
										} 
										else
										{
											$this->Struct->data[$tab][$i][$c] = '';
										}
									}
								}
								ksort($this->Struct->data[$tab][$i],SORT_NUMERIC);
							}
							//$this->preprint($this->Struct->data[$tab]); exit;
							$this->Struct->save();
							$this->Msg->set_msg('The table : '.$act[3].' has been transfered to schema(php)');
						}
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// add a table
				case 14:
					try
					{
						$this->Get->add_table($act[9]);
						//For dmtables
						$lastdm = $this->Param->get_last('tables');
						$idtabdm = $this->Param->get_id_table('tables');
						$post['table'] = $idtabdm;
						$post['id_table'] = ++$lastdm;
						$post['strtable'] = strtolower($act[9]);
						$this->Param->add_line($post,'id_table');
						//
						$this->Msg->set_msg('You added table '.$act[9]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// renumber a column
				case 15:
					try
					{
						$this->Get->renumber($act[3],$act[4],$act[11]);
						$this->Msg->set_msg('You have renumbered column '.$act[4].' from table '.$act[3]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// column number reassignment
				case 16:
					try
					{
						//matches($strMaster,$strMasterOldColumn,$strSlave,$strSlaveOldColumn,$strMasterNewNumbersColumn)
						$this->Get->matches($act[3],$act[4],$act[5],$act[6],$act[12]);
						$this->Msg->set_msg('You have matched column '.$act[9].' from '.$strTable.' to the table  '.$act[3]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// move column keys
				case 17:
					try
					{
						//move_column_keys($strTable,$strColumn,$strToTable,$strToTableKey,$strTableKey)
						$this->Get->move_one_to_many($act[3],$act[4],$act[5],$act[6],$act[12]);
						$this->Msg->set_msg('You have move column '.$act[4].' from '.$act[3].' to the table '.$act[5].' where '.$act[6].' matching '.$act[12]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// copy column keys
				case 18:
					try
					{
						//copy_column_keys($strTable,$strColumn,$strToTable,$strToField,$string,$op='==',$value)
						$this->Get->copy_column_keys($act[3],$act[4],$act[5],$act[6],$act[9],$act[10],$act[11]);
						$this->Msg->set_msg('You have copy column '.$act[4].' from '.$act[3].' to the column '.$act[6].' of '.$act[5].' where '.$act[9].' '.$act[10].' '.$act[11]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// concat columns
				case 19:
					try
					{
						//concat_columns($strTable,$filter,$strToColumn,$delim=',')
						$this->Get->concat_columns($act[3],$act[9],$act[4],$act[11]);
						$this->Msg->set_msg('You have concated columns '.$act[9].' to '.$act[4].' in the table '.$act[3]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// date corrector
				case 20:
					try
					{
						//date_corrector($strTable,$strColumn,$format)
						$this->Get->date_corrector($act[3],$act[4],$act[10]);
						$this->Msg->set_msg('You have fixed date of column '.$act[4].' in the table '.$act[3]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// find replace
				case 21:
					try
					{
						//find_replace($strTable,$strColumn,$find,$replace)
						$this->Get->find_replace($act[3],$act[4],$act[9],$act[11]);
						$this->Msg->set_msg('You have replaced '.$act[9].' for '.$act[11].' in table '.$act[3].' column '.$act[4]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// merge rows
				case 22:
					try
					{
						// merge_rows($strTable,$strColKey,$strColOrder,$strColResult)
						$this->Get->merge_rows($act[3],$act[4],$act[9],$act[12]);
						$this->Msg->set_msg('You merge column '.$act[12].' using '.$act[4].' order by '.$act[9]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// split column needle
				case 23:
					try
					{
						//split_column_needle($strTable,$strColumnFrom,$strColumnTo,$needle=NULL)
						$this->Get->split_column_needle($act[3],$act[4],$act[9],$act[11]);
						$this->Msg->set_msg('You have splitted column '.$act[4].' to '.$act[9].' in the table  '.$act[3]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// copy text where
				case 24:
					try
					{
						//copy_text_where($strTable,$strColumn,$text,$string,$op='==',$value=NULL)
						$this->Get->copy_text_where($act[3],$act[4],$act[7],$act[9],$act[10],$act[11]);
						$this->Msg->set_msg('You copied text '.$act[7].' to  '.$act[4]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// copy data keys
				case 25:
					try
					{
						//copy_data_keys($strTable,$strColumn,$strToTable,$strToField,$left,$right,$string,$op='==',$value=null)
						$this->Get->copy_data_keys($act[3],$act[4],$act[5],$act[6],$act[7],$act[8],$act[9],$act[10],$act[11]);
						$this->Msg->set_msg('You copied text '.$act[3].'->'.$act[4].' to  '.$act[5].'->'.$act[6]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// load big data
				case 26:
					try
					{
						//copy_data_keys($strTable,$strColumn,$strToTable,$strToField,$left,$right,$string,$op='==',$value=null)
						$this->Get->load_big_data($act[3],$act[11]);
						$this->Msg->set_msg('You have loaded '.$act[3].'->'.$act[11].' from action 26');
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// export to mysql
				case 27:
					try
					{
						$server = $this->Sys->get_record('server',1);			
						if($server['passmysql']=='-' || $server['passmysql']=='')
						{
							unset($server['passmysql']);
						}
						//$msg = $this->Get->to_mysql($server['ipmysql'],$server['usermysql'],@$server['passmysql'],$server['dbmysql'],$act[3]);
						$msg = $this->Get->to_mysql($server['ipmysql'],$server['usermysql'],@$server['passmysql'],$server['dbmysql'],$act[3]);
						$this->Msg->set_msg($msg);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;
				// save big data
				case 28:
					try
					{
						$this->Get->save_big_data($act[3],$act[11]);
						$this->Msg->set_msg('You have saved '.$act[3].'->'.$act[11]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;	
				// time corrector
				case 29:
					try
					{
						$this->Get->time_corrector($act[3],$act[4],$act[10]);
						$this->Msg->set_msg('You have fixed time of column '.$act[4].' in the table '.$act[3]);
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;	
				// save as .csv
				case 30:
					try
					{
						$this->Get->save_csv($act[3],$act[11]);
						$this->Msg->set_msg('You saved '.$act[3].'.csv');
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;	
				// load a .csv file
				case 31:
					/*Fields of table: actions
					1 id_action
					2 action
					3 strtable
					4 strfield
					5 totable
					6 tofield
					7 left
					8 right
					9 string
					10 operator
					11 value
					12 unique */
					try
					{
						$this->Get->load_csv($act[3]);
						$this->Msg->set_msg('You loaded '.$act[3].'.csv');
					}
					catch (Throwable $t)
					{
						$this->Msg->set_msg($t->getMessage());
					}
				break;				
			}
			
		}
		header('location:'.WEBROOT.strtolower(get_class($this)));	
	}
	function find_replace($url)
	{
		$strTable=$url[TABLE];
		//LEFT
		$this->properties('left',$strTable);

		$post = @$_POST;
		try
		{
			if(!$this->Get->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			//find_replace($strTable,$strColumn,$find,$replace)
			@$this->Get->find_replace($strTable,$post['strfield'],$post['string'],$post['value']);
			$this->Msg->set_msg('You have replaced '.$post['string'].' to  '.$post['value']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Find and replace a text in a column of the table '.$strTable;
		$this->data['placeholder'] = 'Search a column';
		
		$this->data['columns'] = $this->Get->get_columns_of('actions');
		
		$this->data['liststrfields'] = $this->Template->cdropdown($this->Get,$strTable,'strfield',NULL,NULL,'column',' : Search this column','column');
		$this->data['divstring'] = $this->Template->makediv('string','filter',' : Text to search');	
		$this->data['divvalue'] = $this->Template->makediv('value','text',' : Replace by this text');
		
		$this->data['table'] = $this->Get->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/find_replace/'.$strTable;
		$this->data['design'] = (object)$this->Template;
		$this->data['content'] = $this->Template->load('find-replace', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}

	function clean_post_action(&$post)
	{
		switch((int)$post['action'])
		{
			case 1 :
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 2 :
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 3 :
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 4 :
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';		
			break;
			case 5 :
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';							
			break;
			case 6 :
				//6	add a field
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 7 :
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';		
			break;
			case 8 :
				//8	delete a column	
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';		
			break;
			case 9 :
				// 9 duplicate a column
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';		
			break;
			case 10 :
				// 10 split a column
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				//$post['left'] = '';
				//$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';
			break;
			case 11 :
				// 11	move a column 
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				//$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';
			break;
			case 12 :
				//12 delete records
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				//$post['operator'] = '';
				//$post['value'] = '';
				//$post['unique'] = '';
			break;
			case 13 :
				// 13 transfert table to structure(php)
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 14 :
				// 14 add a table
				$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 15 :
			    // 15 renumber a column
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;	
			case 16 :
				// 16 column reassignment
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				//$post['totable'] = '';
				//$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				//$post['unique'] = '';
			break;	
			case 17 :
				// 17 move a column match one to many
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				//$post['totable'] = '';
				//$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				//$post['unique'] = '';
			break;	
			case 18 :
				// 18 copy column match keys
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				//$post['totable'] = '';
				//$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				//$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';
			break;	
			case 19 :
				// 19 concat columns
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;	
			case 20 :
				// 20 date corrector
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				//$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;	
			case 21 :
				// 21 find and replace
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;	
			case 22 :
				// 21 merge rows
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				//$post['unique'] = '';	
			break;	
			case 23 :
				// 21 split column needle
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 24 :
				// 24 copy text where
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				//$post['left'] = '';
				$post['right'] = '';
				//$post['string'] = '';
				//$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 25 :
				// 25 copy data keys
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				//$post['totable'] = '';
				//$post['tofield'] = '';
				//$post['left'] = '';
				//$post['right'] = '';
				//$post['string'] = '';
				//$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 26 :
				// 26 load big data 
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 27 :
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 28 :
				// 28 save big data 
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 29 :
				// 20 date corrector
				//$post['strtable'] = '';	
				//$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				//$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;	
			case 30 :
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				$post['value'] = '';
				$post['unique'] = '';	
			break;
			case 31 :
				//$post['strtable'] = '';	
				$post['strfield'] = '';	
				$post['totable'] = '';
				$post['tofield'] = '';
				$post['left'] = '';
				$post['right'] = '';
				$post['string'] = '';
				$post['operator'] = '';
				//$post['value'] = '';
				$post['unique'] = '';	
			break;
		}
	}
	function ini()
	{
		$this->Get->initialize();
		//$this->Get->create_demo();
		
		/*$dir = DATADIRECTORY;
		// Ouvre un dossier bien connu, et liste tous les fichiers
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
					//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
					$files[]=$file;
				}
				closedir($dh);
			}
		}
		rsort($files, SORT_NATURAL | SORT_FLAG_CASE);
		foreach ($files as $key => $val) 
		{
			$pos = strripos($val, DEFAULTDATABASE);
			if ($pos === false) 
			{
				echo "Sorry, we did not find ".DEFAULTDATABASE;
			} 
			else 
			{
				$sfile = explode('.',$val);
				rename( DATADIRECTORY.$val,DATADIRECTORY.$sfile[0].'.php');
				$this->Get->connect(DATADIRECTORY,DEFAULTDATABASE,'php');
				break;
			}
		}*/
		$this->Msg->set_msg('You have initialized '.$this->data['title']);
		header('Location:'.WEBROOT.strtolower(get_class($this)));
	}
	function load_last_bkp()
	{
		$dir = DATADIRECTORY;
		// Ouvre un dossier bien connu, et liste tous les fichiers
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
					//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
					$files[]=$file;
				}
				closedir($dh);
			}
		}
		rsort($files, SORT_NATURAL | SORT_FLAG_CASE);
		//var_dump($files); exit();
		foreach ($files as $key => $val) 
		{
			$pos = strripos($val, DEFAULTDATABASE);
			
			if ($pos === false) 
			{
				//echo "Sorry, we did not find ".DEFAULTDATABASE.'.php <br>';
				$this->Msg->set_msg('Sorry, we did not find '.DEFAULTDATABASE.'.php');
			} 
			else 
			{
			$str = "Found! file [$val] : type [". filetype($dir . $val) ."]";
				$this->Msg->set_msg($str);
				$sfile = explode('.',$val);
				if( strlen($sfile[1]) > 3 && $sfile[1] != 'html')
				{
					rename( DATADIRECTORY.$val,DATADIRECTORY.$sfile[0].'.php');
					$this->Get->connect(DATADIRECTORY,DEFAULTDATABASE,'php');
					break;
				}
			}
		}
		$this->Msg->set_msg('You have loaded your last back-up!');
		header('Location:'.WEBROOT.strtolower(get_class($this)));
	}
	function save_script()
	{
		$actions[2] = $this->Get->table('actions',TRUE);
		$this->Get->save_script($_POST['nblock'],$actions);
		$this->Msg->set_msg('You saved '.$_POST['nblock'].'.php actions script');
		$post['table'] = $this->Sys->get_id_table('blocks');
		$post['block'] = $_POST['nblock'];
		// get_last_number($strTable,$strColumn)
		if($record = $this->Sys->get_where_unique('blocks','block',$post['block']))
		{
			//get_real_id($table,$strColumn,$unique)
			$post['id_block'] = $record[1];
			$post['line'] = $this->Sys->get_real_id($post['table'],'block',$post['block']);
			$this->Sys->set_line($post);
		}
		else
		{
			$last = $this->Sys->get_last_number('blocks','id_block');
			$post['id_block'] = ++$last;
			$this->Sys->add_line($post,'id_block');
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/actions');
	}
	function load_big_data($strTable)
	{
		try
		{
			$this->Get->load_big_data($strTable);	
			$_SESSION['sbigfile'] = $strTable;
			$this->Msg->set_msg('You have loaded '.$strTable. '.php');
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$_POST['table']);
	}
	
	function load_post_data()
	{
		//var_dump($_POST); exit;
		try
		{
			$this->Get->load_big_data($_POST['file']);	
			$_SESSION['sbigfile'] = $_POST['file'];
			$this->Msg->set_msg('You have loaded '.$_POST['file'].'.php');
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$_POST['file']);
	}
	function load_script()
	{
		try
		{
			$this->Get->load_script($_POST['block']);
			$_SESSION['sblock'] = $_POST['block'];
			$this->Msg->set_msg('You have loaded '.$_POST['block'].'.php actions script');
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/actions');
	}
	function load_script_get($url)
	{
		try
		{
			$block = $this->Sys->get_field_value_where_unique('blocks','id_block',$url[2],'block');
			$this->Get->load_script($block);
			$_SESSION['sblock'] = $block;
			$this->Msg->set_msg('You have loaded '.$block.'.php actions script');
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/actions');
	}
	
	function add_big_data($data,$file,$strTable)
	{
		$puts = '<?php';
		if(isset($data))
		{
			foreach($data as $table=>$t)
			{
				foreach($t as $line=>$l)
				{
					foreach($l as $column=>$value)
					{
						$puts .= PHP_EOL;
						$this->Get->escape($value);
						//$value = utf8_encode($value); 
						$puts .= '$data['.$table.']['.$line.']['.$column.']='."'".$value."'".';';
					}
				}
			}
		}
		$puts .= PHP_EOL;
		$puts .= '?>';
		file_put_contents(DATADIRECTORY.$file,$puts,LOCK_EX);
		$post['table'] = $this->Sys->get_id_table('files');
		$post['file'] = $file;
		$post['tablename'] = $strTable;
 		$this->Sys->add_line($post,'id_file');
	}

	function save_big_data($url)
	{
		$data = $this->Get->table($url[TABLE],TRUE);
		$table = $this->Get->get_id_table($url[TABLE]);
		//$this->preprint($data); exit;
		$puts = '<?php';
		if(isset($data))
		{
			foreach($data as $line=>$columns)
			{
				foreach($columns as $column=>$value)
				{
					$puts .= PHP_EOL;
					$this->Get->escape($value);
					//$value = utf8_encode($value); 
					$puts .= '$data['.$table.']['.$line.']['.$column.']='."'".$value."'".';';
				}
			}
		}
		$puts .= PHP_EOL;
		$puts .= '?>';
		
		$post['table'] = $this->Sys->get_id_table('files');
		$post['file'] = $url[TABLE];
		$this->Sys->add_line($post,'id_file');
		$this->Msg->set_msg('You have saved '.$post['file'].'.php as a independant datafile.');
		file_put_contents(DATADIRECTORY.$post['file'].'.php',$puts,LOCK_EX);
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}

	function validation($value,$op,$test)
	{
		$return = FALSE;
		$value = trim($value);
		$op = trim($op);
		$test = trim($test);
		$op = preg_replace("/&#039;/", "'",$op);
		$op = preg_replace("/&lt;/", "<", $op);
		$op = preg_replace("/&gt;/", ">", $op);
		$op = str_replace("&#092;", "\\", $op);
		$op = str_replace("&#047;","/", $op);
		//var_dump($value.' '.$op.' '.$test); 
		switch($op)
		{
			case '==':
				if($value == $test)
				{
					$return = TRUE;
				}
			break;
			case '===':
				if($value === $test)
				{
					$return = TRUE;
				}
			break;
			case '!=':
				if($value != $test)
				{
					$return = TRUE;
				}
			break;
			case '<>':
				if($value <> $test)
				{
					$return = TRUE;
				}
			break;
			case '!==':
				if($value !== $test)
				{
					$return = TRUE;
				}
			break;
			case '<':
				if($value < $test)
				{
					$return = TRUE;
				}
			break;
			case '>':
				if($value > $test)
				{
					$return = TRUE;
				}
			break;
			case '<=':
				if($value <= $test)
				{
					$return = TRUE;
				}
			break;
			case '>=':
				if($value >= $test)
				{
					$return = TRUE;
				}
			break;
			case 'LIKE':
				if(stripos($value,$test) !== FALSE)
				{
					$return = TRUE; 
				}
			break;
		}
		//var_dump($return); 
		//exit(0);
		return $return;	
	}
	
	function save_as_csv($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/save_as_csv/'.$url[TABLE];
			$this->question('Do you want to append the current data of '.$url[TABLE].' to '.$url[TABLE].'.csv ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			$this->Get->save_csv($url[TABLE],TRUE);
		}
		elseif ($answer == 'no')
		{
			$this->Get->save_csv($url[TABLE]);
		}
		$this->Msg->set_msg('The table : '.$url[TABLE].' has been saved to '.$url[TABLE].'.csv');
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	
	function load_csv($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/load_csv/'.$url[TABLE];
			$this->question('Are you sure you want to replace current data of '.$url[TABLE].' with '.$url[TABLE].'.csv ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			if(file_exists(DATADIRECTORY.$url[TABLE].'.csv'))
			{
				$this->Get->load_csv($url[TABLE]);
				$this->Msg->set_msg('You have loaded '.$url[TABLE].'.csv');
			}
			else
			{
				$this->Msg->set_msg('The file '.$url[TABLE].'.csv does not exists!');
			}
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	
	public function __destruct()
	{
		$this->cleanup();
	}

	function test()
	{
		$tbl = $this->Get->get_table(3);
		var_dump($tbl);
		$enco = json_encode($tbl);
		echo $enco;
		//echo json_decode($enco);
	}
	
	function get_json()
	{
		header("Content-Type: text/plain");
		echo $this->Get->get_cell(4,1,2);
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
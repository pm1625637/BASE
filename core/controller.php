<?php
/**
* @class: Controller
* @version:	7.2
* @author: pierre.martin@live.ca
* @php: 7.4
* @revision: 2021-01-16
* @licence MIT
*/

class Controller
{
	public static $version = '7.2';
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
		$this->data['title'] = '<a href="'.strtolower(get_class($this)).'">'.$this->data['title'].' '.VERSION.'</a>';
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
				$this->Msg->set_msg("You have added the table : $strTable");
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
		$this->data['tip'] ='The table name must be lowercase, plural, contain only alphabetic characters and have a minimum of 4 caracters.';
		$this->data['placeholder'] = 'Name of the table';
		$this->data['name'] = 'table';
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/add_table';
		$this->data['content'] = $this->Template->load('add', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function edit_table($url)
	{
		try
		{
			$id_table = $this->Get->get_id_table($url[TABLE]);
			$strTableName = $this->Get->get_table_name($id_table);
			$strTable = @$_POST['newname'];
			if($this->Get->edit_table($id_table,$strTable))
			{
				$this->Msg->set_msg("You renamed the table: $strTableName for: $strTable");
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
				exit;
			}
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Rename the table $url[TABLE]" ;
		$this->data['tip'] ='';
		$this->data['placeholder'] = 'Rename the table';
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
		}
		else
		{
			$this->Msg->set_msg("Record not found in: $url[TABLE]");
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit();
		}
		//LEFT
		$this->properties('left',$strTable);
		//CONTENTS
		$this->data['columns'] = $this->Get->get_columns_of($strTable);
		//$this->data['records'] = $this->Get->all();
		$records = $this->Get->all(false,SHOWLIMIT);
		if(isset($records))
		{
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
					else
					{
						$tbody .= '<td>'.$value.'</td>';
					}
					$i++;
				}
				while($i < $this->data['nbrcolonne'] )
				{
					$tbody .= '<td></td>';
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
			$this->Msg->set_msg("You have added table: $strTable");
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
	
	function get($url)
	{
		$cols = $this->Get->get_columns_of($url[TABLE]);
		$rec = $this->Get->get_where_unique($url[TABLE],$url[FIELD],$url[VALUE]);
		$this->Get->unescape($rec);
		$record = $this->Get->combine($cols,$rec);
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($record);
		return json_encode($record);
	}
	
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
	function dropdown($cols,$strTable,$selectName,$value=null)
	{
		$rec = $this->Get->select($cols,$strTable);
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
	
	public function __destruct()
	{
		$this->cleanup();
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
}
?>
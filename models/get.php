<?php 
/**
* @class: Get
* @version:	7.2
* @author: pierre.martin@live.ca
* @php: 7.4
* @revision: 2021-01-16
* @licence MIT
*/ 
class Get extends Model
{
	public function initialize()
	{
		unset($this->data);
		$this->data[0][0][1]='rules';
		$this->data[0][0][2]='actions';
		$this->data[0][0][3]='users';
		$this->data[0][0][4]='notes';
		$this->data[1][0][1]='id_rule';
		$this->data[1][0][2]='master';
		$this->data[1][0][3]='slave';
		$this->data[1][0][4]='comment';
		$this->data[1][1][1]='1';
		$this->data[1][1][2]='rules';
		$this->data[1][1][3]='actions';
		$this->data[1][1][4]='';
		$this->data[1][2][1]='2';
		$this->data[1][2][2]='users';
		$this->data[1][2][3]='notes';
		$this->data[1][2][4]='';
		$this->data[2][0][1]='id_action';
		$this->data[2][0][2]='action';
		$this->data[2][0][3]='strtable';
		$this->data[2][0][4]='strfield';
		$this->data[2][0][5]='totable';
		$this->data[2][0][6]='tofield';
		$this->data[2][0][7]='left';
		$this->data[2][0][8]='right';
		$this->data[2][0][9]='string';
		$this->data[2][0][10]='operator';
		$this->data[2][0][11]='value';
		$this->data[2][0][12]='unique';
		$this->data[2][1][1]='1';
		$this->data[2][1][2]='1';
		$this->data[2][1][3]='users';
		$this->data[2][1][4]='';
		$this->data[2][1][5]='';
		$this->data[2][1][6]='';
		$this->data[2][1][7]='';
		$this->data[2][1][8]='';
		$this->data[2][1][9]='';
		$this->data[2][1][10]='';
		$this->data[2][1][11]='';
		$this->data[2][1][12]='';
		$this->data[2][2][1]='2';
		$this->data[2][2][2]='1';
		$this->data[2][2][3]='notes';
		$this->data[2][2][4]='';
		$this->data[2][2][5]='';
		$this->data[2][2][6]='';
		$this->data[2][2][7]='';
		$this->data[2][2][8]='';
		$this->data[2][2][9]='';
		$this->data[2][2][10]='';
		$this->data[2][2][11]='';
		$this->data[2][2][12]='';
		$this->data[3][0][1]='id_user';
		$this->data[3][0][2]='user';
		$this->data[3][1][1]='1';
		$this->data[3][1][2]='user 1';
		$this->data[3][2][1]='2';
		$this->data[3][2][2]='user 2';
		$this->data[3][3][1]='3';
		$this->data[3][3][2]='user 3';
		$this->data[4][0][1]='id_note';
		$this->data[4][0][2]='note';
		$this->data[4][0][3]='user_id';
		$this->data[4][1][1]='1';
		$this->data[4][1][2]='note 1';
		$this->data[4][1][3]='1';
		$this->data[4][2][1]='2';
		$this->data[4][2][2]='note 2';
		$this->data[4][2][3]='2';
		$this->data[4][3][1]='3';
		$this->data[4][3][2]='note 3';
		$this->data[4][3][3]='2';
		$this->data[4][4][1]='4';
		$this->data[4][4][2]='note 4';
		$this->data[4][4][3]='2';
		$this->data[4][5][1]='5';
		$this->data[4][5][2]='note 5';
		$this->data[4][5][3]='3';
		$this->save();
	}
	public function load_big_data($strTable)
	{
		$table = $this->get_id_table($strTable);
		//var_dump($table); exit;
		if($table == 0)
		{
			$msg = 'You tried to load a table that does not have a key table. Try to import the original table before loading a big file that is attached to it.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new Exception($msg);
			exit;
		}
		if(file_exists($this->datapath.$strTable.'.php')) 
		{
			try
			{
				include($this->datapath.$strTable.'.php');
				sleep(1);
				$firstKey = array_key_first($data);
				if($firstKey !== $table)
				{
					$msg = "First keys : $firstKey of the table does not match the main key : $table for this table !";
					$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
					throw new Exception($msg);
					exit;
				}
				//$this->preprint($data); exit;
				//unset($this->data[$table]);
				$this->data[$table] = $data[$firstKey]; 
				//Cette fonction sert en cas que les indices d'un fichier loader ne commence pas a 1 mais a 10000 par exemple.
				//sleep(1);
				$this->repair_table($table);
				//$_SESSION['sbigfile'] = $strTable.$index.'.php';
				$_SESSION['sbigfile'] = $strTable;
				//$this->save();
			}
			catch (Throwable $t)
			{
				$msg = $t->getMessage();
				$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
				throw new Exception($msg);
			}
		} 
		else 
		{
			$msg = 'The file '.$this->datapath.$strTable.'.php does not exist';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new Exception($msg);
		}
	}
	public function save_big_data($strTable,$offset=NULL)
	{
		$table = $this->get_id_table($strTable);
		$t = $this->table($strTable,TRUE);
		//var_dump($data); exit;
		$puts = '<?php';
		if(isset($t))
		{
			//foreach($data as $table=>$t)
			//{
				foreach($t as $line=>$l)
				{
					foreach($l as $column=>$value)
					{
						$puts .= PHP_EOL;
						$puts .= '$this->data['.$table.']['.$line.']['.$column.']='."'".$value."'".';';
					}
				}
			//}
		}
		$puts .= PHP_EOL;
		$puts .= '?>';
		file_put_contents(DATADIRECTORY.$strTable.$offset.'.php',$puts,LOCK_EX);
	}
	public function to_mysql($server,$user,$pass=NULL,$database,$strTable)
	{
		$msg = '';
		$link = mysqli_connect($server, $user, $pass, $database);	
		if (!$link) {
			$msg = 'Error : Cannot connect to  MySQL.'. PHP_EOL;
			$msg .= "Errno de debug : " . mysqli_connect_errno() . PHP_EOL;
			$msg .= "Error debug : " . mysqli_connect_error() . PHP_EOL;
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new Exception($msg);
			exit;
		}
		else
		{
			$msg .= "Host : " . mysqli_get_host_info($link) .'<br>';
			$msg .= "Client library version: " . mysqli_get_client_version().'<br>';
		}
		$table = $this->get_id_table($strTable);
		$data = $this->data[$table];
		$strTable= mysqli_real_escape_string($link, $strTable);
		$columns = $this->get_columns($table);
		///////// CREATE TABLE /////////
		/*
			CREATE TABLE Persons (
			ID int NOT NULL UNIQUE,
			LastName varchar(255) NOT NULL,
			FirstName varchar(255),
			Age int
			);
		*/
		/* "Create table" ne retournera aucun jeu de résultats */
		mysqli_query($link,"SET NAMES utf8");
		$sql = "CREATE TABLE IF NOT EXISTS $strTable($columns[1] INT NOT NULL UNIQUE)  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";	
		if(mysqli_query($link, $sql) === TRUE) 
		{
			$msg .= "Create Table $strTable <br>";
		}
		//Only if you cannot use a UNIQUE PRIMARY KEY
		$sql = "TRUNCATE $strTable ";	
		if(mysqli_query($link, $sql) === TRUE) 
		{
			$msg .= "Truncate Table $strTable <br>";
		}
		///////// CREATE COLUMNS /////////
		$cols='';
		foreach($columns as $column) 
		{
			// Check if column exists
			$sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
					WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$strTable' AND COLUMN_NAME = '$column'";
			$result = mysqli_query($link, $sql);
			if(mysqli_num_rows($result) > 0) 
			{
				//var_dump($result);
				$msg.= 'Column '.$column.' already exists! <br>';
			} 
			else
			{
				if($this->right($column,3)=='_id')
				{
					$sql = "ALTER TABLE $strTable ADD COLUMN $column INT NOT NULL ;"; 	
				}
				else
				{
					$sql = "ALTER TABLE $strTable ADD COLUMN $column VARCHAR(512) NULL ;"; 
				}
				
				if(mysqli_real_query($link, $sql)) 
				{
					$msg .= 'Column '.$column.' was created! <br>';
				}
			}
			$cols .= $column.','; 
		}
		$cols=rtrim($cols,',');	
		$sql = "INSERT IGNORE INTO $strTable ($cols) VALUES ";
		$tab = $this->table($strTable);
		//var_dump($tab);exit;
		$values='';
		foreach($tab as $i=>$rec)
		{
			foreach($rec as $c=>$value)
			{
				if($value=='' || empty($value) || is_null($value))
				{
					$values .= "DEFAULT".',';	
				}
				else
				{
					//$this->unescape($value);
					$values .= "'".$value."'".',';
				}
			}
			$values=rtrim($values,',');
			$sql.= "($values),";
			$values='';
		}
		$sql = rtrim($sql,',');
		//echo($sql); exit;
		//$msg .= (mysqli_query($link, $sql))?'Insert '.$strTable.' succeeded!':'Insert failed!';
		if (!mysqli_query($link,$sql))
		{
		  $msg .= "Error description: " . mysqli_error($link);
		}
		else
		{
			$msg .= 'Insert Ignore '.$strTable.' succeeded!';
		}
		mysqli_close($link);
		return $msg;
	}
	public function from_mysql($server,$user,$pass=NULL,$database)
	{
		unset($this->data);
		$this->save();
		$msg = '';
		$link = mysqli_connect($server, $user, $pass, $database);	
		if (!$link) {
			$msg = 'Error : Cannot connect to  MySQL.'. PHP_EOL;
			$msg .= "Errno de debug : " . mysqli_connect_errno() . PHP_EOL;
			$msg .= "Error debug : " . mysqli_connect_error() . PHP_EOL;
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new Exception($msg);
			exit;
		}
		else
		{
			$msg .= "Host : " . mysqli_get_host_info($link) .'<br>';
			$msg .= "Client library version: " . mysqli_get_client_version().'<br>';
		}

		//mysqli_query($link,"SET NAMES utf8");
		$sql ="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database'";
		$myquery = mysqli_query($link, $sql); 
		while ($row = $myquery->fetch_assoc()) 
		{  
			//echo($row['TABLE_NAME']).'<br>';
			$this->add_table_only($row['TABLE_NAME']);
			$tblname = $row['TABLE_NAME'];
			$idtable = $this->get_id_table($tblname);
			$qu = 'SELECT * FROM `'.$tblname.'`';
			if ($result = mysqli_query($link,$qu))
			{
				// Get field information for all fields
				$fieldinfo = mysqli_fetch_fields($result);
				foreach ($fieldinfo as $val)
				{
					$this->add_column($idtable,$val->name);
					//echo "Name:".$val->name.'<br>';
					//printf("Table: %s\n",$val->table);
					//printf("max. Len: %d\n",$val->max_length);
				}
				mysqli_free_result($result);
			}
		}
		mysqli_close($link);
		return $msg;
	}
	public function save_script($name,$array)
	{
		$puts = '<?php';
		if(isset($array))
		{
			foreach($array as $table=>$t)
			{
				foreach($t as $line=>$l)
				{
					foreach($l as $column=>$value)
					{
						$puts .= PHP_EOL;
						$this->escape($value);
						if($value == '-')
						{
							$value='';
							//$puts .= '$this->data['.$table.']['.$line.']['.$column.']='."''".';';
						}
						$puts .= '$this->data['.$table.']['.$line.']['.$column.']='."'".$value."'".';';
					}
				}
			}
		}
		$puts .= PHP_EOL;
		$puts .= '?>';
		
		file_put_contents(BLOCKDIRECTORY.$name.'.php',$puts,LOCK_EX);
	}
	public function load_script($name,$table=2)
	{
		if (file_exists(BLOCKDIRECTORY.$name.'.php')) 
		{
			unset($this->data[$table]);
			include(BLOCKDIRECTORY.$name.'.php');
			$this->save();
		} 
		else 
		{
			$msg = 'The file '.BLOCKDIRECTORY.$name.'.php does not exist';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new Exception($msg);
		}
	}
	
	public function save_csv($strTable,$append=FALSE)
	{
		//$data = $this->Get->table('AdaCodes',TRUE);
		$data = $this->table($strTable,TRUE);	
		$puts = '';
		if(isset($data))
		{
			foreach($data as $line=>$l)
			{
				foreach($l as $column=>$value)
				{
					$this->unescape($value);
					$res = strstr($value, ','); 
					if($res)
					{
						$value = '"'.$value.'"'; 
					}
					$puts .= $value.',';
				}
				$puts = rtrim($puts,',');
				$puts .= "\n";
			}
		}
		if($append)
		{
			//$puts .= "\n";
			file_put_contents(DATADIRECTORY.$strTable.'.csv',$puts,FILE_APPEND);
		}
		else
		{
			file_put_contents(DATADIRECTORY.$strTable.'.csv',$puts);	
		}
	}
	
	public function load_csv($strTable)
	{
		include_once(CLASSDIRECTORY."bigfile.php");
		$largefile = new BigFile(DATADIRECTORY.$strTable.'.csv');
		
		$iterator = $largefile->iterate("Text"); // Text or Binary based on your file type
		$t = $this->get_id_table($strTable);
		//$i=0;
		foreach ($iterator as $i=>$line)
		{		
		   $line = trim($line);
		   $rec = explode('|', $line);
		   $c = 1;
		   foreach($rec as $field)
		   {
				//$field = str_replace(',','.', $field);
				//$field = preg_replace('/[^A-Za-z0-9\-]/', '', $field);	
				$field = strval($field);
				//$field= htmlspecialchars ($field);
				if($i==0)
				{
					if($field || $field == 0 || $field == "0")
					{
						$this->data[$t][0][$c] = $field;
					}
					else
					{
						$this->data[$t][0][$c] = '';
					}
				}
				else
				{
					if($field || $field == 0 || $field == "0")
					{
						$this->data[$t][$i][$c] = $field;
					}
					else
					{
						$this->data[$t][$i][$c] = '';
					}
				}				
				$c++;
		   }
	   }
	  // It seems that iterator has 1 more empty row.  
	  unset($this->data[$t][$i]);
	  $this->save();
	}
}
?>
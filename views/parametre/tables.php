<?php 
	echo '<blockquote>'.$thead.'</blockquote>';
	if($thead == 'tables')
	{
		$block = isset($_SESSION['sblock'])?$_SESSION['sblock']:'working copy';
		echo '<h2 id="acsc">Parameters  <small class="text-muted">'.$block.'</small> </h2>';
		echo '<h4>Skippy : This parameter will filter the importation.</h4>';
		echo '<h5><strong>TIP:</strong> To have a fully functional actions script. You must <a href="'.WEBROOT.'main/import_table">Import the table</a> 
		you want to work with before starting <a href="'.WEBROOT.'main/add_action">Adding an action</a>. You will want to <a href="'.WEBROOT.'main/ini">Initialize</a> the DB once your script will be complete.</h5>';
	}
?>
<?php
	//echo '<h3>'.ucfirst($thead).' <small>Secondary text</small></h3>';
	echo '<input class="form-control" id="myInput" type="text" placeholder="Search..">';
	//echo '<div class="table-responsive">';
	echo '<table id="tab" class="table table-striped">';
	echo '<thead>';
	echo '<tr>';
	foreach($columns as $id=>$col)
	{
		echo '<th>';
		/*if($thead <> "actions" && $thead <> "rules")
		{
			echo '<a href="'.WEBROOT.$controller.'/show_table/'.$thead.'/'.$col.'" title="sort by '.$col.'" onclick="includeHTML()">'.$col.'</a>';
			echo '&nbsp;';
			echo '<a title="Edit a field" style="color:red; font-weight:normal; text-decoration:none;" href="'.WEBROOT.$controller.'/edit_field/'.$thead.'/'.$id.'"><em>edit</em></a>';
			echo '&nbsp;';
			echo '<a title="Delete a field"  style="color:red; font-weight:normal; text-decoration:none;"  href="'.WEBROOT.$controller.'/delete_field/'.$thead.'/'.$id.'"><em>delete</em></a>';
		}
		elseif($thead == "actions")
		{
			echo '<a href="'.WEBROOT.$controller.'/show_table/'.$thead.'/'.$col.'" title="sort by '.$col.'">'.$col.'</a>';
		}
		else
		{*/
			echo $col;
		/*}*/
		echo'</th>';
	}
	echo '<th></th>';
	echo '<th></th>';
	echo '</tr>';
	echo '</thead>';
	if($thead <> "actions" && $thead <> "rules")
	{
		echo '<tbody id="myTable">';
	}
	else
	{
		echo '<tbody id="myTable" class="row_drag">';
	}
	echo $tbody;
	echo '<tr id="exec"><th colspan="'.($nbrcolonne+2).'"><span>Execution time : '.number_format($performance,2).' sec.</span></th></tr>';
	echo '</tbody>';
	echo '</table>';
	//echo '</div>';
?>
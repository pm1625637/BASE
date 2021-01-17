<div class="panel panel-default">
	<div class="panel-body">
	Table   : <strong><?=$thead?></strong><br> 
	Fields  : <span class="badge"><?=$nbrcolonne?></span><br>    
	Records : <span class="badge"><?=$nbrligne?></span><br>
	<?php
	/*echo '<div><small><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/edit_table/'.$thead.'">Rename the current table</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/delete_table/'.$thead.'">Delete the current table</a></small></div>';*/
	echo '<hr>';
	//echo '<div><a href="'.WEBROOT.$controller.'/add_record/'.$thead.'">Add a record (test only)</a></div>';
	echo '<div><a href="'.WEBROOT.$controller.'/export/'.$thead.'">Export the current table to mysql</a></div>';
	/*echo '<div><small><a href="'.WEBROOT.$controller.'/add_field/'.$thead.'">Add a field</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/show_fields/'.$thead.'">Show fields</a></small></div>';*/
	?>
	</div>
</div>
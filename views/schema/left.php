<div class="panel panel-default">
	<div class="panel-heading">
		<div class="panel-title">Databases</div> 
	</div>
	<div class="panel-body">
		<ul class="list-group">
		<li class="list-group-item"><a href="<?php echo WEBROOT.$link;?>/ini" title="Import from mysql">Import from mysql <strong><?php echo $dbmysql_import; ?></strong></a></li>
		<li class="list-group-item"><a onclick="includeHTML()" href="<?php echo WEBROOT.$link;?>/export_all" title="Export to mysql">Export to mysql <strong><?php echo $dbmysql_export; ?></strong></a></li>
		</ul>
	</div>
	<div class="panel-footer">
		<a href="<?php echo WEBROOT; ?>system/show_table/server" title="server">Server</a>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<div class="panel-title">Tables</div> 
	</div>
	<div class="panel-body">
		<?php 
		if(isset($tables))
		{
			echo '<ul class="list-group">';
			foreach($tables as $table)
			{
				echo '<li class="list-group-item"><a href="'.WEBROOT.$link.'/show_table/'.$table.'">'.$table.'</a></li>';
			}
			echo '</ul>';
		}
		?>
	</div>
	<div class="panel-footer">
		<a href="<?php echo WEBROOT; ?>main/import_table" title="importation">Importation</a>	
	</div>
</div>

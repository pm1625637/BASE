<!DOCTYPE html>
<html lang="en">
<head> 
	<?php echo (isset($head))? $head:'$head'; ?>
</head>
<body>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="col-md-12">
				<?php echo (isset($banner))? $banner:'$banner'; ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="col-md-3">
				<?php echo (isset($nav))? $nav:'$nav'; ?>
			</div>
			<div class="col-md-9">
				<?php echo (isset($msg))? $msg:'$msg'; ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="col-md-3">
				<?php echo (isset($left))? $left:'$left'; ?>
			</div>
			<div class="col-md-9">
				<table class="table">
				<?php
					echo '<tr>';
					foreach($columns as $c=>$value)
					{
						echo '<th>'.$columns[$c].'</th>';
					}
					echo '</tr>';
					foreach($resultat as $i=>$record)
					{
						echo '<tr>';
						foreach($record as $c=>$value)
						{
							echo '<td>'.$value.'</td>';
						}
						echo '</tr>';
					}
					echo '<tr><th colspan="'.($nbrcolonne+2).'"><span>Execution time : '.number_format($performance,2).' sec.</span></th></tr>';	
				?>
				</table>
			</div>
		</div>
		<div class="row-fluid">
			<div class="col-md-12">
				<?php echo (isset($footer))? $footer:'$footer'; ?>
			</div>
		</div>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>

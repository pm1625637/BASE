<!DOCTYPE html>
<html lang="en">
<head>
<base href="<?php echo WEBROOT; ?>">
<meta charset="utf-8" >
<link rel="stylesheet" href="<?php echo WEBROOT; ?>assets/css/print.css" media="print" >
<title><?=$title?></title>
</head>
<body id="page">
<!-- [if l$titlet IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif] -->
<h1><?=$title?></h1> 
<div id="content" >
	<?php
	//var_dump($records); exit;
	$det = '<table>';
	foreach($records as $index=>$record)
	{
		$det .= '<tr>';
		foreach($record as $field=>$value)
		{
			if($index == 0)
			{
				$det .= '<th scope="col" style="text-align:left">'.$value.'</th>';
			}
			else
			{
				$det .= '<td>'.$value.'</td>';
			}
		}
		$det .= '</tr>';
	}
	$det .= '</table>';
	echo $det;
	?>
</div> 
</body>
</html>
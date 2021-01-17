<?php
	if(isset($records))
	{
		$tbody = '<table class="'.$class.'">';
		foreach($records as $key=>$value)
		{
	
			$tbody .= '<tr>';
			$tbody .= '<td>'.$value.'</td>';
			$tbody .= '</tr>';
		}
		$tbody .='</table>';
	}
	echo $tbody;
?>
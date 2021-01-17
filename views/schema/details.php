<blockquote><?php echo WEBROOT.$link;?></blockquote>
<div class="panel panel-primary">
  <div class="panel-heading">Database : <?php echo '<strong>'.$file.'</strong>'; ?></div>
  <div class="panel-body">	<ul>
	<li>File size : <?php echo $ffilesize; ?> octets</li>
	<li>Import | Export : [<em>.php</em>, <em>.sql</em>, <em>.json</em>]</li>
	<li>Symmetry : <?php echo '['.$numtables.']['.$maxlines.']['.$maxcols.']'; ?></li>
	<li>Number of tables : <?php echo $numtables; ?></li>
	<li>Number of lines : <?php echo $maxlines; ?></li>
	<li>Number of columns : <?php echo $maxcols; ?></li>
<ul></div>
</div>
<!--<div class="alert alert-success" role="alert">
  <h4 class="alert-heading">Well done!</h4>
  <p>You successfully import your data here ! <strong>structures.php</strong> is the last step before exporting to MySQL. 
  Take a last look before exporting it is always a good practice. Noticed that the system will ignore empty tables for exportation to MySQL.</p>
  <hr>
  <p class="mb-0">Whenever you need to, you may take a look at the data dictionnary. Have a good day !</p>
</div>
<h1>Data Dictionnary</h1>
 -->	
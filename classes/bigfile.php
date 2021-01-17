<?php //if ( ! defined('ROOT')) exit('No direct script access allowed');
class BigFile
{
    protected $file;
 
    public function __construct($filename, $mode = "r")
    {
        if (!file_exists($filename)) {
 
            throw new Exception("File not found");
 
        }
 
        $this->file = new SplFileObject($filename, $mode);
    }
 
    protected function iterateText()
    {
        $count = 0;
 
        while (!$this->file->eof()) {
 
            yield $this->file->fgets();
 
            $count++;
        }
        return $count;
    }
 
    protected function iterateBinary($bytes)
    {
        $count = 0;
 
        while (!$this->file->eof()) {
 
            yield $this->file->fread($bytes);
 
            $count++;
        }
    }
 
    public function iterate($type = "Text", $bytes = NULL)
    {
        if ($type == "Text") {
 
            return new NoRewindIterator($this->iterateText());
 
        } else {
 
            return new NoRewindIterator($this->iterateBinary($bytes));
        }
 
    }
}
/****************************************************************************/
/******************************** EXAMPLE ***********************************/
/****************************************************************************/
/*$largefile = new BigFile('Patients.sdf');
$iterator = $largefile->iterate("Text"); // Text or Binary based on your file type
$row = 1;
if (($handle = fopen("Patients.sdf", "r")) !== FALSE)
{
	while (($data = fgetcsv($handle, 8000, ",",'"')) !== FALSE)
	{
		$num = count($data);
		echo "<p> $num champs à la ligne $row: <br /></p>\n";
		$row++;
		for ($c=0; $c < $num; $c++)
		{
			echo $data[$c] . "<br />\n";
		}
	}
	fclose($handle);
}	*/

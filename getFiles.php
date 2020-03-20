<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require("./simplehtmldom/simple_html_dom.php");
$maxCount = 2000;


function tidyHTML($buffer) {
    // load our document into a DOM object
    $dom = new DOMDocument();    
    $dom->preserveWhiteSpace = false;
    $dom->loadHTML(trim($buffer));
    $dom->formatOutput = true;
    return($dom->saveHTML());
}

$dir = "/journals-data/uploads/ojs3-test/journals/73/articles";
//$dir="/var/www/public/journals-data/73_20200319/articles";
$log = "log.txt";
$errorlog = "error_log.txt";
file_put_contents($log, "");
file_put_contents($errorlog, "");

$f = fopen($log, 'a');
$ef = fopen($errorlog, 'a');

$articleDirs = array_diff(scandir($dir,SCANDIR_SORT_ASCENDING), array('..', '.'));

//print_r($articleDirs);
$count = 0;

foreach($articleDirs as $article){


	$count +=1;
	$filesDir = $dir ."/" . $article.  "/submission/proof/" ;
	
	$files = glob($filesDir . "*.html");
	
	foreach($files as $file){		
		echo $file . "<br>";
		$html_string = file_get_contents($file);
		$html = str_get_html($html_string);
		$table = $html->find('table tr',0);
			
		
		// if an anchor tag exists in the first td of the table then assume that it's the navigation table
		if($table->find("td a",0)){
			$table->setAttribute('style','display: none;');
		}else{
			// write to log
			fwrite($f, "first table for {$file} could not be found\n");
			fwrite($ef, "first table for {$file} could not be found\n");
		}
		
		$table_last = $html->find('table',-1);
		$table_last_tr = $table_last->find('tr',0);
		if($table_last_tr->find("td a", 0)){
			$table_last_tr->setAttribute('style','display: none;');
		}else{
			// write to log
			fwrite($f, "last table for {$file} could not be found\n");
			fwrite($ef, "last table for {$file} could not be found\n");
		}
		
		
		
		//	echo $html;
		$cleanHTML = tidyHTML($html);
	//	echo $cleanHTML;
		
		file_put_contents($file,$cleanHTML);

	  $msg = "{$file}  was written. \n";
		fwrite($f, $msg);
		//echo nl2br($msg);
	}
	
	
	//echo $html;
   

   if($count >= $maxCount){
	   fclose($f);
	   fclose($ef);
	   exit();
   }
		
}

fclose($f);
fclose($ef);


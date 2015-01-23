<?
$string =array();
$filePath='';  
$dir = opendir($filePath);
while ($file = readdir($dir)) { 
   if (eregi("\.png",$file) || eregi("\.jpg",$file) || eregi("\.gif",$file) ) { 
  	 $string[] = $file;
   }else{
	printf("%s",$file);
   }
}
?>


?>

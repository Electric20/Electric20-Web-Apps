<?php
error_log("hello 1",3,"error_log.log");
if(empty($_POST["group_name"])) exit("missing group name");
$directory = "userfiles/".strtolower(preg_replace('/[^0-9a-z]/i','',$_POST["group_name"]))."/";
$thumbsdir = $directory."thumbs/";
if(!is_dir($directory))
{
   if(!mkdir($directory)) exit("could not make group directory");
   if(!mkdir($thumbsdir)) exit("could not make thumbs directory");
   $fp = fopen($directory."last.txt", "w");
   fputs($fp,"0");
   fclose($fp);
}
error_log("hello 2",3,"error_log.log");
$fp = fopen($directory."last.txt", "r+");
flock($fp, LOCK_EX);
rewind($fp);
$last = fgets($fp);
$numb=$last+1;

if(!isset($_FILES) && isset($HTTP_POST_FILES))
   $_FILES = $HTTP_POST_FILES;
error_log("hello 3",3,"error_log.log");
if(empty($_FILES['image_file'])) exit("missing image file");
if(empty($_FILES['info_file'])) exit("missing info file");
if(empty($_FILES['image_file']['name'])) exit("missing image filename");
if(empty($_FILES['info_file']['name'])) exit("missing info filename");
error_log("hello 4",3,"error_log.log");
$imagefilename = basename($_FILES['image_file']['name']);
$infofilename = basename($_FILES['info_file']['name']);

$newimage = $directory.$numb.".jpg";
$result = @move_uploaded_file($_FILES['image_file']['tmp_name'], $newimage);
if(empty($result)) exit("error moving image file");

$newtext = $directory.$numb.".txt";
$result = @move_uploaded_file($_FILES['info_file']['tmp_name'], $newtext);
if(empty($result)) exit("error moving info file");
error_log("hello 5",3,"error_log.log");
$lines = file($newtext,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
$keys = explode(',',$lines[0]);
$values = explode(',',$lines[1]);
$type = $values[array_search('source_type',$keys)];

if( exec("imgsize ".$newimage) == 'width="800" height="600"' ) {
   $newthumb = $thumbsdir.$type.$numb.".jpg";
   system("convert ".$newimage." -resize 160x120 ".$newthumb);
}
error_log("hello 6",3,"error_log.log");
rewind($fp);
fputs($fp, $numb);

echo $directory;
?>

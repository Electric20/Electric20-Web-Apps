<?
if(empty($_GET["id"])) exit("missing Id");
$id = strtolower(preg_replace('/[^0-9a-z]/i','',$_GET["id"]));
if(empty($_GET["group"])) exit("missing group name");
$group = strtolower(preg_replace('/[^0-9a-z]/i','',$_GET["group"]));
$group=$_GET["group"];
if(empty($_GET["ride"])) exit("missing ride name");
$ride=$_GET["ride"];
if(empty($_GET["pic1"])) exit("missing pic1 name");
$pic1=$_GET["pic1"];
if(empty($_GET["pic2"])) exit("missing pic2 name");
$pic2=$_GET["pic2"];
if(empty($_GET["pic3"])) exit("missing pic3 name");
$pic3=$_GET["pic3"];

$sourcedir="../userfiles/".$group."/";
$targetdir="../userfiles/".$group."/tmp".getmypid()."/";

if(!mkdir($targetdir)) exit("could not make temp directory");

system("convert ".$sourcedir.$pic1.".jpg -resize 1024x770\! ".$targetdir."1.jpg");
system("convert ".$sourcedir.$pic2.".jpg -resize 1024x770\! ".$targetdir."2.jpg");
system("convert ".$sourcedir.$pic3.".jpg -resize 1024x770\! ".$targetdir."3.jpg");

if($ride=="b") {
   system("convert ../templates/ba.jpg ".$targetdir."1.jpg -geometry +122+871 -composite ".$targetdir."2.jpg -geometry +667+1702 -composite ".$targetdir."3.jpg -geometry +785+2547 -composite ".$targetdir."result.jpg");
   system("mv ".$targetdir."result.jpg ".$sourcedir.$id."-b-result.jpg");
}
;
if($ride=="o") {
   system("convert ../templates/ob.jpg ".$targetdir."1.jpg -geometry +122+871 -composite ".$targetdir."2.jpg -geometry +667+1731 -composite ".$targetdir."3.jpg -geometry +1339+2592 -composite ".$targetdir."result.jpg");
   system("mv ".$targetdir."result.jpg ".$sourcedir.$id."-o-result.jpg");
}
;
if($ride=="s") {
   system("convert ../templates/so.jpg ".$targetdir."1.jpg -geometry +148+1153 -composite ".$targetdir."2.jpg -geometry +154+1973 -composite ".$targetdir."3.jpg -geometry +1252+2282 -composite ".$targetdir."result.jpg");
   system("mv ".$targetdir."result.jpg ".$sourcedir.$id."-s-result.jpg");
}
system("rm -rf ".$targetdir);

print "<html>";
print '<meta name = "viewport" content = "width = 320,';
print ' initial-scale = 2.3, user-scalable = no">';

print "<center>";
print "<h1> Automic Created! </h1>";
print "You'll receive it at the end of the day.<p>If you want to choose different images instead, please <a href=";
print "../author/get-ride.php?id=".$id."&group=".$group;
print ">start again</a>, but remember that you will only receive the final version of the Automic that you submit for this ride.";
print "<p>Press 'menu' to continue.";
print "</center>";
print "</html>";
?>

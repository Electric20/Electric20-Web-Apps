<html>
<meta name = "viewport" content = "width = 320,
       initial-scale = 2.3, user-scalable = no">
<head>
<style type="text/css">
.bigButt {
  width: 50px; height: 50px;
}
</style>
</head>
<body>
<center>
<h1>Author Automic
<?
if(empty($_GET["id"])) exit("missing Id");
$id=$_GET["id"];
if(empty($_GET["group"])) exit("missing group name");
$group=$_GET["group"];
if(empty($_GET["pic1"])) exit("missing pic1 name");
$pic1=$_GET["pic1"];
if(empty($_GET["pic2"])) exit("missing pic2 name");
$pic2=$_GET["pic2"];
$directory = "../userfiles/".$group."/thumbs/";
if(empty($_GET["ride"])) exit("missing ride name");
$ride=$_GET["ride"];
?>
</h1>
<form name=radopt method="GET" action="compose.php">
<? print "<input type=hidden name=id value=".$id.">"; ?>
<? print "<input type=hidden name=group value=".$group.">"; ?>
<? print "<input type=hidden name=ride value=".$ride.">"; ?>
<? print "<input type=hidden name=pic1 value=".$pic1.">"; ?>
<? print "<input type=hidden name=pic2 value=".$pic2.">"; ?>
Select "After Ride" Image<p>
<?
$type="u";
$myDirectory = opendir($directory);
while($entryName = readdir($myDirectory)) {
    if($entryName!="." && $entryName!=".." && $entryName[0]==$type) {
	$dirArray[] = substr($entryName,1);
    }
}
closedir($myDirectory);
sort($dirArray,SORT_NUMERIC);

foreach($dirArray as &$jpgname) {
    print "<input class=bigButt type=radio name=pic3 value=";
    print str_replace(".jpg","",$jpgname);
    print " id=";
    print $jpgname;
    print " onClick='document.radopt.submit.disabled=false'";
    print ">";
    print "<label for=";
    print $jpgname;
    print ">";
    print "<img src=";
    print $directory.$type.$jpgname;
    print ">";
    print "</label>";
    print "<p>";
}
?>
<input name=submit style="width:50px;height:50px" type=submit value=GO disabled=true><p>
</center>
</body>
</html>


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
$id = strtolower(preg_replace('/[^0-9a-z]/i','',$_GET["id"]));
if(empty($_GET["group"])) exit("missing group name");
$group = strtolower(preg_replace('/[^0-9a-z]/i','',$_GET["group"]));
$directory = "../userfiles/".$group."/thumbs/";
if(!glob($directory."u*.jpg")) {
   print "<h1> Sorry! </h1>";
   print "No user images for this group.<p>";
   exit();
}
$o_img = glob($directory."o*.jpg");
$b_img = glob($directory."b*.jpg");
$s_img = glob($directory."s*.jpg");

if(!$o_img&&!$b_img&&!$s_img) {
   print "<h1> Sorry! </h1>";
   print "No ride images for this group.<p>";
   exit();
}
?>
</h1>
<form name=radopt method=GET action=get-1.php>
<? print "<input type=hidden name=id value=".$id.">"; ?>
<? print "<input type=hidden name=group value=".$group.">"; ?>
Select a ride:<p>
<table width=300>
<?
if($o_img) {
print "   <tr>";
print "     <td width=40% height=50 align=right><input class=bigButt type=radio name=ride value=o";
print " onClick='document.radopt.submit.disabled=false'></td>";
print "     <td>Oblivion</td>";
print "   </tr>";
}
if($s_img) {
print "   <tr>";
print "     <td height=50 align=right><input class=bigButt type=radio name=ride value=s";
print " onClick='document.radopt.submit.disabled=false'></td>";
print "     <td>Sonic Spinball</td>";
print "   </tr>";
}
if($b_img) {
print "   <tr>";
print "     <td height=50 align=right><input class=bigButt type=radio name=ride value=b";
print " onClick='document.radopt.submit.disabled=false'></td>";
print "     <td>Battle Galleons</td>";
print "   </tr>";
}
?>
</table>
<br><br>
<input name=submit style="width:50px;height:50px" type=submit value=GO disabled=true><p>
</center>
</body>
</html>

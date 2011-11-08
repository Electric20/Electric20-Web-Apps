<?php
include_once("loader.php");
$auth=authfactory::create();


//If access is set and set to false they are not authenticated so hadle that how you wish : there are three reasons given from the system as in switch below
if(isset($_GET['access']) && $_GET['access']=="false")
{
	switch($_GET["reason"])
	{
		case "service":
				{
					$reason="authentication system unavailable";
					break;
				}
		case "token":
			{
				$reason="not able to access authentication system";
				break;
			}
		default:
			{
				$reason = "Invalid username or password";
				break;
			}
	}
	
	denyAccess($reason);
}

function denyAccess($reason)
{
	
	header("location:http://".$_SERVER["HTTP_HOST"]."/login.php?reason=".urlencode($reason));
}



//OK so they are allowed in 
if(isset($_GET['auth']))
{
	//if auth has been set (the user has been authenticated) therefore the decyrpt method will return an array with ['name',array('roles')]
	$details=unserialize($auth->decrypt($_GET['auth']));
	
	
	$rolesWithAccess=array("Staff","cn_Staff","my_staff"); //An accepted roles list for staff 
	
	
	// so given you have roles you can test if the roles returned are in an accepted list (see the has validRole method below as an example of how to do this) 
	if(testAuthentication::hasValidRole($details['roles'],$rolesWithAccess))
	{
		
		//if they have the right role you know they are authenticated so you can do whatever you want inside you app to make them as authenticated e.g set a session
		setcookie("_lgd", "true", time()+3600, "/", $_SERVER["HTTP_HOST"], 0);
		$_SESSION["loggedin"]=true;
		
		testAuthentication::$access="";
		header("location:http://".$_SERVER["HTTP_HOST"]);
	}
	else
	{
		$reason="not got appropriate role to access";
		denyAccess($reason);
	}
	
	
	
}


// helper functions as example to show how works 

class testAuthentication
{
	
	public static $access;
	
	
	public static function isValidUser()
	{
		
		if((isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]===true) || (isset($_COOKIE['_lgd']) && $_COOKIE["_lgd"]=="true"))
		{
			
		 return true;
			
			
			
		}
		
		return false;
	}

	

	
	public static function hasValidRole($roles,$rolesWithAccess)
	{
		foreach($roles as $role)
		{
			if(in_array(strtolower($role),array_map('strtolower',$rolesWithAccess)))
			{
				return true;
			}
		}
		return false;
	}

	
}
?>
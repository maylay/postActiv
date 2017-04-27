<?php

// gnusocial post expiry script, based on StatExpire by Tony Baldwin
// https://github.com/tonybaldwin/statexpire

//For sanity checks on config.php, this is needed. Might need to look into security ramifications..
//Note: we are NOT modifying the config, just reading it into a variable for a custom include later!
$ct = file_get_contents("/var/www/social/config.php");
//Modify config in memory to remove bs...
define("GNUSOCIAL", true);
define("POSTACTIV", true);
$ct = str_replace("add", "//add", $ct);
$ct = str_replace("Plugin","//Plugin", $ct);
//Also, this might have other security issues - but since only an admin can run this script, maybe it is ok?
eval( '?>' . $ct );
//Get db config from config.php
$config_db = explode("@",$config['db']['database']);
$creds = explode(":",$config_db[0]);
$dbString = $config_db[1];
//Convert creds into usable credentials
array_shift($creds);
$username = str_replace("//","",$creds[0]);
$password = $creds[1];
//Convert $dbString to usable server & database strings
$server = explode("/", $dbString)[0];
$database = explode("/", $dbString)[1];
//Convert string date into datetime formate.
$oldate=date(("Y-m-d"), strtotime("-ExpireMonths months"));
$link = "";
$isi = false;
echo "Logging into $server with username $username and connecting to database $database \n";
if(function_exists("mysql_connect")){
	$isi = false;
	if(!$link = mysql_connect($server, $username, $password)){
		echo "Could not connect to mariadb \n";
    	exit;	
	}
} elseif(function_exists("mysqli_connect")) {
	$isi = true;
	echo "mysql_connect function does not exist! Falling back yo mysqli_*()! \n";
	if(!$link = mysqli_connect($server, $username, $password)){
		//Now we can fail.
		echo "Could not connect to mariadb \n";
    	exit;
	}
}else{
	echo "Is MySQL/MySQLi modules installed...? \n";
	exit;
}
$notice_query="DELETE FROM notice WHERE created <= '$oldate 01:01:01'";
$conversation_query="DELETE FROM conversation WHERE created <= ' 01:01:01'";
$reply_query="DELETE FROM reply WHERE modified <= '$oldate 01:01:01'";
$notification_query="DELETE FROM qvitternotification WHERE created <= '$oldate 01:01:01'";
if($isi){
	if (!mysqli_select_db($link, $database)) {
	    echo "Could not select gnusocial database";
	    exit;
	}
	mysqli_query($link, $notice_query);
	$rowaff1=mysqli_affected_rows($link);
	mysqli_query($link, $conversation_query);
	$rowaff2=mysqli_affected_rows($link);
	mysqli_query($link, $reply_query);
	$rowaff3=mysqli_affected_rows($link);
	mysqli_query($link, $notification_query);
	$rowaff4=mysqli_affected_rows($link);
	mysqli_close($link);	
} else {
	if (!mysql_select_db($database, $link)) {
	    echo "Could not select postactiv database";
	    exit;
	}
	mysql_query($notice_query);
	$rowaff1=mysql_affected_rows();
	mysql_query($conversation_query);
	$rowaff2=mysql_affected_rows();
	mysql_query($reply_query);
	$rowaff3=mysql_affected_rows();
	mysql_query($notification_query);
	$rowaff4=mysql_affected_rows();
	mysql_close();	
}


echo "Expire postActiv posts: $rowaff1 notices, $rowaff2 conversations, $rowaff3 replies, and $rowaff4 qvitter notifications deleted from database.\n";

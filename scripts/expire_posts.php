<?php

// gnusocial post expiry script, based on StatExpire by Tony Baldwin
// https://github.com/tonybaldwin/statexpire

$oldate=date(("Y-m-d"), strtotime("-ExpireMonths months"));
$username="root";
$password="YourPassword";
$database="postactiv";

if (!$link = mysql_connect("localhost", $username, $password)) {
    echo "Could not connect to mariadb";
    exit;
}

if (!mysql_select_db($database, $link)) {
    echo "Could not select postactiv database";
    exit;
}

$notice_query="DELETE FROM notice WHERE created <= '$oldate 01:01:01'";
$conversation_query="DELETE FROM conversation WHERE created <= ' 01:01:01'";
$reply_query="DELETE FROM reply WHERE modified <= '$oldate 01:01:01'";
$notification_query="DELETE FROM qvitternotification WHERE created <= '$oldate 01:01:01'";

mysql_query($notice_query);
$rowaff1=mysql_affected_rows();
mysql_query($conversation_query);
$rowaff2=mysql_affected_rows();
mysql_query($reply_query);
$rowaff3=mysql_affected_rows();
mysql_query($notification_query);
$rowaff4=mysql_affected_rows();
mysql_close();

echo "Expire postActiv posts: $rowaff1 notices, $rowaff2 conversations, $rowaff3 replies, and $rowaff4 qvitter notifications deleted from database.\n";

<?php
##############################################################################
#
# Include Cacti links into Nagios
# Author: Nicolas Hennion
# Modified: Dennis Yusupoff
#
# Distributed under the GPL licence
#
# Web page: http://blog.nicolargo.com/2008/06/lier-cacti-et-nagios.html
#
##############################################################################
$version="0.21";

# Default options (TO BE CONFIGURE)
$cactiurl="http://".$_SERVER['HTTP_HOST']."/cacti";
$database_default = "cacti";
$database_hostname = "localhost";
$database_username = "cactiadmin";
$database_password = "cactipassword";
# End of the options (DO NOT MODIFY THE INFORMATIONS BELLOW)

# Functions
###########

function checkip($address) {
        if(is_string($address)) {
                # Is it IP...?
                $ip = ip2long($address);
                if( $ip != -1 && $ip !== FALSE) {
                        return $ip;
                }
                #...or hostname?
                elseif(gethostbyname($address) != $address) {
                        return $address;
                }
        }
return false;
}

# Main Code
###########

# Get the IP ip oh the host (GET method)
if (isset($_GET["ip"]) && checkip($_GET["ip"])) {
        $ip = $_GET["ip"];
        $address = gethostbyname($ip);  # (We already sanitize it in "checkip" function)
} else {
        die("Incorrect IP or hostname");
}

# Connect to the Cacti DB
$link = mysql_connect($database_hostname, $database_username, $database_password)
    or die("Error while connecting to the DB server");
mysql_select_db($database_default)
        or die("Could not select database");

# Build and execute the SQL request
$query = "SELECT graph_local.id AS local_graph_id, host.id AS host_id, host.hostname AS hostname "
        ."FROM (graph_local, host) "
        ."WHERE graph_local.host_id=host.id AND host.hostname LIKE '".$ip."' OR host.hostname LIKE '".$address."'";
$result = mysql_query($query)
        or die("Query failed");

# Get the result (the last one)
if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
                $action_url = $cactiurl."/graph_view.php?action=preview&host_id=".$row["host_id"]."";
        }
    header("Location: ".$action_url);
}
else {
    die("Host not found, sorry...");
}

# Close the DB session
mysql_free_result($result);
mysql_close($link);

?> 

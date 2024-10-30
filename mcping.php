<?php

/* 
Plugin Name: MCPing
Version: 1.1
Description: This plugin shows if your Minecraft server is online or offline.
Author: Tutorialwork
Author URI: http://Tutorialwork.de
Licenc: GPLv2
*/

function mcping_shortcode($atts)

{

     $server = get_option('mcping_ip');
     $port = get_option('mcping_port');
     $timeout = "10";
     if ($server and $port and $timeout)
     {
     $server= @fsockopen("$server",$port,$timeout); 
     }
     if($server){ 
	 if(get_locale() == 'de_DE'){ 
	 return '<h1>Der Minecraft Server ist derzeit <p><span style="color: #339966;">online</span></p></h1>';
	 } else {
     return '<h1>The Minecraft server is currently <p><span style="color: #339966;">online</span></p></h1>';
	 }
     } else { 
	 if(get_locale() == 'de_DE'){
	 return '<h1>Der Minecraft Server ist derzeit <p><span style="color: #ff0000;">offline</span></p></h1>';
	 } else {
     return '<h1>The Minecraft server is currently <p><span style="color: #ff0000;">offline</span></p></h1>'; 
	 }
     }

}
add_shortcode('mcserver', 'mcping_shortcode');

function mcping_shortcode_onlineplayers($atts)
{
	
$ip = get_option('mcping_ip');
$port = get_option('mcping_port');

$onlinePlayers = 0;
$maxPlayers = 0;

$serverSock = @stream_socket_client('tcp://'.$ip.':'.$port, $empty, $empty, 1);


if($serverSock !== FALSE)
{
    fwrite($serverSock, "\xfe");
    
    $response = fread($serverSock, 2048);
    $response = str_replace("\x00", '', $response);
    $response = substr($response, 2);
    
    $data = explode("\xa7", $response);
    
    unset($response);

    fclose($serverSock);

    if(sizeof($data) == 3)
    {
        $onlinePlayers = (int) $data[1];
        $maxPlayers = (int) $data[2];
        
        return $onlinePlayers.'/'.$maxPlayers;
    }else{
		if(get_locale() == 'de_DE'){
		return 'Es konnte keine Verbindung hergestellt werden';
		} else {
		return 'Could not connect'; 
		}
    }
}else{
    if(get_locale() == 'de_DE'){
	return 'Der Server ist offline';
	} else {
    return 'Server is offline'; 
	}
}

}
add_shortcode('mcserverplayer', 'mcping_shortcode_onlineplayers');

function mcping_sidebarwidget()
{
global $current_user;

if(get_locale() == 'de_DE'){
	   echo '<h3 class="widget-title">Minecraft Server Status</h3>';
   } else {
	   echo '<h3 class="widget-title">Minecraft server status</h3>';
}
   
   $server = get_option('mcping_ip');
   $port = get_option('mcping_port');
   $timeout = "10";
   if ($server and $port and $timeout){
     $server= @fsockopen("$server",$port,$timeout); 
   }
   
   if($server){ 
     echo '<p><span style="color: #339966;">ONLINE</span></p>';
    } else {
     echo '<p><span style="color: #ff0000;">OFFLINE</span></p>';
    }
}

function mcping_player_sidebarwidget()
{
global $current_user;

if(get_locale() == 'de_DE'){
	   echo '<h3 class="widget-title">Minecraft Server Status</h3>';
   } else {
	   echo '<h3 class="widget-title">Minecraft server status</h3>';
}
   
$ip = get_option('mcping_ip');
$port = get_option('mcping_port');

$onlinePlayers = 0;
$maxPlayers = 0;

$serverSock = @stream_socket_client('tcp://'.$ip.':'.$port, $empty, $empty, 1);


if($serverSock !== FALSE)
{
    fwrite($serverSock, "\xfe");
    
    $response = fread($serverSock, 2048);
    $response = str_replace("\x00", '', $response);
    $response = substr($response, 2);
    
    $data = explode("\xa7", $response);
    
    unset($response);

    fclose($serverSock);

    if(sizeof($data) == 3)
    {
        $onlinePlayers = (int) $data[1];
        $maxPlayers = (int) $data[2];
        
		if(get_locale() == 'de_DE'){
		echo '<p><strong>'.$onlinePlayers.'</strong>/'.$maxPlayers.' Spieler online</p>';
		} else {
		echo '<p><strong>'.$onlinePlayers.'</strong>/'.$maxPlayers.' players online</p>';
		}
    }else{
		if(get_locale() == 'de_DE'){
		echo 'Es konnte keine Verbindung hergestellt werden';
		} else {
		echo 'Could not connect'; 
		}
    }
}else{
    echo '<p><span style="color: #ff0000;">OFFLINE</span></p>'; 
}
   
}

function MCPing_widget_init()
{
   wp_register_sidebar_widget('1',__('Minecraft server status'), 'mcping_sidebarwidget');
   wp_register_sidebar_widget('2',__('Minecraft server status with online players'), 'mcping_player_sidebarwidget');
}
add_action("plugins_loaded", "MCPing_widget_init");


function mcping_site()
{
check_admin_referer();

global $current_user;
   echo '<div class="wrap">';
   echo '<h3>MCPing</h3>';
   if(get_locale() == 'de_DE'){
	   echo 'Mit diesem Plugin kannst du den Status deines Minecraft Server einsehen. <br>Damit das ganze funktioniert muss du unten noch die Serverip und den Serverport eintragen.';
   } else {
	   echo 'With this plugin you can see the status of your Minecraft server. <br>Please fill in your server adresse and server port.';
   }
   echo '</div>';


if ( $_REQUEST['page'] == 'mcping' && isset( $_POST['submit'] ) ) {

$server2=filter_var( $_POST['ip'], FILTER_VALIDATE_IP );
$port2=intval($_POST['port']);

if($server2 == false)
{
	
if(get_locale() == 'de_DE'){
	   echo '<p><span style="color: #ff0000;">Dies ist keine gültige Serverip</span></p>';
   } else {
	   echo '<p><span style="color: #ff0000;">This is not a server address</span></p>';
}

exit;
}

if($port2 == false)
{

if(get_locale() == 'de_DE'){
	   echo '<p><span style="color: #ff0000;">Dies ist kein gültiger Serverport</span></p>';
   } else {
	   echo '<p><span style="color: #ff0000;">This is not a server port</span></p>';
}

exit;
}

update_option( 'mcping_ip', $server2);
update_option( 'mcping_port', $port2);

if(get_locale() == 'de_DE'){
	   echo '<p><span style="color: #339966;">Die Änderungen wurden erfolgreich gespeichert</span></p>';
   } else {
	   echo '<p><span style="color: #339966;">The changes have been saved</span></p>';
}
}
?>

<form method="post" action="" id="inputform" class="validate">
<?php

if(get_locale() == 'de_DE'){
	   echo "<p>Serverip:</p>";
   } else {
	   echo "<p>server address:</p>";
}

?>
<input type="text" name="ip" value="<?php echo get_option('mcping_ip') ?>">

<?php

if(get_locale() == 'de_DE'){
	   echo "<p>Serverport:</p>";
   } else {
	   echo "<p>server port:</p>";
}

?>

<input type="text" name="port" value="<?php echo get_option('mcping_port') ?>">
  <p class="submit"><input type="submit" name="submit" id="submit" class="button" value="<?php

if(get_locale() == 'de_DE'){
	   echo "Speichern";
   } else {
	   echo "Save";
}

?>"></p>
   </form>
   </div>
   

<?php	
}

function mcping_plugin_menu()
{
add_menu_page('MCPing', 'MCPing', 'read', 'mcping', 'mcping_site');
}

add_action('admin_menu', 'mcping_plugin_menu');
?>

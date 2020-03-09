<?php

/**
 * Foursquare Token Request
 *
 * Requisita um OAuth Token para autenticação do usuário
 *
 * @category   Foursquare
 * @package    Foursquare-Mass-Editor-Tools
 * @author     Elio Gavlinski <gavlinski@gmail.com>
 * @copyright  Copyleft (c) 2012-2014
 * @version    2.2.2
 * @link       https://github.com/gavlinski/Foursquare-Mass-Editor-Tools/blob/master/index.php
 * @since      File available since Release 1.5
 * @license    GPLv3 <http://www.gnu.org/licenses/gpl.txt>
 */

	require_once("FoursquareAPI.Class.php");

	// Set client key and secret
	include 'includes/app_credentials.php';

	// Load the Foursquare API library
	$foursquare = new FoursquareAPI($client_key, $client_secret);

	// If the link has been clicked, and we have a supplied code, use it to request a token
	if ((isset($_COOKIE['oauth_token'])) && ($_COOKIE['oauth_token'] != "0")) {
		$token = $_COOKIE['oauth_token'];
	} else if (array_key_exists("code", $_GET)) {
		$token = $foursquare->GetToken($_GET['code'], $redirect_uri);
	}
	
	// If we have not received a token, display the link for Foursquare webauth
	if (!isset($token)) {

?>
<!doctype html>
<html lang="pt-BR">
<head>
<title>Elio Tools</title>
<meta charset="utf-8">
<script src="//ajax.googleapis.com/ajax/libs/dojo/1.8.0/dojo/dojo.js" djConfig="parseOnLoad: true"></script>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/dojo/1.8.0/dijit/themes/tundra/tundra.css">
<link rel="stylesheet" type="text/css" href="estilo.css">
</head>
<body class="tundra">
<p>
	<?php

		echo "<a href='" . $foursquare->AuthenticationLink($redirect_uri) . "'><img src='img/connectTo@2x-f07c1cb7c6ed8894bb14dedd1001bcf3.png' alt='Connect to this app via Foursquare'></a>";

	?>
</p>
</body>
</html>
<?php

	// Otherwise save the token in a session variable and redirect browser
	} else {
		if (!isset($_SESSION))
			session_start();
		$_SESSION["oauth_token"] = $token;

		// Save and configure a cookie to expire in 15 days
		setcookie("oauth_token", $token, time()+60*60*24*15);
		
		// Load the Foursquare API library
		$foursquare -> SetAccessToken($_SESSION["oauth_token"]);
	
		// Perform a request to a authenticated-only resource
		$request = $foursquare->GetPrivate("users/self");
		$details = json_decode($request);
		
		// Returns profile information for a given user
		$u = $details->response->user;
		if (property_exists($u, "firstName") && property_exists($u, "lastName")) {
			$name = $u->firstName . " " . $u->lastName;
			setrawcookie("name", rawurlencode($name), time()+60*60*24*1);
		}
		if (property_exists($u->checkins, "items")) {
			$coordinates = $u->checkins->items[0]->venue->location->lat . "," . $u->checkins->items[0]->venue->location->lng;
			setcookie("coordinates", $coordinates, time()+60*60*24*1);
		}
		
		if (isset($_SESSION["venues"]))
			header('Location: load.php');
		else
			header('Location: main.php');
	}
?>


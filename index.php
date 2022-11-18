<?php

require_once 'src/TadaServer.class.php';

$db = new PDO('sqlite:../../../storage/tada-server.sqlite');
// ... or another PDO database handle.

$server = new TadaServer( $db );
$server->handle_request();

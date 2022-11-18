<?php

class TadaError {

	public function FileNotFound () {
		header( "HTTP/1.0 404 Not Found" );
		header( "Content-Type: text/plain" );
		echo "404 Not Found\n";
		die();
	}

	public function Unauthorized () {
		header( "HTTP/1.0 401 Unauthorized" );
		header( "Content-Type: text/plain" );
		echo "401 Unauthorized\n";
		die();
	}

	public function Forbidden () {
		header( "HTTP/1.0 403 Forbidden" );
		header( "Content-Type: text/plain" );
		echo "403 Forbidden\n";
		die();
	}
	
	public function PreconditionFailed () {
		header( "HTTP/1.0 412 Precondition Failed" );
		header( "Content-Type: text/plain" );
		echo "412 Precondition Failed\n";
		die();
	}
}

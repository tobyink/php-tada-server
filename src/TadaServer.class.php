<?php

require_once 'TadaFile.class.php';
require_once 'TadaUser.class.php';

class TadaServer {
	public $db;

	public function __construct ( $db ) {
		$this->db = $db;
		return $this;
	}

	public function handle_request () {
		$db = $this->db;
		$user = TadaUser::from_request( $db );

		switch ( $_SERVER['REQUEST_METHOD'] ) {
			case 'HEAD':
				$file = TadaFile::from_request( $db );
				$file->head( $user );
				break;
			case 'GET':
				$file = TadaFile::from_request( $db );
				$file->get( $user );
				break;
			case 'POST':
				$file = TadaFile::from_request( $db );
				$text = file_get_contents('php://input');
				$file->post( $user, $text );
				break;
			case 'PUT':
				$file = TadaFile::from_put_request( $db, $user );
				$text = file_get_contents('php://input');
				$file->post( $user, $text );
				break;
		}
	}
}

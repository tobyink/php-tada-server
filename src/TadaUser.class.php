<?php

require_once 'TadaError.class.php';

class TadaUser {
	public $db;
	public $id;
	public $username;

	public function __construct ( $db, $id, $username ) {
		$this->db = $db;
		$this->id = $id;
		$this->username = $username;
	}

	public static function from_id ( $db, $id ) {

		$sth = $db->prepare( 'SELECT username FROM user WHERE id = ?' );
		$sth->execute( [ $id ] );
		if ( $result = $sth->fetch( PDO::FETCH_OBJ ) ) {
			return new TadaUser( $db, $id, $result->username );
		}

		return FALSE;
	}

	public static function from_username ( $db, $username ) {

		$sth = $db->prepare( 'SELECT id FROM user WHERE username = ?' );
		$sth->execute( [ $username ] );
		if ( $result = $sth->fetch( PDO::FETCH_OBJ ) ) {
			return new TadaUser( $db, $result->id, $username );
		}

		return FALSE;
	}

	public static function from_token ( $db, $token ) {

		$sth = $db->prepare( 'SELECT id, username FROM user WHERE token = ?' );
		$sth->execute( [ $token ] );
		if ( $result = $sth->fetch( PDO::FETCH_OBJ ) ) {
			return new TadaUser( $db, $result->id, $result->username );
		}

		return FALSE;
	}

	public static function from_request ( $db ) {

		$http_auth = FALSE;
		if ( $http_auth===FALSE && isset( $_SERVER['HTTP_AUTHORIZATION'] ) )
			$http_auth = $_SERVER['HTTP_AUTHORIZATION'];
		if ( $http_auth===FALSE && isset( $_SERVER['HTTP_X_TADA_AUTHORIZATION'] ) )
			$http_auth = $_SERVER['HTTP_X_TADA_AUTHORIZATION'];
		$headers = apache_request_headers();
		if ( $http_auth===FALSE && isset( $headers['Authorization'] ) )
			$http_auth = $headers['Authorization'];
		if ( $http_auth===FALSE && isset( $headers['X-Tada-Authorization'] ) )
			$http_auth = $headers['X-Tada-Authorization'];

		if ( $http_auth!==FALSE && preg_match( '/Bearer\s*(\S+)/i', $http_auth, $matches ) ) {
			$user  = TadaUser::from_token( $db, $matches[1] );
			if ( $user !== FALSE ) {
				return $user;
			}
		}

		die( TadaError::Unauthorized() );
	}

	public function can_read ( TadaFile $file ) {
		if ( $file->owner == $this->id ) {
			return TRUE;
		}

		$sth = $db->prepare('
			SELECT 1 AS found
			FROM permission
			WHERE file = ?
			AND user = ?
			AND read >= 1
		');
		$sth->execute( [ $file->id, $this->id ] );
		if ( $result = $sth->fetch( PDO::FETCH_OBJ ) ) {
			return TRUE;
		}

		return FALSE;
	}

	public function can_write ( TadaFile $file ) {
		if ( $file->owner == $this->id ) {
			return TRUE;
		}

		$sth = $db->prepare('
			SELECT 1 AS found
			FROM permission
			WHERE file = ?
			AND user = ?
			AND write >= 1
		');
		$sth->execute( [ $file->id, $this->id ] );
		if ( $result = $sth->fetch( PDO::FETCH_OBJ ) ) {
			return TRUE;
		}

		return FALSE;
	}
}

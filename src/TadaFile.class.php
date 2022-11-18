<?php

require_once 'TadaError.class.php';

class TadaFile {
	public $db;
	public $id;
	public $filepath;
	public $owner;
	private $_content;

	public function __construct ( $db, $id, $filepath, $owner ) {
		$this->db = $db;
		$this->id = $id;
		$this->filepath = $filepath;
		$this->owner = $owner;
	}

	public static function from_id ( $db, $id ) {

		$sth = $db->prepare( 'SELECT filepath, owner FROM file WHERE id = ?' );
		$sth->execute( [ $id ] );
		if ( $result = $sth->fetch( PDO::FETCH_OBJ ) ) {
			return new TadaFile( $db, $id, $result->filepath, $result->owner );
		}

		return FALSE;
	}

	public static function from_filepath ( $db, $filepath ) {

		$sth = $db->prepare( 'SELECT id, owner FROM file WHERE filepath = ?' );
		$sth->execute( [ $filepath ] );
		if ( $result = $sth->fetch( PDO::FETCH_OBJ ) ) {
			return new TadaFile( $db, $result->id, $filepath, $result->owner );
		}

		return FALSE;
	}

	public static function from_request ( $db ) {

		$path = substr( $_SERVER['PATH_INFO'], 1 );
		$file = TadaFile::from_filepath( $db, $path );
		if ( $file !== FALSE ) {
			return $file;
		}

		die( TadaError::FileNotFound() );
	}

	public static function from_put_request ( $db, TadaUser $user ) {

		$path = substr( $_SERVER['PATH_INFO'], 1 );
		$file = TadaFile::from_filepath( $db, $path );
		if ( $file === FALSE ) {
			$db->prepare( 'INSERT INTO file ( filepath, owner ) VALUES ( ?, ? )' );
			$db->execute( [ $path, $owner->id ] );
			$file = TadaFile::from_filepath( $db, $path );
		}

		return $file;
	}

	public function owner_user () {

		return TadaUser::from_id( $this->db, $this->owner );
	}

	public function latest_content () {

		$sth = $this->db->prepare('
			SELECT updated, etag, content
			FROM content
			WHERE file = ?
			ORDER BY updated DESC
			LIMIT 1
		');
		$sth->execute( [ $this->id ] );
		$this->_content = $sth->fetch( PDO::FETCH_OBJ );
		return $this->_content;
	}

	public function add_content ( TadaUser $user, $text ) {
		
		$past = time() - 90*24*60*60; // 90 days ago
		$sth = $this->db->prepare('
			DELETE FROM content
			WHERE file = ?
			AND creator = ?
			AND updated < ?
		');
		$sth->execute( [ $this->id, $user->id, $past ] );
		
		$sth = $this->db->prepare('
			INSERT INTO content ( file, creator, updated, etag, content )
			VALUES ( ?, ?, ?, ?, ? )
		');
		$sth->execute( [ $this->id, $user->id, time(), uniqid(), $text ] );
	}

	public function head ( TadaUser $user ) {

		if ( ! $user->can_read( $this ) ) {
			die( TadaError::Forbidden() );
		}

		$content = $this->latest_content();
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( sprintf( 'Content-Length: %d', strlen( $content->content ) ) );
		header( sprintf( 'ETag: "%s"', $content->etag ) );
		header( sprintf( 'Last-Modified: %s', date( 'r', $content->updated ) ) );
	}

	public function get ( TadaUser $user ) {

		$this->head( $user );

		$content = $this->_content;
		if ( ! $content ) {
			$content = $this->latest_content();
		}

		echo $content->content;
	}

	public function post ( TadaUser $user, $text ) {

		if ( ! $user->can_write( $this ) ) {
			die( TadaError::Forbidden() );
		}

		$latest = $this->latest_content();

		if ( isset( $_SERVER['HTTP_IF_MATCH'] ) ) {
			if ( $_SERVER['HTTP_IF_MATCH'] != sprintf( '"%s"', $latest->etag ) ) {
				die( TadaError::PreconditionFailed() );
			}
		}

		if ( isset( $_SERVER['HTTP_IF_UNMODIFIED_SINCE'] ) ) {
			$d = strtotime( $_SERVER['HTTP_IF_UNMODIFIED_SINCE'] );
			if ( $latest->updated > $d ) {
				die( TadaError::PreconditionFailed() );
			}
		}

		$this->add_content( $user, $text );

		if ( $user->can_read( $this ) ) {
			$this->get( $user );
		}
		else {
			header( "HTTP/1.0 204 No Content" );
			die();
		}
	}
}

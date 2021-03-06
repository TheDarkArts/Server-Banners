<?php
//------------------------------------------------------------------------------------------------------------+
//
// Name: BF.php
//
// Description: Code to parse Battlefield servers
// Initial author: momo5502 <MauriceHeumann@googlemail.com>
// Note: Main algorithm by Richard Pery, copied from LGSL @ http://greycube.com
//
//------------------------------------------------------------------------------------------------------------+

if ( !defined( "BANNER_CALL" ) ) {
	exit( "DIRECT ACCESS NOT ALLOWED" );
}

//------------------------------------------------------------------------------------------------------------+
//Query BF server - main function!

function queryMain( $ip, $port )
{
	$server  = "tcp://" . $ip;
	$connect = @fsockopen( $server, $port, $errno, $errstr, 1 );
	
	if ( !$connect ) {
		return getErr( $ip, getPort() );
	}
	
	@fwrite( $connect, "\x00\x00\x00\x00\x1b\x00\x00\x00\x01\x00\x00\x00\x0a\x00\x00\x00serverInfo\x00" );
	stream_set_timeout( $connect, 2 );
	$buffer = @fread( $connect, 4096 );
	$info   = stream_get_meta_data( $connect );
	
	if ( !$buffer || $info[ 'timed_out' ] ) {
		if ( !$alt_port )
			return query( $ip, 48888, true );
		
		else
			return getErr( $ip, getPort() );
	}
	
	$length = lgsl_unpack( substr( $buffer, 4, 4 ), "L" );
	
	while ( strlen( $buffer ) < $length ) {
		$packet = fread( $lgsl_fp, 4096 );
		
		if ( $packet ) {
			$buffer .= $packet;
		} else {
			break;
		}
	}
	
	$data = array(
		 "protocol" => "BF",
		"value" => 1,
		"server" => $ip . ":" . $port,
		"response" => $buffer 
	);
	
	$buffer = substr( $buffer, 12 );
	
	$response_type = lgsl_cut_pascal( $buffer, 4, 0, 1 );
	
	if ( $response_type != "OK" ) {
		return getErr( $ip, getPort() );
	}
	
	$data[ "hostname" ]   = lgsl_cut_pascal( $buffer, 4, 0, 1 );
	$data[ "clients" ]    = lgsl_cut_pascal( $buffer, 4, 0, 1 );
	$data[ "maxclients" ] = lgsl_cut_pascal( $buffer, 4, 0, 1 );
	$data[ "gametype" ]   = lgsl_cut_pascal( $buffer, 4, 0, 1 );
	$data[ "mapname" ]    = strtolower( lgsl_cut_pascal( $buffer, 4, 0, 1 ) );
	$data[ "unclean" ]    = $data[ "hostname" ];
	
	$data[ "gametype" ] = substr( $data[ "gametype" ], 0, strlen( $data[ "gametype" ] ) - 1 );
	
	foreach ( $data as $key => $value )
		$data[ $key ] = preg_replace( '/[^(\x20-\x7F)]*/', '', $value ); //Remove all non-ascii chars
	
	$data[ "mapname" ] = strtolower( str_replace( "Levels/", "", $data[ "mapname" ] ) );
	
	cleanMapname( $data[ "mapname" ] );
	
	return $data;
}

//------------------------------------------------------------------------------------------------------------+
//Clean mapname ending

function cleanMapname( &$mapname )
{
	$mapname = str_replace( "levels/", "", $mapname );
	$endings = array(
		 "gr",
		"cq",
		"sdm",
		"sr" 
	);
	
	foreach ( $endings as $ending ) {
		if ( strpos( $mapname, $ending ) == strlen( $mapname ) - strlen( $ending ) )
			$mapname = substr( $mapname, 0, strlen( $mapname ) - strlen( $ending ) );
	}
	
	if ( $mapname[ strlen( $mapname ) - 1 ] == "_" )
		$mapname = substr( $mapname, 0, strlen( $mapname ) - 1 );
	
	if ( substr( $mapname, 0, 4 ) == "nam_" )
		$mapname = substr( $mapname, 4 );
}

//------------------------------------------------------------------------------------------------------------+
//LGSL code - thx to Richard Pery @ http://greycube.com

function lgsl_cut_pascal( &$buffer, $start_byte = 1, $length_adjust = 0, $end_byte = 0 )
{
	$length = ord( substr( $buffer, 0, $start_byte ) ) + $length_adjust;
	$string = substr( $buffer, $start_byte, $length );
	$buffer = substr( $buffer, $start_byte + $length + $end_byte );
	
	return $string;
}

function lgsl_unpack( $string, $format )
{
	list( , $string ) = @unpack( $format, $string );
	
	return $string;
}
//------------------------------------------------------------------------------------------------------------+
?>
<?
//------------------------------------------------------------------------------------------------------------+
//
// Name: banner-code.php
//
// Description: Core code/setup code for the banners.
// Initial author: momo5502 <MauriceHeumann@googlemail.com>
//
//------------------------------------------------------------------------------------------------------------+

//------------------------------------------------------------------------------------------------------------+

include( 'game-cod.php' );
include( 'game-minecraft.php' );
include( 'game-samp.php' );
include( 'banner-print.php' );

//------------------------------------------------------------------------------------------------------------+
//Setup a banner

function banner()
{
    //Get basic information
    $ip   = getIP();
    $port = getPort();
    
    if ( !isSet( $_GET[ "game" ] ) )
        $info = queryCOD( $ip, $port );
    
    else {
        switch ( $_GET[ "game" ] ) {
            case "COD":
                $info = queryCOD( $ip, $port );
                break;
            
            case "SAMP":
                $info = querySAMP( $ip, $port );
                break;
            
            default:
                $info = getErr( $ip, $port );
                break;
        }
    }
    
    printimage( $info );
}

//------------------------------------------------------------------------------------------------------------+
?>
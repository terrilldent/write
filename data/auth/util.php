<?php
include_once("config.php");

function connectToDB()
{
    global $HOST, $USER, $PASS, $DBNAME;
    $link = mysql_connect( $HOST, $USER, $PASS );
    mysql_select_db( $DBNAME ) or die( "Unable to select database");
    
    return $link;
}

function getIPAddress()
{
    return mysql_real_escape_string( $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_X_FORWARDED_FOR'] );
}

// TOOD: user agent

function checkAuth( $refreshToken )
{
    // Connect
    $link = connectToDB();

    // Require Values
    if( !isset( $_POST[ 'user' ] ) || !isset( $_POST[ 'token' ] ) || 
         empty( $_POST[ 'user' ] ) ||  empty( $_POST[ 'token' ] ) ) {
        echo "missing values";
        return;
    }

    // Get incoming Password
    $user = mysql_real_escape_string( $_POST[ 'user' ] );
    $token = $_POST[ 'token' ];

    // Get Stored User
    $query = "SELECT token, password, ipaddress, expiry FROM users WHERE username = '$user'";
    $result = mysql_query($query,$link);
    if(!$result) {
        echo "no result";

        return false;
    }
    if( mysql_numrows($result) != 1 ){
        echo "multi results";
        return false;
    }

    $entry       = mysql_fetch_array($result);
    $storedToken = $entry['token'];
    $storedHash  = $entry['password'];
    $storedIP    = $entry['ipaddress'];
    $expiry      = $entry['expiry'];

    // Check for null values
    if( !isset( $entry[ 'token' ] )     || empty( $entry[ 'token' ] ) || 
        !isset( $entry[ 'ipaddress' ] ) || empty( $entry[ 'ipaddress' ] ) || 
        !isset( $entry[ 'expiry' ] )    || empty( $entry[ 'expiry' ] ) ) {
        echo "bad value";
        return false;
    }

    // Check if token is expired
    if( strtotime( $expiry ) < time() ) {
        echo "expired";
        return false;
    }
    
    // Test incoming Token == StoredToken
    if( strcmp( $token, $storedToken ) != 0 ) {
        echo "token";
        return false;
    } 

    // Incoming IP Address
    $IP = getIPAddress();
    if( strcmp( $IP, $storedIP ) != 0 ) {
        echo "ip";
        return false;
    } 

    if( $refreshToken ) {
        // Generate New Token
        $token = hash( 'sha256', $user . $IP . rand( 0, 10000000 ) . dechex( gmmktime() ) . $storedHash );
    }

    // Insert Token into Database with expiry
    $query = "UPDATE users SET token = '$token', expiry = DATE_ADD( NOW(), INTERVAL 20 MINUTE ) WHERE username = '$user';";
    $result = mysql_query($query,$link);
    if(!$result) {
        echo "update";
        return false;
    }

    // Close Database
    mysql_close($link);

    // Return Token
    return array(
        "login" => "true",
        "user" => "$user",
        "token" => "$token"
    );
}


if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        return $code;
    }
}

?>
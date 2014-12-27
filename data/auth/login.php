<?
include_once("util.php");

/**
 * Perform a full login check 
 */
function login()
{
    // Constants
    $SECONDS_BETWEEN_TRIES = 15;

    // Connect
    $link = connectToDB();

    // Require Username
    if( !isset( $_POST[ 'user' ] ) || empty( $_POST[ 'user' ] ) ) {
    	http_response_code( 403 );
        echo '{ "login": "false", "error": 1 }';
        return;
    }

    // Require either Token or Password
	if( ( !isset( $_POST[ 'pass' ] ) && !empty( $_POST[ 'pass' ] ) ) ||
		( !isset( $_POST[ 'token' ] ) && !empty( $_POST[ 'token' ] ) ) ) {
    	http_response_code( 403 );
        echo '{ "login": "false", "error": 2 }';
        return;
    }

    // Get incoming username
    $user = mysql_real_escape_string( $_POST[ 'user' ] );

    // Incoming IP Address
    $IP = getIPAddress();

    // Get Stored User
    $query = "SELECT password, token, attempt, ipaddress FROM users WHERE username = '$user'";
    $result = mysql_query($query,$link);
    if(!$result) {
        http_response_code( 403 );
        echo '{ "login": "false", "error": 3 }';
        return;
    }
    if( mysql_numrows($result) != 1 ){
        http_response_code( 403 );
        echo '{ "login": "false", "error": 4 }';
        return;
    }

    $entry = mysql_fetch_array($result);
    $storedHash = $entry['password'];
    $storedToken = $entry['token'];
    $storedIP = $entry['ipaddress'];

    // Check if last attempt was too soon
    if( isset( $entry['attempt'] ) && !empty( $entry['attempt'] ) ) {
        $lastAttempt = strtotime( $entry['attempt'] );
        $now = time();
        if( $now < $lastAttempt + $SECONDS_BETWEEN_TRIES ) { //
            http_response_code( 403 );
            echo '{ "login": "false", "error": 5 }';
            return;
        }
    }


    if( isset( $_POST[ 'pass' ] ) ) {
    	$pass = $_POST[ 'pass' ];

	    // Hex of GMT Minutes
	    $timeHash = hash( "sha256", dechex( round( gmmktime() / 100 ) ) );
	    $combination = hash( "sha256", $timeHash . $storedHash );

	    // Test incoming Password == Combination
	    if( strcmp( $pass, $combination ) != 0 ) {
	        http_response_code( 403 );
	        echo '{ "login": "false", "error": 6 }';
	        return;
	    } 

    } else if( isset( $_POST[ 'token' ] ) ) {
    	$token = $_POST[ 'token' ];

	    // Test incoming Token == StoredToken
	    if( strcmp( $token, $storedToken ) != 0 ||
	    	strcmp( $IP, $storedIP ) != 0 ) {
	        http_response_code( 403 );
        	echo '{ "login": "false", "error": 7 }';
	        return;
	    } 

    } else {
        http_response_code( 403 );
        echo '{ "login": "false", "error": 8 }';
        return;
    }

    // Generate Token
    $token = hash( 'sha256', $user . $IP . rand( 0, 10000000 ) . dechex( gmmktime() ) . $storedHash );

    // Insert Token into Database with expiry
    $query = "UPDATE users SET token = '$token', ipaddress = '$IP', attempt = null, expiry = DATE_ADD( NOW(), INTERVAL 10 MINUTE ) WHERE username = '$user';";
    $result = mysql_query($query,$link);
    if(!$result) {
        http_response_code( 403 );
        echo '{ "login": "false", "error": 9 }';
        return;
    }

    // Close Database
    mysql_close($link);

    // Return Token or Failure
    http_response_code( 200 );
    echo "{ \"login\":\"true\", \"user\":\"$user\", \"token\":\"$token\" }";
}

login();
?>
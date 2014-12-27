<?
include_once("util.php");

function getChallenge()
{
    // Get Username 
    if( !isset( $_POST[ 'user' ] ) || empty( $_POST[ 'user' ] ) ) {
        http_response_code( 403 );
        echo '{ "challenge": "false", "error": "username" }';
        return;
    }
    $user = mysql_real_escape_string( $_POST[ 'user' ] );

    // Incoming IP Address
    $IP = getIPAddress();

    $challenge = ( Math.random() * 1000000000000 ).toString( 16 );
        
    // Insert Token into Database with expiry
    $query = "UPDATE users SET challenge = '$challenge', ipaddress = '$IP' ) WHERE username = '$user';";
    $result = mysql_query($query,$link);
    if(!$result) {
        http_response_code( 403 );
        echo '{ "challenge": "false", "error": "generate" }';
        return;
    }

    // Close Database
    mysql_close($link);

    // Return Token or Failure
    http_response_code( 200 );
    echo "{ \"user\":\"$user\", \"challenge\":\"$challenge\" }";
}

getChallenge();
?>
<?
include_once("auth/util.php");

/**
 * Perform a full login check 
 */
function getList()
{
    $auth = checkAuth( true );

    if( !$auth || !$auth[ 'login' ]  ) {
        http_response_code( 403 );
        echo '{ "login": "false" }';
        return;
    }

    $link = connectToDB();

    $query = "SELECT * FROM posts ORDER BY `index` DESC";
    $result = mysql_query($query,$link);
    if( !$result ) {
        http_response_code( 403 );
        echo '{ "login": "false" }';
        return;
    }

    $first = true;
    $results = "{ \"token\" : \"" . $auth[ 'token' ] . "\", ";
    $results .= "  \"posts\" : { ";
    while( $entry = mysql_fetch_array($result) ) {
        if( !$first ) {
            $results .= ",";
        }
        $results .= "\"" . $entry["id"] . "\" : { ";
        $results .= "\"id\" : \"" . $entry["id"] ."\",";
        $results .= "\"title\" : " . json_encode( $entry["title"] ) .",";
        $results .= "\"body\" : " . json_encode( stripslashes( htmlspecialchars_decode( $entry["body"] ) ) ) .",";
        $results .= "\"date\" : \"" . $entry["date"] ."\",";
        $results .= "\"index\" : \"" . $entry["index"] ."\",";
        $results .= "\"status\" : \"" . $entry["status"] ."\",";
        $results .= "\"type\" : \"" . $entry["type"] ."\",";
        $results .= "\"category\" : \"" . $entry["category"] ."\"";
        $results .= "}";
        $first = false;
    }
    $results .= "}";

    /*
    $results .= ",";

    $query = "SELECT * FROM headlines";
    $result = mysql_query($query,$link);
    if( !$result ) {
        http_response_code( 403 );
        echo '{ "login": "false" }';
        return;
    }

    $first = true;
    $results .= "  \"headlines\" : { ";
    $entry = mysql_fetch_array($result);
    while( $entry != null ) {
        $results .= "\"" . $entry["id"] . "\" : { ";
        $results .= "\"model\" : " . json_encode( $entry["model"] ) .",";
        $results .= "\"name\" : " . json_encode( $entry["name"] ) .",";
        $results .= "\"headline\" : " . json_encode( $entry["headline"] ) ."";
        $results .= "}";
        $first = false;

        $entry = mysql_fetch_array($result);
        if( $entry != null ) {
            $results .= ",";
        }
    }
    $results .= "},";
    */

    $results .= "}";

    // Close Database
    mysql_close($link);

    // Return Token or Failure
    http_response_code( 200 );
    echo $results;
}

getList();
?>
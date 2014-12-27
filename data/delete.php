<?
include_once("auth/util.php");


function delete()
{
    $auth = checkAuth( true );

    if( !$auth || !$auth[ 'login' ]  ) {
        http_response_code( 403 );
        echo '{ "login":"false", "error":"login" }';
        return;
    }

    // Check for ID parameter
    if( !isset( $_POST[ 'id' ] ) || empty( $_POST[ 'id' ] ) ) {
        http_response_code( 403 );
        echo '{ "login": "false", "error":"idmissing" }';
        return;
    }

    // Make sure it's numeric
    $id = preg_replace( "/[^0-9]/", "", $_POST[ 'id' ] );
    if( strlen( $id ) <= 0 ) {
        http_response_code( 403 );
        echo '{ "login": "false", "error":"idnumeric" }';
        return;
    }

    $link = connectToDB();

    $query = "DELETE FROM promos WHERE id = '$id'";
    $result = mysql_query($query,$link);
    if( !$result ) {
        http_response_code( 403 );
        echo '{ "login": "false", "error":"fail" }';
        return;
    }

    // Close Database
    mysql_close($link);

    // Return Token or Failure
    http_response_code( 200 );
    echo "{ \"token\" : \"" . $auth[ 'token' ] . "\" }";
}

delete();
?>
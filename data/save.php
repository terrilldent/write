<?
include_once("auth/util.php");

function copyRow()
{
    $auth = checkAuth( true );


    if( !$auth || !$auth[ 'login' ]  ) {
        http_response_code( 403 );
        echo '{ "login":"false", "error":"login2" }';
        return;
    }

    // Check for ID parameter
    if( !isset( $_POST[ 'id' ] )    || empty( $_POST[ 'id' ] ) ||
        !isset( $_POST[ 'field' ] ) || empty( $_POST[ 'field' ] ) ||
        !isset( $_POST[ 'table' ] ) || empty( $_POST[ 'table' ] ) ||
        !isset( $_POST[ 'value' ] ) ) {
        http_response_code( 403 );
        echo '{ "login":"false", "error":"idmissing" }';
        return;
    }


    $link = connectToDB();

    // Make sure it's numeric
    $id =    mysql_real_escape_string( preg_replace( "/[^0-9a-z_]/", "", $_POST[ 'id' ] ) );
    $table = mysql_real_escape_string( preg_replace( "/[^a-z]/", "", $_POST[ 'table' ] ) );
    $field = mysql_real_escape_string( preg_replace( "/[^0-9a-z_]/", "", $_POST[ 'field' ] ) );
    $value = mysql_real_escape_string( $_POST[ 'value' ], $link );
    if( strlen( $id ) <= 0 || strlen( $field ) <= 0 || strlen( $table ) <= 0) {
        http_response_code( 403 );
        echo '{ "login": "false", "error":"inputinvalid" }';
        return;
    }


    $query = "UPDATE $table SET $field = '$value' WHERE id = '$id';";

    $result = mysql_query($query,$link);
    if( !$result ) {
        http_response_code( 403 );
        echo '{ "login": "false", "error":"fail" }';
        return;
    }

    $newid = mysql_insert_id();

    // Close Database
    mysql_close($link);

    // Return Token or Failure
    http_response_code( 200 );
    echo "{ \"token\" : \"" . $auth[ 'token' ] . "\" }";
}

copyRow();
?>
<?
include_once("auth/util.php");
include_once("util.php");

function publish()
{
    $auth = checkAuth( true );

    if( !$auth || !$auth[ 'login' ]  ) {
        http_response_code( 403 );
        echo '{ "login":"false", "error":"login" }';
        return;
    }

    // Start output buffering
    ob_start();

    outputFullHTML();

    // Store buffer in variable
    $results = ob_get_contents();

    ob_end_clean(); // End buffering and clean up

    file_put_contents( "../../updates/data.html", $results );

    // Return Token or Failure
    http_response_code( 200 );
    echo "{ \"token\" : \"" . $auth[ 'token' ] . "\" }";
}

publish();
?>
<?php

$POSTS_ON_PAGE = 15;

function outputPosts( $start, $number )
{
    $link = connectToDB();

    $start = mysql_real_escape_string( preg_replace( "/[^0-9]/", "", $start ) );
    $number = mysql_real_escape_string( preg_replace( "/[^0-9]/", "", $number ) );
    $query = "SELECT * FROM posts WHERE status = 'live' ORDER BY date DESC LIMIT $start, $number;";
    $result = mysql_query($query,$link);
    if( !$result ) { return; }

    while( $entry = mysql_fetch_array($result) ) {
        outputPostHTML( $entry, true );
    }
    mysql_close($link);
}

function outputPost( $postID )
{
    $link = connectToDB();

    $num = $end - $start;
    $postID = mysql_real_escape_string( $postID, $link );
    $query = "SELECT * FROM posts WHERE id = '$postID';";
    $result = mysql_query($query,$link);
    if( !$result ) { return; }

	$entry = mysql_fetch_array($result);
	if( !$entry ) {
		echo "<p>Not found</p>";
	} else {
	    outputPostHTML( $entry, false );
	}

    mysql_close($link);
}

function outputPostHTML( $assocResult, $includeLink )
{
    echo( "<div class='post'>" );
    echo( "<h2>" );
    if( $includeLink ) {
    	echo( "<a href='/blog/" . $assocResult["id"] . "'>" );
    }
    echo( $assocResult["title"] );
    if( $includeLink ) {
    	echo( "</a>" );
    }
    echo( "</h2>" );
    echo( "<p class='date'>" . date("F jS, Y", strtotime( $assocResult["date"] ) ) . "</p>" );

    echo( $assocResult["body"] );
    echo( "</div>" );
}
?>
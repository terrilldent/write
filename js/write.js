/*jslint browser: true, devel: false, plusplus: true, white: true */
/*global alert, document, prompt, spark, window */

var write = (function() {
    "use strict";

    var DATA,
        selectedPost,

        // FUNCTIONS
        getListRow,
        getListRowID,
        refreshList,
        selectedRow,
        selectPost,
        showPost,
        newPost,
        populatePosts,
        createKeyListener,
        showPreview,

        // HTML
        postList = spark.$('list'),

        STATUS_MAP = {
            "draft" : "Draft",
            "live" : "Live",
            "short" : "Short",
            "removed" : "Removed"
        };


    getListRow = function( target )
    {
        var tagName;
        while( target !== null ) {
            tagName = target.tagName.toLowerCase();
            if( tagName === 'li' ) {
                return target;
            }
            target = target.parentNode; 
        }
        return null;
    };

    getListRowID = function( target )
    {
        var tagName;
        while( target !== null ) {
            tagName = target.tagName.toLowerCase();
            if( tagName === 'body' ) {
                return null;
            }
            if( tagName === 'li' ) {
                return target.getAttribute( 'data-id' );
            }
            target = target.parentNode; 
        }
        return null;
    };

    createKeyListener = function( table )
    {
        return function( e ) 
        {
            var target = e.target;
            if( target.tagName.toLowerCase() !== 'input' && 
                target.tagName.toLowerCase() !== 'select'  && 
                target.tagName.toLowerCase() !== 'textarea' ) {
                return;
            }

            if( target.saveTimeout ) {
                clearTimeout( target.saveTimeout );
            }

            target.saveTimeout = setTimeout( function() {
                var value = target.value,
                    field = target.getAttribute( 'data-field' ),
                    rowID = getListRowID( target );

                if( selectedPost && field && rowID && 
                    ( field === 'title' || 
                      field === 'date' || 
                      field === 'body' ) ) {
                    selectedPost[ field ] = target.value;
                }

                if( field === 'title' ) {
                    selectedRow.innerHTML = target.value;
                }

                showPreview();

                spark.ajax.post( 'data/save.php', 
                    [ [ 'table', table ],
                      [ 'id', rowID ],
                      [ 'field', field ],
                      [ 'value', value ] ],
                    function( status, data ) {
                    },
                    function( status, data ) {
                    }); 

            }, 250 );
        };
    };

    selectPost = function( e ) 
    {
        var id = getListRowID( e.target );
        if( id && DATA.posts[ id ] ) {
            selectedRow = getListRow( e.target );
            selectedPost = DATA.posts[ id ];
            showPost();
            showPreview();
        }
    };

    populatePosts = function( posts )
    {   
        var fragment = document.createDocumentFragment(),
            post,
            row,
            id;

        for( id in posts ) {
            if( posts.hasOwnProperty( id ) ) {

                post = posts[ id ];
                row = spark.create( 'li', { className : 'post-row',
                                            innerHTML : post.title }  );
                row.setAttribute( 'data-id', id );
                
                fragment.appendChild( row );
            }
        }

        postList.innerHTML = '';
        postList.appendChild( fragment );
    };

    showPost = function()
    {
        var fragment = document.createDocumentFragment(),
            row,
            statusType,
            input;

        row = spark.create( 'li' );
        row.setAttribute( 'data-id', selectedPost.id );

        input = spark.create( 'input', { 'value' : selectedPost.title || '',
                                        className : 'title-input' } );
        input.setAttribute( 'data-field', 'title' );
        row.appendChild( input );

        input = spark.create( 'input', { 'value' : selectedPost.date || '',
                                        className : 'date-input' } );
        input.setAttribute( 'data-field', 'date' );
        row.appendChild( input );

        input = spark.create( 'select', { className : 'status-input'  } );
        for( statusType in STATUS_MAP ) {
            if( STATUS_MAP.hasOwnProperty( statusType ) ) {
                input.appendChild( spark.create( 'option', 
                        { value : statusType,
                          textContent : STATUS_MAP[ statusType ],
                          selected : ( selectedPost.status === statusType ) } ) );  
            } 
        }
        input.addEventListener( 'change', createKeyListener( 'posts' ), false );
        input.setAttribute( 'data-field', 'status' );
        row.appendChild( input );

        input = spark.create( 'textarea', { 'value' : selectedPost.body || '',
                                        className : 'body-input' } );
        input.setAttribute( 'data-field', 'body' );
        row.appendChild( input );

        fragment.appendChild( row );
        
        spark.$('editor').innerHTML = '';
        spark.$('editor').appendChild( fragment );
    };

    showPreview = function()
    {
        if( !selectedPost ) {
            return;
        }

        spark.$('preview').innerHTML = '<h2>' + selectedPost.title + '</h2>' + 
                                 '<p class="data">' + selectedPost.date + '</p>' + 
                                 selectedPost.body;
    };

    refreshList = function()
    {
        spark.ajax.post( 'data/get.php', [],
            function( status, data ) {

                try {
                    DATA = JSON.parse( data );
                } catch( e ) {
                    alert( 'problem loading data' );
                    return;
                }

                populatePosts( DATA.posts );
            }); 
    };

    newPost = function()
    {
        var urlID = prompt("URL ID");
        if( urlID ) {
            spark.ajax.post( 'data/new.php', 
                [ [ 'id', urlID ] ],
                function( status, data ) {
                    refreshList();
                },
                function( status, data ) {
                }); 
        }
    };

    return {
        init : function() {
            refreshList();
            postList.addEventListener( 'click', selectPost, false );
            spark.$('editor').addEventListener( 'keydown', createKeyListener( 'posts' ), false );
            spark.$('refresh-button').addEventListener( 'keydown', refreshList, false );
            spark.$('posts-button').addEventListener( 'click', function() { 
                    spark.removeClass( document.body, 'show-files' ); 
                    spark.removeClass( document.body, 'show-three' ); 
                }, false );
            spark.$('files-button').addEventListener( 'click', function() { 
                    spark.removeClass( document.body, 'show-three' ); 
                    spark.addClass( document.body, 'show-files' ); 
                }, false );
            spark.$('new-button').addEventListener( 'click', newPost, false );
        }
    };
}());


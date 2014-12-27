/*jslint browser: true, devel: false, plusplus: true, white: true */
/*global document, window */

/* Helper Function Library */

var spark = (function(){
    "use strict";
        
    if( !Object.create ){
        Object.create = function( prototype ) {
            var Obj = function(){return undefined;};
            Obj.prototype = prototype;
            return new Obj();
        };
    }

    return {
        $: function( id ) {
            return document.getElementById( id );
        },
        create: function( tagName, attributes )
        {
            var element = document.createElement( tagName ),
                key;

            for( key in attributes ) {
                if( attributes.hasOwnProperty( key ) ) {
                    element[ key ] = attributes[ key ];
                }
            }
            
            return element;
        },

        getPagePosition: function( e )
        {
            e = e.touches ? e.touches[0] : e;
            return { x : e.pageX , y : e.pageY };
        },  

        remove: function( element )
        {
            if( element.parentNode ) {
                element.parentNode.removeChild( element );
            }
        },

        forEach: function( arrayCollection, callback )
        {
            var num = arrayCollection.length,
                i;

            for( i = 0; i < num; i++ ) {
                callback( arrayCollection[ i ] );
            }
        },

        hasClass: function( element, className )
        {
            if( !element ){ return; }
            return element.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));                    
        },

        addClass: function( element, className )
        {
            if( !element ){ return; }
            if( !spark.hasClass( element, className ) ){
                 element.className += " " + className;
            }
        },

        removeClass: function( element, className )
        {
            if( !element ){ return; }
            if( spark.hasClass( element, className ) ){
                var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
                element.className = element.className.replace( reg, ' ' );
            }
        }
    };
}());

spark.ajax = (function() {
    "use strict";
        
    var boundary = ( Math.random() * 1000000000000 ).toString( 16 ),
        postHeaders = [ [ 'Content-Type', 'multipart/form-data; boundary=' + boundary ] ],
        user,
        token,
        authController,
        addRequest,
        checkQueue,
        requestOutstanding,
        queue = [];

    addRequest = function( request )
    {
        queue.push( request );
        checkQueue();
    };

    checkQueue = function()
    {
        if( requestOutstanding ) {
            return;
        }
        requestOutstanding = queue.shift();
        if( requestOutstanding ) {
            requestOutstanding.send( requestOutstanding.contentToSend );
        }
    };

    return {

        setAuthController : function( controller ) 
        {
            authController = controller;
        },

        post : function( url, parameters, callbackOnSuccess, callbackOnFailure ) 
        {
            var parametersText,
                parametersLength, 
                parameter,
                parameterName,
                parameterValue,
                i;
            
            parameters = parameters || [];

            if( user && token ) {
                parameters.push( [ "user", user ] );
                parameters.push( [ "token", token ] );
            }
            
            parametersText = '';
            parametersLength = parameters.length;


            for( i = 0; i < parametersLength; i++ ) {
                parameter = parameters[ i ];

                parameterName = parameter[ 0 ];    
                parameterValue = parameter[ 1 ] || '';

                parametersText += '--' + boundary + '\r\n';
                parametersText += 'Content-Disposition: form-data; name="' + parameterName + '"\r\n\r\n';
                parametersText += parameterValue + '\r\n';
            }
            parametersText += '--' + boundary + '--\r\n';
            
            spark.ajax.request( 'POST', url, callbackOnSuccess, callbackOnFailure, parametersText );
        },

        request : function( method, url, successCallback, failureCallback, content ) 
        {
            var request = new XMLHttpRequest();
            
            request.open( method, url, true );
            request.contentToSend = content;

            spark.forEach( postHeaders, function( header ){
                request.setRequestHeader( header[ 0 ], header[ 1 ] );
            } );

            request.onreadystatechange = function() 
            {
                var responseObject;
                if( this.readyState === 4 ) {  
                    if( this.status === 200 && successCallback ) {
                        // Try to update Token
                        try {
                            responseObject = JSON.parse( request.responseText );
                            if( responseObject.token ) {
                                token = responseObject.token;
                                localStorage.token = token;
                            }
                            if( responseObject.user ) {
                                user = responseObject.user;
                                localStorage.user = user;
                            }
                        } catch( ignore ) {
                        }

                        successCallback( request.status, request.responseText, request.getResponseHeader('Content-Type') );
                        successCallback = null; // prevent double calling for local ajax calls                            

                    } else if( this.status === 403 ) {
                        // Authentication failed 
                        if( authController && authController.failure ) {
                            authController.failure();
                        }
                    } else if( failureCallback ) {
                        failureCallback( request.status, request.responseText, request.getResponseHeader('Content-Type') );                
                    }
                    requestOutstanding = null;
                    checkQueue();
                }
            };
            
            addRequest( request );
        }
    };
}());


spark.button = (function() {
    "use strict";

    var buttonPrototype,
        MOVEMENT_ALLOWED = 15,
        SIMULATE_TOUCH = !window.hasOwnProperty( 'ontouchstart' );

    buttonPrototype = {
        
        handleEvent : function( e )
        {
            switch( e.type ) {
                case 'touchstart':
                    this.handleTouchStart( e );
                    break;
                case 'touchmove':
                    this.handleTouchMove( e );
                    break;
                case 'touchend':
                    this.handleTouchEnd( e );
                    break;
            }
        },
        
        reset : function()
        {
            var that = this;
            document.body.removeEventListener( 'touchmove', that, false );
            document.body.removeEventListener( 'touchend',  that, false );
        },
            
        handleTouchStart : function( e )
        {
            var that = this;

            that.previousEvent = e;       
            that.touchStartXY = spark.getPagePosition( e );

            document.body.addEventListener( 'touchmove', that, false );
            document.body.addEventListener( 'touchend', that, false );

            return false;
        },
        
        handleTouchMove : function( e )
        {
            var that = this;
            
            that.previousEvent = e;

            if( that.outOfBounds( that.touchStartXY, spark.getPagePosition( e ) ) ) {
                that.reset();
            }

            return false;
        },
          
        handleTouchEnd : function( e )
        {
            var that = this;
            
            e.stopPropagation();
            e.preventDefault();
            
            that.reset();
            
            if( that.invokeCallback ) {
                that.invokeCallback( that.target, e );
            }

            return false;
        },

        outOfBounds : function( point1, point2 )
        {
            if( Math.abs( point2.x - point1.x ) > MOVEMENT_ALLOWED ||
                Math.abs( point2.y - point1.y ) > MOVEMENT_ALLOWED ) {  
                return true;
            }
            return false;
        }
    };

    return {
        clear : function( target ) {
            target.ontouchstart = function() {
                return undefined;
            };

            if( SIMULATE_TOUCH ) {
                target.onclick = function() {
                    return undefined;
                };
            }
        },
        create : function( target, invokeCallback ) {

            var button = Object.create( buttonPrototype );
            
            if( !target ) {
                return;
            }
            
            button.target = target;
            button.invokeCallback = invokeCallback;
            target.ontouchstart = function( e ) {
                button.handleEvent( e ); 
            };

            if( SIMULATE_TOUCH ) {
                target.onclick = function( e ) {
                    invokeCallback( target, e );
                    e.preventDefault();
                    return false;
                };
            }
        }
    };
}());



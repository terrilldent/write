/*jslint browser: true, devel: false, plusplus: true, white: true */
/*global alert, document, prompt, spark, window */


var main = (function() 
{
    "use strict";

    var startup,
    	loginForm = spark.$('login');

    startup = function( username, token )
    {
        spark.addClass( loginForm, 'success' );

        // Remove Login
        setTimeout( function() { spark.remove( loginForm ); }, 400 );
        
        // Show UI
        setTimeout( function() { spark.addClass( document.body, 'loggedin' ); }, 200 );
    
        write.init();
    };

    return {
        init : function() {
            var loginControl = login.create( spark.$('user'), 
                                             spark.$('pass'), 
                                             spark.$('login-submit'), 
                                             spark.$('wait-time'), 
                                             startup );

            loginControl.init();
            spark.ajax.setAuthController( loginControl );
        }
    };
}());

window.addEventListener( 'DOMContentLoaded', main.init, true );
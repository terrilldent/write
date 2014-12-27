/*jslint browser: true, devel: false, plusplus: true, white: true */
/*global alert, document, prompt, spark, window, CryptoJS */

var login = (function()
{
    "use strict";

    var lg = {};

    lg.create = function( userInput, passInput, submitButton, waitIndicator, onSuccess ) 
    {
        var encodePassword,
            testLogin,
            testToken,
            initButton,
            success,
            failure,
            init;

        encodePassword = function( password ) {
            // Hex of GMT Minutes
            var timeHash = CryptoJS.SHA256( Math.round( Date.now() / 1000 / 100 ).toString( 16 ) ),
                passHash = CryptoJS.SHA256( password + '1803b29ffb.9bfa' );

            return CryptoJS.SHA256( timeHash + passHash );
        };

        testLogin = function() {
            var parameters = [];

            // Disable Button
            submitButton.textContent = 'Checking...';
            spark.button.clear( submitButton );

            // Collect Data
            parameters.push( [ "user", userInput.value ] );
            parameters.push( [ "pass", encodePassword( passInput.value ) ] );

            spark.ajax.post( 'data/auth/login.php', parameters, success, failure );
        };

        testToken = function() {
        	var cachedToken = localStorage.token,
        		cachedUser  = localStorage.user,
                parameters = [];

        	if( cachedToken && cachedUser ) {

	            // Disable Button
	            submitButton.textContent = 'Checking...';

	            // Collect Data
	            parameters.push( [ "user", cachedUser ] );
	            parameters.push( [ "token", cachedToken ] );

	            spark.ajax.post( 'data/auth/login.php', parameters, success, failure );
            }
        };

        success = function( status, response, contentType ) {
            var obj = JSON.parse( response );
            submitButton.textContent = "Welcome";
            if( onSuccess ) {
                onSuccess( obj );
            }
        };

        initButton = function() {
            spark.button.create( submitButton, testLogin );
        };

        return {
            failure: function() {
                submitButton.textContent = 'Try again';
                initButton();

                localStorage.removeItem( 'token' );
                localStorage.removeItem( 'user' );

                spark.addClass( waitIndicator, 'show' );
                setTimeout( function() {
                    spark.removeClass( waitIndicator, 'show' );
                }, 15000);
            },

            init: function() {
            	initButton();
                passInput.addEventListener( 'keydown', 
                    function(e) {
                        if( e.keyCode === 13 ) {
                            testLogin();
                        }
                    }, false );

            	//testToken();
            }
        };
    };

    return lg;
}());

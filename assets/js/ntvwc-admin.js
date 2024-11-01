/**
 * Set Default
**/
window.ntvwc = window.ntvwc || {};
window.ntvwc.admin = window.ntvwc.admin || {};

/**
 * Define ntvwc
 *
 * @requires Underscore
 * 
**/
( function ( root, factory ) {

	"use strict";

	window.ntvwc.admin = window.ntvwc.admin || {};
	root.ntvwc.admin = new factory();

} ( this, function() {

	"use strict";

	function methods() {

		/**
		 * Tools
		**/

			/**
			 * Get the template of underscore by ID 
			 * @param  string   noticeClass
			 * @return template 
			**/
			function addNotice( noticeClass )
			{

				var noticeClass = noticeClass || '';

				var template = _.template( jQuery( '#' + templateID ).html() );

				return template;

			}

		return {
			"addNotice": addNotice,
		};

	}

	return methods();

}));
<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( 'ntvwc_doing_it_wrong' ) ) {
	/**
	 * Wrapper for _doing_it_wrong.
	 *
	 * @since 3.0.0
	 * @param string $function
	 * @param string $version
	 * @param string $replacement
	 */
	function ntvwc_doing_it_wrong( $function, $message, $version )
	{

		// Get Backtrace
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

		// AJAX
		if ( defined( 'NTVWC_IS_WP_AJAX' ) && NTVWC_IS_WP_AJAX ) {

			// Log
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );

		}
		// Regular called
		else {

			// Trigger error
			_doing_it_wrong( $function, $message, $version );

		}

	}
}

if ( ! function_exists( 'ntvwc_test_var_dump' ) ) {
	/**
	 * Test Var Dump
	 * 
	 * @param mixed $var
	 * @param bool  $echo : Default true
	 * 
	 * @see ntvwc_test_var_dump( $var )
	**/
	function ntvwc_test_var_dump( $var, $echo = true )
	{

		$count = 0;
		$message = '';
		$debug_backtraces = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
		foreach( $debug_backtraces as $debug_backtrace ) {

			$count++;

			$function = $debug_backtrace['function'];
			$line     = $debug_backtrace['line'];
			$file     = $debug_backtrace['file'];

			if ( isset( $debug_backtrace['class'] ) ) {
				$function = $debug_backtrace['class'] . '::' . $function;
			}

			$format = '%1$d : "<strong>%2$s line %3$d</strong>" <strong>%4$s</strong><br>';
			$current_message = sprintf(
				$format,
				$count,
				$file,
				$line,
				$function
			);

			$message .= $current_message;

		}

		// Case : Enabled debug mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// Get Output
			ob_start();
				var_dump( $var );
				echo $message;
			$var_dump_str = ob_get_clean();

			// Case : Echo true
			if ( boolval( $echo ) ) {
				echo '<pre>';
				echo( $var_dump_str );
				echo '</pre>';
			}

			// End
			return $var_dump_str;

		}

	}
}

if ( ! function_exists( 'ntvwc_notice_message' ) ) {
	/**
	 * Add action admin notice 
	 * 
	 * @param string $notice_message : Message to be wrapped
	 * @param string $type           : 'notice', 'warning', 'updated'
	 * 
	 * @see ntvwc_is_string_and_not_empty( $string )
	 * 
	 * @return string
	**/
	function ntvwc_notice_message( $notice_message = '', $type = 'notice' )
	{

		// Check the param
		if ( ! is_string( $notice_message ) ) {
			ob_start();
			ntvwc_test_var_dump( $notice_message );
			$notice_message = ob_get_clean();
			ob_start();
			echo '<pre>';
			echo $notice_message;
			echo '</pre>';
			$notice_message = ob_get_clean();
		}

		if ( ! did_action( 'all_admin_notices' ) ) {
			add_action( 'all_admin_notices', function() use ( $notice_message, $type ) {
				echo ntvwc_wrap_as_notices( $notice_message, $type );
			} );
		}

	}
}

if ( ! function_exists( 'ntvwc_wrap_as_notices' ) ) {
	/**
	 * Test Var Dump
	 * 
	 * @param string $notice_message : Message to be wrapped
	 * @param string $type           : 'notice', 'warning', 'updated'
	 * 
	 * @see ntvwc_is_string_and_not_empty( $string )
	 * 
	 * @return string
	**/
	function ntvwc_wrap_as_notices( $notice_message = '', $type = 'notice' )
	{

		// Check the param
		if ( ! is_string( $notice_message ) ) {
			ob_start();
			var_dump( $notice_message );
			$notice_message = ob_get_clean();
			ob_start();
			echo '<pre>';
			echo $notice_message;
			echo '</pre>';
			$notice_message = ob_get_clean();
		}

		// Init Message
		$format = '<div class="notice %s wc-stripe-apple-pay-notice is-dismissible"><p>%s</p></div>' . PHP_EOL;
		$notice_type = ( in_array( $type, array( 'warning' ) )
			? 'notice-' . $type
			: $type
		);
		$notice = sprintf( $format, $notice_type, $notice_message );

		// End
		return $notice;

	}
}

if ( ! function_exists( 'ntvwc_current_file_and_line' ) ) {
	/**
	 * Test Var Dump
	 * 
	 * @param int  $add_line
	 * @param bool $return
	 * @param bool $add_eol
	 * 
	 * @return string
	**/
	function ntvwc_current_file_and_line( $add_line = 0, $echo = true, $add_eol = true )
	{

		// Get Backtraces
		$debugtraces = debug_backtrace();
		$prev_backtrace = $debugtraces[0];

		// Vars
		$file = $prev_backtrace['file'];
		$line = intval( $prev_backtrace['line'] ) + intval( $add_line );

		// Vars
		$format = esc_html__( 'Now Here is line %1$d in file "%2$s"', 'wcyss' );
		$message = sprintf(
			$format,
			$line,
			$file
		);

		// Add End of Line
		if ( $add_eol ) {
			$message .= PHP_EOL;
		}

		// Echo if you want
		if ( $echo ) {
			echo $message;
		}

		// End
		return $message;

	}
}

if ( ! function_exists( 'ntvwc_error_log' ) ) {
	/**
	 * Error log
	**/
	function ntvwc_error_log( $message )
	{

		$result = error_log(
			$message,
			3,
			NTVWC_Exception::ERROR_LOG_FILE
		);

	}
}

if ( ! function_exists( 'ntvwc_debug_backtrace' ) ) {
	/**
	 * Error log
	**/
	function ntvwc_debug_backtrace( $limit )
	{

		$debug_backtraces = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $limit );
		if ( ! is_array( $debug_backtraces ) 
			|| 0 >= count( $debug_backtraces )
		) {
			return false;
		}
		array_shift( $debug_backtraces );
		array_shift( $debug_backtraces );
		//$debug_backtraces = array_reverse( $debug_backtraces );
		return $debug_backtraces;

	}
}

if ( ! function_exists( 'ntvwc_debug_print_backtrace' ) ) {
	/**
	 * Error log
	**/
	function ntvwc_debug_print_backtrace( $limit )
	{

		ob_start();
		debug_print_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $limit );
		$debug_backtraces = ob_get_clean();
		$debug_backtraces = str_replace( ABSPATH, '', $debug_backtraces );

		echo $debug_backtraces;

	}
}

if ( ! function_exists( 'ntvwc_is_called_by' ) ) {
	/**
	 * Check if the function or method is called by the
	 * @param callable $callable
	 * @return bool
	**/
	function ntvwc_is_called_by( callable $callable )
	{

		$debug_backtraces = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 3 );
		array_shift( $debug_backtraces );
		array_shift( $debug_backtraces );

		if ( ! is_array( $debug_backtraces ) 
			|| 0 >= count( $debug_backtraces )
		) {
			return false;
		}

		$called_by = $debug_backtraces[0];

		$function_called_by = $called_by['function'];
		if ( isset( $called_by['class'] ) ) {
			$class = $called_by['class'];
			$function_called_by = array( $class, $function_called_by );
		}

		if ( $callable === $function_called_by ) {
			return true;
		}

		return false;

	}
}

if ( ! function_exists( 'ntvwc_is_called_by_context' ) ) {
	/**
	 * Check if the function or method is called by the
	 * @param string|array $context : Like Class Name
	 * @return bool
	**/
	function ntvwc_is_called_by_context( $context )
	{

		$debug_backtraces = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 3 );
		array_shift( $debug_backtraces );
		array_shift( $debug_backtraces );

		if ( ! is_array( $debug_backtraces ) 
			|| 0 >= count( $debug_backtraces )
		) {
			return false;
		}

		$called_by = $debug_backtraces[0];

		$context_called_by = '';
		if ( isset( $called_by['class'] ) ) {
			$context_called_by = $called_by['class'];
		}

		if ( is_string( $context ) ) {
			if ( $context === $context_called_by ) {
				return true;
			}
		} else {
			if ( in_array( $context_called_by, $context ) ) {
				return true;
			}

		}
		return false;

	}
}










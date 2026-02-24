<?php declare(strict_types=1);

namespace UCSC\Blocks\API;

/**
 * Course Schedule API
 *
 * Provides WordPress REST API endpoints for PeopleSoft course schedule data.
 *
 * @package ucsc-gutenberg-blocks
 */
class Course_Schedule_API {

	/**
	 * PeopleSoft REST API base URL
	 */
	private const PS_BASE_URL = 'https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD';

	/**
	 * WordPress REST API namespace
	 */
	private const WP_NAMESPACE = 'ucsc/v1';

	/**
	 * Cache duration in seconds (15 minutes)
	 */
	private const CACHE_DURATION = 900;

	/**
	 * Initialize the API endpoints
	 */
	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes(): void {
		// Terms endpoint
		register_rest_route(
			self::WP_NAMESPACE,
			'/terms',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_terms' ],
				'permission_callback' => '__return_true', // Public endpoint
			]
		);

		// Courses list endpoint
		register_rest_route(
			self::WP_NAMESPACE,
			'/courses/(?P<term>[0-9]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_courses' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'term'    => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					],
					'subject' => [
						'required' => false,
						'type'     => 'string',
					],
					'dept'    => [
						'required' => false,
						'type'     => 'string',
					],
				],
			]
		);

		// Course details endpoint
		register_rest_route(
			self::WP_NAMESPACE,
			'/course/(?P<term>[0-9]+)/(?P<course>[0-9]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_course_details' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'term'   => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					],
					'course' => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					],
				],
			]
		);
	}

	/**
	 * Get available terms
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_terms( \WP_REST_Request $request ) {
		$cache_key = 'ucsc_ps_terms';

		// Try to get from cache
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		// Fetch from PeopleSoft API
		$url      = self::PS_BASE_URL . '/SCX_CLASS_TERMS.v1';
		$response = wp_remote_get( $url, [
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'api_error',
				'Failed to fetch terms from PeopleSoft API',
				[ 'status' => 500 ]
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error(
				'json_error',
				'Invalid JSON response from PeopleSoft API',
				[ 'status' => 500 ]
			);
		}

		// Cache the result
		set_transient( $cache_key, $data, self::CACHE_DURATION );

		return rest_ensure_response( $data );
	}

	/**
	 * Get courses for a term
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_courses( \WP_REST_Request $request ) {
		$term    = $request->get_param( 'term' );
		$subject = $request->get_param( 'subject' );
		$dept    = $request->get_param( 'dept' );

		// Build query string
		$query_params = [];
		if ( $subject ) {
			$query_params['subject'] = strtoupper( $subject );
		}
		if ( $dept ) {
			$query_params['dept'] = strtoupper( $dept );
		}
		$query_string = http_build_query( $query_params );

		// Build cache key
		$cache_key = 'ucsc_ps_courses_' . md5( $term . $query_string );

		// Try to get from cache
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		// Fetch from PeopleSoft API
		$url      = self::PS_BASE_URL . '/SCX_CLASS_LIST.v1/' . $term;
		if ( $query_string ) {
			$url .= '?' . $query_string;
		}

		$response = wp_remote_get( $url, [
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'api_error',
				'Failed to fetch courses from PeopleSoft API',
				[ 'status' => 500 ]
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error(
				'json_error',
				'Invalid JSON response from PeopleSoft API',
				[ 'status' => 500 ]
			);
		}

		// Cache the result
		set_transient( $cache_key, $data, self::CACHE_DURATION );

		return rest_ensure_response( $data );
	}

	/**
	 * Get course details
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_course_details( \WP_REST_Request $request ) {
		$term   = $request->get_param( 'term' );
		$course = $request->get_param( 'course' );

		$cache_key = 'ucsc_ps_course_' . $term . '_' . $course;

		// Try to get from cache
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		// Fetch from PeopleSoft API
		$url      = self::PS_BASE_URL . '/SCX_CLASS_DETAIL.v1/' . $term . '/' . $course;
		$response = wp_remote_get( $url, [
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'api_error',
				'Failed to fetch course details from PeopleSoft API',
				[ 'status' => 500 ]
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error(
				'json_error',
				'Invalid JSON response from PeopleSoft API',
				[ 'status' => 500 ]
			);
		}

		// Cache the result
		set_transient( $cache_key, $data, self::CACHE_DURATION );

		return rest_ensure_response( $data );
	}
}

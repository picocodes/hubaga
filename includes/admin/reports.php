<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'H_Report' ) ) :

/**
 * Reports class
 * @version     1.0.0
 */
class H_Report {

	/**
	 * @var string Path to the Hubaga admin directory
	 */
	public $admin_dir = '';

	/**
	 * @var string Platforms
	 */
	public $platforms = null;

	/**
	 * @var string Browsers
	 */
	public $browsers = null;

	/**
	 * The chart interval.
	 *
	 * @var array
	 */
	public $intervals;

	/**
	 * Group by SQL query.
	 *
	 * @var string
	 */
	public $group_by;

	/**
	 * Query restrictions
	 *
	 * @var string
	 */
	public $restrictions;

	/**
	 * The date field.
	 *
	 * @var string
	 */
	public $date_query;

	/**
	 * The current date range filled with zeros.
	 *
	 * @var string
	 */
	public $zeros;

	/**
	 * The main constructor
	 *
	 * @since Hubaga 1.0.0
	 * @access public
	 */
	public function __construct() {

		$this->admin_dir = hubaga_get_includes_path( 'admin' ); // Admin path

		//Filters based on platforms
		if( hubaga_is_array_key_valid( $_REQUEST, 'browserFilter', 'is_string' ) && 'all' != $_REQUEST[ 'browserFilter' ] ) {
			$this->browsers = hubaga_clean( $_REQUEST[ 'browserFilter' ] );
		}

		if( hubaga_is_array_key_valid( $_REQUEST, 'platformFilter', 'is_string' ) && 'all' != $_REQUEST[ 'platformFilter' ] ) {
			$this->platforms = hubaga_clean( $_REQUEST[ 'platformFilter' ] );
		}

		//Lets calculate this right now to avoid recalculating it several times
		$this->restrictions = $this->restrictions();
		$this->date_query	= $this->date_query();
		$this->group_by		= $this->group_by();
		$this->zeros		= $this->get_zero_filled_dataset();
	}

	/**
	 * Excecutes a sql aggregate query
	 */
	public function get_aggregate( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'function'   => 'COUNT',
			'field'		 => 'DISTINCT(ID)',
			'meta_key'	 => null,
			'post_status'=> hubaga_get_completed_order_status(),
		);
		extract( wp_parse_args( $args, $defaults ));
		$post_status || $post_status = hubaga_get_completed_order_status();

		$sql 		 = "SELECT $function($field) FROM $wpdb->posts ";
		$post_status = $wpdb->prepare( 'AND post_status =  %s', $post_status );

		if( !$meta_key ){
			return $wpdb->get_var( "$sql $this->restrictions $post_status" );
		}

		$meta_key = $wpdb->prepare( 'AND meta_key = %s', $meta_key );
		return (float) $wpdb->get_var( "$sql INNER JOIN $wpdb->postmeta ON ID = post_id $this->restrictions $meta_key $post_status" );
	}

	/**
	 * Fetches a given dataset
	 * Dataset is simply an array of [x,y] values that represents points to chart
	 */
	public function get_dataset( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'function'   => 'SUM',
			'field'		 => 'meta_value',
			'meta_key'	 => null,
			'post_status'=> hubaga_get_completed_order_status(),
		);
		extract( wp_parse_args( $args, $defaults ));
		$post_status || $post_status = hubaga_get_completed_order_status();

		$sql 		 = "SELECT $this->date_query as date, $function($field) as aggregate FROM $wpdb->posts";
		$post_status = $wpdb->prepare( 'AND post_status = %s', $post_status );

		if(! $meta_key ){
			$sql .= " $this->restrictions $post_status $this->group_by";
			return $this->normalize_dataset($sql);
		}

		$meta_key = $wpdb->prepare( '%s', $meta_key );
		$sql 	 .= "
			INNER JOIN $wpdb->postmeta ON ID = post_id
			$this->restrictions AND meta_key = $meta_key $post_status
			$this->group_by
			ORDER BY post_date ASC
		";

		return  $this->normalize_dataset($sql);

	}

	/**
	 * Normalize the provided dataset sql
	 */
	public function normalize_dataset( $sql ) {
		global $wpdb;
		$initial = $this->zeros;
		$dataset = $wpdb->get_results( $sql, ARRAY_N );

		foreach ( $dataset as $key => $val ) {
			$initial[$val[0]] = $val[1];
		}

		$modified = array();
		foreach ($initial as $x => $y) {
			$modified[] = array( $x, $y );
		}

		return $modified;
	}

	/**
	 * Fetches all browsers
	 */
	public function get_browsers() {
		global $wpdb;

		$post_status = $wpdb->prepare( '%s', hubaga_get_completed_order_status() );
		$post_type   = $wpdb->prepare( '%s', hubaga_get_order_post_type() );
		$sql = "SELECT
					meta_value
				FROM $wpdb->posts
				INNER JOIN
					$wpdb->postmeta ON ID = post_id
				WHERE
					post_status = $post_status AND meta_key = '_order_browser' AND post_type = $post_type
				GROUP BY
					meta_value
				ORDER BY
					meta_value ASC";

		return  $wpdb->get_col( $sql );

	}

	/**
	 * Fetches all Platforms
	 */
	public function get_platforms() {
		global $wpdb;

		$post_status = $wpdb->prepare( '%s', hubaga_get_completed_order_status() );
		$post_type   = $wpdb->prepare( '%s', hubaga_get_order_post_type() );
		$sql = "SELECT
					meta_value
				FROM $wpdb->posts
				INNER JOIN
					$wpdb->postmeta ON ID = post_id
				WHERE
					post_status = $post_status AND meta_key = '_order_platform' AND post_type = $post_type
				GROUP BY
					meta_value
				ORDER BY
					meta_value ASC";

		return  $wpdb->get_col( $sql );

	}

	/**
	 * Helper function to get total order revenue
	 */
	public function get_revenue(  $post_status = null ) {

		$args = array(
			'function'   => 'SUM',
			'field'		 => 'meta_value',
			'meta_key'	 => '_order_total',
			'post_status'=> $post_status,
		);
		return hubaga_price( $this->get_aggregate( $args ) );

	}

	/**
	 * Renders the report page
	 */
	public function output() {

		//Set the vue data to the global object so that our template can access it
		hubaga()->report_data	= $this->vue_data();

		$elements = apply_filters( 'hubaga_report_elements', array(

			'reports_header' => array(
				'type' 		 => 'reports_header',
			),
			'reports_cards'  => array(
				'type' 		 => 'reports_cards',
			),
			'reports_footer' => array(
				'type' 		 => 'reports_footer',
			),

		) );

		//Reference to Elementa
		foreach( $elements as $id => $args ) {

			$args['id'] 		= $id;
			do_action( "hubaga_report_elements_before_add_{$id}" );
			hubaga_add_option( $args, 'hubaga_reports' );

		}
		hubaga_elementa('hubaga_reports')->set_template( $this->admin_dir . 'views/reports-template.php' );
		hubaga_elementa('hubaga_reports')->render();
	}

	/**
	 * Returns an array of data that is passed onto our vue app
	 */
	public function vue_data() {
		return apply_filters( 'hubaga_report_vue_data', array(
			'filters' 			=> $this->filters(),
			'cards'  			=> $this->cards(),
			'revenueStreams'  	=> $this->revenue_streams(),
			'currentStream'  	=> false,
			'ajaxUrl'  			=> hubaga()->ajax_url,
			'loading'  			=> false,
			'filtersShowing'	=> false,
			'currentCard'		=> 'Revenue',
			'msg'				=> 'Revenue',
		));
	}

	/**
	 * Returns a list of all available report cards
	 */
	public function cards() {

		$total_discounts = $this->get_aggregate( array(
			'function'   => 'SUM',
			'field'		 => 'meta_value',
			'meta_key'	 => '_order_discount_total',
		) );

		$order_avg = $this->get_aggregate( array(
			'function'   => 'AVG',
			'field'		 => 'meta_value',
			'meta_key'	 => '_order_total',
		) );

		//This is basically an array of tab sections
		return apply_filters( 'hubaga_report_cards', array(

			//Revenue Tab
			array(
				'title' 		=> __( 'Revenue', 'hubaga' ),

				//These will be displayed as cards at the top of the tab
				'aggregates'    => array(
					__( 'Net Revenue', 'hubaga' ) 	  => $this->get_revenue(),
					__( 'Refunded', 'hubaga' )		  => $this->get_revenue( hubaga_get_refunded_order_status() ),
					__( 'Pending Payment', 'hubaga' ) => $this->get_revenue( hubaga_get_pending_order_status() ),
					__( 'Failed', 'hubaga' ) 		  => $this->get_revenue( hubaga_get_failed_order_status() ),
					__( 'Cancelled', 'hubaga' ) 	  => $this->get_revenue( hubaga_get_cancelled_order_status() ),
					__( 'Total Discounts', 'hubaga' ) => hubaga_price( $total_discounts ),
				),

				//These will be displayed on a single flot chart
				'datasets'    	=> array(
					array(
						'label' => __( 'Net Revenue', 'hubaga' ),
						'data'  => $this->get_dataset( array(
							'meta_key' => '_order_total'
						)),
					),
					array(
						'label' => __( 'Total Discounts', 'hubaga' ),
						'data'  => $this->get_dataset( array(
							'meta_key' => '_order_discount_total'
						)),
					)
				),

				//Passed on to flot
				'options'			=> array(
					'xaxis'			=> array(
						'mode'		=> 'categories',
					),
				),
			),

			//Orders tab
			array(
				'title' 		=> __( 'Orders', 'hubaga' ),
				'aggregates'    => array(
					__( 'Completed Orders', 'hubaga' )  => $this->get_aggregate(),
					__( 'Revenue Per Order', 'hubaga' ) => hubaga_price( $order_avg ),
				),

				'datasets'    	=> array(
					array(
						'label' => esc_html__( 'All Orders', 'hubaga' ),
						'data'  => $this->get_dataset( array(
							'function'   => 'COUNT',
							'field'		 => 'DISTINCT(ID)',
						) ),
					)
				),

				'options'			=> array(
					'xaxis'			=> array(
						'mode'		=> 'categories',
					),
				),
			),
		));

	/////###### DEFAULT FLOT OPTIONS FOR REFERENCE #####/////
	/* START

	 {
                // the color theme used for graphs
                colors: ["#edc240", "#afd8f8", "#cb4b4b", "#4da74d", "#9440ed"],
                legend: {
                    show: true,
                    noColumns: 1, // number of colums in legend table
                    labelFormatter: null, // fn: string -> string
                    labelBoxBorderColor: "#ccc", // border color for the little label boxes
                    container: null, // container (as jQuery object) to put legend in, null means default on top of graph
                    position: "ne", // position of default legend container within plot
                    margin: 5, // distance from grid edge to default legend container within plot
                    backgroundColor: null, // null means auto-detect
                    backgroundOpacity: 0.85, // set to 0 to avoid background
                    sorted: null    // default to no legend sorting
                },
                xaxis: {
                    show: null, // null = auto-detect, true = always, false = never
                    position: "bottom", // or "top"
                    mode: null, // null or "time"
                    font: null, // null (derived from CSS in placeholder) or object like { size: 11, lineHeight: 13, style: "italic", weight: "bold", family: "sans-serif", variant: "small-caps" }
                    color: null, // base color, labels, ticks
                    tickColor: null, // possibly different color of ticks, e.g. "rgba(0,0,0,0.15)"
                    transform: null, // null or f: number -> number to transform axis
                    inverseTransform: null, // if transform is set, this should be the inverse function
                    min: null, // min. value to show, null means set automatically
                    max: null, // max. value to show, null means set automatically
                    autoscaleMargin: null, // margin in % to add if auto-setting min/max
                    ticks: null, // either [1, 3] or [[1, "a"], 3] or (fn: axis info -> ticks) or app. number of ticks for auto-ticks
                    tickFormatter: null, // fn: number -> string
                    labelWidth: null, // size of tick labels in pixels
                    labelHeight: null,
                    reserveSpace: null, // whether to reserve space even if axis isn't shown
                    tickLength: null, // size in pixels of ticks, or "full" for whole line
                    alignTicksWithAxis: null, // axis number or null for no sync
                    tickDecimals: null, // no. of decimals, null means auto
                    tickSize: null, // number or [number, "unit"]
                    minTickSize: null // number or [number, "unit"]
                },
                yaxis: {
                    autoscaleMargin: 0.02,
                    position: "left" // or "right"
                },
                xaxes: [],
                yaxes: [],
                series: {
                    points: {
                        show: false,
                        radius: 3,
                        lineWidth: 2, // in pixels
                        fill: true,
                        fillColor: "#ffffff",
                        symbol: "circle" // or callback
                    },
                    lines: {
                        // we don't put in show: false so we can see
                        // whether lines were actively disabled
                        lineWidth: 2, // in pixels
                        fill: false,
                        fillColor: null,
                        steps: false
                        // Omit 'zero', so we can later default its value to
                        // match that of the 'fill' option.
                    },
                    bars: {
                        show: false,
                        lineWidth: 2, // in pixels
                        barWidth: 1, // in units of the x axis
                        fill: true,
                        fillColor: null,
                        align: "left", // "left", "right", or "center"
                        horizontal: false,
                        zero: true
                    },
                    shadowSize: 3,
                    highlightColor: null
                },
                grid: {
                    show: true,
                    aboveData: false,
                    color: "#545454", // primary color used for outline and labels
                    backgroundColor: null, // null for transparent, else color
                    borderColor: null, // set if different from the grid color
                    tickColor: null, // color for the ticks, e.g. "rgba(0,0,0,0.15)"
                    margin: 0, // distance from the canvas edge to the grid
                    labelMargin: 5, // in pixels
                    axisMargin: 8, // in pixels
                    borderWidth: 2, // in pixels
                    minBorderMargin: null, // in pixels, null means taken from points radius
                    markings: null, // array of ranges or fn: axes -> array of ranges
                    markingsColor: "#f4f4f4",
                    markingsLineWidth: 2,
                    // interactive stuff
                    clickable: false,
                    hoverable: false,
                    autoHighlight: true, // highlight in case mouse is near
                    mouseActiveRadius: 10 // how far the mouse can be away to activate an item
                },
                interaction: {
                    redrawOverlayInterval: 1000/60 // time between updates, -1 means in same flow
                },
                hooks: {}
            }

		STOP*/
	}

	/**
	 * Returns an array of revenue streams
	 */
	public function revenue_streams() {

		//Get the last 20 orders
		global $wpdb;
		$post_type 	   = $wpdb->prepare( '%s' ,hubaga_get_order_post_type() );
		$_post_statuses= hubaga_get_order_statuses();
		$post_statuses = implode( ',', hubaga_wpdb_clean( array_keys( $_post_statuses ) ) );
		$sql		   = "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_type = $post_type AND post_status IN( $post_statuses )
			ORDER BY post_date DESC
			LIMIT 20
		";
		$orders 	   = $wpdb->get_col(  $sql );

		$streams = array();
		foreach( $orders as $id ){

			$order 		= hubaga_get_order( $id );
			$status		= $_post_statuses[$order->post_status]['label'];
			$product	= esc_html( hubaga_get_product_title( $order->product ) );
			$customer	= esc_html( hubaga_get_order_customer_email( $order ) );
			$currency	= $order->currency;
			$date		= strtotime( $order->post_date );
			$date 		= human_time_diff( $date, current_time('timestamp'));

			$streams[] 			= array(
				'id'			=>	(int) $id,
				'price'			=>	hubaga_price( $order->total, $currency ),
				'priceClass'	=>	esc_attr( $this->stream_price_class( $order->post_status ) ),
				'status'		=>	esc_html( $status ),
				'readMore'		=>	esc_html__( 'View More', 'hubaga' ),
				'date'			=>	sprintf( esc_html__('%s ago ', 'hubaga' ), $date ),
				'moreData'		=>	$this->stream_order_details( $order ),
			);

		}

		return $streams;
	}

	/**
	 * Returns a css class for a given revenue price
	 */
	public function stream_price_class( $status ) {

		switch ( $status ) {
			case hubaga_get_completed_order_status():
				return 'green';
				break;

			case hubaga_get_pending_order_status():
				return 'orange';
				break;

			case hubaga_get_cancelled_order_status():
				return 'black';
				break;

			case hubaga_get_failed_order_status():
				return 'red';
				break;

			case hubaga_get_refunded_order_status():
				return 'teal';
				break;

			default:
				return 'light-blue';
		}
	}

	/**
	 * Returns a html class of order details
	 */
	public function stream_order_details( $order ) {

		$currency 	= $order->currency;
		$id 		= '#' . $order->ID;
		$product 	= hubaga_get_product_title( $order->product );
		$gateway 	= hubaga_get_gateway_title( $order->payment_method );
		$subtotal 	= hubaga_get_order_pre_discount_total( $order );
		$discount 	= $order->discount_total;
		$total 		= $order->total;

		$details	= array(
			__( 'Order', 'hubaga' ) 			=> $id,
			__( 'Product', 'hubaga' ) 			=> $product ? $product : '__',
			__( 'Payment Method', 'hubaga' ) 	=> $gateway ? $gateway : '__',
			__( 'Subtotal', 'hubaga' ) 			=> hubaga_price( $subtotal, $currency ),
			__( 'Discount', 'hubaga' ) 			=> hubaga_price( $discount, $currency ),
			__( 'Order Total', 'hubaga' ) 		=> hubaga_price( $total, $currency ),
		);

		$html = '';

		foreach( $details as $left => $right ){
			$html .= "<div class='col s10 m5'>$left</div> <div class='col s10 m5'><strong>$right</strong></div>";
		}

		return "$html" ;
	}

	/**
	 * Returns a list of all available filters
	 */
	public function filters() {

		$dates  = array();
		foreach( $this->date_ranges() as $id => $data ){
			$dates[] = array( 'value' => $id, 'text' => $data['label'] );
		}

		$browsers = array(
			array( 'value' => 'all', 'text' => __( 'All Browsers', 'hubaga' ) )
		);

		foreach( $this->get_browsers() as $browser ) {
			$browsers[] = array( 'value' => $browser, 'text' => $browser );
		}

		$platforms = array(
			array( 'value' => 'all', 'text' => __( 'All Platforms', 'hubaga' ) )
		);
		foreach( $this->get_platforms() as $platform ) {
			$platforms[] = array( 'value' => $platform, 'text' => $platform );
		}

		return apply_filters( 'hubaga_report_filters', array(
			'browserFilter' 	=> 'all',
			'platformFilter' 	=> 'all',
			'dateFilter' 		=> 'last_7_days',
			'platforms'			=> $platforms,
			'browsers'			=> $browsers,
			'dates'				=> $dates,
			'action' 			=> 'hubaga_handle_report_data',
			'nonce' 			=> wp_create_nonce( 'hubaga_reports_nonce' ),

		));
	}

	/**
	 * Returns the date ranges
	 */
	public function date_ranges() {

		/*
		 * PHP 5.2 treats -1 week as -7 days etc
		 */
		$now 	  = current_time( 'timestamp' );
		$midnight = strtotime( 'Today midnight', $now );

		return apply_filters( 'hubaga_report_date_ranges', array(
			'today' 		=> array(
				'id'		=> 'today',
				'label'		=> __( 'Today', 'hubaga' ),
				'start'		=> $midnight,
				'end'		=> $now,
				'group_by'	=> 'DAY(post_date), HOUR(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%I %p')",
				'category'	=> "Day",
			),

			'yesterday' 	=> array(
				'id'		=> 'yesterday',
				'label'		=> __( 'Yesterday', 'hubaga' ),
				'start'		=> strtotime( 'yesterday midnight', $now ),
				'end'		=> $midnight - 1,
				'group_by'	=> 'DAY(post_date), HOUR(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%I %p')",
				'category'	=> "Day",
			),

			'last_7_days' 	=> array(
				'id'		=> 'last_7_days',
				'label'		=> __( 'Last 7 Days', 'hubaga' ),
				'start'		=> strtotime( '-6 days midnight', $now ),
				'end'		=> $now,
				'group_by'	=> 'MONTH(post_date), DAY(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%a')",
				'category'	=> "Week",
			),

			'this_week' 	=> array(
				'id'		=> 'this_week',
				'label'		=> __( 'This Week', 'hubaga' ),
				'start'		=> strtotime( 'This week midnight', $now ),
				'end'		=> $now,
				'group_by'	=> 'MONTH(post_date), DAY(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%a')",
				'category'	=> "Week",
			),

			'last_week' 	=> array(
				'id'		=> 'last_week',
				'label'		=> __( 'Last Week', 'hubaga' ),
				'start'		=> strtotime( 'Last week midnight', $now ),
				'end'		=> strtotime( 'This week midnight', $now ) - 1,
				'group_by'	=> 'MONTH(post_date), DAY(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%a')",
				'category'	=> "Week",
			),

			'last_30_days' 	=> array(
				'id'		=> 'last_30_days',
				'label'		=> __( 'Last 30 Days', 'hubaga' ),
				'start'		=> strtotime( '-30 days midnight',  $now ),
				'end'		=> $now,
				'group_by'	=> 'MONTH(post_date), DAY(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%D %b')",
				'category'	=> "Month",
			),

			'this_month' 	=> array(
				'id'		=> 'this_month',
				'label'		=> __( 'This Month', 'hubaga' ),
				'start'		=> strtotime( 'first day of this month', $now ),
				'end'		=> $now,
				'group_by'	=> 'MONTH(post_date), DAY(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%D %b')",
				'category'	=> "Month",
			),

			'last_month' 	=> array(
				'id'		=> 'last_month',
				'label'		=> __( 'Last Month', 'hubaga' ),
				'start'		=> strtotime( 'first day of last month midnight', $now ),
				'end'		=> strtotime( 'first day of this month', $now ) - 1,
				'group_by'	=> 'MONTH(post_date), DAY(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%D %b')",
				'category'	=> "Month",
			),

			'this_year' 	=> array(
				'id'		=> 'this_year',
				'label'		=> __( 'This Year', 'hubaga' ),
				'start'		=> strtotime( date( 'Y-01-01', $now ) ),
				'end'		=> $now,
				'group_by'	=> 'YEAR(post_date), MONTH(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%b')",
				'category'	=> "Year",
			),

			'last_year' 	=> array(
				'id'		=> 'last_year',
				'label'		=> __( 'Last Year', 'hubaga' ),
				'start'		=> strtotime( '-1 year midnight', strtotime( date( 'Y-01-01', $now ) ) ),
				'end'		=> strtotime( date( 'Y-01-01', $now ) ) - 1,
				'group_by'	=> 'YEAR(post_date), MONTH(post_date)',
				'select'	=> "DATE_FORMAT(post_date, '%b')",
				'category'	=> "Year",
			),
		));
	}

	/**
	 * Returns the current date range
	 */
	public function current_date_range() {
		$ranges = $this->date_ranges();
		$current_range = $ranges['last_7_days'];

		if(! empty( $_REQUEST['dateFilter'] )){
			if( $ranges[$_REQUEST['dateFilter']]){
				$current_range = $ranges[$_REQUEST['dateFilter']];
			}
		}

		return $current_range;
	}

	/*
	 * Zero fills a day dataset
	 */
	public function zero_fill_day( $start, $end ){
		$dataset = array();
		while ( $end > $start ) {
			$time = date( "h A", $start);
			$dataset[$time] = 0;
			$start += HOUR_IN_SECONDS;
		}
		return $dataset;
	}

	/*
	 * Zero fills a week dataset
	 */
	public function zero_fill_week( $start, $end ){
		$dataset = array();
		while ( $end > $start ) {
			$time = date( "D", $start);
			$dataset[$time] = 0;
			$start += DAY_IN_SECONDS;
		}
		return $dataset;
	}

	/*
	 * Zero fills a month dataset
	 */
	public function zero_fill_month( $start, $end ){
		$dataset = array();
		while ( $end > $start ) {
			$time = date( "jS M", $start);
			$dataset[$time] = 0;
			$start += DAY_IN_SECONDS;
		}
		return $dataset;
	}

	/*
	 * Zero fills a year dataset
	 */
	public function zero_fill_year( $start, $end ){
		$dataset = array();
		while ( $end > $start ) {
			$time = date( "M", $start);
			$dataset[$time] = 0;
			$start = strtotime( '+1 month', $start );
		}
		return $dataset;
	}

	/*
	 * Returns a zero filled dataset
	 */
	public function get_zero_filled_dataset(){
		$date_range   	= $this->current_date_range();
		if( !$date_range ){
			return array();
		}

		$start = $date_range['start'];
		$end   = $date_range['end'];
		$cat   = 'zero_fill_' . strtolower($date_range['category']);

		if( isset( $date_range['zeros_cb'] ) ) {
			return call_user_func( $date_range['zeros_cb'], $start, $end );
		}

		if( method_exists( $this, $cat ) ){
			return call_user_func( array( $this, $cat ), $start, $end );
		}

		return array();
	}

	/**
	 * Returns the mysql restrictions
	 */
	public function restrictions() {
		global $wpdb;

		$post_type	  = hubaga_get_order_post_type();
		$restrictions = $wpdb->prepare( "WHERE post_type = %s ", $post_type );

		//The date restriction
		$date_range   	= $this->current_date_range();
		$start_date   	= date( 'Y-m-d H:i:s', $date_range['start'] );
		$end_date   	= date( 'Y-m-d H:i:s', $date_range['end'] );
		$restrictions 	.= $wpdb->prepare( "AND post_date >= '%s' AND post_date <= '%s' ", $start_date, $end_date );

		//Browser restriction
		if( $this->browsers ) {
			$restrictions 	.= $wpdb->prepare( " AND ID in( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s ) ", '_order_browser', $this->browsers );
		}

		//Platform restriction
		if( $this->platforms ) {
			$restrictions 	.= $wpdb->prepare( " AND ID in( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s ) ", '_order_platform', $this->platforms );
		}

		return $restrictions;
	}

	/**
	 * Returns the mysql date_query
	 */
	public function date_query() {
		$date_range   	= $this->current_date_range();
		return $date_range['select'];
	}

	/**
	 * Returns the mysql group_by query
	 */
	public function group_by() {
		$date_range   	= $this->current_date_range();
		return " GROUP BY {$date_range['group_by']} ";
	}

}
endif; // class_exists check

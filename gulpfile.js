// Load Gulp...of course
var gulp         = require( 'gulp' );

// CSS related plugins
var sass         = require( 'gulp-sass' );
var autoprefixer = require( 'gulp-autoprefixer' );

// JS related plugins
var concat       = require( 'gulp-concat' );
var uglify       = require( 'gulp-uglify' );
var babelify     = require( 'babelify' );
var browserify   = require( 'browserify' );
var source       = require( 'vinyl-source-stream' );
var buffer       = require( 'vinyl-buffer' );

// Utility plugins
var rename       = require( 'gulp-rename' );
var sourcemaps   = require( 'gulp-sourcemaps' );

// Browers related plugins
var browserSync  = require( 'browser-sync' ).create();

// Project related variables 
var projectURL   = 'https://pubapp-light.local';

// Styles SRC
var adminStyleSRC     = './src/scss/rpd-admin.scss';
var bookingsStyleSRC     = './src/scss/rpd-bookings.scss';
var forgotPasswordModalStyleSRC = './src/scss/modal.scss';
var themesMapStyleSRC = './src/scss/rpd-themes-map.scss';
var checkoutLightStyleSRC = './src/scss/rpd-checkout-light.scss';
var checkoutStyleSRC = './src/scss/rpd-checkout.scss';
var datetimePickerStyleSRC = './src/scss/datetimepicker.scss';
var restaurantSolution = './src/scss/rpd-restaurant-solution.scss';
var checkoutV2StyleSRC = './src/scss/rpd-checkout-v2.scss';
var checkoutV2StyleLiteSRC = './src/scss/rpd-checkout-v2-lite.scss';
var checkoutColorThemesStyleLiteSRC = './src/scss/rpd-checkout-color-themes-lite.scss';
var rpdProductActionsSRC = './src/scss/rpd-product-actions.scss';
var rpdCustomPickupPointSRC = './src/scss/rpd-custom-pickup-point.scss';

// Styles DEST
var rootStyleDEST = './assets/css/';
var apiStyleDEST = './assets/css/api/';
var woocommerceStyleDEST = './assets/css/woocommerce/';
var mapURL       = './';

// Scripts SRC
var adminScriptSRC = 'rpd-admin.js';
var dataserviceScriptSRC = 'rpd-dataservice.js';
var headerScriptSRC = 'rpd-header-scripts.js';
var helperScriptSRC = 'rpd-helper.js';
var modalScriptSRC = 'modal.js';
var settingsScriptSRC = 'rpd-settings.js';
var checkoutScriptSRC = 'rpd-checkout.js';
var clientInformationScriptSRC = 'rpd-client-information.js';
var customCodeScriptSRC = 'rpd-custom-code.js';
var onlyWebScriptSRC = 'rpd-custom-light-only-web.js';
var customLightScriptSRC = 'rpd-custom-light.js';
var datetimePickerScriptSRC = 'datetimepicker.js';
var hideCityScriptSRC = 'rpd-hide-city.js';
var keepUserInformation = 'rpd-keep-user-information.js';
var checkoutV2 = 'rpd-checkout-v2.js';
var timepickerAdminCode = 'timepicker-admin-code.js';
var rpdProductActions = 'rpd-product-actions.js';
var rpdCustomPickupPoint = 'rpd-custom-pickup-point.js';
var jsFolder = 'src/js/';

var jsRootFiles = [adminScriptSRC];
var jsApiFiles = [dataserviceScriptSRC, headerScriptSRC, helperScriptSRC, modalScriptSRC, settingsScriptSRC];
var jsWoocommerceFiles = [clientInformationScriptSRC, customCodeScriptSRC, onlyWebScriptSRC, customLightScriptSRC, datetimePickerScriptSRC, hideCityScriptSRC, keepUserInformation, checkoutV2, timepickerAdminCode, rpdProductActions, rpdCustomPickupPoint];

// Scripts DEST
var rootScriptsDEST = './assets/js/';
var apiScriptsDEST = './assets/js/api/';
var woocommerceScriptsDEST = './assets/js/woocommerce/';

// CSS Functions
function rpd_admin_css( done ) {
	gulp.src( adminStyleSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			outputStyle: 'compressed'
		}) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( rootStyleDEST ) );
	
	done();
};

function rpd_bookings_css( done ) {
	gulp.src( bookingsStyleSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			outputStyle: 'compressed'
		}) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( rootStyleDEST ) );

	done();
};


function rpd_modal_css( done ) {
	gulp.src( forgotPasswordModalStyleSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( apiStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};

function rpd_themes_map_css( done ) {
	gulp.src( themesMapStyleSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( apiStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};

function rpd_checkout_light_css( done ) {
	gulp.src( checkoutLightStyleSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( woocommerceStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};

function rpd_checkout_css( done ) {
	gulp.src( checkoutStyleSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( woocommerceStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};

function rpd_checkout_v2_css( done ) {
	gulp.src( checkoutV2StyleSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( woocommerceStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};
 
function rpd_checkout_v2_lite_css( done ) {
	gulp.src( checkoutV2StyleLiteSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( woocommerceStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
}; 

function rpd_checkout_color_theme_lite_css( done ) {
	gulp.src( checkoutColorThemesStyleLiteSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( woocommerceStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
}; 

function rpd_datetimepicker_css( done ) {
	gulp.src( datetimePickerStyleSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( woocommerceStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};

function rpd_restaurant_solution_css( done ) {
	gulp.src( restaurantSolution )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( rootStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};

function rpd_product_actions( done ) {
	gulp.src( rpdProductActionsSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( woocommerceStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};

function rpd_custom_pickup_point( done ) {
	gulp.src( rpdCustomPickupPointSRC )
		.pipe( sourcemaps.init() )
		.pipe( sass({
			errLogToConsole: true,
			outputStyle: 'compressed'
		}) )
		.on( 'error', console.error.bind( console ) )
		.pipe( autoprefixer() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( sourcemaps.write( mapURL ) )
		.pipe( gulp.dest( woocommerceStyleDEST ) )
		.pipe( browserSync.stream() );

	done();
};

// JS Functions
function rpd_admin_js( done ) {
	jsRootFiles.map( function( entry ){
		return browserify({
			entries: [jsFolder + entry]
		})
		.transform( babelify, { presets: [ '@babel/preset-env' ] } )
		.bundle()
		.pipe( source( entry ) )
		.pipe( rename({ extname: '.min.js' }) )
		.pipe( buffer() )
		.pipe( sourcemaps.init({ loadMaps: true }) )
		.pipe( uglify() )
		.pipe( sourcemaps.write( './' ) )
		.pipe( gulp.dest( rootScriptsDEST ) )
	});

	done();
};

function rpd_woocommerce_js( done ) {
	jsWoocommerceFiles.map( function( entry ){
		return browserify({
			entries: [jsFolder + entry]
		})
		.transform( babelify, { presets: [ '@babel/preset-env' ] } )
		.bundle()
		.pipe( source( entry ) )
		.pipe( rename({ extname: '.min.js' }) )
		.pipe( buffer() )
		.pipe( sourcemaps.init({ loadMaps: true }) )
		.pipe( uglify() )
		.pipe( sourcemaps.write( './' ) )
		.pipe( gulp.dest( woocommerceScriptsDEST ) )
	});

	done();
};

// CSS Tasks
gulp.task( 'rpd-admin', rpd_admin_css );
gulp.task( 'rpd-bookings', rpd_bookings_css );
gulp.task( 'rpd-modal', rpd_modal_css );
gulp.task( 'rpd-themes-map', rpd_themes_map_css );
gulp.task( 'rpd-checkout-light', rpd_checkout_light_css );
gulp.task( 'rpd-checkout', rpd_checkout_css );
gulp.task( 'rpd-datetimepicker', rpd_datetimepicker_css );
gulp.task( 'rpd-restaurant-solution', rpd_restaurant_solution_css );
gulp.task( 'rpd-checkout-v2', rpd_checkout_v2_css );
gulp.task( 'rpd-checkout-v2-lite', rpd_checkout_v2_lite_css ); // rpd_checkout_color_theme_lite_css
gulp.task( 'rpd-checkout-color-theme-lite', rpd_checkout_color_theme_lite_css );
gulp.task( 'rpd-product-actions', rpd_product_actions );
gulp.task( 'rpd-custom-pickup-point', rpd_custom_pickup_point );

// JS Tasks
gulp.task( 'rpd-admin-js', rpd_admin_js );
//gulp.task( 'rpd-api-js', rpd_api_js );
gulp.task( 'rpd-woocommerce-js', rpd_woocommerce_js );

// Default Task
gulp.task( 'default', gulp.parallel(rpd_admin_css, rpd_bookings_css, rpd_modal_css, rpd_themes_map_css, rpd_checkout_light_css, rpd_checkout_css, rpd_datetimepicker_css, rpd_restaurant_solution_css, rpd_admin_js, rpd_woocommerce_js, rpd_checkout_v2_css, rpd_checkout_v2_lite_css, rpd_checkout_color_theme_lite_css, rpd_product_actions, rpd_custom_pickup_point) );
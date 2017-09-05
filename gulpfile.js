/**
 *  Gulp File
 *
 *  Used for automating development tasks.
 */

/* Modules (Can be installed with npm install command using package.json)
 ------------------------------------- */
var gulp = require('gulp'),
	sort = require('gulp-sort'),
	wpPot = require('gulp-wp-pot'),
	checktextdomain = require('gulp-checktextdomain'),
	uglify = require('gulp-uglify'),
	pump = require('pump'),
	rename = require('gulp-rename'),
	sourcemaps = require('gulp-sourcemaps');

/* POT file task
 ------------------------------------- */
gulp.task('pot', function () {
	return gulp.src('**/*.php')
		.pipe(sort())
		.pipe(wpPot({
			package: 'CampTix-Razorpay',
			domain: 'campt-indian-payment-gateway', //textdomain
			destFile: 'camptix-indian-payments.pot',
			bugReport: 'https://github.com/wpindiaorg/camptix-indian-payments/issues/new',
			lastTranslator: '',
			team: 'wpindiaorg <wpindiaorg@gmail.com>'
		}))
		.pipe(gulp.dest('languages'));
});

/* Text-domain task
 ------------------------------------- */
gulp.task('textdomain', function () {
	var options = {
		text_domain: 'campt-indian-payment-gateway',
		keywords: [
			'__:1,2d',
			'_e:1,2d',
			'_x:1,2c,3d',
			'esc_html__:1,2d',
			'esc_html_e:1,2d',
			'esc_html_x:1,2c,3d',
			'esc_attr__:1,2d',
			'esc_attr_e:1,2d',
			'esc_attr_x:1,2c,3d',
			'_ex:1,2c,3d',
			'_n:1,2,4d',
			'_nx:1,2,4c,5d',
			'_n_noop:1,2,3d',
			'_nx_noop:1,2,3c,4d'
		],
		correct_domain: true
	};
	return gulp.src('**/*.php')
		.pipe(checktextdomain(options))
});

/* Compress task
 ------------------------------------- */
gulp.task('compress', function (cb) {
  pump([
        gulp.src('assets/js/*.js')
        .pipe( sourcemaps.init() )
        .pipe( sourcemaps.init( { loadMaps: true } ) )
        .pipe( uglify() )
        .pipe( sourcemaps.write ( '/' ) )
        .pipe( rename( { suffix: '.min' } ) ),
        gulp.dest('assets/js/dist')
    ],
    cb
  );
});

/* Default Gulp task
 ------------------------------------- */
gulp.task('default', function () {
	// Run all the tasks!
	gulp.start('textdomain','pot','compress');
});

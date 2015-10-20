var elixir = require('laravel-elixir');


 // |--------------------------------------------------------------------------
 // | Elixir Asset Management
 // |--------------------------------------------------------------------------
 // |
 // | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 // | for your Laravel application. By default, we are compiling the Sass
 // | file for our application, as well as publishing vendor resources.
 // |
 

elixir(function(mix) {
    mix.sass('app.scss');
});





// var gulp = require('gulp');
// var concat = require('gulp-concat');
// var rename = require('gulp-rename');
// var uglify = require('gulp-uglify');
// var sass = require('gulp-sass');
// var autoprefixer = require('gulp-autoprefixer');
// var jshint = require('gulp-jshint');

// gulp.task('sass', function () {
//   return gulp.src('/resources/assets/scss/*.scss')
//       .pipe(sass())
//       .pipe(autoprefixer({
//               browsers: ['last 5 versions'],
//               cascade: false
//           }))
//       .pipe(gulp.dest('/resources/assets/css'));
// });

// gulp.task('lint', function() {
//   return gulp.src('/resources/js/*.js')
//     .pipe(jshint('.jshintrc'))
//     .pipe(jshint.reporter('jshint-stylish'));
// });

// gulp.task('js', function() {
//   return gulp.src([
//       './bower_components/jquery/dist/jquery.js',
//   		'/resources/js/*.js'
//   	])
//     .pipe(concat('build.js'))
//     .pipe(gulp.dest('/resources/js/'))
//     .pipe(uglify())
//     .pipe(rename('build.min.js'))
//     .pipe(gulp.dest('/resources/js/'))
// });

// // Rerun the task when a file changes
// gulp.task('watch', function() {
// 	gulp.watch(['/resources/js/*.js'], ['js']);
// 	gulp.watch(['/resources/assets/scss/*.scss'], ['sass']);
// });

// // The default task (called when you run `gulp` from cli)
// gulp.task('default', ['watch', 'js', 'lint', 'sass']);










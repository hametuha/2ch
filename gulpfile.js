var gulp = require('gulp'),
    $    = require('gulp-load-plugins')(),
    pngquant = require('imagemin-pngquant');

// Sassのタスク
gulp.task('sass', function () {
  return gulp.src(['./assets/scss/**/*.scss'])
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sourcemaps.init())
    .pipe($.sass({
      errLogToConsole : true,
      outputStyle     : 'compressed',
      omitSourceMapUrl: false,
      sourceMap       : true,
      includePaths    : [],
      onSuccess       : function (css) {

      }
    }))
    .pipe($.autoprefixer({
      browsers: ['last 3 versions'],
      cascade : false
    }))
    .pipe($.sourcemaps.write())
    .pipe($.plumber.stop())
    .pipe(gulp.dest('./dist/css'));
});

// JSHint and minify
gulp.task('js', ['jshint', 'uglify']);

// Run JS hint
gulp.task('jshint', function () {
  return gulp.src(['./assets/js/**/*.js'])
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.jshint())
    .pipe($.jshint.reporter('jshint-stylish'));
});

// Uglify JS
gulp.task('uglify', function () {
  return gulp.src(['./assets/js/**/*.js'])
    .pipe($.plumber())
    .pipe($.sourcemaps.init())
    .pipe($.uglify())
    .pipe($.sourcemaps.write())
    .pipe(gulp.dest('./dist/js'));
});

// Image min
gulp.task('imagemin', function () {
  return gulp.src(['./assets/img/**/*'])
    .pipe($.imagemin({
      progressive: true,
      svgoPlugins: [{removeViewBox: false}],
      use: [pngquant()]
    }))
    .pipe(gulp.dest('./dist/img'));
});

// watch
gulp.task('watch', function () {
  gulp.watch('./assets/scss/**/*.scss', ['sass'] );
  gulp.watch('./assets/js/**/*.js', ['js']);
  gulp.watch('./assets/img/**/*', ['imagemin'] );
});

// Copy library
gulp.task( 'lib', function(){
  return gulp.src('./node_modules/js-cookie/src/js.cookie.js')
    .pipe($.plumber())
    .pipe($.uglify())
    .pipe(gulp.dest('./dist/js'));
} );

// Build
gulp.task('build', ['sass', 'uglify', 'imagemin', 'lib']);

// Default Tasks
gulp.task('default', ['build', 'watch']);
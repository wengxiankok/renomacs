// gulpfile.js
const gulp = require('gulp');
const webpack = require('webpack-stream');
const uglify = require('gulp-uglify');
const plumber = require('gulp-plumber');

// Task: Webpack for app.js
function bundleApp() {
  return gulp.src('assets/js/app.js')
    .pipe(plumber())
    .pipe(webpack({
      mode: 'production',
      output: {
        filename: 'app.js',
      },
      module: {
        rules: [
          {
            test: /\.js$/,
            exclude: /node_modules/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env']
              }
            }
          }
        ]
      }
    }))
    .pipe(gulp.dest('assets/js/min'));
}

// Task: Uglify for all other JS (excluding app.js)
function minifyScripts() {
  return gulp.src(['assets/js/*.js', '!assets/js/app.js'])
    .pipe(plumber())
    .pipe(uglify())
    .pipe(gulp.dest('assets/js/min'));
}

// Combined single command
gulp.task('minify-js', gulp.parallel(bundleApp, minifyScripts));

// Watch (optional)
gulp.task('watch', function () {
  gulp.watch('assets/js/app.js', bundleApp);
  gulp.watch(['assets/js/*.js', '!assets/js/app.js'], minifyScripts);
});
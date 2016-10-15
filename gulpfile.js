var gulp = require('gulp'),
  connect = require('gulp-connect-php'),
  browserSync = require('browser-sync');

gulp.task('connect-sync', function() {
  connect.server({}, function() {
    browserSync({
      proxy: 'wp12.dev'
    });
  });

  gulp.watch([
    '**/*.php',
    'css/*.css',
  ]).on('change', function() {
    browserSync.reload();
  });
});

gulp.task('default', ['connect-sync']);
gulp.task('watch', ['connect-sync']);

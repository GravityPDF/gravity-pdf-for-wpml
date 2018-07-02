var gulp = require('gulp'),
  wpPot = require('gulp-wp-pot')

/* Generate the latest language files */
gulp.task('language', function () {
  return gulp.src(['src/**/*.php', '*.php'])
    .pipe(wpPot({
      domain: 'gravity-pdf-for-wpml',
      package: 'Gravity PDF for WPML'
    }))
    .pipe(gulp.dest('languages/gravity-pdf-for-wpml.pot'))
})

gulp.task('default', ['language'])
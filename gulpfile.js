"use strict";

const gulp = require('gulp')
const sass = require('gulp-sass')
const concat = require('gulp-concat')

const options = {
	sass:{
		outputStyle:'compressed'
	}
}

gulp.task('sass', () => {
	return gulp.src([
		'layouts/main.css',
		'{layouts,gtfs,components/*}/*.css',
		'{layouts,gtfs,components/*}/*.scss'
	])
		.pipe(sass(options.sass))
		.pipe(concat('min.css'))
		.pipe(gulp.dest('./dist/'))
})

gulp.task('watch', () => {
	gulp.watch('./**/*.scss', ['sass'])
})

gulp.task('default', () => {
	return gulp.src('./**/*.js')
})

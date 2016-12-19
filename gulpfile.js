'use strict';

const gulp = require('gulp')
const plugins = {
	prefixSass: require('gulp-autoprefixer'),
	rmLines: require('gulp-delete-lines'),
	compileSass: require('gulp-sass'),
	addHeader: require('gulp-header'),
	concat: require('gulp-concat'),
}

const options = {
	compileSass:{
		outputStyle:'compressed'
	},
	prefixSass:{
	},
	addHeader:(()=> {
		return "/* <%= file.basename %> */\n"
	})(),
	rmLines:{'filters':[
		'^\s*$',
	]}
}

gulp.task('sass', () => {
	const tasks = [
		'compileSass',
		'prefixSass',
		'addHeader',
		'rmLines',
	]
	let stream = gulp.src([
		'layouts/main.css',
		'{layouts,gtfs,components/*}/*.css',
		'{layouts,gtfs,components/*}/*.scss'
	])
	for (let i=0, k=tasks.length; i<k; i++) {
		stream = stream.pipe(plugins[tasks[i]](options[tasks[i]]))
	}
	return stream.pipe(plugins.concat('min.css'))
		.pipe(gulp.dest('./dist/'))
})

gulp.task('watch', () => {
	gulp.watch('./**/*.scss', ['sass'])
})

gulp.task('default', () => {
	return gulp.src('./**/*.js')
})

'use strict';

const gulp = require('gulp');
const plugins = require('gulp-load-plugins')({
	rename:{
		'gulp-autoprefixer': 'prefixCSS',
		'gulp-htmlmin': 'compileHTML',
		'gulp-babel': 'compileJS',
		'gulp-sass': 'compileSass',
	},
});

const options = {
	compileSass: {
		outputStyle:'compressed'
	},
	prefixCSS: {
		browsers: [
			'last 2 versions',
			'Safari 10',
			'IE 11',
		],
	},
	rmLines:{'filters':[
		'^\s*$',
	]},
};

gulp.task('sass', () => {
	const tasks = [
		'compileSass',
		'prefixCSS',
		'rmLines',
	];
	let stream = gulp.src([
		'layouts/main.css',
		'{layouts,gtfs,components/*}/*.css',
		'{layouts,gtfs,components/*}/*.scss',
	]);
	for (let i=0, k=tasks.length; i<k; i++) {
		stream = stream.pipe(plugins[tasks[i]](options[tasks[i]]));
	}
	return stream.pipe(plugins.concat('min.css'))
		.pipe(gulp.dest('./dist/'));
});

gulp.task('watch', () => {
	gulp.watch('./**/*.scss', ['sass']);
});

gulp.task('default', gulp.parallel(
	'sass',
	() => {
		return gulp.src('./**/*.js');
	}
));

var gulp = require('gulp');
var rimraf = require('gulp-rimraf');
var minifyCss = require('gulp-minify-css');
var uglify = require('gulp-uglify');
var gulpUtil = require('gulp-util');
var htmlmin = require('gulp-htmlmin');
var imageop = require('gulp-image-optimization');
var rev = require('gulp-rev');
var revCollector = require('gulp-rev-collector');
var replace = require('gulp-replace');

/**
 * PHP模板构建：gulp tpl|tpl-dev
 * 静态页构建：gulp html|html-dev
 */
gulp.task('default', function () {
    console.log('PHP模板构建：gulp tpl|tpl-dev');
    console.log('静态页构建：gulp html|html-dev');
});

gulp.task('clean', function () {
    return gulp.src(['tmp'])
        .pipe(rimraf({force: true}));
});
gulp.task('img', ['clean'], function () {
    return gulp.src('static/img/**/*')
        .pipe(imageop({
            optimizationLevel: 3,
            progressive: true,
            interlaced: true
        }))
        .pipe(rev())
        .pipe(gulp.dest('htdocs/dist/img'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('tmp/img'));
});
gulp.task('style', ['img'], function () {
    return gulp.src(['tmp/img/*.json', 'static/css/**/*.css'])
        .pipe(revCollector({
            replaceReved: true
        }))
        .pipe(minifyCss())
        .pipe(rev())
        .pipe(gulp.dest('htdocs/dist/css'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('tmp/css'));
});
gulp.task('scripts', ['img'], function () {
    return gulp.src(['tmp/img/*.json', 'static/js/**/*.js'])
        .pipe(revCollector({
            replaceReved: true
        }))
        .pipe(uglify({
            mangle: false
        }).on('error', gulpUtil.log))
        .pipe(rev())
        .pipe(gulp.dest('htdocs/dist/js'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('tmp/js'));
});
gulp.task('tpl', ['scripts', 'style'], function () {
    return gulp.src(['tmp/**/*.json', 'static/tpl/**/*.html'])
        .pipe(revCollector({
            replaceReved: true,
            dirReplacements: {
                '../../css': '/dist/css/',
                '../../js': '/dist/js/',
                '../../img': '/dist/img/'
            }
        }))
        .pipe(htmlmin({
           collapseWhitespace: true,
           minifyJS : {
               mangle: false
           }
        }))
        .pipe(gulp.dest('tpl/'));
});
gulp.task('html', ['scripts', 'style'], function () {
    return gulp.src(['tmp/**/*.json', 'static/html/**/*.html'])
        .pipe(revCollector({
            replaceReved: true
        }))
        .pipe(htmlmin({
           collapseWhitespace: true,
           minifyJS : {
               mangle: false
           }
        }))
        .pipe(gulp.dest('htdocs/dist/html/'));
});

// dev 开发专用
gulp.task('img-dev', function () {
    return gulp.src('static/img/**/*')
        .pipe(gulp.dest('htdocs/src/img'));
});
gulp.task('style-dev', ['img-dev'], function () {
    return gulp.src('static/css/**/*.css')
        .pipe(gulp.dest('htdocs/src/css'));
});
gulp.task('scripts-dev', ['img-dev'], function () {
    return gulp.src('static/js/**/*.js')
        .pipe(gulp.dest('htdocs/src/js'));
});
gulp.task('tpl-dev', ['scripts-dev', 'style-dev'], function () {
    return gulp.src('static/tpl/**/*.html')
        .pipe(replace('../../css', '/src/css'))
        .pipe(replace('../../js', '/src/js'))
        .pipe(replace('../../img', '/src/img'))
        .pipe(gulp.dest('tpl_src/'));
});
gulp.task('html-dev', ['scripts-dev', 'style-dev'], function () {
    return gulp.src('static/html/**/*.html')
        .pipe(gulp.dest('htdocs/src/html'));
});
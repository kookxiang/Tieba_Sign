/*jslint node: true*/
"use strict";

var project = require('./package.json');
var gulp = require('gulp');
var watchify = require('watchify');
var browserify = require('browserify');
var notify = require('gulp-notify');
var replace = require('gulp-replace');
var uglify = require('gulp-uglify');
var streamify = require('gulp-streamify');
var source = require('vinyl-source-stream');
var autoprefixer = require('gulp-autoprefixer');
var del = require('del');
var cleancss = require('gulp-cleancss');

var errorHandler = function (err) {
    notify.onError({
        title: "Compile Error",
        message: "<%= error.message %>",
        sound: "Bottle"
    })(err);
    this.emit('end');
};

var TokenList = [],
    whitelist = require('./Resource/Helper/ManglePropWhitelist');
function handleJavaScriptBundle(bundle, filePath) {
    return bundle.on('error', errorHandler)
        .pipe(source(filePath))
        .pipe(replace(/throw \w+code="MODULE_NOT_FOUND",/gi, 'throw '))
        .pipe(replace(/new Error\(.*?\)/gi, 'new Error()'))
        .pipe(replace(/require\('(.+)'\)/g, function (match, path) {
            var index = TokenList.length;
            TokenList.push(path);
            return "require(" + index + ")";
        }))
        .pipe(replace(/"(.+?)":([\d])/g, function (match, path, id) {
            if (TokenList.indexOf(path) < 0) {
                return match;
            }
            return TokenList.indexOf(path) + ": " + id;
        }))
        .pipe(replace(/require/g, '_x'))
        .pipe(replace(/module/g, '_X'))
        .pipe(replace(/exports/g, 'X'))
        .pipe(streamify(uglify({
            mangle: {
                toplevel: true,
                eval: true,
                screw_ie8: true,
                sort: true
            },
            mangleProperties: {
                regex: /^[^A-Z]/,
                reserved: whitelist
            },
            compress: {
                drop_debugger: false,
                drop_console: false
            }
        })))
        .pipe(gulp.dest('./Public/'));
}

gulp.task('browserify', function () {
    project.entries.js.forEach(function (filePath) {
        handleJavaScriptBundle(browserify(filePath).bundle(), filePath);
    });
});

gulp.task('styles', function () {
    return gulp.src(project.entries.css, {base: './'})
        .on('error', errorHandler)
        .pipe(autoprefixer('ie >= 9, > 3%, last 2 version'))
        .pipe(cleancss({
            advanced: true,
            keepBreaks: false,
            processImport: true,
            processImportFrom: ['local'],
            restructuring: true
        }))
        .pipe(gulp.dest('./Public/'));
});

gulp.task('clean', function () {
    return del([
        './Public/**',
        '!./Public',
        '!./Public/**/*.php'
    ]);
});

gulp.task('build', ['clean'], function () {
    gulp.start('styles', 'browserify');
});

gulp.task('watch', ['styles'], function () {
    gulp.watch('./Resource/**/*.css', ['styles']);
    project.entries.js.forEach(function (filePath) {
        var task = watchify(browserify(filePath));
        task.on('update', function () {
            gulp.start('browserify');
        });
        handleJavaScriptBundle(task.bundle(), filePath);
    });
});
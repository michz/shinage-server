var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var less = require('gulp-less');
var clean_css = require('gulp-clean-css');
var environments = require('gulp-environments');
var sourcemaps = require('gulp-sourcemaps');
var eslint = require('gulp-eslint');
var del = require('del');

var development = environments.development;
//var production = environments.production;

var distPath = 'public/assets/';
var elfinderDistPath = 'public/vendor/studio-42/elfinder';
var semanticDistPath = 'public/semantic-ui-css';
var jqueryUiDistPath = 'public/jquery-ui-dist';
var revealDistPath = 'public/reveal.js';
var esLintPaths = [
    'src/Resources/private/js/include/**',
];
var paths = {
    libraryJs: [
        'node_modules/jquery/dist/jquery.js',
        'node_modules/jquery-ui-dist/jquery-ui.js',
        'node_modules/semantic-ui-css/semantic.js',
        'node_modules/moment/min/moment-with-locales.js',
        'node_modules/fullcalendar/dist/fullcalendar.js',
        'node_modules/fullcalendar/dist/locale-all.js',
        'node_modules/codemirror/lib/codemirror.js',
        'node_modules/codemirror/mode/xml/xml.js',
        'node_modules/codemirror/mode/css/css.js',
        'node_modules/codemirror/mode/javascript/javascript.js',
        'node_modules/codemirror/mode/htmlmixed/htmlmixed.js',
        'node_modules/jquery-datetimepicker/build/jquery.datetimepicker.full.js',
        'vendor/studio-42/elfinder/js/elfinder.full.js',
        'src/Resources/private/js/lib/**',
    ],
    js: [
        'src/Resources/private/js/include/**',
    ],
    css: [
        'node_modules/reset-css/reset.css',
        'node_modules/semantic-ui-css/semantic.css',
        'node_modules/jquery-ui-dist/jquery-ui.css',
        'node_modules/jquery-ui-dist/jquery-ui.theme.css',
        'node_modules/fullcalendar/dist/fullcalendar.css',
        'node_modules/codemirror/lib/codemirror.css',
        'node_modules/jquery-datetimepicker/build/jquery.datetimepicker.min.css',
        'vendor/studio-42/elfinder/css/elfinder.full.css',
        'vendor/studio-42/elfinder/css/theme.css',
        'src/Resources/private/css/**',
    ],
    lessMain: [
        'src/Resources/private/less/main.less',
    ],
    lessSrc: [
        'src/Resources/private/less/**',
    ],
};
var playerPaths = {
    js: [
        'node_modules/jquery/dist/jquery.js',
        'node_modules/reveal.js/lib/js/head.min.js',
        'node_modules/reveal.js/js/reveal.js',
        'shinage-client.js',
    ],
    css: [
        'node_modules/reset-css/reset.css',
        'node_modules/reveal.js/css/reveal.css',
        'public/css/reveal_theme_very_black.css',
        'src/Resources/private/player_css/**',
    ],
    less: [
    ],
};

gulp.task('clean', function() {
    return del([
        distPath,
        elfinderDistPath,
        semanticDistPath,
        jqueryUiDistPath,
        revealDistPath,
    ]);
});

gulp.task('copy1', function() {
    return gulp.src('node_modules/semantic-ui-css/themes/**')
        .pipe(gulp.dest(semanticDistPath + '/themes/'));
});
gulp.task('copy2a', function() {
    return gulp.src('vendor/studio-42/elfinder/img/**')
        .pipe(gulp.dest(elfinderDistPath + '/img/'));
});
gulp.task('copy2b', function() {
    return gulp.src('vendor/studio-42/elfinder/js/i18n/**')
        .pipe(gulp.dest(elfinderDistPath + '/js/i18n/'));
});
gulp.task('copy3', function() {
    return gulp.src('node_modules/jquery-ui-dist/images/**')
        .pipe(gulp.dest(jqueryUiDistPath + '/images/'));
});
gulp.task('copy4', function() {
    return gulp.src('node_modules/reveal.js/**')
        .pipe(gulp.dest(revealDistPath + '/'));
});
gulp.task('copy', ['copy1','copy2a','copy2b','copy3','copy4']);

gulp.task('less', ['copy'], function() {
    return gulp.src(paths.lessMain)
        .pipe(development(sourcemaps.init()))
        .pipe(less())
        .pipe(concat('less.css'))
        .pipe(development(sourcemaps.write()))
        .pipe(gulp.dest(distPath));
});

gulp.task('css', ['copy'], function() {
    var finalPaths = paths.css;
    finalPaths.push(distPath + '/less.css');
    return gulp.src(finalPaths)
        .pipe(development(sourcemaps.init()))
        .pipe(clean_css({}))
        .pipe(concat('all.min.css'))
        .pipe(development(sourcemaps.write()))
        .pipe(gulp.dest(distPath));
});

gulp.task('libraryJs', ['copy'], function() {
    return gulp.src(paths.libraryJs)
        .pipe(development(sourcemaps.init()))
        .pipe(uglify())
        .pipe(concat('lib.min.js'))
        .pipe(development(sourcemaps.write()))
        .pipe(gulp.dest(distPath));
});

gulp.task('js', [], function() {
    return gulp.src(paths.js)
        .pipe(development(sourcemaps.init()))
        .pipe(uglify())
        .pipe(concat('app.min.js'))
        .pipe(development(sourcemaps.write()))
        .pipe(gulp.dest(distPath));
});

gulp.task('player-css', ['copy'], function() {
    return gulp.src(playerPaths.css)
        .pipe(development(sourcemaps.init()))
        .pipe(clean_css({rebaseTo: './public/assets'}))
        .pipe(concat('player.min.css'))
        .pipe(development(sourcemaps.write()))
        .pipe(gulp.dest(distPath));
});

gulp.task('player-js', ['copy'], function() {
    return gulp.src(playerPaths.js)
        .pipe(development(sourcemaps.init()))
        .pipe(uglify())
        .pipe(concat('player.min.js'))
        .pipe(development(sourcemaps.write()))
        .pipe(gulp.dest(distPath));
});

gulp.task('default', ['copy', 'less', 'css', 'libraryJs', 'js', 'player-css', 'player-js']);

gulp.task('eslint', () => {
    return gulp.src(esLintPaths)
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError());
});

// Rerun the task when a file changes
gulp.task('watch', function() {
  gulp.watch(paths.js, ['js']);
  gulp.watch(paths.css, ['css']);
  gulp.watch(paths.lessSrc, ['less']);
});


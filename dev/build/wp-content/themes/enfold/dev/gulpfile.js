/**
 * Main js file to minimize theme css and js files
 * see https://github.com/clean-css/clean-css#how-to-use-clean-css-api for IE compatiblity modes
 *
 * @since 5.2
 */
const { foldersCSS, filegroupsCSS, modulesCSS, compatiblityCSS } = require( './gulpfile_data_css.js' );
const { foldersJS, filegroupsJS, modulesJS } = require( './gulpfile_data_js.js' );

const { src, dest } = require('gulp');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
//const minify = require('gulp-minify');		//	replaced by 'gulp-terser' since 5.5 for full ES6+ syntax (e.g. class)
const terser = require('gulp-terser');
const clean = require('gulp-clean');

const module_path = '../config-templatebuilder/avia-shortcodes/';


function minifyCSS()
{
	console.log( 'Start minify CSS' );

	for( let foldergroup in foldersCSS )
	{
		let files = filegroupsCSS[ foldergroup ];

		console.log( '------- Minify CSS in foldergroup ' +  foldergroup );

		files.forEach( file =>
		{
			src( foldersCSS[ foldergroup ] + file + '.css' )
			.pipe( cleanCSS( compatiblityCSS ) )
			.pipe( rename( file + '.min.css' ) )
			.pipe( dest( foldersCSS[ foldergroup ] ) );
		});
	}

	console.log( '------- Minify CSS in ALB shortcode modules' );

	for( let module in modulesCSS )
	{
		let files = modulesCSS[ module ];

		files.forEach( file =>
		{
			src( module_path + module + '/' + file + '.css' )
			.pipe( cleanCSS( compatiblityCSS ) )
			.pipe( rename( file + '.min.css' ) )
			.pipe( dest( module_path + module + '/' ) );
		});
	}

	console.log( 'Finished minify CSS' );

}

function minifyJS()
{
	console.log( 'Start minify JS' );

	for( let foldergroup in foldersJS )
	{
		let files = filegroupsJS[ foldergroup ];

		console.log( '------- Minify JS in foldergroup ' +  foldergroup );

		files.forEach( file =>
		{
//			src( foldersJS[ foldergroup ] + file + '.js' )
//			.pipe( minify( { ext: { min: '.min.js' }  } ) )
//			.pipe( dest( foldersJS[ foldergroup ] ) );

			src( foldersJS[ foldergroup ] + file + '.js' )
			.pipe( terser( { keep_classnames: true, safari10: true } ) )
			.pipe( rename( file + '.min.js' ) )
			.pipe( dest( foldersJS[ foldergroup ] ) );
		});
	}

	console.log( '------- Minify JS in ALB shortcode modules' );

	for( let module in modulesJS )
	{
		let files = modulesJS[ module ];

		files.forEach( file =>
		{
//			src( module_path + module + '/' + file + '.js' )
//			.pipe( minify( { ext: { min: '.min.js' }  } ) )
//			.pipe( dest( module_path + module + '/' ) );

			src( module_path + module + '/' + file + '.js' )
			.pipe( terser( { keep_classnames: true, safari10: true } ) )
			.pipe( rename( file + '.min.js' ) )
			.pipe( dest( module_path + module + '/' ) );
		});
	}

	console.log( 'Finished minify JS' );
}

function deleteCSS()
{
	console.log( 'Start delete minified CSS' );

	for( let foldergroup in foldersCSS )
	{
		let files = filegroupsCSS[ foldergroup ];

		console.log( '------- Delete minified CSS in foldergroup ' +  foldergroup );

		files.forEach( file =>
		{
			src( foldersCSS[ foldergroup ] + file + '.min.css', { read: false, allowEmpty: true } )
			.pipe( clean( { force: true } ) );
		});
	}

	console.log( '------- Delete minified CSS in modules' );

	for( let module in modulesCSS )
	{
		let files = modulesCSS[ module ];

		files.forEach( file =>
		{
			src( module_path + module + '/' + file + '.min.css', { read: false, allowEmpty: true } )
			.pipe( clean( { force: true } ) );
		});
	}

	console.log( 'Finished delete minified CSS' );

}

function deleteJS()
{
	console.log( 'Start delete minified JS' );

	for( let foldergroup in foldersJS )
	{
		let files = filegroupsJS[ foldergroup ];

		console.log( '------- Delete minified JS in foldergroup ' +  foldergroup );

		files.forEach( file =>
		{
			src( foldersJS[ foldergroup ] + file + '.min.js', { read: false, allowEmpty: true } )
			.pipe( clean( { force: true } ) );
		});
	}

	console.log( '------- Delete minified JS in modules' );

	for( let module in modulesJS )
	{
		let files = modulesJS[ module ];

		files.forEach( file =>
		{
			src( module_path + module + '/' + file + '.min.js', { read: false, allowEmpty: true } )
			.pipe( clean( { force: true } ) );
		});
	}

	console.log( 'Finished delete minified JS' );
}

function minifyEnfoldCSS( cb )
{
	minifyCSS();

	// default callback to render any error
	cb();
}

function minifyEnfoldJS( cb )
{
	minifyJS();

	// default callback to render any error
	cb();
}

function minifyEnfold( cb )
{
	minifyCSS();
	minifyJS();

	// default callback to render any error
	cb();
}

function deleteEnfoldJS( cb )
{
	deleteJS();

	// default callback to render any error
	cb();
}

function deleteEnfoldCSS( cb )
{
	deleteCSS();

	// default callback to render any error
	cb();
}

function deleteEnfold( cb )
{
	deleteCSS();
	deleteJS();

	// default callback to render any error
	cb();
}


exports.minifyEnfoldCSS = minifyEnfoldCSS;
exports.minifyEnfoldJS = minifyEnfoldJS;
exports.minifyEnfold = minifyEnfold;

exports.deleteEnfoldCSS = deleteEnfoldCSS;
exports.deleteEnfoldJS = deleteEnfoldJS;
exports.deleteEnfold = deleteEnfold;

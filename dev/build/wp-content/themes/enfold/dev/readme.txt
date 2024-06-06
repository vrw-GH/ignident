This folder /dev is only needed to (re)create minified css and js files.

========================================================
IT IS SAFE TO DELETE IT IF YOU DO NOT INTEND TO DO THIS.
========================================================


Instructions to (re)create minified css and js files:
=====================================================

You need to install NodeJS and npm: https://docs.npmjs.com/downloading-and-installing-node-js-and-npm

1. Open Command Line or Windows Power Shell (Windows users) or Terminal (MacOS users)
2. Navigate to ../enfold/dev folder

Due to the size of the folder dev/node_modules we removed this from the github repo. Always run the following command before starting a gulp command:

3. npm install

4. Minify the files using one of the following commands:

   - gulp minifyEnfold ( ---> minifies all files )
   - gulp minifyEnfoldCSS
   - gulp minifyEnfoldJS
   - gulp deleteEnfold ( ---> deletes all minified files)
   - gulp deleteEnfoldCSS
   - gulp deleteEnfoldJS

-----------------------------------------------------------------------------------------------------------

In case you want to create a bundled file and avoid to run npm install and the node_modules directory:

    Check webpack.config_bundle_gulp.js


------------------------
@since 5.5  supports ES6 syntax (use of "gulp-terser")  https://www.npmjs.com/package/gulp-terser

[easyphp-generator](https://www.npmjs.com/package/easyphp-generator)

[![NPM Version][npm-image]][npm-url]
[![NPM Downloads][downloads-image]][downloads-url]
[![Linux Build][travis-image]][travis-url]
[![Windows Build][appveyor-image]][appveyor-url]
[![Gratipay][gratipay-image]][gratipay-url]

## Quick Start

Easyphp application generator

Use the application generator tool, easyphp-generator, to quickly create an application skeleton.

Install easyphp-generator with the following command:

$ npm install easyphp-generator -g
Display the command options with the -h option:

$ easyphp -h

  Usage: easyphp [options] [dir]

  Options:

    -h, --help     output usage information
    -V, --version  output the version number
    -f, --force    force on non-empty directory

For example, the following creates an easyphp app named myapp in the current working directory:

$ easyphp myapp

   create : myapp
   create : myapp/trunk/package.json
   create : myapp/branches
   create : myapp/trunk
   create : myapp/trunk/gulpfile.js
   create : myapp/release
   create : myapp/document
   create : myapp/trunk/htdocs
   create : myapp/trunk/htdocs/index.php
   create : myapp/trunk/htdocs/json.php
   create : myapp/trunk/tpl
   create : myapp/trunk/tpl_src
   create : myapp/tags
   create : myapp/trunk/module
   create : myapp/trunk/module/mod.php
   create : myapp/trunk/static/css
   create : myapp/trunk/static/css/style.css
   create : myapp/trunk/static/tpl/index
   create : myapp/trunk/static/tpl/index/index.html
   create : myapp/trunk/weblib/api
   create : myapp/trunk/static/html/index
   create : myapp/trunk/static/html/index/index.html
   create : myapp/trunk/static/js
   create : myapp/trunk/static/js/zepto.min.js
   create : myapp/trunk/static/img
   create : myapp/trunk/weblib/etc
   create : myapp/trunk/weblib/etc/config.inc.php
   create : myapp/trunk/weblib/interface
   create : myapp/trunk/weblib/lib
   create : myapp/trunk/weblib/lib/Env.php
   create : myapp/trunk/weblib/lib/FileCache.php
   create : myapp/trunk/weblib/lib/loadClass.php
   create : myapp/trunk/weblib/lib/Logger.php
   create : myapp/trunk/weblib/lib/Mysql.php
   create : myapp/trunk/weblib/lib/Template.php
   create : myapp/trunk/weblib/lib/Utils.php

   install dependencies:
     > cd myapp && npm install

   run the app:
     > SET DEBUG=myapp:* & npm start

The generated app has the following directory structure:

.
├── branches
├── document
├── myapp.zip
├── release
├── tags
└── trunk
    ├── gulpfile.js
    ├── htdocs
    │   ├── index.php
    │   └── json.php
    ├── module
    │   └── mod.php
    ├── package.json
    ├── static
    │   ├── css
    │   │   └── style.css
    │   ├── html
    │   │   └── index
    │   │       └── index.html
    │   ├── img
    │   ├── js
    │   │   └── zepto.min.js
    │   └── tpl
    │       └── index
    │           └── index.html
    ├── tpl
    ├── tpl_src
    └── weblib
        ├── api
        ├── etc
        │   └── config.inc.php
        ├── interface
        └── lib
            ├── Env.php
            ├── FileCache.php
            ├── loadClass.php
            ├── Logger.php
            ├── Mysql.php
            ├── Template.php
            └── Utils.php

22 directories, 18 files

## Command Line Options

This generator can also be further configured with the following command line flags.

    -h, --help          output usage information
    -V, --version       output the version number
    -e, --ejs           add ejs engine support (defaults to jade)
        --hbs           add handlebars engine support
    -H, --hogan         add hogan.js engine support
    -c, --css <engine>  add stylesheet <engine> support (less|stylus|compass|sass) (defaults to plain css)
        --git           add .gitignore
    -f, --force         force on non-empty directory

## License

[MIT](LICENSE)

[npm-image]: https://img.shields.io/npm/v/express-generator.svg
[npm-url]: https://npmjs.org/package/express-generator
[travis-image]: https://img.shields.io/travis/expressjs/generator/master.svg?label=linux
[travis-url]: https://travis-ci.org/expressjs/generator
[appveyor-image]: https://img.shields.io/appveyor/ci/dougwilson/generator/master.svg?label=windows
[appveyor-url]: https://ci.appveyor.com/project/dougwilson/generator
[downloads-image]: https://img.shields.io/npm/dm/express-generator.svg
[downloads-url]: https://npmjs.org/package/express-generator
[gratipay-image]: https://img.shields.io/gratipay/dougwilson.svg
[gratipay-url]: https://gratipay.com/dougwilson/

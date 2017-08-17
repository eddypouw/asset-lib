var uglify = require("uglify-js");
var fs = require("fs");

var source = fs.readFileSync(process.argv[2]);

var result = uglify.minify(source.toString());

process.stdout.write(result.code);
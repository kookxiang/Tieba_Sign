var fs = require("fs");
var yaml = require("js-yaml");
var webpack = require("webpack");
var i18nTransform = require("i18n-webpack-plugin");
var i18nDatas = {};
var ExtractTextPlugin = require("extract-text-webpack-plugin");
var i18nFile = "./Library/Language/zh-CN.yml";
i18nUpdater();

module.exports = {
    entry: {
        Dashboard: ["./Resource/Dashboard.js"],
        Error: ["./Resource/Misc/Error.css"],
        Member: ["./Resource/Member/Member.js"]
    },
    output: {
        path: "Public/Resource",
        filename: "[name].js",
        sourceMapFilename: "[file].map"
    },
    module: {
        loaders: [{
            test: /\.(sa|sc|c)ss$/,
            loader: ExtractTextPlugin.extract("style", "css?importLoaders=1!postcss!sass?sourceMap")
        }, {
            test: /\.(jpe?g|gif|png|svg|woff\d*|ttf|eot)(\?.*|#.*)?$/,
            loader: "url?limit=8192"
        }, {
            test: /\.(tpl|ejs)$/,
            loader: "ejs-compiled"
        }, {
            test: /\.js$/,
            exclude: /(node_modules|bower_components)/,
            loader: "babel",
            query: {
                presets: ["es2015"]
            }
        }]
    },
    "ejs-compiled-loader": {
        "htmlmin": true,
        "htmlminOptions": {
            collapseWhitespace: true,
            removeComments: true
        }
    },
    "postcss": function () {
        return [
            require("autoprefixer")({
                browsers: ["ie >= 9", "> 2%", "last 1 version"]
            })
        ];
    },
    plugins: [
        new ExtractTextPlugin("[name].css"),
        new i18nTransform(i18nDatas),
        new i18nUpdaterPlugin()
    ],
    progress: true
};

function i18nUpdater() {
    if (!i18nFile) return;
    try {
        var datas = yaml.safeLoad(fs.readFileSync(i18nFile));
        i18nDatas = flattenObject(datas, "");
    } catch (e) {
        i18nDatas = {};
    }
}

function i18nUpdaterPlugin() {}
i18nUpdaterPlugin.prototype.apply = function (compiler) {
    compiler.plugin("compile", i18nUpdater);
};

function flattenObject(obj, prefix) {
    var ret = {};
    for (var key in obj) {
        if (typeof obj[key] == "object") {
            var _obj = flattenObject(obj[key], prefix + key + ".");
            for (var _key in _obj) {
                ret[_key] = _obj[_key];
            }
        } else {
            ret[prefix + key] = obj[key];
        }
    }
    return ret;
}

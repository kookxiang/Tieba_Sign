/*jslint node: true*/
"use strict";

var domProps = require('uglify-js/tools/domprops').props;

function push_unique(array, el) {
    if (array.indexOf(el) < 0) {
        array.push(el);
    }
}

function find_builtins() {
    var a = [], i = 0;
    function add(name) {
        push_unique(a, name);
    }
    [Object, Array, Function, Number,
        String, Boolean, Error, Math,
        Date, RegExp ].forEach(function (ctor) {
        Object.getOwnPropertyNames(ctor).map(add);
        if (ctor.prototype) {
            Object.getOwnPropertyNames(ctor.prototype).map(add);
        }
    });
    for (i in domProps) {
        push_unique(a, domProps[i]);
    }
    return a;
}

module.exports = find_builtins();

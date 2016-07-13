"use strict";
var $ = require("jquery");
var maskCount = 0;
var maskObj = null;
var closeTimer = null;

function showMask() {
    if (maskCount++ > 0) return;
    if (closeTimer !== null) {
        clearTimeout(closeTimer);
        closeTimer = null;
    } else {
        maskObj = $('<div class="dialog-mask"></div>');
        $(document.body).append(maskObj);
    }
    setTimeout(function () {
        maskObj.addClass('active');
    }, 1);
}

function hideMask() {
    maskCount--;
    if (maskCount > 0) return;
    maskObj.removeClass('active');
    closeTimer = setTimeout(function () {
        maskObj.remove();
        maskObj = null;
        closeTimer = null;
    }, 600);
}

module.exports = {
    Show: showMask,
    Hide: hideMask
};

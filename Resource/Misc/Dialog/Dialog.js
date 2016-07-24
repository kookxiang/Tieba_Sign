"use strict";
require("./Dialog.scss");
var $ = require("jquery");
var emitter = require("event-emitter");
var dialogTpl = require("./Dialog.ejs");
var Mask = require("./Mask");
var autoIncrement = 0;

var Dialog = function (options) {
    var dialog = emitter({
        id: autoIncrement++,
        Title: "System Message",
        Content: "",
        Actions: [],
        Element: null,
        WithMask: true,
        shown: false,
        onDismiss: function (event) {
            event.preventDefault();
            dialog.hide();
        },
        show: function () {
            dialog.emit("show");
            dialog.shown = true;
            if (dialog.WithMask) Mask.Show();
            dialog.Element = $(dialogTpl({
                Config: dialog
            }));
            $(document.body).append(dialog.Element);
            dialog.Element.find("[data-action]").click(function (event) {
                var action = $(this).data("action");
                var methodName = "on" + action.charAt(0).toUpperCase() + action.substring(1);
                if (dialog.hasOwnProperty(methodName)) {
                    dialog[methodName](event);
                }
            });
            dialog.reposition();
            $(window).on("resize", dialog.reposition);
            dialog.emit("after_show");
            return dialog;
        },
        hide: function () {
            dialog.emit("hide");
            if (dialog.WithMask) Mask.Hide();
            $(window).off("resize", dialog.reposition);
            dialog.Element.addClass("hide");
            setTimeout(function () {
                dialog.emit("after_hide");
            }, 125);
            return dialog;
        },
        reposition: function () {
            var width = window.innerWidth;
            var height = window.innerHeight;
            dialog.Element.css("left", (width - dialog.Element.outerWidth()) / 2);
            dialog.Element.css("top", (height - dialog.Element.outerHeight()) / 2);
        },
        destroy: function () {
            if (dialog.shown) {
                dialog.Element.remove();
            }
            dialog = null;
        },
        before: function (event, callback) {
            dialog.on(event, callback);
            return dialog;
        },
        after: function (event, callback) {
            dialog.on("after_" + event, callback);
            return dialog;
        }
    });
    for (var key in options) {
        dialog[key] = options[key];
    }
    return dialog;
};

Dialog.ShowMessage = function (content, title, callback) {
    return new Dialog({
        Title: title || __("Dialog.DefaultTitle"),
        Content: content,
        Actions: [{
            Action: "dismiss",
            Label: __("Dialog.Buttons.OK"),
            Default: true
        }],
        onDismiss: function (event) {
            event.preventDefault();
            this.hide();
            if (callback) callback();
        }
    }).after("hide", function () {
        this.destroy();
    }).show();
};

Dialog.Confirm = function (content, title, callback) {
    return new Dialog({
        Title: title || __("Dialog.DefaultTitle"),
        Content: content,
        Actions: [{
            Action: "confirm",
            Label: __("Dialog.Buttons.Confirm"),
            Default: true
        }, {
            Action: "dismiss",
            Label: __("Dialog.Buttons.Cancel")
        }],
        onConfirm: function (event) {
            event.preventDefault();
            this.hide();
            if (callback) callback();
        }
    }).after("hide", function () {
        this.destroy();
    }).show();
};

module.exports = Dialog;

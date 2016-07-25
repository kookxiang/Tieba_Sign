require("./Dialog.scss");
import $ from "jquery";
import EventEmitter from "eventemitter3";
import Mask from "./Mask";
import DialogTemplate from "./Dialog.ejs";
var autoIncrement = 0;

class Dialog extends EventEmitter {
    constructor(config) {
        super({});
        this.id = autoIncrement++;
        this.Title = "System Message";
        this.Content = "";
        this.Actions = [];
        this.Element = null;
        this.WithMask = true;
        this.shown = false;
        for (var key in config) {
            this[key] = config[key];
        }
    }
    onDismiss(event) {
        event.preventDefault();
        this.hide();
    }
    show() {
        let that = this;
        this.emit("show");
        this.shown = true;
        if (this.WithMask) Mask.Show();
        this.Element = $(DialogTemplate({
            Config: this
        }));
        $(document.body).append(this.Element);
        this.Element.find("[data-action]").click(function (event) {
            var action = $(this).data("action");
            var methodName = "on" + action.charAt(0).toUpperCase() + action.substring(1);
            if (that.hasOwnProperty(methodName)) {
                that[methodName](event);
            }
        });
        this.reposition();
        $(window).on("resize", this.reposition);
        this.emit("after_show");
        return this;
    }
    hide() {
        this.emit("hide");
        if (this.WithMask) Mask.Hide();
        $(window).off("resize", this.reposition);
        this.Element.addClass("hide");
        setTimeout(() => this.emit("after_hide"), 125);
        return this;
    }
    reposition() {
        var width = window.innerWidth;
        var height = window.innerHeight;
        this.Element.css("left", (width - this.Element.outerWidth()) / 2);
        this.Element.css("top", (height - this.Element.outerHeight()) / 2);
    }
    destroy() {
        if (this.shown) {
            this.Element.remove();
        }
        delete this;
    }
    before(event, callback) {
        this.on(event, callback);
        return this;
    }
    after(event, callback) {
        this.on("after_" + event, callback);
        return this;
    }
    static ShowMessage(content, title, callback) {
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
    }
    static Confirm(content, title, callback) {
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
    }
}

module.exports = Dialog;

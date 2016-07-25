import "./notice.scss";
import $ from "jquery";
import EventEmitter from "eventemitter3";
import template from "./notice.ejs";

let AppendParents = $("<div class='notice-wrapper'>");
let hasParents = false;

class Notice extends EventEmitter {
    constructor(content, isError) {
        super({});
        this.type = isError ? "error" : "warn";
        this.content = content;
        this.actions = [];
        this.element = null;
    }
    addAction(text, callback) {
        this.actions.push({
            action: text,
            callback: callback
        });
        return this;
    }
    render() {
        this.emit("render");
        let that = this;
        this.element = $(template(this));
        this.element.on("click", ".action", function (event) {
            event.preventDefault();
            let id = $(this).data("id");
            if (that.actions[id] && that.actions[id].callback) {
                that.actions[id].callback(event);
            }
            that.dismiss();
        });
    }
    show() {
        this.render();
        this.emit("show");
        if (!hasParents) {
            AppendParents.appendTo(document.body);
            hasParents = true;
        }
        AppendParents.append(this.element);
        return this;
    }
    dismiss() {
        // Wait for animation finish
        setTimeout(() => this.distory(), 100);
        this.emit("dismiss");
        this.element.addClass("dismiss");
        return this;
    }
    distory() {
        this.element.remove();
        if (AppendParents.children().length == 0) {
            AppendParents.detach();
            hasParents = false;
        }
    }
}

module.exports = Notice;

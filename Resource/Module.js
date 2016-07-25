import $ from "jquery";
import EventEmitter from "eventemitter3";

class Module extends EventEmitter {
    constructor(id) {
        super({});
        this.type = id;
        this.routeTable = [];
        this.Element = null;
    }
    initialize() {}
    addRoute(regex, callback) {
        this.routeTable.push({
            url: regex,
            callback: callback
        });
    }
    hasRoute(url) {
        let canHandle = false;
        this.routeTable.forEach(obj => {
            if (url.match(obj.url)) {
                canHandle = true;
            }
        });
        return canHandle;
    }
    goto(url) {
        let callback = null;
        this.routeTable.forEach(obj => {
            if (url.match(obj.url)) {
                callback = obj.callback;
            }
        });
        if (callback === null) {
            throw new Error("Cannot handle request: " + url);
        }
        callback(url);
    }
    attach() {
        this.Element.appendTo($(".main-content"));
    }
    detach() {
        this.Element.detach();
    }
    updateState() {
        throw new Error("Method not defined");
    }
    updateTitle(title) {
        $("body > .main-content > .header").text(title);
    }
}

module.exports = Module;

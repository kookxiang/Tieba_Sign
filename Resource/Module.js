import $ from "jquery";
import React from "react";

class Module extends React.Component {
    constructor(props) {
        super(props);
        this.state = {};
        this.id = props.id;
        this.routeTable = [];
    }
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
    updateState(data, url) {
        history.pushState({
            module: this.id,
            path: "",
            url: url
        }, "", url);
    }
    updateTitle(title) {
        $("body > .main-content > .header").text(title);
    }
}

module.exports = Module;

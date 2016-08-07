require("./Dashboard.css");
import React from "react";
import ReactDom from "react-dom";
import $ from "jquery";
import Sidebar from "./Sidebar/Sidebar";
let ModuleList = [];
let currentModule = "";
let mainContent = $("body > .main-content")[0];

function getModule(id) {
    let instance = ModuleList.find(m => m.id == id);
    if (instance) {
        return instance;
    }
    let Module = require("./Module/" + id + "/index.jsx");
    let moduleElement = document.createElement("div");
    moduleElement.classList = "container module-" + id;
    instance = ReactDom.render(<Module id={id} />, moduleElement);
    instance.Element = moduleElement;
    ModuleList.push(instance);
    return instance;
}

function switchModule(newModule, path, url, skipState) {
    if (!skipState) {
        history.pushState({
            module: newModule,
            path: "",
            url: url
        }, "", url);
    }
    if (currentModule) {
        ModuleList.filter(m => m.id == currentModule).forEach(m => mainContent.removeChild(m.Element));
    }
    currentModule = newModule;
    let module = getModule(newModule);
    mainContent.appendChild(module.Element);
    module.goto(path);
}

Sidebar.element.find(".sidebar-menu").on("click", "a[data-module]", function (event) {
    event.preventDefault();
    switchModule($(this).data("module"), "", this.href);
});

$(window).on("popstate", function (event) {
    let state = event.originalEvent.state;
    if (state && state.module && state.url) {
        switchModule(state.module, state.path, state.url, true);
    } else {
        location.reload();
    }
});

// Find current module
(function () {
    let baseUrl = $("head > base").attr("href");
    let url = location.href.replace(baseUrl, "").trim("/");

    Sidebar.element.find(".sidebar-menu a[data-module]").each((i, el) => {
        let moduleName = $(el).data("module");
        if (!moduleName) return;
        try {
            let module = getModule(moduleName);
            if (module.hasRoute(url)) {
                currentModule = moduleName;
            }
        } catch (e) {
            // Ignore error;
        }
    });

    if (!currentModule) {
        currentModule = Sidebar.element.find(".sidebar-menu a[data-module]").eq(0).data("module");
    }

    let module = getModule(currentModule);
    mainContent.appendChild(module.Element);
    module.goto(url);
})();

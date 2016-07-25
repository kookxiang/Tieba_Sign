require("./Dashboard.css");
import $ from "jquery";
import Sidebar from "./Sidebar/Sidebar";
let ModuleList = [];
let currentModule = "";

function getModule(id) {
    let instance = ModuleList.find(m => m.type == id);
    if (instance) {
        return instance;
    }
    let Module = require("./Module/" + id + "/index.js");
    instance = new Module(id);
    instance.initialize();
    instance.emit("init");
    instance.updateState = function (data, url) {
        history.pushState({
            module: currentModule,
            path: "",
            url: url
        }, "", url);
    };
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
        ModuleList.filter(m => m.type == currentModule).forEach(m => m.detach());
    }
    currentModule = newModule;
    let module = getModule(newModule);
    module.attach();
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
        } catch (e) {}
    });

    if (!currentModule) {
        currentModule = Sidebar.element.find(".sidebar-menu a[data-module]").eq(0).data("module");
    }

    let module = getModule(currentModule);
    module.attach();
    module.goto(url);
})();

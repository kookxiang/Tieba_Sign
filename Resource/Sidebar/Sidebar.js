"use strict";
import "./Sidebar.scss";
import $ from "jquery";
import Dialog from "../Misc/Dialog/Dialog";
import Mask from "../Misc/Dialog/Mask";

let sidebar = $(".sidebar");
let header = sidebar.find(".sidebar-header");
let bottom = sidebar.find(".sidebar-bottom");
let expanded = false;

sidebar.find(".switch-account").on("click", function (event) {
    event.preventDefault();
    expanded = !expanded;
    $(this).toggleClass("expanded", expanded);
    $(header).toggleClass("expanded", expanded).scrollTop(0);
});

sidebar.find(".account-list").on("click", "li[data-action=switch]", function (event) {
    event.preventDefault();
    var accountId = $(this).data("id");
    Mask.Show();
    $.ajax({
        url: "/Member/Account/" + accountId + "/Switch.action",
        type: "POST",
        error: function () {
            Mask.Hide();
        }
    }).done(function (result) {
        if (result.code != 200) {
            Dialog.ShowMessage(result.message, __("Member.SwitchAccount"));
            Mask.Hide();
        } else {
            Dialog.ShowMessage(__("Member.Messages.AccountSwitchSucceed"), __("Member.SwitchAccount"), () => {
                location.reload();
            });
        }
    });
}).on("click", "li[data-action=add]", function (event) {
    event.preventDefault();
    Dialog.ShowMessage("添加功能还没写好…", "debug");
});

bottom.find(".sign-out").on("click", () => {
    Dialog.Confirm("确定要退出登录吗?", __("Member.Logout"), () => {
        $.ajax({
            url: "/Member/Logout.action",
            type: "POST"
        }).done(function (result) {
            return Dialog.ShowMessage(result.message, __("Member.Logout"));
        });
    });
});

module.exports = {
    element: sidebar
};

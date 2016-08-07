"use strict";
require("./Member.css");
var $ = require("jquery");
var Dialog = require("../Misc/Dialog/Dialog");

$(function () {
    $(".member-form.login .submit").click(function (event) {
        event.preventDefault();
        var username = $("input[name=\"username\"]").val().trim();
        var password = $("input[name=\"password\"]").val().trim();
        if (username === "") {
            Dialog.ShowMessage(__("Member.Messages.NoUsername"), __("Member.Login"));
        } else if (password === "") {
            Dialog.ShowMessage(__("Member.Messages.NoPassword"), __("Member.Login"));
        } else {
            $.ajax({
                url: "/Member/Login.action",
                type: "POST",
                data: {
                    username: username,
                    password: password
                }
            }).done(function (result) {
                if (result.code != 200) {
                    return Dialog.ShowMessage(result.message, __("Member.LoginFailed"));
                }
                // Login success
                Dialog.ShowMessage(__("Member.Messages.Welcome") + " " + result.data.username, __("Member.Login"), () => location.href = "/Dashboard");
            });
        }
    });
    $(".member-form.register .submit").click(function (event) {
        event.preventDefault();
        var username = $("input[name=\"username\"]").val().trim();
        var password = $("input[name=\"password\"]").val().trim();
        var email = $("input[name=\"email\"]").val().trim();
        var invite = $("input[name=\"invite\"]").val().trim();
        if (username === "") {
            Dialog.ShowMessage(__("Member.Messages.NoUsername"), __("Member.Register"));
        } else if (password === "") {
            Dialog.ShowMessage(__("Member.Messages.NoPassword"), __("Member.Register"));
        } else if (email === "") {
            Dialog.ShowMessage(__("Member.Messages.NoEmail"), __("Member.Register"));
        } else if (email.match(/(qq|foxmail)\.com$/i)) {
            Dialog.ShowMessage(__("Member.Messages.NotSupportedEmail"), __("Member.Register"));
        } else {
            $.ajax({
                url: "/Member/Register.action",
                type: "POST",
                data: {
                    username: username,
                    password: password,
                    email: email,
                    invite: invite
                }
            }).done(function (result) {
                if (result.code != 200) {
                    return Dialog.ShowMessage(result.message, __("Member.RegisterFailed"));
                }
                // Login success
                Dialog.ShowMessage(__("Member.Messages.RegistrationComplete"), __("Member.Register"), () => location.href = "/Dashboard");
            });
        }
    });
});

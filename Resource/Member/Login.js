"use strict";
require('./Member.css');
var $ = require('jquery');
var Dialog = require('../Misc/Dialog/Dialog');

$(function () {
    $('.login-form .submit').click(function (event) {
        event.preventDefault();
        var username = $('input[name="username"]').val().trim();
        var password = $('input[name="password"]').val().trim();
        if (username === '') {
            Dialog.ShowMessage(__('Member.Messages.NoUsername'), __('Member.Login'));
        } else if (password === '') {
            Dialog.ShowMessage(__('Member.Messages.NoPassword'), __('Member.Login'));
        } else {
            $.ajax({
                url: '/Member/Login.action',
                type: 'POST',
                data: {
                    username: username,
                    password: password
                }
            }).done(function (result) {
                if (result.code != 200) {
                    return Dialog.ShowMessage(result.message, __('Member.LoginFailed'));
                }
                // Login success
                Dialog.ShowMessage(__('Member.Messages.Welcome') + result.data.nickname, __('Member.Login'), function () {
                    location.href = '/';
                });
            });
        }
    });
});

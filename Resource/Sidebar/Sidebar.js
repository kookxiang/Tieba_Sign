"use strict";
import $ from 'jquery';
import Dialog from '../Misc/Dialog/Dialog';

let sidebar = $('.sidebar');
let header = sidebar.find('.sidebar-header');
let bottom = sidebar.find('.sidebar-bottom');
let expanded = false;

sidebar.find('.switch-account').on('click', function (event) {
    event.preventDefault();
    expanded = !expanded;
    $(this).toggleClass('expanded', expanded);
    $(header).toggleClass('expanded', expanded).scrollTop(0);
});

bottom.find('.sign-out').on('click', () => {
    Dialog.Confirm('确定要退出登录吗?', __('Member.Logout'), () => {
        $.ajax({
            url: '/Member/Logout.action',
            type: 'POST'
        }).done(function (result) {
            return Dialog.ShowMessage(result.message, __('Member.Logout'));
        });
    });
});

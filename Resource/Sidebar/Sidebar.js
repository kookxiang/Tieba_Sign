"use strict";
import $ from 'jquery';

let sidebar = $('.sidebar');
let header = sidebar.find('.sidebar-header');
let expanded = false;

sidebar.find('.switch-account').on('click', function (event) {
    event.preventDefault();
    expanded = !expanded;
    $(this).toggleClass('expanded', expanded);
    $(header).toggleClass('expanded', expanded);
});

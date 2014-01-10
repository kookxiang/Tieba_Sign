$(document).ready(function() {
	$('#menu_login').click(function(){
		switch_tabs('login');
	});
	$('#menu_register').click(function(){
		switch_tabs('register');
	});
});
function switch_tabs(target){
	$('.main').addClass('hidden');
	$('#content-'+target).removeClass('hidden');
	$('.side-bar li').removeClass('current');
	$('#menu_'+target).addClass('current');
}
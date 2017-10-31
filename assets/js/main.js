$(function()
{
	$('#logout').click(function()
	{
		$(location).attr('href', '/logout');
	});

	$('#login').click(function()
	{
		$(location).attr('href', $('#login').attr('data-url'));
	});
});
$(function()
{
	$('#logout').click(function()
	{
		$(location).attr('href', $('#logout').attr('data-url'));
	});

	$('#login').click(function()
	{
		$(location).attr('href', $('#login').attr('data-url'));
	});
});
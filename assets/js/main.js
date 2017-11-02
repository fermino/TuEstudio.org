$(function()
{
	$('.button-link').each(function(i, button)
	{
		$(button).click(function()
		{
			$(location).attr('href', $(button).attr('data-url'));
		});
	});
});
$(function()
{
	if(null != error)
		iziToast.error({title: 'Error', message: 'La columna ' + pcol +' debe ser única'});

	$('.button-link').each(function(i, button)
	{
		$(button).click(function()
		{
			$(location).attr('href', $(button).attr('data-url'));
		});
	});
});
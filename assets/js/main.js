$(function()
{
	if(typeof error !== 'undefined')
	{
		if('unique' == error)
			iziToast.error({title: 'Error', message: 'El ítem con el ' + pcol + ' "' + val + '" ya existe'});
	}

	if(typeof success !== 'undefined')
	{
		if('inserted' == success)
			iziToast.success({title: 'Listo!', message: 'El ítem ha sido insertado'});
		else if('updated' == success)
			iziToast.success({title: 'Listo!', message: 'El ítem ha sido actualizado'});
	}

	$('.button-link').each(function(i, button)
	{
		$(button).click(function()
		{
			$(location).attr('href', $(button).attr('data-url'));
		});
	});
});
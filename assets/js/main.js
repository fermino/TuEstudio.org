$(function()
{
	if(typeof error !== 'undefined')
		if('unique' == error)
			iziToast.error({title: 'Error', message: 'El ítem con el ' + pcol + ' "' + val + '" ya existe'});

	if(typeof success !== 'undefined')
		iziToast.success({title: 'Listo!', message: 'La acción ha sido completada con éxito'});

	$('.button-link').each(function(i, button)
	{
		$(button).click(function()
		{
			$(location).attr('href', $(button).attr('data-url'));
		});
	});
});
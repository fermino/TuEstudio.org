$(function()
{
	if(typeof error !== 'undefined')
	{
		switch(error)
		{
			case 'unique':
				iziToast.error({title: 'Error', message: 'El ítem con el ' + pcol + ' "' + val + '" ya existe'});
				break;
			case 'max_length':
				iziToast.error({title: 'Error', message: 'El ' + pcol + ' es demasiado largo'});
		}
	}

	if(typeof success !== 'undefined')
	{
		switch(success)
		{
			case 'inserted':
				iziToast.success({title: 'Listo!', message: 'El ítem ha sido insertado'});
				break;
			case 'updated':
				iziToast.success({title: 'Listo!', message: 'El ítem ha sido actualizado'});
				break;
			case 'deleted':
				iziToast.success({title: 'Listo!', message: 'El ítem ha sido eliminado'});
				break;
		}
	}

	$('.button-link').each(function(i, button)
	{
		$(button).click(function()
		{
			$(location).attr('href', $(button).attr('data-url'));
		});
	});

	$('.modal').on('show.bs.modal', function(event)
	{
		var data = $(event.relatedTarget).data();

		$.each(data, function(key, value)
		{
			$(event.currentTarget).find('#span-' + key).each(function(i, span)
			{
				$(span).text(value);
			});

			$(event.currentTarget).find('#input-' + key).each(function(i, input)
			{
				$(input).val(value);
			});
		});
	});
});
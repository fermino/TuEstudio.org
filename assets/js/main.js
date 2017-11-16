$(function()
{
	if(typeof error !== 'undefined')
	{
		switch(error)
		{
			case 'unique':
				iziToast.error({title: 'Error', message: 'El ítem con el dato ' + pcol + ' "' + val + '" ya existe'});
				break;
			case 'max_length':
				iziToast.error({title: 'Error', message: 'El ' + pcol + ' es demasiado largo'});
				break;
			case 'redundant':
				iziToast.error({title: 'Error', message: 'Se detectaron datos redundantes. Abortando...'});
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

	$('button[data-link]').each(function(i, button)
	{
		$(button).click(function()
		{
			$(location).attr('href', $(button).data('link'));
		});
	});

	$('.modal').on('show.bs.modal', function(event)
	{
		var data = $(event.relatedTarget).data();

		$.each(data, function(key, value)
		{
			$('input[data="' + key + '"]')
				.val(value);

			$('select[data="' + key + '"] option[value="' + value + '"]')
				.prop('selected', true);

			$('[data="' + key + '"]').not('input').not('select')
				.text(value);
		});
	});
});
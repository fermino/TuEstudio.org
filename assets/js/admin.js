$(function()
{
	$('#admin-' + $('#admin-nav').attr('data-active')).addClass('active');

	$('#createEditModal').on('show.bs.modal', function(event)
	{
		var actionMessage = $(event.relatedTarget).data('action');

		$(this).find('h4 #createEditModal-title').text(actionMessage);
		$(this).find('button #createEditModal-title').text(actionMessage);

		var button = $(event.relatedTarget);

		$('#createEdit-id-label').text(button.data('id'));
		$('#createEdit-id').val(button.data('id'));

		$('#createEdit-name').val(button.data('name'));
		$('#createEdit-name').focus();
	});
});
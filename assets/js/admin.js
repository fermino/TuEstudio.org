$(function()
{
	$('#admin-' + $('#admin-nav').attr('data-active')).addClass('active');

	$('#create_edit-modal').on('show.bs.modal', function(event)
	{
		var button = $(event.relatedTarget);

		$('h4 #create_edit-modal-title').text(button.data('action'));
		$('button #create_edit-modal-title').text(button.data('action'));

		$('#create_edit-id').text(button.data('id'));
		$('#create_edit-id-hidden').val(button.data('id'));

		$('#create_edit-name').val(button.data('name'));
		$('#create_edit-name').focus();
	});

	$('#delete-modal').on('show.bs.modal', function(event)
	{
		var button = $(event.relatedTarget);

		$('#delete-delete_id').text(button.data('id'));
		$('#delete-delete_id-hidden').val(button.data('id'));

		$('#delete-delete_name').text(button.data('name'));
	});
});
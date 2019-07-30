!function () {
    $(document).ready(function () {
        $('#newPatternModal').on('show.bs.modal', function (event) {
            var modal = $(this);
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var pattern = button.data('pattern');
            modal.find('.modal-header h5').html((id === undefined) ? 'New pattern' : 'Edit pattern #' + id);
            modal.find('.modal-body input#pattern').val(pattern);
            modal.find('.modal-body input#pattern_id').val(id);
            modal.find('.modal-body div.new-pattern-form-container').html('');
        });
        $('#deleteConfirmationModal').on('show.bs.modal', function (event) {
            var modal = $(this);
            var button = $(event.relatedTarget);
            var id = button.data('id');
            modal.find('.modal-body span#delete-confirmation-id').html(id);
            modal.find('.modal-footer button#modal-btn-delete').attr('data-id', id);
        });
        $('button#modal-btn-delete').click(function (e) {
            e.preventDefault();
            $('#deleteConfirmationModal').modal('hide');
            var id = $(this).attr('data-id');
            $.ajax('/api/patterns/' + id, {
                method: 'DELETE'
            }).done(function (data) {
                $('#toast .toast-body').html('<div class="alert alert-success" ' +
                    'role="alert">Pattern with ID ' + id + ' deleted!</div>');
                $('#toast').toast('show');
                loadPatternList(page, perPage, not_found_callback);
            }).fail(function (jqxhr, textStatus, error) {
                if (error === 'Not Found') {
                    $('#toast .toast-body').html('<div class="alert alert-danger" ' +
                        'role="alert">Pattern not found.</div>');
                }
                if (error === 'Bad Request') {
                    $('#toast .toast-body').html('<div class="alert alert-danger" ' +
                        'role="alert">Bad API Request</div>');
                }
                $('#toast').toast('show');
            });
        });
    });
}();
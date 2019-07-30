!function () {
    $(document).ready(function () {
        $('#newPatternModal').on('show.bs.modal', function (event) {
            var modal = $(this);
            modal.find('.modal-body input#pattern').val('');
            modal.find('.modal-body input#pattern').val('');
            modal.find('.modal-body div.new-pattern-form-container').html('');
        });
    });
}();
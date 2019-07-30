!function () {
    $(document).ready(function () {
        $('form#new-pattern-form').submit(function (e) {
            var id = $('input#pattern_id').val();
            var url = (id === '') ? '/api/patterns/' : '/api/patterns/' + id;
            var method = (id === '') ? 'POST' : 'PUT';
            var action = (id === '') ? 'added' : 'updated';
            e.preventDefault();
            $.ajax(url, {
                method: method,
                dataType: 'json',
                data: {
                    pattern: $('input#pattern').val()
                }
            }).done(function (data) {
                $('#toast .toast-body').html('<div class="alert alert-success" ' +
                    'role="alert">Pattern ' + action + '. Pattern ID is ' + data['pattern_id'] + '</div>');
                $('#newPatternModal').modal('hide');
                $('#toast').toast('show');
                $('td#pattern-' + data['pattern_id']).html(data['pattern']);
            }).fail(function (jqxhr, textStatus, error) {
                if (error === 'Conflict') {
                    $('#toast .toast-body').html('<div class="alert alert-danger" ' +
                        'role="alert">Pattern already exist.</div>');
                }
                if (error === 'Bad Request') {
                    $('#toast .toast-body').html('<div class="alert alert-danger" ' +
                        'role="alert">Bad API Request</div>');
                }
                $('#newPatternModal').modal('hide');
                $('#toast').toast('show');
            });
        });
    });
}();
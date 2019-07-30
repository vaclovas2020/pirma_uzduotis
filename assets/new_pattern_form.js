!function () {
    $(document).ready(function () {
        $('form#new-pattern-form').submit(function (e) {
            e.preventDefault();
            $.ajax('/api/patterns/', {
                method: 'POST',
                dataType: 'json',
                data: {
                    pattern: $('input#pattern').val()
                }
            }).done(function (data) {
                $('#new-pattern-form-error-container').html('');
                $('#new-pattern-form-success-container').html('<div class="alert alert-success" ' +
                    'role="alert">Pattern added. Pattern ID is ' + data['pattern_id'] + '</div>');
            }).fail(function (jqxhr, textStatus, error) {
                $('#new-pattern-form-success-container').html('');
                if (error === 'Conflict') {
                    $('#new-pattern-form-error-container').html('<div class="alert alert-danger" ' +
                        'role="alert">Pattern already exist.</div>');
                }
                if (error === 'Bad Request') {
                    $('#new-pattern-form-error-container').html('<div class="alert alert-danger" ' +
                        'role="alert">Bad API Request</div>');
                }
            });
        })
    });
}();
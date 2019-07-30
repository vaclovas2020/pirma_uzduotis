!function (w, d) {
    w.loadPatternList = function (page, perPage, not_found_callback) {
        $.getJSON("/api/patterns", {page: page, per_page: perPage})
            .done(function (json) {
                var html = '';
                for (var x in json) {
                    html += '<tr id="pattern-row-' + json[x]['pattern_id'] + '">' +
                        '<th scope="row">' + json[x]['pattern_id'] + '</th>' +
                        '<td id="pattern-' + json[x]['pattern_id'] + '">' + json[x]['pattern'] + '</td>' +
                        '<td>' +
                        '<button data-toggle="modal" data-target="#newPatternModal" ' +
                        'data-id="' + json[x]['pattern_id'] + '"' +
                        ' data-pattern="' + json[x]['pattern'] + '" type="button" class="btn btn-light">' +
                        '<i class="fas fa-edit"></i></button>' +
                        '<button data-toggle="modal" data-target="#deleteConfirmationModal" ' +
                        'data-id="' + json[x]['pattern_id'] + '" type="button" class="btn btn-light">' +
                        '<i class="fas fa-trash"></i></button>' +
                        '</td>' +
                        '</tr>'
                }
                $('#pattern-table-body').html(html);
                $('span#page_num').html(page);
            })
            .fail(function (jqxhr, textStatus, error) {
                if (error === "Not Found") {
                    not_found_callback();
                }
            });
    };
    $(d).ajaxStart(function () {
        $('.loader-container').show();
    });
    $(d).ajaxStop(function () {
        $('.loader-container').hide();
    });
    $(d).ready(function () {
        w.page = 1;
        w.perPage = 10;
        var nextLi = $('li#next-li');
        var prevLi = $('li#prev-li');
        w.not_found_callback = function () {
            nextLi.addClass('disabled');
            if (page > 1) {
                page--;
            }
        };

        loadPatternList(page, perPage, not_found_callback);
        $('a#prev').click(function (e) {
            e.preventDefault();
            nextLi.removeClass('disabled');
            page--;
            loadPatternList(page, perPage, not_found_callback);
            if (page === 1) {
                prevLi.addClass('disabled');
            }
        });
        $('a#next').click(function (e) {
            e.preventDefault();
            prevLi.removeClass('disabled');
            page++;
            loadPatternList(page, perPage, not_found_callback);
        });
    });
}(window, document);
/* carica i movies salvati nel DB in base all'ordinamento scelto */
function show(search = '') {
    var value = $('#order-value').find('option').filter(':selected').text();
    var type = $('#order-type').find('option').filter(':selected').text();

    $.ajax({
        url: '/include/modules/srv/show.php',
        method: 'POST',
        data: {value:value, type:type, search:search},
        success: function (data) { if (data !== '') $('#box').html(data); }
    });
}

/* mostra il messaggio di notifica */
function notification(color, message, icon) {
    $('#notification').addClass(color);
    $('#message').html(message);
    $('#message-icon').attr('src', ('/resources/icons/' + icon + '.svg'));

    const toast = new bootstrap.Toast($('#notification'));
    toast.show();
    $('#notification').on('hidden.bs.toast', function () { $('#notification').removeClass(color) });
}

/* ripulire gli input */
function clear() {
    $('#name').val('');
    $('#review').val('');
}

/* ottenere i valori del movie cliccato */
function read(movie) {
    $('#name').val(movie.find('.element-name').text().trim());
    $('#review').val(movie.find('.element-review').text().trim());
}

/* gestione eventi quando clicco su un elemento */
function clicked(movie) {
    if (!movie.hasClass('clicked')) {
        $('.element').removeClass('clicked');
        $('.element').find('.card').removeClass('border-primary');

        movie.addClass('clicked');
        movie.find('.card').addClass('border-primary');

        if ($('#modify').hasClass('visually-hidden')) {
            $('#order').addClass('visually-hidden');
            $('#modify').removeClass('visually-hidden');
        }

        read(movie);
    } else {
        movie.removeClass('clicked');
        movie.find('.card').removeClass('border-primary');

        if (!$('#modify').hasClass('visually-hidden')) {
            $('#order').removeClass('visually-hidden');
            $('#modify').addClass('visually-hidden');
        }

        clear();
    }
}

function rename(extension) {
    var name = new Date().toISOString();
    var y = name.slice(0, 4);
    var m = name.slice(5, 7);
    var d = name.slice(8, 10);
    var h = name.slice(11, 13);
    var i = name.slice(14, 16);
    var s = name.slice(17, 19);

    name = y + m + d + h + i + s;
    if (extension === 'text/csv') name = name + '.csv';
    return name;
}

$(document).ready(function () {
    /* verifico se ho già effettuato l'accesso */
    $.post('/include/modules/srv/checker.php', function (data) {
        if (data === '0') $('#modal-login').modal('show');
    });

    /* verifico credenziali di accesso */
    $('#login').on('click', function () {
        var username = $('#username').val().trim();
        var password = $('#password').val().trim();

        if ((username !== '') && (password !== '')) {
            $.ajax({
                url: '/include/modules/srv/login.php',
                method: 'POST',
                data: {username:username, password:password},
                success: function (data) {
                    if (data === '0') {
                        $('#login-warning').addClass('visually-hidden');
                        $('#login-error').removeClass('visually-hidden');
                    } else {
                        $('#modal-login').modal('hide');
                        location.reload();
                    }
                }
            });
        } else {
            $('#login-error').addClass('visually-hidden');
            $('#login-warning').removeClass('visually-hidden');
        }
    });

    /* carico la lista dei movie dell'utente */
    show();

    $('#logout').on('click', function () {
        $.ajax({
            url: '/include/modules/srv/logout.php',
            method: 'POST',
            success: function (data) { location.reload(); }
        });
    });

    $('body').on('click', '.element', function () { clicked($(this)); });

    $('select').on('change', function () { show(); });

    $('#create').on('click', function () {
        var name = $('#name').val().trim();
        var review = $('#review').val().trim();

        if (name === '')
            notification('color-warning', 'WARNING: enter movie name!', 'warning');
        else if (name.length > 50)
            notification('color-warning', 'WARNING: movie name too long!', 'warning');
        else if ((review < 1) || (review > 10))
            notification('color-warning', 'WARNING: movie review invalid!', 'warning');
        else {
            $.ajax({
                url: '/include/modules/srv/create.php',
                method: 'POST',
                data: {name: name, review: review},
                success: function (data) {
                    if (data === '0') {
                        notification('color-danger', 'ERROR: movie already exist!', 'danger');
                        if ($('.element').hasClass('clicked')) clicked($('.clicked'));
                        clear();
                    } else {
                        notification('color-success', 'Movie added!', 'success');
                        if ($('.element').hasClass('clicked')) clicked($('.clicked'));
                        clear();
                        show();
                    }
                }
            });
        }
    });

    $('#change').on('click', function () {
        var name = $('#name').val().trim();
        var review = $('#review').val().trim();
        var named = $('.clicked').find('.element-name').text().trim();
        var reviewed = $('.clicked').find('.element-review').text().trim();

        if ((name === named) && (review === $('.clicked').find('.element-review').text().trim()))
            notification('color-warning', 'WARNING: movie has the same values!', 'warning');
        else if (name === '')
            notification('color-warning', 'WARNING: enter movie name!', 'warning');
        else if (name.length > 50)
            notification('color-warning', 'WARNING: movie name too long!', 'warning');
        else if ((review < 1) || (review > 10))
            notification('color-warning', 'WARNING: movie review invalid!', 'warning');
        else {
            $.ajax({
                url: '/include/modules/srv/change.php',
                method: 'POST',
                data: {name: name, review: review, named:named, reviewed:reviewed},
                success: function (data) {
                    if (data === '0') {
                        notification('color-danger', 'ERROR: movie already exist!', 'danger');
                        clicked($('.clicked'));
                    } else {
                        notification('color-success', 'Movie changed!', 'success');
                        clicked($('.clicked'));
                        show();
                    }
                }
            });
        }
    });

    $('#remove').on('click', function () {
        var name = $('.clicked').find('.element-name').text().trim();
        var review = $('.clicked').find('.element-review').text().trim();

        $.ajax({
            url: '/include/modules/srv/remove.php',
            method: 'POST',
            data: {name: name, review: review},
            success: function (data) {
                notification('color-success', 'Movie removed!', 'success');
                clicked($('.clicked'));
                show();
            }
        });
    });

    $('#download').on('click', function () {
        $.ajax({
            url: '/include/modules/srv/download.php',
            method: 'POST',
            success: function (data) {
                const download = document.createElement('a');
                download.setAttribute('download', 'movies.csv');
                download.setAttribute('href', data);
                download.click();
            }
        });
    });

    $('#load').on('click', function () { $('#modal-load').modal('show'); });

    $('#charge').on('click', function () {
        var file = document.getElementById('file').files;

        if (file.length > 0) {
            var data = new FormData();
            data.append('file', file[0], rename(file[0].type));

            var request = new XMLHttpRequest();
            request.open('POST', '/include/modules/srv/charge.php', true);
            request.send(data);

            request.onreadystatechange = function () {
                if ((this.readyState === 4) && (this.status === 200)) {
                    var res = this.responseText;

                    if (res === '0') notification('color-warning', 'WARNING: CSV format required!', 'warning');
                    else {
                        if (res === '-2') notification('color-warning', 'WARNING: some invalid movie values!', 'warning');
                        else if (res === '-1') notification('color-danger', 'ERROR: some movies already exist!', 'danger');
                        else notification('color-success', 'Movies loaded!', 'success');

                        $('#file').val('');
                        $('#modal-load').modal('hide');
                        $('#offcanvas').offcanvas('hide');
                        show();
                    }
                }
            }
        }
    });

    $('#source').on('click', function () {
        const code = document.createElement('a');
        code.setAttribute('href', 'https://github.com/ciaoiomichiamoalex/movies')
        code.setAttribute('target', '_blank');
        code.click();
    });

    $('#search').on('click', function () {
        $('#search').toggleClass('border-primary');
        $('#order-selection').toggleClass('visually-hidden');
        $('#search-selection').toggleClass('visually-hidden');
        show();

        if (!$('#search-selection').hasClass('visually-hidden')) {
            $('#search-value').val('');
            $('#search-value').focus();
        }
    });

    $('#search-value').on('input', function () {
        var value = $('#search-value').val().trim();
        if (value !== '') show(value);
        else show();
    });
});

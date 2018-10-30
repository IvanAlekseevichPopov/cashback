$('.auth-required').click(function (event) {
    event.preventDefault();
    bootbox.alert({
        'message': $(this).attr('data-message'),
        'backdrop': true,
        'size': 'large'
    });
});
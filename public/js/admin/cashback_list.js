function checkAwaitingStatus(button, platformId, cashBackExtId) {
    if (0 === cashBackExtId.length) {
        alert('Не найден внешний id кешбека - невозможно сделать запрос');
        return;
    }
    $(button).button('loading');
    $.ajax({
        method: "POST",
        url: $(button).attr('data-route'),
        data: {'platformId': platformId, 'extId': cashBackExtId},
        success: function (response) {
            $(button).button('reset');
            processAwaitingResult(button, response);
        },
        error: function (response) {
            bootbox.alert('Error. '.response.responseJSON.message);
            console.log('Error: ', response);
        }
    });
}

function sendPartnershipRequest(button, platformId, cashBackExtId) {
    if (0 === cashBackExtId.length) {
        alert('Не найден внешний id кешбека - невозможно сделать запрос');
        return;
    }

    $(button).button('loading');
    $.ajax({
        method: "POST",
        url: $(button).attr('data-route'),
        data: {'platformId': platformId, 'extId': cashBackExtId},
        success: function (response) {
            $(button).button('reset');
            processPartnershipResult(button, response);
        },
        error: function (response) {
            bootbox.alert('Backend error! See console');
            console.log('Error: ', response);
        }
    });
}

function updateInfo(button, platformId, cashBackExtId) {
    if (0 === cashBackExtId.length || 0 === platformId.length) {
        bootbox.alert('Переданы пустые параметры');
        console.log(platformId);
        console.log(cashBackExtId);

        return;
    }
    $(button).button('loading');

    $.ajax({
        method: "POST",
        url: $(button).attr('data-route'),
        data: {'platformId': platformId, 'extId': cashBackExtId},
        success: function (response) {
            $(button).button('reset');
            processUpdateResult(button, response);
        },
        error: function (response) {
            bootbox.alert('Error. '.response.responseJSON.message);
            console.log('Error: ', response);
        }
    });
}

function processAwaitingResult(button, data) {
    var message = 'По прежнему в обработке.'; //TODO translator
    if ('active' === data['connection_status']) {
        message = 'Сотрудничество подтверждено! Статус обновлен';
        $(button).remove();
        updateCashback(button, data);
    } else if ('declined' === data['connection_status']) {
        message = 'Отказано в сотрудничестве. Статус обновлен';
        $(button).remove();
    }

    bootbox.alert({
        'message':
            '<h5>' + message + '</h5>' +
            '<button class="btn btn-primary btn-small" data-toggle="collapse" data-target="#demo">Посмотреть полный ответ</button>' +
            '<div id="demo" class="collapse">' +
            '<pre>' + JSON.stringify(data, null, 4) + '</pre>' +
            '</div>',
        'backdrop': true,
    });
}

function processPartnershipResult(button, data) {
    var message = '';

    if (data['connection_status'] === 'pending') {
        message = 'Запрос на партнерство еще в обработке';
    } else if (data['connection_status'] === 'active') {
        message = 'Запрос на партнерство был успешно подтвержден';
        updateCashback(button, data);
    } else if (data['connection_status'] === 'declined') {
        message = 'Запрос на партнерство был отклонен';
        $(button).closest('tr').find('.cashback-status').first().html('Отклонен');
    }

    bootbox.alert({
        'message':
            '<h5>' + message + '</h5>' +
            '<button class="btn btn-primary btn-small" data-toggle="collapse" data-target="#demo">Посмотреть полный ответ</button>' +
            '<div id="demo" class="collapse">' +
            '<pre>' + JSON.stringify(data, null, 4) + '</pre>' +
            '</div>',
        'backdrop': true,
    });
    $(button).remove();
}


function processUpdateResult(button, data) {
    var message = 'Не зафиксировано изменений. Обновление не требуется.';

    if (data['update_result'] === true) {
        message = 'Изменения внесены в кешбек';
        try {
            updateCashback(button, data);
        } catch (error) {
            console.log(error.message);
        }
    }

    bootbox.alert({
        'message':
            '<h5>' + message + '</h5>' +
            '<button class="btn btn-primary btn-small" data-toggle="collapse" data-target="#demo">Посмотреть полный ответ</button>' +
            '<div id="demo" class="collapse">' +
            '<pre>' + JSON.stringify(data, null, 4) + '</pre>' +
            '</div>',
        'backdrop': true,
    });
}

function updateCashback(button, data) {
    var parent = $(button).closest('tr');

    //Обновление категорий
    var categories = parent.find('.cashback-categories').first();
    categories.html('');
    Object.keys(data['actions']).forEach(function (key) {
        categories.append('<div class="cashback-categories">' + data['actions'][key]['name'] + ': ' + data['actions'][key]['payment_size'] + '</div>');
    });

    //Обновление url
    var url = parent.find('.cashback-url').first();
    url.html('<div class="cashback-url">' + data.gotolink + '</div>');

    //Обновление рейтинга
    var rating = parent.find('.cashback-rating').first();
    rating.html('<div class="cashback-rating">' + data.rating + '</div>');

    //Обновление статуса
    var status = parent.find('.cashback-status').first();
    var newStatus = '';
    switch (data['connection_status']) { //Это дублирование логики бекенда (
        case 'active':
            newStatus = CASHBACK_STATUSES['STATUS_APPROVED_PARTNERSHIP'];
            break;
        case 'pending':
            newStatus = CASHBACK_STATUSES['STATUS_AWAITING_PARTNERSHIP'];
            break;
        case 'declined':
            newStatus = CASHBACK_STATUSES['STATUS_REJECTED_PARTNERSHIP'];
            break;
        default:
            newStatus = CASHBACK_STATUSES['STATUS_NOT_PARTNER'];
    }

    status.html('<div class="cashback-status">' + newStatus + '</div>');
}

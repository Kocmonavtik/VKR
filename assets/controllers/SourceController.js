$(document).ready(function () {
    $('[button = processSource]').on('click', function () {
        const controller = '/source/load'
        let id = $(this).attr('id').split('Source');
        $('[button = processSource]').each(function () {
            $(this).attr('disabled', true);
        })
        $('#Action' + id[1] + '').empty().html('<div class="loader"></div>');
        $('#SourceElement' + id[1] + '').attr('class', 'table-primary');
        $('#StatusSource' + id[1] + '').html('Обрабатывается');
        sourceLoad(controller, id[1]);
    });
});

function sourceLoad(controller, id)
{
    $.ajax({
        url: controller,
        type: 'GET',
        timeout: 1700000,
        data: {
            'id': id,
        },
        success:
            function (response) {
                $('[button = processSource]').each(function () {
                    $(this).attr('disabled', false);
                })
                //$('#Action' + id + '').empty();
                if (response.code === 200) {
                    console.log('Производится обработка Xml файла');
                } else {
                    $('#SourceElement' + id + '').attr('class', 'table-danger');
                    $('#StatusSource' + id + '').html('Ошибка при обработке');
                }
            },
        error:
            function (jqXHR, status, e) {
                if (status === 'timeout') {
                    console.log('Время ожидания истекло');
                } else {
                    console.log(status);
                }
            }
    });
}



$(document).ready(function () {

    $('[button = Accept]').on('click', function () {
        const controller = '/application/accept';
        let id = $(this).attr('id').split('Accept');
        // $('#Status' + id[1] + '').html('изменено');
        // console.log($('#Status' + id[1] + '').html())
        //alert(id[1]);
      /*  let td = '<a type="button" button = "Accept" id="Accept'+ id[1] +'" class="btn btn-success">Заявка принята</a>';
        $('#Action'+ id[1] +'').html(td);
        console.log('тест');*/
        acceptApplication(controller, id[1]);
    });
    $('[button = Reject]').on('click', function () {
        const controller = '/application/reject';
        let id = $(this).attr('id').split('Reject');
        rejectApplication(controller, id[1]);
    });
    $('[button = Considered]').on('click', function () {
        const controller = '/application/considered';
        let id = $(this).attr('id').split('Considered');
        consideredApplication(controller, id[1]);
    })
});

function consideredApplication(controller, id)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'id': id,
        },
        success:
            function (response) {
                if (response.result === 200) {
                    $('#Status' + id + '').html('На рассмотрении');
                    $('#Considered' + id + '').attr('class', 'btn btn-light').attr('disabled', true);
                } else {
                    console.log(response.result);
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}

function rejectApplication(controller, id)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'id': id,
        },
        success:
            function (response) {
                if (response.result === 200) {
                    $('#Status' + id + '').html('Отклонена');
                    let td = '<a type="button" button = "Reject" id="Reject' + id + '" class="btn btn-danger" disabled>Отказано</a>';
                    $('#Action' + id + '').html(td);
                } else {
                    console.log(response.result);
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}

function acceptApplication(controller, id)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'id': id,
        },
        success:
            function (response) {
                if (response.result === 200) {
                    $('#Status' + id + '').html('Принята');
                    let td = '<a type="button" button = "Accept" id="Accept' + id + '" class="btn btn-success" disabled>Заявка принята</a>';
                    $('#Action' + id + '').html(td);
                } else {
                    console.log(response.result);
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}
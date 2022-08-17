var currentIdForReport;
//var ratingValue = 0;
var flag = false;
var currentRatingId;
var currentCommentId;
$(document).ready(function () {
    let store = $('#selectStore');
    if (store.val() === 'nothing') {
        $('#editReview').addClass('disabled');
        $('#addReview').addClass('disabled');
    } else {
        $('#editReview').removeClass('disabled');
        $('#addReview').removeClass('disabled');
    }
    $("#change-rating").rating({
        step: 1,
        starCaptions: {1: '1', 2: '2', 3: '3', 4: '4', 5: '5'},
        starCaptionClasses: {1: 'text-danger', 2: 'text-warning', 3: 'text-info', 4: 'text-primary', 5: 'text-success'},
        clearCaption: 'Без рейтинга'
    });
    $("#input-rating").rating({
        step: 1,
        starCaptions: {1: '1', 2: '2', 3: '3', 4: '4', 5: '5'},
        starCaptionClasses: {1: 'text-danger', 2: 'text-warning', 3: 'text-info', 4: 'text-primary', 5: 'text-success'},
        clearCaption: 'Без рейтинга'
    });
    $('#selectStore').on('change', function () {
        let store = $(this).val();
        if (store === 'nothing') {
            $('#editReview').addClass('disabled');
            $('#addReview').addClass('disabled');
        } else {
            $('#editReview').removeClass('disabled');
            $('#addReview').removeClass('disabled');
        }
    });


    $("#editReview").click(function () {
        $('#errorComment').empty();
        $('#errorComment').prop('hidden', true);
        if ($('#currentCommentText').val() == '') {
            $('#errorComment').append('Поле не должно быть пустым!');
            $('#errorComment').removeAttr('hidden');
        } else {
            const controller = "/comment/edit"
            let commentId = $(this).attr('name');
            let offerId = $("#selectStore option:selected").val();
            let text = $("#currentCommentText").val();
            let ratingId = $('#change-rating').rating().attr('name');
            editComment(controller,commentId, offerId, text, ratingId);
        }
    });
    $("#change-rating").rating().on('rating:clear', function (event) {
    }).on("rating:change", function (event, value, caption) {
            let ratingId = $(this).attr('name');
            const controller = '/comment/editRating';
            let offerId = $("#selectStore option:selected").val();
            let commentId = $('#currentCommentText').attr('name');
            editRating(controller, value, ratingId, offerId, commentId);
    });
    $("#addReview").on('click',function () {
        if (flag) {
            $('#errorComment').empty();
            $('#errorComment').prop('hidden', true);
            if ($('#newCommentText').val() == '') {
                $('#errorComment').append('Поле не должно быть пустым!');
                $('#errorComment').removeAttr('hidden');
            } else {
                const controller = "/comment/edit"
                let commentId = currentCommentId;
                /*console.log(commentId)*/
                let offerId = $("#selectStore option:selected").val();
                /*  console.log(offerId)*/
                let text = $("#newCommentText").val();
                let ratingId = currentRatingId;
                editComment(controller,commentId, offerId, text, ratingId);
            }
        } else {
            $('#errorComment').empty();
            $('#errorComment').prop('hidden', true);
            if ($('#newCommentText').val() == '') {
                $('#errorComment').append('Поле не должно быть пустым!');
                $('#errorComment').removeAttr('hidden');
            } else {
                const controller = '/comment/new';
                let rating = $('#input-rating').rating().val();
                let text = $('#newCommentText').val();
                let offerId = $("#selectStore option:selected").val();
                newComment(controller, rating, text, offerId);
            }
        }
    })

    $('[labelButton = "sendResponse"]').on('click', function () {
        let id = $(this).attr('id').split('buttonCollapse');
        $('#errorComment').empty();
        $('#errorComment').prop('hidden', true);
        if ($('#responseForm' + id[1] + '').val() == ''){
            $('#errorComment').append('Поле не должно быть пустым!');
            $('#errorComment').removeAttr('hidden');
        } else {
            const controller = '/comment/response/new';
            let text = $('#responseForm' + id[1] + '').val();
            newResponse(controller, id[1], text);
        }
    })
    $('[labelButton = "reportComment"]').on('click', function () {
        let id = $(this).attr('data-bs-whatever');
        currentIdForReport = id;
        /*console.log(id);*/
    })
    $('[labelButton = "offerReport"]').on('click', function () {
        let id = $(this).attr('data-bs-whatever');
        currentIdForReport = id;
        /*console.log(id);*/
    })
    $('[labelButton = sendReportComment]').on('click', function () {
        $('#errorReportComment').empty();
        $('#errorReportComment').prop('hidden', true);
        let text = $('#message-text-comment').val();
        if(text == '') {
            $('#errorReportComment').append('Поле не должно быть пустым!');
            $('#errorReportComment').removeAttr('hidden');
        } else {
            const controller = '/comment/sendReportComment';
            commentReport(controller, text);
        }
    })
    $('[labelButton = sendReportOffer]').on('click', function () {
        $('#errorReportOffer').empty();
        $('#errorReportOffer').prop('hidden', true);
        let text = $('#message-text-offer').val();
        if(text == ''){
            $('#errorReportOffer').append('Поле не должно быть пустым!');
            $('#errorReportOffer').removeAttr('hidden');
        } else {
            const controller = '/comment/sendReportOffer';
            offerReport(controller, text);
        }
    })
});

function offerReport(controller, text)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'id': currentIdForReport,
            'text': text
        },
        success:
            function (response) {
                if (response.result === 200) {
                    $('#closeOfferModal').trigger('click');
                    $('#formReportOffer')[0].reset();
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}


function commentReport(controller, text)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'id': currentIdForReport,
            'text': text
        },
        success:
            function (response) {
                if (response.result === 200) {
                    $('#closeCommentModal').trigger('click');
                    $('#formReportComment')[0].reset();
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}
function newResponse(controller, id, text)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'id': id,
            'text':text
        },
        success:
            function (response) {
                let htmlResponse = ' ' +
                '<div class="d-flex flex-start mt-4">\n' +
                    '<a class="me-3" href="#">\n' +
                    '<img class="rounded-circle shadow-1-strong"\n' +
                    'src="/upload/' + response.avatar + '" alt="avatar"\n' +
                    'width="65" height="65" />\n' +
                    '</a>\n' +
                    '<div class="flex-grow-1 flex-shrink-1">\n' +
                        '<div>\n' +
                            '<div class="d-flex justify-content-between align-items-center">\n' +
                                '<p class="mb-1">\n' +
                                '' + response.name + ' <span class="small">- ' + response.date + '</span>\n' +
                                '</p>\n' +
                            '</div>\n' +
                            '<p class="small mb-0">\n' +
                            '' + text + '\n' +
                            '</p>\n' +
                        '</div>\n' +
                        '<a class="fas fa-reply fa-xs small text-start"\n' +
                        'type="button" data-bs-toggle="modal" data-bs-target="#reportModal"\n' +
                        'data-bs-whatever="' + response.id + '" aria-expanded="false">Жалоба</a>\n' +
                    '</div>\n' +
                '</div>';
                $('#responseComments' + id + '').append(htmlResponse);
                $('#response-collapse' + id + '').dispose();
            },
        error:
            function () {
                console.log("error")
            }
    });
}

function newComment(controller, rating, text, offerId)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'value' : rating,
            'offerId': offerId,
            'text': text
        },
        success:
        function (response) {
            currentCommentId = response.commentId;
            currentRatingId = response.ratingId;
            let newComment = '' +
            '<div class="d-flex flex-start pt-3">\n' +
                '<img class="rounded-circle shadow-1-strong me-3"\n' +
                ' src="/upload/' + response.comment.avatar + '" alt="avatar" width="65"\n' +
                ' height="65" />\n' +
                '<div class="flex-grow-1 flex-shrink-1">\n' +
                    '<div>\n' +
                        '<div class="d-flex justify-content-between align-items-center">\n' +
                            '<p class="mb-1">\n' +
                            '' + response.comment.name + '<span id="dateOrigComment' + response.comment.id + '"\n' +
                            'class="small">- ' + response.comment.date + '</span>\n' +
                            '</p>\n' +
                            '<a class="fas fa-reply fa-xs small"\n' +
                            'data-bs-toggle="collapse"\n' +
                            'data-bs-target="#response-collapse' + response.comment.id + '"\n' +
                            'aria-expanded="false" role="button">Ответить<</a>\n' +
                            '<div class="collapse" id="response-collapse' + response.comment.id + '" style>\n' +
                                '<div class="form-group">\n' +
                                    '<textarea class="form-control" id="responseForm' + response.comment.id + '" rows="3"></textarea>\n' +
                                '</div>\n' +
                                '<button type="submit" id="buttonCollapse' + response.comment.id + '" class="btn btn-primary mt-3">Отправить</button>\n' +
                            '</div>\n' +
                        '</div>\n' +
                        '<p id="textOrigComment' + response.comment.id + '" class="small mb-0">' + response.comment.text + '</p>\n' +
                    '</div>\n' +
                    '<a class="fas fa-reply fa-xs small text-end"\n' +
                    'type="button" data-bs-toggle="modal" data-bs-target="#reportModal"\n' +
                    'data-bs-whatever="' + response.comment.id + '" aria-expanded="false">Жалоба</a>\n' +
                    '<div id="responseComments' + response.comment.id + '">\n' +
                    '</div>\n' +
                '</div>\n' +
            '</div>\n';
            $('#commentList').prepend(newComment);
            flag = true;
            $('#addReview').html('Изменить отзыв');
            $('#AverageRating' + offerId + '').text('Средняя оценка: ' + response.avgRating);
         /*   $('#addReview').attr('name', response.comment.id);
            $('#input-rating').rating().attr('name', response.ratingId)*/
        },
        error:
            function () {
                console.log("error")
            }
        });
}

function editRating(controller, value ,ratingId, offerId, commentId)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'value': value,
            'ratingId' : ratingId,
            'offerId': offerId,
            'commentId': commentId
        },
        success:
        function (response) {
            $('#AverageRating' + offerId + '').text('Средняя оценка: ' + response.ratingNew);
            $('#AverageRating' + response.oldOfferId + '').text('Средняя оценка: ' + response.ratingOld);
        },
        error:
            function () {
                console.log("error")
            }
        });
}

function editComment(controller, commentId, offerId, text, ratingId)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'id': commentId,
            'offer' : offerId,
            'text': text,
            'ratingId' : ratingId
        },
        success:
        function (response) {
            if (response.result === 200) {
                //dateElement.textContent =
                var now = new Date();
                let date = ("0" + (now.getUTCDate())).slice(-2) + "." +
                ("0" + (now.getUTCMonth() + 1)).slice(-2) + "." +
                now.getUTCFullYear() + ", " +
                ("0" + (now.getUTCHours())).slice(-2) + ":" +
                ("0" + (now.getUTCMinutes())).slice(-2);
                $('#dateOrigComment' + commentId + '').text('- ' + date);
                $('#textOrigComment' + commentId + '').text(text);
                $('#AverageRating' + offerId + '').text('Средняя оценка: ' + response.ratingNew);
                $('#AverageRating' + response.oldOfferId + '').text('Средняя оценка: ' + response.ratingOld);
            }
        },
        error:
            function () {
                console.log("error")
            }
        });
}
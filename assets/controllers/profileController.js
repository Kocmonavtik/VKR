let currentIdForSource;
$(document).ready(function () {

    $('#SaveProfile').click(function () {
        let name = $('#inputName').val();
        let gender = $('#selectGender option:selected').val();
        let email = $('#inputEmail').val();
        var fd = new FormData();
        var files = $('#avatarFile')[0].files;
        fd.append('file',files[0]);
        fd.append('name', name);
        fd.append('gender', gender)
        fd.append('email', email)
        const controller = '/profile/save'
        $('#error').empty();
        $('#error').prop('hidden', true);
        saveProfile(controller, fd)
    })
    $('#SavePassword').click(function () {
        var fd = new FormData();
        fd.append('currentPass',$('#currentPassword').val());
        fd.append('newPass',  $('#newPassword').val());
        fd.append('repeatPass',$('#repeatPassword').val());
        const controller = '/profile/changePass';
        $('#errorPass').empty().prop('hidden', true);
        $('#successPass').prop('hidden',true);
        changePass(controller,fd)
    });
    $('#sendApplication').click(function () {
        const controller = '/profile/application/send';
        var fd = new FormData();
        var files = $('#logoFile')[0].files;
        fd.append('fullName', $('#FIO-modal').val());
        fd.append('nameStore', $('#Store-name-modal').val());
        fd.append('logoFile', files[0]);
        fd.append('urlStore', $('#URL-store-modal').val());
        $('#errorModal').empty();
        $('#errorModal').prop('hidden', true);
        sendApplic(controller,fd);
    });
    //button="ChangeSource"
    $('[button = ChangeSource]').on('click', function () {
        let id = $(this).attr('id').split('Source');
        currentIdForSource = id[1];
    })
    $('#sendSource').on('click', function () {
        const controller = '/profile/source/change';
        $('#errorSourceModal').empty();
        $('#errorSourceModal').prop('hidden', true);
        let url = $('#URL-xml-modal').val();
        changeSource(controller, currentIdForSource, url);
    })
});

function changeSource(controller, id, url)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            'id': id,
            'url': url
        },
        success:
            function (response) {
                if (response.result === 200) {
                    $('#SourceElement' + id + '').attr('class', 'table-warning');
                    $('#StatusSource' + id + '').html('В очереди');
                    $('#UrlSource' + id + '').html(url);
                    $('#closeSourceModal').trigger('click');
                    $('#sourceForm')[0].reset();
                } else {
                    $('#errorSourceModal').append(response.result);
                    $('#errorSourceModal').removeAttr('hidden');
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}

function sendApplic(controller, fd)
{
    $.ajax({
        url: controller,
        type: 'POST',
        processData: false,
        contentType: false,
        data: fd,
        success:
            function (response) {
                if (response.result === 200) {
                    let application = '' +
                        '<tr>\n' +
                            '<td>' + response.nameStore + '</td>\n' +
                            '<td>' + response.fullName + '</td>\n' +
                            '<td>' + response.url + '</td>\n' +
                            '<td>' + response.status + '</td>\n' +
                        '</tr>'
                    $('#applicationList').append(application);
                    $('#closeModal').trigger('click');
                    $('#applicationForm')[0].reset();
                    /*$('#successPass').removeAttr('hidden');*/
                } else {
                    $('#errorModal').append(response.result);
                    $('#errorModal').removeAttr('hidden');
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}

function changePass(controller, fd)
{
    $.ajax({
        url: controller,
        type: 'POST',
        processData: false,
        contentType: false,
        data: fd,
        success:
            function (response) {
                if (response.result === 200) {
                    //location.reload();
                    //$('#successPass').append('Пароль успешно изменён!');
                    $('#successPass').removeAttr('hidden');
                } else {
                    $('#errorPass').append(response.result);
                    $('#errorPass').removeAttr('hidden');
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}
function saveProfile(controller, fd)
{
    $.ajax({
        url: controller,
        type: 'POST',
        processData: false,
        contentType: false,
        data: fd,
        success:
            function (response) {
                if (response.result) {
                    $('#error').append(response.result);
                    $('#error').removeAttr('hidden');
                } else {
                    $('#username').empty().append(response.name)
                    $('#userEmail').empty().append(response.email)
                    $('#avatar').attr("src", '/upload/' + response.avatar + '')
                }
            },
        error:
            function () {
                console.log("error")
            }
    });
}
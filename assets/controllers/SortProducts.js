/*$(document).on("change", '#selectSort', function () {
    const controller = "/about/filters";
    let select = document.getElementById("selectSort");
    let value = select.options[select.selectedIndex].value;
    sendajax(controller, value)
    setInterval(sendajax, 3000, controller, value);
});*/
/*$(document).ready(function () {
    const controller = "/about/filters";
    let select = document.getElementById("selectSort");
    let value = select.options[select.selectedIndex].value;
    sendajax(controller, value)
    setInterval(sendajax, 3000, controller, value);
});*/
$(document).ready(function () {
    const controller = "/about/filters";
    let select = document.getElementById("selectSort");
    let value = select.options[select.selectedIndex].value;
    sendajax(controller, value)
    $('#selectSort').on("change", function () {
        document.getElementById('productList').innerHTML = ''; //удаление прошлых данных
        const controller = "/about/filters";
        let select = document.getElementById("selectSort");
        let value = select.options[select.selectedIndex].value;
        sendajax(controller, value)
      /*  setInterval(sendajax, 3000, controller, value);*/ // нужно для бесконечного обновления информации
    })
   /* const controller = "/about/filters";
    let select = document.getElementById("selectSort");
    let value = select.options[select.selectedIndex].value;
    sendajax(controller, value)*/
});

function sendajax(controller, value)
{
    let data = new FormData();
    data.append('filter', value);

    $.ajax({
        url: controller,
        type: 'POST',
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        data: data,
        success:
            function (response) {
                buildHtml(response);
            },
        error:
            function () {
                console.log("error")
            }
    });
}
function buildHtml(response)
{
    if (response.pagination.length === 0) {
        let productNotExists =
            '<div>' +
            '<p class="text-center text-warning">Товары не найдены!</p>'
        '</div>'
        $("#productList").append(productNotExists)
    } else {
        for (var i = 0; i < response.pagination.length; ++i) {
            let divOne = document.createElement('div');
            divOne.className = "card mb-3";
            divOne.style = "max-width: 840px;";
            let divTwo = document.createElement('div');
            divTwo.className = "row g-0";
            let divThree = document.createElement('div');
            divThree.className = "col-md-4";
            let divCarousel = document.createElement('div');
            divCarousel.id = 'carousel(' + response.pagination[i].id + ')';
            divCarousel.className = 'carousel slide';
            divCarousel.dataset.bsRide = 'carousel';
            let divCarouselIndicators = document.createElement('div');
            divCarouselIndicators.className = "carousel-indicators";
            //divCarouselIndicators.id='images('+ response.pagination[i].id +')';
            let divCarouselInner= document.createElement('div');
            divCarouselInner.className="carousel-inner";

            //$("#productList").append(elementOne)
            var items = response.pagination[i].images;
            var count = items.length
            for (var j = 0; j < count; ++j) {
                if (j === 0) {
                    let buttonCarousel = '<button type="button"' +
                        ' data-bs-target="#carousel(' + response.pagination[i].id + ')"' +
                        ' data-bs-slide-to="(' + j + ')" class="active"' +
                        ' aria-current="true" aria-label="Slide (' + j + ')"></button>'
                    //$("#productList").append(elementTwo)
                    divCarouselIndicators.innerHTML = divCarouselIndicators.innerHTML + buttonCarousel
                   /* divCarouselIndicators.append(buttonCarousel);*/

                } else {
                    let buttonCarousel = '<button type="button"' +
                        ' data-bs-target="#carousel(' + response.pagination[i].id + ')"' +
                        ' data-bs-slide-to="(' + j + ')" aria-label="Slide (' + j + ')"></button>'
                    //$("#productList").append(elementTwo)
                    //divCarouselIndicators.append(buttonCarousel);
                    divCarouselIndicators.innerHTML = divCarouselIndicators.innerHTML + buttonCarousel
                }
                /*let element = '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                $("#productList").append(element)*/
            }


            $("#productList").append(divOne);
            divOne.append(divTwo);
            divTwo.append(divThree);
            divThree.append(divCarousel);
            divCarousel.append(divCarouselIndicators);
            divCarousel.append(divCarouselInner);
        }
        /*for (var i = 0; i < response.pagination.length; ++i) {
            let elementOne =
                '<div class="card mb-3" style="max-width: 840px;">' +
                    '<div class="row g-0">' +
                        '<div class="col-md-4">' +
                            '<div id="carousel(' + response.pagination[i].id + ')" class="carousel slide" data-bs-ride="carousel">' +
                                '<div class="carousel-indicators" id="images(' + response.pagination[i].id + ')">'; /!*+*!/
                             /!*   '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'*!/
            $("#productList").append(elementOne)
            var items = response.pagination[i].images;
            var count = items.length
            for (var j = 0; j < count; ++j) {
                if (j === 0) {
                    let elementTwo = '<button type="button"' +
                        ' data-bs-target="#carousel(' + response.pagination[i].id + ')"' +
                        ' data-bs-slide-to="(' + j + ')" class="active"' +
                        ' aria-current="true" aria-label="Slide (' + j + ')"></button>'
                    $("#productList").append(elementTwo)
                } else {
                    let elementTwo = '<button type="button"' +
                        ' data-bs-target="#carousel(' + response.pagination[i].id + ')"' +
                        ' data-bs-slide-to="(' + j + ')" aria-label="Slide (' + j + ')"></button>'
                    $("#productList").append(elementTwo)
                }
                let element = '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                $("#productList").append(element)
            }
        }*/
    }
}

/*
for (var j = 0; j < response.images[response.pargination[i].id]; ++j) {
    if (j === 0) {
        let elementTwo = '<button type="button"' +
            ' data-bs-target="#carousel(' + response.pagination[i].id + ')"' +
            ' data-bs-slide-to="(' + j + ')" class="active"' +
            ' aria-current="true" aria-label="Slide (' + j + ')"></button>'

        $('#images(' + response.pagination[i].id + ')').append(elementTwo)
    } else {
        let elementTwo = '<button type="button"' +
            ' data-bs-target="#carousel(' + response.pagination[i].id + ')"' +
            ' data-bs-slide-to="(' + j + ')" aria-label="Slide (' + j + ')"></button>'

        $('#images(' + response.pagination[i].id + ')').append(elementTwo);
    }
}*/

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
    let valueFilter = select.options[select.selectedIndex].value;
    let page = 1;
    sendajax(controller, valueFilter, page)
    $('#selectSort').on("change", function () {
        document.getElementById('productList').innerHTML = ''; //удаление прошлых данных
        document.getElementById('paginationList').innerHTML = '';
        const controller = "/about/filters";
        let select = document.getElementById("selectSort");
        let value = select.options[select.selectedIndex].value;
        sendajax(controller, value, page);
      /*  setInterval(sendajax, 3000, controller, value);*/ // нужно для бесконечного обновления информации
    })
    $('#paginationList').on("click", "li", function () {
        let page = $(this).text();
        document.getElementById('productList').innerHTML = ''; //удаление прошлых данных
        document.getElementById('paginationList').innerHTML = '';
        const controller = "/about/filters";
        let select = document.getElementById("selectSort");
        let value = select.options[select.selectedIndex].value;
        sendajax(controller, value, page);
    })
});

function sendajax(controller, valueFilter, page)
{

    $.ajax({
        url: controller,
        type: 'GET',
        data: {
          page: page,
          filter: valueFilter
        },
        success:
            function (response) {
                buildHtml(response, page);
            },
        error:
            function () {
                console.log("error")
            }
    });
}
function buildHtml(response, page)
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
            divCarousel.id = 'carousel' + response.pagination[i].id + '';
            divCarousel.className = 'carousel slide';
            divCarousel.dataset.bsRide = 'carousel';
            let divCarouselIndicators = document.createElement('div');
            divCarouselIndicators.className = "carousel-indicators";
            let divCarouselInner = document.createElement('div');
            divCarouselInner.className = "carousel-inner";


            var items = response.pagination[i].images;
            var count = items.length
            for (var j = 0; j < count; ++j) {
                if (j === 0) {
                    let buttonCarousel = '<button type="button"' +
                        ' data-bs-target="#carousel' + response.pagination[i].id + '"' +
                        ' data-bs-slide-to="' + j + '" class="active"' +
                        ' aria-current="true" aria-label="Slide ' + j + '"></button>'

                    divCarouselIndicators.innerHTML = divCarouselIndicators.innerHTML + buttonCarousel
                } else {
                    let buttonCarousel = '<button type="button"' +
                        ' data-bs-target="#carousel' + response.pagination[i].id + '"' +
                        ' data-bs-slide-to="' + j + '" aria-label="Slide ' + j + '"></button>'

                    divCarouselIndicators.innerHTML = divCarouselIndicators.innerHTML + buttonCarousel
                }
            }
            for (var j = 0; j < count; ++j) {
                let link = '/product/' + response.pagination[i].id;
                let image = '/upload/pictures/' + response.pagination[i].images[0];

                if (j === 0) {
                    let carouselItem = '<div class="carousel-item active">' +
                        '<a href="' + link + '"><img src="' + image + '" class="img-fluid rounded-start" alt="..."></a>' +
                        '</div>'
                    divCarouselInner.innerHTML = divCarouselInner.innerHTML + carouselItem;
                } else {
                    let carouselItem = '<div class="carousel-item">' +
                        '<a href="' + link + '"><img src="' + image + '" class="img-fluid rounded-start" alt="..."></a>' +
                        '</div>'
                    divCarouselInner.innerHTML = divCarouselInner.innerHTML + carouselItem;
                }
            }
            let buttons = '<button class="carousel-control-prev" type="button" data-bs-target="#carousel' + response.pagination[i].id + '" data-bs-slide="prev">\n' +
                    '<span class="carousel-control-prev-icon" aria-hidden="true"></span>\n' +
                    '<span class="visually-hidden">Previous</span>\n' +
                '</button>\n' +
                '<button class="carousel-control-next" type="button" data-bs-target="#carousel' + response.pagination[i].id + '" data-bs-slide="next">\n' +
                    ' <span class="carousel-control-next-icon" aria-hidden="true"></span>\n' +
                    ' <span class="visually-hidden">Next</span>\n' +
                ' </button>'
            let id = response.pagination[i].id;
            let link = '/product/' + id;
            let infoHeader = document.createElement('div');
            infoHeader.className = "col-md-8";
            let infoProduct = document.createElement('div');
            infoProduct.className = "card-body";
            let innerInfoProduct =
                '<h5 class="card-title"><a  class="text-decoration-none" href="' + link + '">' + response.pagination[i].name + '</a></h5>\n' +
                '<h5 class="card-title">Рейтинг: ' + Number(response.ratingProducts[id]).toFixed(1) + '</h5>\n' +
                '<h5 class="card-title">Лучшая цена: ' + Number(response.productMinValue[id]).toFixed(0) + '</h5>\n';
            let arrayProperties = '';
            for (var key in response.pagination[i].properties) {
                arrayProperties += '<p class="card-text">' + key + ': ' + response.pagination[i].properties[key] + '</p>'
            }
            infoProduct.innerHTML = innerInfoProduct + arrayProperties;
            infoHeader.append(infoProduct);

            $("#productList").append(divOne);
            divOne.append(divTwo);
            divTwo.append(divThree);
            divTwo.append(infoHeader);
            divThree.append(divCarousel);
            divCarousel.append(divCarouselIndicators);
            divCarousel.append(divCarouselInner);
            divCarouselInner.innerHTML += buttons;
        }

        let pageItems = '';
        for (var j = 0; j < response.colPages; ++j) {
            if (j === (page - 1)) {
                pageItems += '  <li class="page-item active"><a class="page-link" href="#">' + (j + 1) + '</a></li>'
            } else {
                pageItems += '  <li class="page-item"><a class="page-link" href="#">' + (j + 1) + '</a></li>'
            }
        }
        $("#paginationList").append(pageItems);
    }
}

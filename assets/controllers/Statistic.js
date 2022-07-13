import {Chart} from "chart.js";
/*import {Star} from "bootstrap-star-rating"*/
/*require("bootstrap-star-rating");
require("bootstrap-star-rating/css/star-rating.css");*/

$(document).ready(function () {
    const controller = "/statistic/filtersData";
    $("#input-2").rating();
    /* let select = document.getElementById("selectSort");
     let valueFilter = select.options[select.selectedIndex].value;
     let page = 1;*/
     let chart = new Chart(document.getElementById("Chart"), { type: "bar"});
    dataajax(controller)
    $('#selectCategory').on("change", function () {
        //let category = $(this).options[$(this).selectedIndex].value;
        let category = $(this).val();
        let statisticData = $("#selectStatisticData").val();
        let button = document.getElementById('createChart');
        if (category === 'nothing' || statisticData === 'nothing') {
            button.className = 'btn btn-success btn-lg disabled';
        } else {
            button.className = 'btn btn-success btn-lg';
        }
        document.getElementById('storeList').innerHTML = '';
        document.getElementById('brandList').innerHTML = '';
        const controller = "/statistic/category";
        changeCategoryAjax(controller, category);
    })

    $('#selectStatisticData').on("change", function () {
        let statisticData = $(this).val();
        let category = $("#selectCategory").val();
        let button = document.getElementById('createChart');
        if (category === 'nothing' || statisticData === 'nothing') {
            button.className = 'btn btn-success btn-lg disabled';
        } else if (statisticData === 'rating') {
            button.className = 'btn btn-success btn-lg';
        } else if (statisticData === 'visit') {
            button.className = 'btn btn-success btn-lg';
        }
    })

    $('#createChart').click(function () {
        let dataStatistic = $("#selectStatisticData").val();
        let dateFirst = $("#date_dateFirst").val();
        let dateSecond=$("#date_dateSecond").val();
       /* switch (dataStatistic) {
            case 'rating':
                var statisic= dataStatistic;
                break;
            case 'visit':
                var statistic = dataStatistic
                break;
        }*/
        var stores = [];
        $('.storeCheck:checkbox:checked').each(function () {
            stores.push($(this).val());
        })
        if (stores.length === 0) {
            $('.storeCheck:checkbox:not(:checked)').each(function () {
                stores.push($(this).val());
            })
        }
        var manufacturers = [];
        $('.brandCheck:checkbox:checked').each(function () {
            manufacturers.push($(this).val());
        })
        if (manufacturers.length === 0) {
            $('.brandCheck:checkbox:not(:checked)').each(function () {
                manufacturers.push($(this).val());
            })
        }
        var category = $('#selectCategory').val();
       /* alert(stores);
        alert(manufacturers);*/
        //alert($('.storeCheck:checkbox:checked'));
        const controller = '/statistic/getData';
        createChartAjax(controller, dataStatistic, stores, manufacturers, category, chart, dateFirst, dateSecond);



      /*  let chart = new Chart(document.getElementById("Chart"));
        chart.config.type='bar';*/
      /*  chart.config.data={}*/
        //const ctx = document.getElementById('densityChart').getContext('2d');
      /*  new Chart(document.getElementById("Chart"), {
            type: 'bar',
            data: {
                labels: ["1900", "1950", "1999", "2050"],//список брендов
                datasets: [
                    {
                        label: "Africa",
                        backgroundColor: "#3e95cd",
                        data: [133,221,783,2478]//для одного магазина в массиве рейтинг каждого бренда
                }, {
                    label: "Europe",
                    backgroundColor: "#8e5ea2",
                    data: [408,547,675,734]//тоже самое
                }
                ]
            },
            options: {
                title: {
                    display: true,
                    text: 'Population growth (millions)'
                }
            }
        });
*/

        /*const densityData = {
            label: 'Density of Planet (kg/m3)',
            data: [5427, 5243, 5514, 3933, 1326, 687, 1271, 1638],
            backgroundColor: 'rgba(0, 99, 132, 0.6)',
            borderColor: 'rgba(0, 99, 132, 1)',
            yAxisID: "y-axis-density"
        };

        const gravityData = {
            label: 'Gravity of Planet (m/s2)',
            data: [3.7, 8.9, 9.8, 3.7, 23.1, 9.0, 8.7, 11.0],
            backgroundColor: 'rgba(99, 132, 0, 0.6)',
            borderColor: 'rgba(99, 132, 0, 1)',
            yAxisID: "y-axis-gravity"
        };

        const planetData = {
            labels: ["Mercury", "Venus", "Earth", "Mars", "Jupiter", "Saturn", "Uranus", "Neptune"],
            datasets: [densityData, gravityData]
        };

        const chartOptions = {
            scales: {
                xAxes: [{
                    barPercentage: 1,
                    categoryPercentage: 0.6
                }],
                yAxes: [{
                    id: "y-axis-density"
                }, {
                    id: "y-axis-gravity"
                }]
            }
        };

        const barChart = new Chart(ctx, {
            type: 'bar',
            data: planetData,
            options: chartOptions
        });*/
    });
})

function createChartAjax(controller, dataStatistic, stores, manufacturers, category, chart,dateFirst, dateSecond)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data:{
            dataType: dataStatistic,
            stores: stores,
            manufacturers: manufacturers,
            category: category,
            dateFirst: dateFirst,
            dateSecond: dateSecond
        },
        success:
            function (response) {
                /*new Chart(document.getElementById("Chart"), {
                    type: 'bar',
                    data: {
                        labels: ["1900", "1950", "1999", "2050"],//список брендов
                        datasets: [
                            {
                                label: "Africa",
                                backgroundColor: "#3e95cd",
                                data: [133,221,783,2478]//для одного магазина в массиве рейтинг каждого бренда
                            }, {
                                label: "Europe",
                                backgroundColor: "#8e5ea2",
                                data: [408,547,675,734]//тоже самое
                            }
                        ]
                    },
                    options: {
                        title: {
                            display: true,
                            text: 'Population growth (millions)'
                        }
                    }
                });*/

                if (dataStatistic === 'rating') {
                    var text = 'Статистика рейтинга по категории' + category;
                } else {
                    var text = 'Cтатистика посещений по категории' + category;
                }
                let datasetsMas = [];
                for (var key in response.result) {
                    let datasetElement = {
                        label: key,
                        backgroundColor: createRandomColor(),
                        data: response.result[key]
                    }
                    datasetsMas.push(datasetElement);
                }

                chart.data.labels = manufacturers;
                chart.data.datasets = datasetsMas;
                chart.options = {
                    title: {
                        display: true,
                        text: text
                    }
                }
                chart.update();
               /* new Chart(document.getElementById("Chart"), {
                    type: 'bar',
                    data: {
                        labels: manufacturers,//список брендов
                        datasets: datasetsMas
                    },
                    options: {
                        title: {
                            display: true,
                            text: text
                        }
                    }
                });*/
            },
        error:
            function () {
                console.log("error")
            }
    });
}



function changeCategoryAjax(controller, category)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data:{
            category: category
        },
        success:
            function (response) {
                /* buildHtml(response);*/
                let stores = '';
                for (var key in response.stores) {
                    stores += '<div class="form-check">\n' +
                        '                    <input class="form-check-input storeCheck" type="checkbox" value="' + response.stores[key].name_store + '" id="flexCheckDefault">\n' +
                        '                        <label class="form-check-label" for="flexCheckDefault">\n' +
                        '                            ' + response.stores[key].name_store + '' +
                        '                        </label>\n' +
                        '                </div>'
                }
                $('#storeList').append(stores)

                let brand = '';
                for (var key in response.manufacturers) {
                    brand += '<div class="form-check">\n' +
                        '                    <input class="form-check-input brandCheck" type="checkbox" value="' + response.manufacturers[key].name + '" id="flexCheckDefault">\n' +
                        '                        <label class="form-check-label" for="flexCheckDefault">\n' +
                        '                            ' + response.manufacturers[key].name + '' +
                        '                        </label>\n' +
                        '                </div>'
                }
                $('#brandList').append(brand);

            },
        error:
            function () {
                console.log("error")
            }
    });
}


function dataajax(controller)
{
    $.ajax({
        url: controller,
        type: 'GET',
        success:
            function (response) {
                let categories = $('#selectCategory').innerHTML;
                for (var key in response.categories) {
                    categories += ' <option value="' + response.categories[key].name + '">' + response.categories[key].name + '</option>';
                }
                $('#selectCategory').append(categories);

                let stores = '';
                for (var key in response.stores) {
                    stores += '<div class="form-check">\n' +
                        '                    <input class="form-check-input storeCheck" type="checkbox" value="' + response.stores[key].name_store + '" id="flexCheckDefault">\n' +
                        '                        <label class="form-check-label" for="flexCheckDefault">\n' +
                        '                            ' + response.stores[key].name_store + '' +
                        '                        </label>\n' +
                        '                </div>'
                }
                $('#storeList').append(stores)
                let brand = '';
                for (var key in response.manufacturers) {
                    brand += '<div class="form-check">\n' +
                        '                    <input class="form-check-input brandCheck" type="checkbox" value=" ' + response.manufacturers[key].name + '" id="flexCheckDefault">\n' +
                        '                        <label class="form-check-label" for="flexCheckDefault">\n' +
                        '                            ' + response.manufacturers[key].name + '' +
                        '                        </label>\n' +
                        '                </div>'
                }
                $('#brandList').append(brand);
            },
        error:
            function () {
                console.log("error")
            }
    });
}
function createRandomColor()
{

    var r = Math.floor(Math.random() * (256));
    var g = Math.floor(Math.random() * (256));
    var b = Math.floor(Math.random() * (256));
    var color = '#' + r.toString(16) + g.toString(16) + b.toString(16);
    return color;
}
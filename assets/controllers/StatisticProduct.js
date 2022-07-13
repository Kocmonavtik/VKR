import {Chart} from "chart.js";

$(document).ready(function () {
    let chart = new Chart(document.getElementById("Chart"), { type: "bar"});
    $('#selectStatisticData').on("change", function () {
        let statisticData = $(this).val();
        let button = document.getElementById('createChart');
        if (statisticData === 'nothing') {
            button.className = 'btn btn-success btn-lg disabled';
        } else {
            button.className = 'btn btn-success btn-lg';
        }
    })

    $('#createChart').click(function () {
        let dataStatistic = $("#selectStatisticData").val();
        let dateFirst = $("#date_dateFirst").val();
        let dateSecond = $("#date_dateSecond").val();

        var stores = [];
        $('.storeCheck:checkbox:checked').each(function () {
            stores.push($(this).val());
        })
        if (stores.length === 0) {
            $('.storeCheck:checkbox:not(:checked)').each(function () {
                stores.push($(this).val());
            })
        }
        //let productId = $("#cardProduct").val();
        let productId = $("#hiddenId").val();
        const controller = '/statistic/productData';
      /*  alert(controller)
        alert(dataStatistic)
        alert(stores)
        alert(chart)
        alert(productId)
        alert(dateFirst)
        alert(dateSecond)*/
        //alert(productId)
        createChart(controller, dataStatistic, stores, chart, productId, dateFirst, dateSecond);
    });
})
function createChart(controller, dataStatistic, stores, chart, productId ,dateFirst, dateSecond)
{
    $.ajax({
        url: controller,
        type: 'GET',
        data: {
            dataType: dataStatistic,
            stores: stores,
            productId: productId,
            dateFirst: dateFirst,
            dateSecond: dateSecond
        },
        success:
            function (response) {
                if (dataStatistic === 'rating') {
                    var textBar = 'Статистика рейтинга по товару';
                    var labelText = 'Рейтинг товара'
                } else {
                    var textBar = 'Cтатистика посещений по товару';
                    var labelText = 'Просмотры товара'
                }
                let dataMas = [];
                let backgroundColorChart = [];
                for (var key in response.result) {
                    dataMas.push(response.result[key])
                    backgroundColorChart.push(createRandomColor())
                   /* let datasetElement = {
                        label: labelText,
                        backgroundColor: createRandomColor(),
                        data: response.result[key]
                    }
                    datasetsMas.push(datasetElement);*/
                }

                chart.data.labels = stores;
                chart.data.datasets = [{
                    label: labelText,
                    backgroundColor: createRandomColor(),
                    data: dataMas
                }];
                chart.options = {
                    title: {
                        display: true,
                        text: textBar
                    }
                }
                chart.update();
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

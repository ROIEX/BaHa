$(document).ready(function() {
    renderHosLogStatus();

    renderOnTimePercentage();

    renderDvirStatus();

    renderDriverRatings();
});

function renderHosLogStatus() {
    new Chartist.Bar('#hos-log-status', {
        labels: hos_log_statuses_labels,
        series: hos_log_statuses_data
    }, {
        stackBars: true,
        axisY: {
            labelInterpolationFnc: function(value) {
                return (value) + '%';
            }
        }
    }).on('draw', function(data) {
            if(data.type === 'bar') {
                data.element.attr({
                    style: 'stroke-width: 30px'
                });
            }
        });
}

function renderOnTimePercentage() {
    // on-time percentage
    var data = {
        series: on_time_percentage_data
    };

    new Chartist.Pie('#on-time-percentage', data, {
        labelInterpolationFnc: function(value) {
            //return Math.round(value / data.series.reduce(sum) * 100) + '%';
            return value + '%';
        }
    });
}

function renderDvirStatus() {
    var sum = function(a, b) {
        if (isNaN(a)) a = a.reduce(sum);
        if (isNaN(b)) b = b.reduce(sum);
        return parseInt(a) + parseInt(b);
    };

    var data = {series: dvir_status_data, labels: dvir_status_labels};

    new Chartist.Bar('#dvir-status', data, {
        stackBars: true,
        axisY: {
            labelInterpolationFnc: function(value) {
                var total = data.series.reduce(sum);
                return Math.round(value / total * 100) + '%';
            }
        }
    }).on('draw', function(data) {
            if(data.type === 'bar') {
                data.element.attr({
                    style: 'stroke-width: 30px'
                });
            }
        });
}

function renderDriverRatings() {
    new Chartist.Bar('#driver-ratings', {
        labels: ['1 star', '2 stars', '3 stars', '4 start', '5 stars'],
        series: driver_rating
    }, {
        stackBars: true,
        horizontalBars: true,
        seriesBarDistance: 10,
        reverseBars: true,
        axisY: {
            offset: 70
        }
    });
}
// Chart Initializations
var lineChart = document.getElementById("lineChart").getContext("2d"),
    barChart = document.getElementById("barChart").getContext("2d"),
    pieChart = document.getElementById("pieChart").getContext("2d"),
    doughnutChart = document.getElementById("doughnutChart").getContext("2d"),
    radarChart = document.getElementById("radarChart").getContext("2d"),
    bubbleChart = document.getElementById("bubbleChart").getContext("2d"),
    multipleLineChart = document.getElementById("multipleLineChart").getContext("2d"),
    multipleBarChart = document.getElementById("multipleBarChart").getContext("2d"),
    htmlLegendsChart = document.getElementById("htmlLegendsChart").getContext("2d");

// Line Chart
var myLineChart = new Chart(lineChart, {
    type: "line",
    data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
            label: "Active Users",
            borderColor: "#1d7af3",
            pointBorderColor: "#FFF",
            pointBackgroundColor: "#1d7af3",
            pointBorderWidth: 2,
            pointHoverRadius: 4,
            pointHoverBorderWidth: 1,
            pointRadius: 4,
            backgroundColor: "transparent",
            fill: true,
            borderWidth: 2,
            data: [542, 480, 430, 550, 530, 453, 380, 434, 568, 610, 700, 900]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            position: "bottom",
            labels: { padding: 10, fontColor: "#1d7af3" }
        },
        tooltips: {
            bodySpacing: 4,
            mode: "nearest",
            intersect: 0,
            position: "nearest",
            xPadding: 10,
            yPadding: 10,
            caretPadding: 10
        },
        layout: { padding: { left: 15, right: 15, top: 15, bottom: 15 } }
    }
});

// Bar Chart
var myBarChart = new Chart(barChart, {
    type: "bar",
    data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
            label: "Sales",
            backgroundColor: "rgb(23, 125, 255)",
            borderColor: "rgb(23, 125, 255)",
            data: [3, 2, 9, 5, 4, 6, 4, 6, 7, 8, 7, 4]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { yAxes: [{ ticks: { beginAtZero: true } }] }
    }
});

// Pie Chart
var myPieChart = new Chart(pieChart, {
    type: "pie",
    data: {
        datasets: [{
            data: [50, 35, 15],
            backgroundColor: ["#1d7af3", "#f3545d", "#fdaf4b"],
            borderWidth: 0
        }],
        labels: ["New Visitors", "Subscribers", "Active Users"]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            position: "bottom",
            labels: { fontColor: "rgb(154, 154, 154)", fontSize: 11, usePointStyle: true, padding: 20 }
        },
        pieceLabel: { render: "percentage", fontColor: "white", fontSize: 14 },
        tooltips: false,
        layout: { padding: { left: 20, right: 20, top: 20, bottom: 20 } }
    }
});

// Doughnut Chart
var myDoughnutChart = new Chart(doughnutChart, {
    type: "doughnut",
    data: {
        datasets: [{
            data: [60, 30, 10],
            backgroundColor: ["#1d7af3", "#f3545d", "#fdaf4b"]
        }],
        labels: ["Used Storage", "System Storage", "Available Storage"]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            position: "bottom",
            labels: { fontColor: "rgb(154, 154, 154)", fontSize: 11, usePointStyle: true, padding: 20 }
        },
        pieceLabel: { render: "percentage", fontColor: "white", fontSize: 14 },
        tooltips: false,
        layout: { padding: { left: 20, right: 20, top: 20, bottom: 20 } }
    }
});

// Radar Chart
var myRadarChart = new Chart(radarChart, {
    type: "radar",
    data: {
        labels: ["Running", "Swimming", "Eating", "Cycling"],
        datasets: [{
            label: "Activity",
            borderColor: "#1d7af3",
            backgroundColor: "rgba(29, 122, 243, 0.25)",
            data: [20, 10, 4, 2]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { position: "bottom" }
    }
});

// Bubble Chart
var myBubbleChart = new Chart(bubbleChart, {
    type: "bubble",
    data: {
        datasets: [{
            label: "Dataset 1",
            borderColor: "#1d7af3",
            backgroundColor: "rgba(29, 122, 243, 0.25)",
            data: [{ x: 20, y: 30, r: 15 }, { x: 25, y: 10, r: 10 }, { x: 15, y: 40, r: 20 }]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { xAxes: [{ ticks: { beginAtZero: true } }], yAxes: [{ ticks: { beginAtZero: true } }] }
    }
});

// Multiple Line Chart
var myMultipleLineChart = new Chart(multipleLineChart, {
    type: "line",
    data: {
        labels: [1, 2, 3, 4, 5, 6, 7, 8],
        datasets: [
            {
                label: "First Dataset",
                borderColor: "#f3545d",
                pointBackgroundColor: "#FFF",
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: "transparent",
                borderWidth: 2,
                data: [10, 20, 30, 40, 50, 60, 70, 80]
            },
            {
                label: "Second Dataset",
                borderColor: "#fdaf4b",
                pointBackgroundColor: "#FFF",
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: "transparent",
                borderWidth: 2,
                data: [80, 70, 60, 50, 40, 30, 20, 10]
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { position: "bottom" }
    }
});

// Multiple Bar Chart
var myMultipleBarChart = new Chart(multipleBarChart, {
    type: "bar",
    data: {
        labels: [1, 2, 3, 4, 5, 6, 7, 8],
        datasets: [
            {
                label: "First Dataset",
                backgroundColor: "#f3545d",
                borderWidth: 1,
                data: [10, 20, 30, 40, 50, 60, 70, 80]
            },
            {
                label: "Second Dataset",
                backgroundColor: "#fdaf4b",
                borderWidth: 1,
                data: [80, 70, 60, 50, 40, 30, 20, 10]
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { position: "bottom" }
    }
});

// HTML Legends Chart
var myHtmlLegendsChart = new Chart(htmlLegendsChart, {
    type: "line",
    data: {
        labels: [1, 2, 3, 4, 5, 6, 7, 8],
        datasets: [
            {
                label: "First Dataset",
                borderColor: "#177dff",
                pointBackgroundColor: "#FFF",
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: "transparent",
                borderWidth: 2,
                data: [10, 20, 30, 40, 50, 60, 70, 80]
            },
            {
                label: "Second Dataset",
                borderColor: "#fdaf4b",
                pointBackgroundColor: "#FFF",
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: "transparent",
                borderWidth: 2,
                data: [80, 70, 60, 50, 40, 30, 20, 10]
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false }
    }
});

var myLegendContainer = document.getElementById("myChartLegend");
myLegendContainer.innerHTML = myHtmlLegendsChart.generateLegend();

var legendItems = myLegendContainer.getElementsByTagName("li");
for (var i = 0; i < legendItems.length; i += 1) {
    legendItems[i].addEventListener("click", function (e) {
        var index = Array.prototype.slice.call(legendItems).indexOf(e.target);
        var meta = myHtmlLegendsChart.getDatasetMeta(index);
        meta.hidden = meta.hidden === null ? !myHtmlLegendsChart.data.datasets[index].hidden : null;
        myHtmlLegendsChart.update();
    });
}

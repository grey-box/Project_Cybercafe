
  document.addEventListener('DOMContentLoaded', function () {
    // Line Chart for Total Bandwidth Used
    var ctx1 = document.getElementById('bandwidthChart').getContext('2d');
    var bandwidthChart = new Chart(ctx1, {
      type: 'line',
      data: {
        labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
        datasets: [{
          label: 'Bandwidth Used (GB)',
          data: [12, 15, 10, 20, 18, 25, 30],
          borderColor: '#4e73df',
          backgroundColor: 'rgba(78, 115, 223, 0.1)',
          borderWidth: 2
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });


    // Bar Chart for Devices Connected
    var ctx3 = document.getElementById('deviceStatsChart').getContext('2d');
    var deviceStatsChart = new Chart(ctx3, {
      type: 'bar',
      data: {
        labels: ['Device 1', 'Device 2', 'Device 3', 'Device 4', 'Device 5'],
        datasets: [{
          label: 'Data Used (GB)',
          data: [10, 15, 5, 20, 25],
          backgroundColor: '#ffc107',
          borderColor: '#ffc107',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  });

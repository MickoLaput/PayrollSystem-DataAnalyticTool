// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
  // Set new default font family and font color to mimic Bootstrap's default styling
  Chart.defaults.global.defaultFontFamily =
    '-apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
  Chart.defaults.global.defaultFontColor = '#292b2c';

	// Create a global object to hold today's attendance
window.pieChartData = { late: 0, present: 0, absent: 0 };

  // Fetch the pie chart data from PHP
  fetch('php/getPieChartData.php')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error fetching pie chart data:', data.error);
        return;
      }
      // Extract counts from the returned data
      const lateCount = data.lateCount;
      const presentCount = data.presentCount;
      const absentCount = data.absentCount;

	  // Store in global var
    window.pieChartData = {
      late: lateCount,
      present: presentCount,
      absent: absentCount
    };

      // Get the chart context from the canvas element
      const ctx = document.getElementById('myPieChart').getContext('2d');

      // Create the pie chart with the fetched data
      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['Late', 'Present', 'Absent'],
          datasets: [{
            data: [lateCount, presentCount, absentCount],
            // Colors:
            // Yellow (#ffc107) for Late, Green (#28a745) for Present, Red (#dc3545) for Absent
            backgroundColor: ['#ffc107', '#28a745', '#dc3545']
          }]
        },
        options: {
          responsive: true,
          legend: {
            position: 'bottom'
          }
        }
      });
    })
    .catch(error => {
      console.error('Error fetching pie chart data:', error);
    });
});

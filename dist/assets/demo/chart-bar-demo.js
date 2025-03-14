// chart-bar-demo.js

// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily =
  '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#292b2c';

// Create a global array to hold monthly attendance
window.barChartData = [];

// Fetch monthly attendance data for the current year
fetch('php/getMonthlyAttendance.php')
  .then(response => response.json())
  .then(data => {
    // 'data' is an object like { "1": 10, "2": 15, "3": 20, ..., "12": 5 }

    // Convert that object into an array for each month (1..12)
    const monthlyCounts = [];
    for (let m = 1; m <= 12; m++) {
      monthlyCounts.push(data[m] || 0);
    }
	
	// Store in global var
    window.barChartData = monthlyCounts;

    // Prepare labels for each month
    const monthLabels = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];

    // Get the canvas element
    var ctx = document.getElementById("myBarChart");
    // Create the bar chart
    var myLineChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: [{
          label: "Total Attendance",
          backgroundColor: "rgba(2,117,216,1)",
          borderColor: "rgba(2,117,216,1)",
          data: monthlyCounts,
        }],
      },
      options: {
        scales: {
          xAxes: [{
            time: {
              unit: 'month'
            },
            gridLines: {
              display: false
            },
            ticks: {
              maxTicksLimit: 12
            }
          }],
          yAxes: [{
            ticks: {
              min: 0,
              // Optionally set a max if you want a fixed scale
              maxTicksLimit: 5
            },
            gridLines: {
              display: true
            }
          }],
        },
        legend: {
          display: false
        }
      }
    });
  })
  .catch(error => console.error("Error fetching monthly attendance:", error));

// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily =
  '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#292b2c';

window.areaChartData = [];

// Fetch monthly employee data and build the area chart
fetch("php/getMonthlyEmployees.php")
  .then((response) => response.json())
  .then((data) => {
    // 'data' is an object: { "1": <countJan>, "2": <countFeb>, ..., "12": <countDec> }

    // Convert that object into an array for each month [Jan..Dec]
    const monthlyCounts = [];
    for (let m = 1; m <= 12; m++) {
      monthlyCounts.push(data[m] || 0);
    }

    // Prepare labels for each month
    const monthLabels = [
      "Jan", "Feb", "Mar", "Apr", "May", "Jun",
      "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
    ];

	
	// Store in global var for Excel export
    window.areaChartData = monthlyCounts;
    // Get the chart context from your HTML
    var ctx = document.getElementById("myAreaChart").getContext("2d");

    // Create the chart
    var myLineChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [
          {
            label: "Employees Added",
            lineTension: 0.3,
            backgroundColor: "rgba(2,117,216,0.2)",
            borderColor: "rgba(2,117,216,1)",
            pointRadius: 5,
            pointBackgroundColor: "rgba(2,117,216,1)",
            pointBorderColor: "rgba(255,255,255,0.8)",
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(2,117,216,1)",
            pointHitRadius: 50,
            pointBorderWidth: 2,
            data: monthlyCounts,
          }
        ],
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
              // Optionally adjust your max or let Chart.js handle it
              maxTicksLimit: 5
            },
            gridLines: {
              color: "rgba(0, 0, 0, .125)"
            }
          }],
        },
        legend: {
          display: false
        }
      }
    });
  })
  .catch((error) => console.error("Error fetching monthly employees:", error));

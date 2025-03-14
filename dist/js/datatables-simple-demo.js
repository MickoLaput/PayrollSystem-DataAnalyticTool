window.addEventListener('DOMContentLoaded', event => {
  const datatablesSimple = document.getElementById('datatablesSimple');
  if (datatablesSimple) {
    // Fetch data from the PHP endpoint
    fetch('php/getDataTableData.php')
      .then(response => response.json())
      .then(jsonData => {
        // Initialize the DataTable with the fetched data
        new simpleDatatables.DataTable(datatablesSimple, {
          data: {
            headings: jsonData.headings,
            data: jsonData.data
          }
        });
      })
      .catch(error => {
        console.error('Error fetching datatable data:', error);
        datatablesSimple.innerHTML = '<p>Error loading data.</p>';
      });
  }
});

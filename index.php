<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sales Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --bg: #f4efe6;
      --panel: #fffaf0;
      --ink: #1f1f1f;
      --accent: #b3541e;
      --accent-2: #5f6f52;
      --line: #dfd3c3;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: "Trebuchet MS", "Segoe UI", sans-serif;
      background:
        radial-gradient(circle at 10% 10%, #f9e5c7 0%, transparent 25%),
        radial-gradient(circle at 90% 90%, #d2e0c4 0%, transparent 20%),
        var(--bg);
      color: var(--ink);
    }

    .container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 24px;
    }

    h1 {
      margin-top: 0;
      letter-spacing: 0.5px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 16px;
    }

    .card {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    }

    .card h2 {
      font-size: 1.05rem;
      margin: 0 0 12px;
    }

    canvas {
      width: 100% !important;
      height: 280px !important;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Data Warehouse Dashboard</h1>
    <div class="grid">
      <section class="card">
        <h2>Total Penjualan per Bulan</h2>
        <canvas id="salesByMonth"></canvas>
      </section>
      <section class="card">
        <h2>Produk Terlaris</h2>
        <canvas id="bestSelling"></canvas>
      </section>
      <section class="card">
        <h2>Penjualan per Kota</h2>
        <canvas id="salesByCity"></canvas>
      </section>
    </div>
  </div>

  <script>
    async function loadChart(endpoint, canvasId, color) {
      const res = await fetch(endpoint);
      const data = await res.json();

      if (data.error) {
        console.error(data.error, data.details || '');
        return;
      }

      new Chart(document.getElementById(canvasId), {
        type: data.type,
        data: {
          labels: data.labels,
          datasets: [{
            label: data.title,
            data: data.values,
            borderColor: color,
            backgroundColor: data.type === 'pie'
              ? ['#b3541e', '#5f6f52', '#f0bb78', '#8a9a5b', '#aa6f39']
              : color + '55',
            fill: data.type === 'line',
            tension: 0.25,
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: data.type === 'pie' }
          }
        }
      });
    }

    loadChart('api.php?chart=sales_by_month', 'salesByMonth', '#b3541e');
    loadChart('api.php?chart=best_selling_products', 'bestSelling', '#5f6f52');
    loadChart('api.php?chart=sales_by_city', 'salesByCity', '#2a6f97');
  </script>
</body>
</html>

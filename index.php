<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$topProductName = '-';
$topProductQty = 0;
$topCityName = '-';
$topCitySales = 0.0;
$growthMonthLabel = '-';
$growthValue = 0.0;
$insightError = null;

try {
    $pdo = get_pdo();

    $productStmt = $pdo->query(" 
        SELECT p.nama_produk, SUM(f.jumlah) AS total_terjual
        FROM fact_penjualan f
        JOIN dim_produk p ON p.id_produk = f.id_produk
        GROUP BY p.nama_produk
        ORDER BY total_terjual DESC
        LIMIT 1
    ");
    $topProduct = $productStmt->fetch();
    if ($topProduct !== false) {
        $topProductName = (string) $topProduct['nama_produk'];
        $topProductQty = (int) $topProduct['total_terjual'];
    }

    $cityStmt = $pdo->query(" 
        SELECT pl.kota, SUM(f.total_harga) AS total_penjualan
        FROM fact_penjualan f
        JOIN dim_pelanggan pl ON pl.id_pelanggan = f.id_pelanggan
        GROUP BY pl.kota
        ORDER BY total_penjualan DESC
        LIMIT 1
    ");
    $topCity = $cityStmt->fetch();
    if ($topCity !== false) {
        $topCityName = (string) $topCity['kota'];
        $topCitySales = (float) $topCity['total_penjualan'];
    }

    $monthlyStmt = $pdo->query(" 
        SELECT w.bulan, SUM(f.total_harga) AS total_penjualan
        FROM fact_penjualan f
        JOIN dim_waktu w ON w.id_waktu = f.id_waktu
        GROUP BY w.bulan
        ORDER BY w.bulan
    ");
    $monthlyRows = $monthlyStmt->fetchAll();

    $previous = null;
    $maxIncrease = null;
    $maxIncreaseMonth = null;

    foreach ($monthlyRows as $row) {
        $month = (int) $row['bulan'];
        $total = (float) $row['total_penjualan'];

        if ($previous !== null) {
            $diff = $total - $previous;
            if ($maxIncrease === null || $diff > $maxIncrease) {
                $maxIncrease = $diff;
                $maxIncreaseMonth = $month;
            }
        }

        $previous = $total;
    }

    if ($maxIncrease !== null && $maxIncrease > 0 && $maxIncreaseMonth !== null) {
        $growthMonthLabel = 'Bulan ' . $maxIncreaseMonth;
        $growthValue = $maxIncrease;
    } else {
        $growthMonthLabel = 'Belum ada kenaikan';
        $growthValue = 0.0;
    }
} catch (Throwable $e) {
    $insightError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Warehouse Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
  <main class="container py-4 py-md-5">
    <header class="mb-4">
      <h1 class="display-6 fw-bold mb-2">Data Warehouse Dashboard</h1>
      <p class="text-secondary mb-0">Visualisasi penjualan dan insight utama berdasarkan query data warehouse.</p>
    </header>

    <?php if ($insightError !== null): ?>
      <div class="alert alert-danger" role="alert">
        Gagal memuat insight: <?php echo htmlspecialchars($insightError, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <section class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <article class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h6 text-uppercase text-secondary mb-2">Produk paling laris</h2>
            <p class="fs-4 fw-semibold mb-1"><?php echo htmlspecialchars($topProductName, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-secondary mb-0">Total terjual: <?php echo number_format($topProductQty, 0, ',', '.'); ?> unit</p>
          </div>
        </article>
      </div>
      <div class="col-12 col-md-4">
        <article class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h6 text-uppercase text-secondary mb-2">Penjualan meningkat di bulan</h2>
            <p class="fs-4 fw-semibold mb-1"><?php echo htmlspecialchars($growthMonthLabel, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-secondary mb-0">Kenaikan: Rp <?php echo number_format($growthValue, 0, ',', '.'); ?></p>
          </div>
        </article>
      </div>
      <div class="col-12 col-md-4">
        <article class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h6 text-uppercase text-secondary mb-2">Kota paling potensial</h2>
            <p class="fs-4 fw-semibold mb-1"><?php echo htmlspecialchars($topCityName, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-secondary mb-0">Total penjualan: Rp <?php echo number_format($topCitySales, 0, ',', '.'); ?></p>
          </div>
        </article>
      </div>
    </section>

    <section class="row g-3">
      <div class="col-12 col-lg-4">
        <article class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 mb-3">Produk Terlaris</h2>
            <canvas id="bestSelling" height="260"></canvas>
          </div>
        </article>
      </div>
      <div class="col-12 col-lg-4">
        <article class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 mb-3">Total Penjualan per Bulan</h2>
            <canvas id="salesByMonth" height="260"></canvas>
          </div>
        </article>
      </div>
      <div class="col-12 col-lg-4">
        <article class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 mb-3">Penjualan per Kota</h2>
            <canvas id="salesByCity" height="260"></canvas>
          </div>
        </article>
      </div>
    </section>
  </main>

  <script>
    async function loadChart(endpoint, canvasId, chartColor, piePalette) {
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
            borderColor: chartColor,
            backgroundColor: data.type === 'pie' ? piePalette : chartColor,
            fill: data.type === 'line',
            tension: 0.25,
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: data.type === 'pie' }
          },
          scales: data.type === 'pie' ? {} : { y: { beginAtZero: true } }
        }
      });
    }

    loadChart('api.php?chart=sales_by_month', 'salesByMonth', 'rgba(13, 110, 253, 0.85)', [
      'rgba(13, 110, 253, 0.75)',
      'rgba(25, 135, 84, 0.75)',
      'rgba(255, 193, 7, 0.75)',
      'rgba(220, 53, 69, 0.75)',
      'rgba(13, 202, 240, 0.75)'
    ]);

    loadChart('api.php?chart=best_selling_products', 'bestSelling', 'rgba(25, 135, 84, 0.85)', [
      'rgba(25, 135, 84, 0.75)',
      'rgba(13, 110, 253, 0.75)',
      'rgba(255, 193, 7, 0.75)',
      'rgba(220, 53, 69, 0.75)',
      'rgba(111, 66, 193, 0.75)'
    ]);

    loadChart('api.php?chart=sales_by_city', 'salesByCity', 'rgba(111, 66, 193, 0.85)', [
      'rgba(13, 110, 253, 0.75)',
      'rgba(25, 135, 84, 0.75)',
      'rgba(255, 193, 7, 0.75)',
      'rgba(220, 53, 69, 0.75)',
      'rgba(13, 202, 240, 0.75)'
    ]);
  </script>
</body>
</html>

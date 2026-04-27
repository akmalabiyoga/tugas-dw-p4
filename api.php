<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$chart = $_GET['chart'] ?? 'sales_by_month';

try {
    $pdo = get_pdo();

    switch ($chart) {
        case 'sales_by_month':
            $sql = "
                SELECT w.bulan, SUM(f.total_harga) AS total_penjualan
                FROM fact_penjualan f
                JOIN dim_waktu w ON f.id_waktu = w.id_waktu
                GROUP BY w.bulan
                ORDER BY w.bulan
            ";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            echo json_encode([
                'title' => 'Total Penjualan per Bulan',
                'labels' => array_map(fn($r) => 'Bulan ' . $r['bulan'], $rows),
                'values' => array_map(fn($r) => (float) $r['total_penjualan'], $rows),
                'type' => 'line',
            ]);
            break;

        case 'best_selling_products':
            $sql = "
                SELECT p.nama_produk, SUM(f.jumlah) AS total_terjual
                FROM fact_penjualan f
                JOIN dim_produk p ON f.id_produk = p.id_produk
                GROUP BY p.nama_produk
                ORDER BY total_terjual DESC
            ";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            echo json_encode([
                'title' => 'Produk Terlaris',
                'labels' => array_column($rows, 'nama_produk'),
                'values' => array_map(fn($r) => (int) $r['total_terjual'], $rows),
                'type' => 'bar',
            ]);
            break;

        case 'sales_by_city':
            $sql = "
                SELECT pl.kota, SUM(f.total_harga) AS total_penjualan
                FROM fact_penjualan f
                JOIN dim_pelanggan pl ON f.id_pelanggan = pl.id_pelanggan
                GROUP BY pl.kota
                ORDER BY total_penjualan DESC
            ";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            echo json_encode([
                'title' => 'Penjualan per Kota',
                'labels' => array_column($rows, 'kota'),
                'values' => array_map(fn($r) => (float) $r['total_penjualan'], $rows),
                'type' => 'pie',
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown chart']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to load chart data',
        'details' => $e->getMessage(),
    ]);
}

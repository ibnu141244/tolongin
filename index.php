<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

 $judul_halaman = "Tolongin! — Yang butuh bantuan bertemu yang siap tolong";

// ===== pagination =====
 $perHalaman = 6;
 $halaman    = max(1, (int)($_GET["page"] ?? 1));

// ===== parameter pencarian (GET) =====
 $q = trim($_GET["q"] ?? "");

// ===== bangun WHERE dinamis =====
 $conditions = [];
 $params     = [];
 $types      = "";

// katalog hanya menampilkan job OPEN
 $conditions[] = "status = ?";
 $params[]     = "OPEN";
 $types       .= "s";

if ($q !== "") {
    $conditions[] = "(judul LIKE ? OR deskripsi LIKE ?)";
    $params[]     = "%" . $q . "%";
    $params[]     = "%" . $q . "%";
    $types       .= "ss";
}

 $where = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

// ===== query 1: hitung total (filter sama) =====
 $sqlCount = "SELECT COUNT(*) AS total FROM jobs $where";
 $stmt = mysqli_prepare($konek, $sqlCount);
if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
 $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];
mysqli_stmt_close($stmt);

// ===== hitung pagination =====
 $totalHalaman = max(1, (int)ceil($total / $perHalaman));
 $halaman      = min($halaman, $totalHalaman);
 $offset       = ($halaman - 1) * $perHalaman;

// ===== query 2: satu jendela data =====
 $sql = "SELECT * FROM jobs $where
        ORDER BY created_at DESC
        LIMIT $perHalaman OFFSET $offset";

 $stmt = mysqli_prepare($konek, $sql);
if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
 $hasil = mysqli_stmt_get_result($stmt);
 $jobs  = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

include 'includes/header.php';
?>

<main>
    <section class="hero">
        <h1>Butuh bantuan? Ada yang siap tolong.</h1>
        <p>Memertemukan warga Padang yang butuh bantuan pekerjaan kecil
           dengan yang siap membantu — dengan imbalan yang disepakati.</p>
    </section>

    <section id="daftar-job">
        <h2>Job Tersedia</h2>

        <form method="get" action="index.php" class="search-panel" autocomplete="off">
            <input type="text" name="q" placeholder="Cari judul atau deskripsi"
                   value="<?php echo e($q); ?>">
            <button type="submit" class="btn">Cari</button>
            <a href="index.php" class="reset-link">Reset</a>
        </form>

        <p class="result-count">
            <?php echo count($jobs); ?> job di halaman ini
            · total <?php echo $total; ?> job
            · halaman <?php echo $halaman; ?> dari <?php echo $totalHalaman; ?>
        </p>

        <?php if (count($jobs) === 0): ?>
            <p class="empty-state">Tidak ada job yang cocok dengan pencarianmu.
                Coba kata kunci lain, atau reset filter.</p>
        <?php else: ?>

        <div class="job-list">
            <?php foreach ($jobs as $job): ?>
                <article class="job-card">
                    <div class="card-top">
                        <span class="badge badge-<?php echo strtolower(e($job["status"])); ?>">
                            <?php echo e($job["status"]); ?>
                        </span>
                    </div>
                    <h3><?php echo e($job["judul"]); ?></h3>
                    <ul>
                        <li><?php echo e($job["lokasi"]); ?></li>
                        <li><?php echo date("d M Y, H:i", strtotime($job["tanggal"])); ?></li>
                        <li>Imbalan: <span class="reward"><?php echo formatRupiah($job["imbalan"]); ?></span></li>
                    </ul>
                    <a href="job.php?id=<?php echo (int)$job["id"]; ?>">Lihat detail</a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalHalaman > 1): ?>
            <nav class="pagination" aria-label="Navigasi halaman">
                <?php if ($halaman > 1): ?>
                    <a href="index.php?<?php echo http_build_query(array_merge($_GET, ["page" => $halaman - 1])); ?>"
                       class="page-link">&larr; Sebelumnya</a>
                <?php endif; ?>

                <?php
                for ($i = 1; $i <= $totalHalaman; $i++) {
                    if ($i === $halaman) {
                        echo '<span class="page-link page-current">' . $i . '</span>';
                    } else {
                        echo '<a class="page-link" href="index.php?' .
                             http_build_query(array_merge($_GET, ["page" => $i])) . '">' .
                             $i . '</a>';
                    }
                }
                ?>

                <?php if ($halaman < $totalHalaman): ?>
                    <a href="index.php?<?php echo http_build_query(array_merge($_GET, ["page" => $halaman + 1])); ?>"
                       class="page-link">&rarr; Berikutnya</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

        <?php endif; ?>
    </section>

    <section id="cara-kerja">
        <h2>Cara kerja</h2>
        <ol>
            <li>Requester memasang job — jelaskan kebutuhan, waktu, dan imbalannya.</li>
            <li>Helper mengajukan diri; requester memilih yang paling pas.</li>
            <li>Pekerjaan selesai — keduanya saling memberi ulasan.</li>
        </ol>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Keluar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logout-card {
            max-width: 400px;
            border-radius: 15px; 
            border: none; 
        }
        .card-title {
            color: #dc3545; 
            font-weight: 700;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card shadow-lg p-4 text-center logout-card">
        <div class="mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-box-arrow-right text-danger" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h8a.5.5 0 0 1 .5.5z"/>
                <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
            </svg>
        </div>
        <h5 class="card-title mb-3">Konfirmasi Keluar</h5>
        <p class="mb-4">Anda akan mengakhiri sesi saat ini. Apakah Anda yakin ingin **keluar** dari sistem?</p>
        
        <form method="POST">
            <input type="hidden" name="from" value="<?= htmlspecialchars($from_page ?? '') ?>">
            
            <button type="submit" name="confirm_logout" class="btn btn-danger btn-lg w-100 mb-2">
                <i class="bi bi-box-arrow-right"></i> Ya, Keluar Sekarang
            </button>
            
            <button type="submit" name="cancel_logout" class="btn btn-outline-secondary w-100">
                Batal
            </button>
        </form>
    </div>
</body>
</html>
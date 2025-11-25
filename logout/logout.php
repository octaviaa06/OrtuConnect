<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .card-logout {
            max-width: 380px;
            border: none;
            border-radius: 24px;
            background: white;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .icon-logout {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -50px auto 20px;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.4);
        }

        /* Tombol Keluar — premium banget */
        .btn-keluar {
            height: 56px;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 16px;
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            border: none;
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
            transition: all 0.3s ease;
        }
        .btn-keluar:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(67, 97, 238, 0.5);
        }

        /* Tombol Batal — soft & clean */
        .btn-batal {
            height: 56px;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 16px;
            background: #f8f9ff;
            color: #4361ee;
            border: 2px solid #e0e6ff;
            transition: all 0.3s ease;
        }
        .btn-batal:hover {
            background: #e0e6ff;
            border-color: #4361ee;
            transform: translateY(-3px);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

  <div class="card-logout text-center pt-5 pb-4 px-4">
    
    <div class="icon-logout">
      <i class="bi bi-box-arrow-right" style="font-size: 2.2rem;"></i>
    </div>

    <h4 class="mt-4 mb-2 fw-bold text-dark">Yakin ingin keluar?</h4>
    <p class="text-muted mb-5 px-3" style="font-size: 0.95rem;">
      Sesi akan berakhir dan Anda akan dialihkan ke halaman login.
    </p>

    <form method="POST" class="d-grid gap-3 px-4">
      <input type="hidden" name="from" value="<?= htmlspecialchars($from_page ?? '') ?>">

      <button type="submit" name="confirm_logout" class="btn btn-keluar text-white">
        <i class="bi bi-box-arrow-right me-2"></i>
        Ya, Keluar Sekarang
      </button>

      <button type="submit" name="cancel_logout" class="btn btn-batal">
        <i class="bi bi-arrow-left me-2"></i>
        Batal, Kembali
      </button>
    </form>
  </div>

</body>
</html>
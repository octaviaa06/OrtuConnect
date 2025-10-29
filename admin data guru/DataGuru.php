<?php
session_start();
//if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  //  header("Location: ../login/index.php");
    //exit;
    
$username = "admin";
$initial = strtoupper(substr($username, 0, 1));

//$username = htmlspecialchars($_SESSION['username']);
//$initial = strtoupper(substr($username, 0, 1));
//$api_url = "http://ortuconnect.atwebpages.com/api/";
//$response = file_get_contents($api_url);
//$guru_list = json_decode($response, true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Guru | OrtuConnect</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar" id="sidebar">
    <img src="../assets/logo.png" class="logo">
    <img src="../assets/slide.png" class="toggle" id="toggleBtn">
    <ul>
        <li><img src="../assets/Dashboard.png" class="icon"><span class="label">Dashboard</span></li>
        <li class="active"><img src="../assets/Data Guru.png" class="icon"><span class="label">Data Guru</span></li>
        <li><img src="../assets/Data Siswa.png" class="icon"><span class="label">Data Murid</span></li>
        <li><img src="../assets/Absensi.png" class="icon"><span class="label">Absensi</span></li>
        <li><img src="../assets/Perizinan.png" class="icon"><span class="label">Perizinan</span></li>
        <li><img src="../assets/Kalender.png" class="icon"><span class="label">Kalender</span></li>
    </ul>
</div>

<div class="main">
    <div class="header">
        <h2>Data Guru</h2>
        <div class="profile">
            <div class="circle"><?= $initial ?></div>
            <span><?= $username ?></span>
        </div>
    </div>

    <div class="content">
        <div class="top-bar">
            <div class="search-box">
                <img src="../assets/cari.png" class="icon-cari">
                <input type="text" id="searchInput" placeholder="Cari guru...">
            </div>
            <button id="btnTambah" class="btn">+ Tambah Guru</button>
        </div>

        <div class="cards" id="cardContainer"></div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal" id="modalForm">
  <div class="modal-content">
    <h3 id="modalTitle">Tambah Guru Baru</h3>
    <label>Nama Lengkap</label>
    <input type="text" id="namaGuru">
    <label>NIP</label>
    <input type="text" id="nipGuru">
    <label>Alamat</label>
    <input type="text" id="alamatGuru">
    <label>No Telepon</label>
    <input type="text" id="telpGuru">
    <label>Email</label>
    <input type="text" id="emailGuru">

    <div class="modal-actions">
      <button id="btnBatal">Batal</button>
      <button id="btnSimpan">Konfirmasi</button>
    </div>
  </div>
</div>

<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleBtn');
toggleBtn.addEventListener('click', () => sidebar.classList.toggle('expanded'));

const modal = document.getElementById('modalForm');
const btnTambah = document.getElementById('btnTambah');
const btnBatal = document.getElementById('btnBatal');
const btnSimpan = document.getElementById('btnSimpan');
const searchInput = document.getElementById('searchInput');
const cardContainer = document.getElementById('cardContainer');

let editIndex = null;
let guruList = [
  { nama: "Luca Modric", nip: "0123444", alamat: "Brighton, Inggris", telp: "0811111111", email: "luca@email.com" },
  { nama: "Ahmad Fauzi", nip: "12345678", alamat: "Jakarta", telp: "08123456789", email: "ahmadf@sekolah.id" },
  { nama: "Rina Sari", nip: "99887766", alamat: "Bandung", telp: "085212345678", email: "rina@edu.id" }
];

function renderCards(data) {
  cardContainer.innerHTML = "";
  data.forEach((guru, index) => {
    const card = document.createElement("div");
    card.className = "card";
    card.innerHTML = `
      <div class="info">
        <h3>${guru.nama}</h3>
        <p>NIP: ${guru.nip}</p>
        <p><img src="../assets/lokasi.png" class="mini-icon"> ${guru.alamat}</p>
        <p><img src="../assets/hp.png" class="mini-icon"> ${guru.telp}</p>
        <p><img src="../assets/save.png" class="mini-icon"> ${guru.email}</p>
      </div>
      <div class="actions">
        <a href="#" class="btn-akun">Buat Akun</a>
        <a href="#" class="btn-edit" data-index="${index}">✏️</a>
        <a href="#" class="btn-delete" data-index="${index}">🗑️</a>
      </div>
    `;
    cardContainer.appendChild(card);
  });
}

renderCards(guruList);

btnTambah.onclick = () => {
  modal.style.display = "flex";
  document.getElementById("modalTitle").innerText = "Tambah Guru Baru";
  editIndex = null;
  document.getElementById("namaGuru").value = "";
  document.getElementById("nipGuru").value = "";
  document.getElementById("alamatGuru").value = "";
  document.getElementById("telpGuru").value = "";
  document.getElementById("emailGuru").value = "";
};

btnBatal.onclick = () => modal.style.display = "none";

btnSimpan.onclick = () => {
  const newData = {
    nama: document.getElementById("namaGuru").value,
    nip: document.getElementById("nipGuru").value,
    alamat: document.getElementById("alamatGuru").value,
    telp: document.getElementById("telpGuru").value,
    email: document.getElementById("emailGuru").value
  };
  if (editIndex === null) guruList.push(newData);
  else guruList[editIndex] = newData;
  renderCards(guruList);
  modal.style.display = "none";
};

cardContainer.addEventListener("click", (e) => {
  if (e.target.classList.contains("btn-edit")) {
    const index = e.target.dataset.index;
    const g = guruList[index];
    document.getElementById("modalTitle").innerText = "Edit Data Guru";
    document.getElementById("namaGuru").value = g.nama;
    document.getElementById("nipGuru").value = g.nip;
    document.getElementById("alamatGuru").value = g.alamat;
    document.getElementById("telpGuru").value = g.telp;
    document.getElementById("emailGuru").value = g.email;
    editIndex = index;
    modal.style.display = "flex";
  }
  if (e.target.classList.contains("btn-delete")) {
    const index = e.target.dataset.index;
    guruList.splice(index, 1);
    renderCards(guruList);
  }
});

searchInput.addEventListener("keyup", (e) => {
  const value = e.target.value.toLowerCase();
  const filtered = guruList.filter(g => g.nama.toLowerCase().includes(value));
  renderCards(filtered);
});
</script>
</body>
</html>
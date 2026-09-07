// Jika device terdeteksi bukan HP yang didukung, hentikan semua logika aplikasi
if (window.__APP_BLOCKED__) {
    // tidak melakukan apa-apa lagi
} else {

const viewForm   = document.getElementById('viewForm');
const viewCamera = document.getElementById('viewCamera');
const viewCard   = document.getElementById('viewCard');
const form       = document.getElementById('formBiodata');
const camStatus  = document.getElementById('camStatus');
const camProgress= document.getElementById('camProgress');

function showView(el) {
    [viewForm, viewCamera, viewCard].forEach(v => v.classList.add('d-none'));
    el.classList.remove('d-none');
}

document.getElementById('btnAmbilFoto').addEventListener('click', () => {
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    showView(viewCamera);

    // ==== SIMULASI proses kamera (bukan kamera asli) ====
    const steps = [
        { pct: 20,  text: 'Membuka kamera...' },
        { pct: 45,  text: 'Mendeteksi wajah...' },
        { pct: 70,  text: 'Mengambil gambar...' },
        { pct: 90,  text: 'Memproses foto...' },
        { pct: 100, text: 'Selesai!' },
    ];

    let i = 0;
    const interval = setInterval(() => {
        const s = steps[i];
        camProgress.style.width = s.pct + '%';
        camStatus.textContent = s.text;
        i++;
        if (i >= steps.length) {
            clearInterval(interval);
            setTimeout(submitBiodata, 400);
        }
    }, 500);
});

function submitBiodata() {
    const formData = new FormData(form);

    fetch('save.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Gagal menyimpan data: ' + data.message);
            showView(viewForm);
            return;
        }
        renderCard(data.data);
        showView(viewCard);
    })
    .catch(err => {
        alert('Terjadi kesalahan koneksi ke server.');
        console.error(err);
        showView(viewForm);
    });
}

function renderCard(d) {
    document.getElementById('cardFoto').src = d.foto_path;
    document.getElementById('cNama').textContent  = d.nama;
    document.getElementById('cKelas').textContent = d.kelas;
    document.getElementById('cAbsen').textContent = d.absen;
    document.getElementById('cJk').textContent    = d.jk;
    document.getElementById('cHp').textContent    = d.no_hp;

    document.getElementById('awsEc2Id').textContent  = d.aws.ec2_id;
    document.getElementById('awsEc2Ip').textContent  = d.aws.ec2_ip;
    document.getElementById('awsRds').textContent    = d.aws.rds_endpoint;
    document.getElementById('awsRegion').textContent = d.aws.region;

    const badge = document.getElementById('awsSource');
    if (d.aws.source === 'real') {
        badge.textContent = 'LIVE';
        badge.className = 'badge bg-success';
        badge.title = 'Diambil langsung dari AWS Instance Metadata Service (IMDSv2)';
    } else {
        badge.textContent = 'N/A';
        badge.className = 'badge bg-secondary';
        badge.title = 'Instance metadata tidak terdeteksi (aplikasi tidak berjalan di EC2)';
    }
}

document.getElementById('btnUlangi').addEventListener('click', () => {
    form.reset();
    showView(viewForm);
});

} // akhir blok if (!window.__APP_BLOCKED__)
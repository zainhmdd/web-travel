// Konfirmasi sebelum menghapus paket wisata
function confirmDelete() {
    return confirm("Apakah Anda yakin ingin menghapus paket ini?");
}

// Validasi form pendaftaran
function validateForm() {
    const jumlahPeserta = document.getElementById("jumlah_peserta").value;
    if (jumlahPeserta <= 0) {
        alert("Jumlah peserta harus lebih dari 0.");
        return false;
    }
    return true;
}

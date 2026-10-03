// ============================================================
// db.js — sumber data DUMMY (in-memory). M22: diganti PostgreSQL.
// ============================================================

const users = [
    {
        id: 1,
        nama: "Alex Putra",
        email: "alex@contoh.com",
        password: "rahasia123",     // DUMMY: plaintext — lihat catatan di bawah
        role: "requester",
        api_token: null,
    },
    {
        id: 2,
        nama: "Asep Helper",
        email: "asep@contoh.com",
        password: "rahasia456",
        role: "helper",
        api_token: null,
    },
];

let nextJobId = 5;

const jobs = [
    { id: 1, judul: "Bantu angkat kulkas", lokasi: "Kuranji, Padang",
      deskripsi: "Dua orang dibutuhkan untuk menurunkan kulkas dari lantai 2.",
      tanggal: "2026-10-30 14:00:00", imbalan: 50000, status: "OPEN",
      user_id: 1, created_at: "2026-09-25 09:00:00" },
    { id: 2, judul: "Antar berkas ke kantor pos", lokasi: "Ulak Karang, Padang",
      deskripsi: "Mengantar amplop penting, pulang bawa bukti kirim.",
      tanggal: "2026-11-05 10:00:00", imbalan: 40000, status: "OPEN",
      user_id: 1, created_at: "2026-09-26 11:00:00" },
    { id: 3, judul: "Bantu stem motor", lokasi: "Lubuk Begalung, Padang",
      deskripsi: "Antar motor ke bengkel pagi, ambil sore.",
      tanggal: "2026-10-29 16:17:00", imbalan: 17000, status: "OPEN",
      user_id: 1, created_at: "2026-09-27 15:00:00" },
];

let nextUserId = 3;

module.exports = { users, jobs, nextUserId: () => nextUserId, nextJobId: () => nextJobId, setNextJobId: (v) => { nextJobId = v; } };
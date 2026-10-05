import { useState } from "react";

const API = "http://localhost:3000";

export default function BuatJob({ onSelesai }) {
  const [judul, setJudul] = useState("");
  const [lokasi, setLokasi] = useState("");
  const [deskripsi, setDeskripsi] = useState("");
  const [tanggal, setTanggal] = useState("");
  const [imbalan, setImbalan] = useState("");
  const [errors, setErrors] = useState([]);
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setErrors([]);
    setLoading(true);

    try {
      const res = await fetch(API + "/api/jobs", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          // token dari localStorage — "identitas" React (padanan $_SESSION PHP)
          Authorization: "Bearer " + localStorage.getItem("token"),
        },
        body: JSON.stringify({ judul, lokasi, deskripsi, tanggal, imbalan }),
      });
      const json = await res.json();

      if (!res.ok || !json.success) {
        throw new Error(json.message ?? "Gagal menyimpan job.");
      }

      onSelesai(); // beri tahu induk: job jadi — kembali ke daftar
    } catch (err) {
      setErrors([err.message]);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="form-panel">
      <h2>Buat Job Baru</h2>

      {errors.length > 0 &&
        errors.map((err, i) => (
          <p key={i} className="error">{err}</p>
        ))}

      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="judul">Judul</label>
          <input
            type="text"
            id="judul"
            value={judul}
            onChange={(e) => setJudul(e.target.value)}
          />
        </div>

        <div className="form-group">
          <label htmlFor="lokasi">Lokasi</label>
          <input
            type="text"
            id="lokasi"
            value={lokasi}
            onChange={(e) => setLokasi(e.target.value)}
          />
        </div>

        <div className="form-group">
          <label htmlFor="deskripsi">Deskripsi</label>
          <textarea
            id="deskripsi"
            rows="4"
            value={deskripsi}
            onChange={(e) => setDeskripsi(e.target.value)}
          />
        </div>

        <div className="form-group">
          <label htmlFor="tanggal">Tanggal &amp; Jam</label>
          <input
            type="datetime-local"
            id="tanggal"
            value={tanggal}
            onChange={(e) => setTanggal(e.target.value)}
          />
        </div>

        <div className="form-group">
          <label htmlFor="imbalan">Imbalan (Rp)</label>
          <input
            type="number"
            id="imbalan"
            step="1000"
            value={imbalan}
            onChange={(e) => setImbalan(e.target.value)}
          />
        </div>

        <button type="submit" className="btn" disabled={loading}>
          {loading ? "Menyimpan..." : "Simpan Job"}
        </button>
      </form>
    </div>
  );
}
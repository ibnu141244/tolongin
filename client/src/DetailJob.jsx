import { useState, useEffect } from "react";

const API = "http://localhost:3000";

function formatRupiah(angka) {
  return "Rp" + Number(angka).toLocaleString("id-ID");
}

function formatTanggal(iso) {
  const d = new Date(String(iso).replace(" ", "T"));
  if (isNaN(d)) return iso;
  return (
    d.toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" }) +
    ", " +
    d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })
  );
}

export default function DetailJob({ jobId, onKembali }) {
  const [job, setJob] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    async function muat() {
      setLoading(true);
      setError("");
      try {
        const res = await fetch(API + "/api/jobs/" + jobId);
        const json = await res.json();

        if (!res.ok || !json.success) {
          throw new Error(json.message ?? "Gagal memuat job.");
        }

        setJob(json.data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }
    muat();
  }, [jobId]);

  if (loading) {
    return (
      <div className="page-content">
        <p className="result-count">Memuat...</p>
      </div>
    );
  }

  if (error !== "") {
    return (
      <div className="page-content">
        <p className="error">Terjadi masalah: {error}</p>
        <button type="button" className="btn" onClick={onKembali}>
          ← Kembali
        </button>
      </div>
    );
  }

  return (
    <div className="page-content">
      <article className="job-card job-detail">
        <div className="card-top">
          <span className={"badge badge-" + job.status.toLowerCase()}>
            {job.status}
          </span>
        </div>

        <h2>{job.judul}</h2>

        <ul>
          <li>{job.lokasi}</li>
          <li>{formatTanggal(job.tanggal)}</li>
          <li>
            Imbalan: <span className="reward">{formatRupiah(job.imbalan)}</span>
          </li>
          <li>Dibuat oleh: {job.pemilik ?? "—"}</li>
        </ul>

        {job.deskripsi && job.deskripsi.trim() !== "" && (
          <>
            <h3>Deskripsi</h3>
            <p>{job.deskripsi}</p>
          </>
        )}

        <div className="job-actions">
          <button type="button" className="btn" onClick={onKembali}>
            ← Kembali ke daftar
          </button>
        </div>
      </article>
    </div>
  );
}
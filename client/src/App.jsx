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

function JobCard({ job }) {
  return (
    <article className="job-card">
      <div className="card-top">
        <span className={"badge badge-" + job.status.toLowerCase()}>
          {job.status}
        </span>
      </div>
      <h3>{job.judul}</h3>
      <ul>
        <li>{job.lokasi}</li>
        <li>{formatTanggal(job.tanggal)}</li>
        <li>
          Imbalan: <span className="reward">{formatRupiah(job.imbalan)}</span>
        </li>
      </ul>
    </article>
  );
}

export default function App() {
  const [q, setQ] = useState("");
  const [jobs, setJobs] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const timer = setTimeout(async () => {
      setLoading(true);
      setError("");
      try {
        const res = await fetch(API + "/api/jobs?q=" + encodeURIComponent(q));
        const json = await res.json();

        if (!res.ok || !json.success) {
          throw new Error(json.message ?? "Respons tidak berhasil.");
        }

        setJobs(json.data);
        setMeta(json.meta);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }, 300);

    return () => clearTimeout(timer);
  }, [q]);

  return (
    <>
      <header>
        <nav>
          <a href="#" className="logo">
            Tolongin<span className="logo-accent">!</span>
          </a>
          <div className="nav-menu">
            <span className="nav-user">frontend React · API Node</span>
          </div>
        </nav>
      </header>

      <main>
        <section className="hero">
          <h1>Butuh bantuan? Ada yang siap tolong.</h1>
          <p>
            Memertemukan warga Padang yang butuh bantuan pekerjaan kecil dengan
            yang siap membantu — dengan imbalan yang disepakati.
          </p>
        </section>

        <section id="daftar-job">
          <h2>Job Tersedia</h2>

          <form className="search-panel" onSubmit={(e) => e.preventDefault()}>
            <input
              type="text"
              placeholder="Cari judul atau deskripsi"
              value={q}
              onChange={(e) => setQ(e.target.value)}
            />
          </form>

          {loading && <p className="result-count">Memuat...</p>}

          {error !== "" && <p className="error">Terjadi masalah: {error}</p>}

          {!loading && error === "" && meta && (
            <p className="result-count">
              {meta.total} job ditemukan · halaman {meta.page} dari{" "}
              {meta.total_pages}
            </p>
          )}

          {!loading && error === "" && jobs.length === 0 && (
            <p className="empty-state">
              Tidak ada job yang cocok dengan pencarianmu.
            </p>
          )}

          <div className="job-list">
            {jobs.map((job) => (
              <JobCard key={job.id} job={job} />
            ))}
          </div>
        </section>
      </main>

      <footer>
        <div className="footer-inner">
          <div>
            <span className="footer-brand">
              Tolongin<span className="logo-accent">!</span>
            </span>
            <p>Dibangun dengan React + Node.js + Express</p>
          </div>
          <span className="footer-copy">© 2026 Tolongin! · proyek belajar</span>
        </div>
      </footer>
    </>
  );
}
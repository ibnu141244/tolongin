import { useState, useEffect } from "react";
import Login from "./Login";
import BuatJob from "./BuatJob";
import DetailJob from "./DetailJob";

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

// ===== komponen kartu job — menerima onBuka dari induk =====
function JobCard({ job, onBuka }) {
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
      <a
        href="#"
        onClick={function (e) {
          e.preventDefault();
          onBuka(job.id);
        }}
      >
        Lihat detail
      </a>
    </article>
  );
}

export default function App() {
  const [user, setUser] = useState(() => {
    const token = localStorage.getItem("token");
    const nama = localStorage.getItem("nama");
    const role = localStorage.getItem("role");
    return token ? { token, nama, role } : null;
  });

  function handleLogout() {
    localStorage.removeItem("token");
    localStorage.removeItem("nama");
    localStorage.removeItem("role");
    setUser(null);
  }

  // router mini: daftar | buat | detail
  const [halaman, setHalaman] = useState("daftar");
  const [detailId, setDetailId] = useState(null);

  const [q, setQ] = useState("");
  const [jobs, setJobs] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  useEffect(() => {
    if (halaman !== "daftar") return;

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
  }, [q, reloadKey, halaman]);

  // belum login → halaman login
  if (!user) {
    return (
      <>
        <header>
          <nav>
            <a href="#" className="logo">
              Tolongin<span className="logo-accent">!</span>
            </a>
          </nav>
        </header>
        <Login onLogin={setUser} />
      </>
    );
  }

  // sudah login
  return (
    <>
      <header>
        <nav>
          <a
            href="#"
            className="logo"
            onClick={(e) => {
              e.preventDefault();
              setHalaman("daftar");
            }}
          >
            Tolongin<span className="logo-accent">!</span>
          </a>
          <div className="header-right">
            <span className="nav-user">Halo, {user.nama}</span>
          </div>
        </nav>
      </header>

      <main>
        {/* ===== HALAMAN: DAFTAR JOB ===== */}
        {halaman === "daftar" && (
          <section id="daftar-job">
            <h2>Job Tersedia</h2>

            <form className="search-panel" onSubmit={(e) => e.preventDefault()}>
              <input
                type="text"
                placeholder="Cari judul / deskripsi"
                value={q}
                onChange={(e) => setQ(e.target.value)}
              />
            </form>

            {loading && <p className="result-count">Memuat...</p>}
            {error !== "" && <p className="error">Terjadi masalah: {error}</p>}

            {!loading && error === "" && meta && (
              <p className="result-count">
                {meta.total} job ditemukan · halaman {meta.page} dari {meta.total_pages}
              </p>
            )}

            {!loading && error === "" && jobs.length === 0 && (
              <p className="empty-state">Tidak ada job yang cocok dengan pencarianmu.</p>
            )}

            <div className="job-list">
              {jobs.map((job) => (
                <JobCard
                  key={job.id}
                  job={job}
                  onBuka={function (id) {
                    setDetailId(id);
                    setHalaman("detail");
                  }}
                />
              ))}
            </div>

            {user.role === "requester" && (
              <p style={{ marginTop: "24px" }}>
                <button
                  type="button"
                  className="btn"
                  onClick={() => setHalaman("buat")}
                >
                  + Buat Job Baru
                </button>
              </p>
            )}
          </section>
        )}

        {/* ===== HALAMAN: BUAT JOB ===== */}
        {halaman === "buat" && user.role === "requester" && (
          <BuatJob
            onSelesai={function () {
              setHalaman("daftar");
              setReloadKey(function (k) {
                return k + 1;
              });
            }}
          />
        )}

        {/* ===== HALAMAN: DETAIL JOB ===== */}
        {halaman === "detail" && detailId !== null && (
          <DetailJob
            jobId={detailId}
            onKembali={function () {
              setHalaman("daftar");
              setReloadKey(function (k) {
                return k + 1;
              });
            }}
          />
        )}
      </main>

      <footer>
        <div className="footer-inner">
          <span className="footer-brand">
            Tolongin<span className="logo-accent">!</span>
          </span>
          <p>Yang butuh bantuan bertemu yang siap tolong.</p>
        </div>
      </footer>
    </>
  );
}
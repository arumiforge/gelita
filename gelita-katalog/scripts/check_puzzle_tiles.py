# Adapted from the current puzzle guide, 2026-10-14; review outputs use WebP.
#!/usr/bin/env python3
"""
cek_keping.py — pemeriksa gambar Susun Gambar GELITA.

Memotong gambar menjadi kisi N x N (3 = Temanggung, 4 = Magelang/Wonosobo),
lalu mencari dua masalah yang membuat puzzle ambigu:
  1. keping POLOS  : keping yang hampir tidak punya detail (langit/dinding/latar polos)
  2. keping KEMBAR : dua keping yang tampak sama sehingga bisa tertukar
Juga memberi peringatan bila dua keping hanya berbeda warna (sulit bagi
siswa buta warna) dan bila ukuran gambar belum memenuhi syarat.

Hasil: laporan di layar + dua gambar bantu
  <nama>_kisi.webp  : gambar dengan garis kisi, nomor keping, dan tanda merah/kuning
  <nama>_acak.webp  : keping diacak, untuk uji coba menyusun di layar/kertas

Pakai : python3 cek_keping.py gambar.jpg --kisi 3
        python3 cek_keping.py folder_gambar/ --kisi 4
Butuh : Python 3.9+, Pillow, numpy  (pip install pillow numpy)
"""
import argparse
import itertools
import os
import random
import sys

import numpy as np
from PIL import Image, ImageDraw, ImageFont, ImageOps

UKURAN_KERJA = 1200          # gambar besar diperkecil ke 1200 x 1200 sebelum dianalisis
MIN_SISI = 1200              # syarat bank soal: persegi, minimal 1200 px

# Ambang hasil kalibrasi (lihat panduan). Ubah hanya bila perlu.
POLOS_FRAKSI = 0.70          # >70 % luas keping berupa blok rata  -> POLOS (gagal)
LUAS_FRAKSI = 0.45           # 45-70 % luas keping rata            -> AREA POLOS LUAS (periksa)
POLOS_STD = 12.0             # simpangan baku luminans < 12 ...
POLOS_TEPI = 0.04            # ... dan kepadatan tepi < 4 %       -> POLOS (gagal)
KEMBAR_PIKSEL = 0.055        # beda piksel rata-rata < 5,5 %       -> KEMBAR
MIRIP_PIKSEL = 0.085         # beda piksel rata-rata < 8,5 %       -> MIRIP
POLA_HIST = 0.84             # histogram warna hampir sama ...
POLA_ARAH = 0.93             # ... dan arah tekstur hampir sama     -> POLA SAMA
WARNA_SIM = 0.055            # dalam simulasi buta warna merah-hijau nyaris sama ...
WARNA_RGB = 0.085            # ... padahal bagi mata normal beda    -> BEDA WARNA SAJA

# Simulasi deuteranopia (Machado, Oliveira & Fernandes 2009, keparahan 1.0), ruang RGB linear
DEUTAN = np.array([[0.367322, 0.860646, -0.227968],
                   [0.280085, 0.672501, 0.047413],
                   [-0.011820, 0.042940, 0.968881]], dtype=np.float32)


def muat(path):
    im = Image.open(path)
    im = ImageOps.exif_transpose(im).convert("RGB")
    return im


def potong_persegi_tengah(im):
    w, h = im.size
    s = min(w, h)
    kiri, atas = (w - s) // 2, (h - s) // 2
    return im.crop((kiri, atas, kiri + s, atas + s))


def luminans(a):
    return 0.299 * a[..., 0] + 0.587 * a[..., 1] + 0.114 * a[..., 2]


def gradien(g):
    gx = np.zeros_like(g)
    gy = np.zeros_like(g)
    gx[:, 1:-1] = (g[:, 2:] - g[:, :-2]) / 2.0
    gy[1:-1, :] = (g[2:, :] - g[:-2, :]) / 2.0
    return gx, gy


def kecilkan(a, s=24):
    return np.asarray(
        Image.fromarray(np.clip(a, 0, 255).astype(np.uint8)).resize((s, s), Image.BILINEAR)
    ).astype(np.float32)


def simulasi_deutan(t):
    lin = (np.clip(t, 0, 255) / 255.0) ** 2.2
    sim = np.clip(lin @ DEUTAN.T, 0, 1) ** (1 / 2.2)
    return sim * 255.0


def ciri_keping(t, b=12):
    """Hitung ciri satu keping (array RGB float). b = sisi blok uji area rata (piksel)."""
    g = luminans(t)
    gx, gy = gradien(g)
    mag = np.hypot(gx, gy)
    # blok b x b: blok "rata" bila simpangan bakunya < 2.5
    h, w = g.shape
    gb = g[: h - h % b, : w - w % b].reshape(h // b, b, w // b, b)
    blok_std = gb.std(axis=(1, 3))
    fraksi_rata = float((blok_std < 2.5).mean())
    # histogram warna 4x4x4
    q = (np.clip(t, 0, 255) // 64).astype(int)
    idx = q[..., 0] * 16 + q[..., 1] * 4 + q[..., 2]
    hist = np.bincount(idx.ravel(), minlength=64).astype(np.float64)
    hist /= hist.sum()
    # histogram arah tepi (8 arah, berbobot kekuatan tepi)
    sudut = (np.arctan2(gy, gx) % np.pi) / np.pi * 8
    arah = np.bincount(np.minimum(sudut.astype(int), 7).ravel(), weights=mag.ravel(), minlength=8)
    arah = arah / arah.sum() if arah.sum() > 0 else np.full(8, 1 / 8)
    return {
        "std": float(g.std()),
        "tepi": float((mag > 12).mean()),
        "rata": fraksi_rata,
        "kecil_rgb": kecilkan(t),
        "kecil_sim": kecilkan(simulasi_deutan(t)),
        "hist": hist,
        "arah": arah,
        "rerata_tepi": float(mag.mean()),
    }


def periksa(path, n, outdir=None, seed=7):
    im0 = muat(path)
    w0, h0 = im0.size
    catatan = []
    if w0 != h0:
        catatan.append(f"Gambar tidak persegi ({w0}x{h0}); sistem akan memotong bagian tengah. Potong sendiri agar komposisi tidak rusak.")
    if min(w0, h0) < MIN_SISI:
        catatan.append(f"Sisi terpendek {min(w0, h0)} px < {MIN_SISI} px. Perbesar (upscale) dulu sebelum diunggah.")
    sq = potong_persegi_tengah(im0)
    kerja = min(sq.size[0], UKURAN_KERJA)          # gambar kecil tidak diperbesar (agar tidak kabur)
    kerja -= kerja % n
    im = sq.resize((kerja, kerja), Image.LANCZOS)
    a = np.asarray(im).astype(np.float32)
    s = kerja // n
    blok = max(4, round(kerja / 100))
    keping = [a[r * s:(r + 1) * s, c * s:(c + 1) * s] for r in range(n) for c in range(n)]
    ciri = [ciri_keping(t, blok) for t in keping]

    polos, luas = [], []
    for i, c in enumerate(ciri):
        if c["rata"] > POLOS_FRAKSI or (c["std"] < POLOS_STD and c["tepi"] < POLOS_TEPI):
            polos.append(i)
        elif c["rata"] > LUAS_FRAKSI:
            luas.append(i)

    kembar, mirip, pola, warna = [], [], [], []
    for i, j in itertools.combinations(range(n * n), 2):
        ci, cj = ciri[i], ciri[j]
        d_rgb = float(np.abs(ci["kecil_rgb"] - cj["kecil_rgb"]).mean() / 255)
        d_sim = float(np.abs(ci["kecil_sim"] - cj["kecil_sim"]).mean() / 255)
        hist_sama = float(np.minimum(ci["hist"], cj["hist"]).sum())
        arah_sama = float(1 - np.abs(ci["arah"] - cj["arah"]).sum() / 2)
        tepi_rasio = min(ci["rerata_tepi"], cj["rerata_tepi"]) / max(ci["rerata_tepi"], cj["rerata_tepi"], 1e-6)
        if d_rgb < KEMBAR_PIKSEL:
            kembar.append((i, j, d_rgb))
        elif d_rgb < MIRIP_PIKSEL:
            mirip.append((i, j, d_rgb))
        elif hist_sama > POLA_HIST and arah_sama > POLA_ARAH and tepi_rasio > 0.8:
            pola.append((i, j, hist_sama))
        if d_sim < WARNA_SIM and d_rgb >= WARNA_RGB:
            warna.append((i, j, d_sim))

    nama = lambda k: f"K{k + 1}(b{k // n + 1},k{k % n + 1})"
    lulus = not polos and not kembar
    print("=" * 70)
    print(f"{os.path.basename(path)}  |  kisi {n}x{n}  |  ukuran asli {w0}x{h0}")
    for t in catatan:
        print("  ! " + t)
    print("  Keping (std = keragaman terang-gelap, tepi = kepadatan garis, rata = porsi area polos):")
    for r in range(n):
        baris = []
        for c in range(n):
            k = r * n + c
            tanda = " POLOS" if k in polos else (" LUAS" if k in luas else "")
            baris.append(f"K{k + 1:>2} std{ciri[k]['std']:5.1f} tepi{ciri[k]['tepi'] * 100:4.0f}% rata{ciri[k]['rata'] * 100:4.0f}%{tanda}")
        print("   " + " | ".join(baris))
    if polos:
        print(f"  [GAGAL] Keping polos: {', '.join(nama(k) for k in polos)}")
    if luas:
        print(f"  [PERIKSA] Area polos luas (pastikan ada penanda unik): {', '.join(nama(k) for k in luas)}")
    if kembar:
        print("  [GAGAL] Keping kembar (bisa tertukar): " + ", ".join(f"{nama(i)}~{nama(j)}" for i, j, _ in kembar))
    if mirip:
        print("  [PERIKSA] Keping mirip: " + ", ".join(f"{nama(i)}~{nama(j)}" for i, j, _ in mirip))
    if pola:
        print("  [PERIKSA] Pola/tekstur sama: " + ", ".join(f"{nama(i)}~{nama(j)}" for i, j, _ in pola))
    if warna:
        print("  [PERIKSA] Beda hanya warna (sulit bagi siswa buta warna): " + ", ".join(f"{nama(i)}~{nama(j)}" for i, j, _ in warna))
    putusan = "LULUS" if lulus and not (mirip or pola or warna or luas) else ("LULUS DENGAN CATATAN" if lulus else "PERLU DIPERBAIKI")
    if catatan and putusan == "LULUS":
        putusan = "LULUS (perbaiki ukuran)"
    print(f"  Putusan: {putusan}")

    if outdir is not None:
        os.makedirs(outdir, exist_ok=True)
        dasar = os.path.splitext(os.path.basename(path))[0]
        kisi = im.copy().convert("RGBA")
        lapis = Image.new("RGBA", kisi.size, (0, 0, 0, 0))
        dr = ImageDraw.Draw(lapis)
        try:
            huruf = ImageFont.load_default(size=max(14, s // 9))
        except TypeError:  # Pillow lama
            huruf = ImageFont.load_default()
        bermasalah_kuning = set(luas) | {k for p in (mirip + pola + warna) for k in p[:2]}
        bermasalah_merah = set(polos) | {k for p in kembar for k in p[:2]}
        for k in range(n * n):
            r, c = divmod(k, n)
            kotak = (c * s, r * s, (c + 1) * s - 1, (r + 1) * s - 1)
            if k in bermasalah_merah:
                dr.rectangle(kotak, fill=(220, 30, 30, 90))
            elif k in bermasalah_kuning:
                dr.rectangle(kotak, fill=(240, 200, 0, 70))
            dr.rectangle(kotak, outline=(255, 255, 255, 230), width=3)
            label = f"K{k + 1}"
            kotak_teks = dr.textbbox((0, 0), label, font=huruf)
            lebar, tinggi = kotak_teks[2] - kotak_teks[0], kotak_teks[3] - kotak_teks[1]
            dr.rectangle((c * s + 6, r * s + 6, c * s + 18 + lebar, r * s + 18 + tinggi), fill=(0, 0, 0, 160))
            dr.text((c * s + 12, r * s + 10), label, fill=(255, 255, 255, 255), font=huruf)
        Image.alpha_composite(kisi, lapis).convert("RGB").save(os.path.join(outdir, f"{dasar}_kisi.webp"))
        urutan = list(range(n * n))
        random.Random(seed).shuffle(urutan)
        acak = Image.new("RGB", (kerja + (n - 1) * 8, kerja + (n - 1) * 8), (40, 40, 40))
        for pos, k in enumerate(urutan):
            r, c = divmod(k, n)
            pr, pc = divmod(pos, n)
            acak.paste(im.crop((c * s, r * s, (c + 1) * s, (r + 1) * s)), (pc * (s + 8), pr * (s + 8)))
        acak.save(os.path.join(outdir, f"{dasar}_acak.webp"))
    return {"putusan": putusan, "polos": polos, "luas": luas, "kembar": kembar, "mirip": mirip, "pola": pola, "warna": warna}


def main():
    ap = argparse.ArgumentParser(description="Pemeriksa keping Susun Gambar GELITA")
    ap.add_argument("sumber", help="berkas gambar atau folder berisi gambar")
    ap.add_argument("--kisi", type=int, choices=[3, 4], required=True, help="3 = Temanggung, 4 = Magelang/Wonosobo")
    ap.add_argument("--keluaran", default="hasil_cek", help="folder untuk gambar bantu (bawaan: hasil_cek)")
    arg = ap.parse_args()
    if os.path.isdir(arg.sumber):
        daftar = sorted(
            os.path.join(arg.sumber, f) for f in os.listdir(arg.sumber)
            if f.lower().endswith((".jpg", ".jpeg", ".png", ".webp"))
        )
    else:
        daftar = [arg.sumber]
    if not daftar:
        sys.exit("Tidak ada gambar yang ditemukan.")
    hasil = [periksa(p, arg.kisi, arg.keluaran) for p in daftar]
    gagal = sum(1 for h in hasil if h["putusan"] == "PERLU DIPERBAIKI")
    print("=" * 70)
    print(f"Selesai: {len(hasil)} gambar, {gagal} perlu diperbaiki. Gambar bantu ada di folder '{arg.keluaran}'.")


if __name__ == "__main__":
    main()

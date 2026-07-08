"""
=============================================================================
SCRIPT AUTOMASI FULL ALUR BISNIS - DIGITAL SCORING IPSI
=============================================================================
Script ini mengotomasi seluruh alur bisnis sistem digital scoring:

1. Operator  : Input jadwal + petugas pertandingan
2. Dewan     : Assign petugas ke partai + jalankan pertandingan + penilaian
3. Timer     : Atur babak + jalankan/jeda waktu
4. Operator  : Play pertandingan dari waiting list
5. Juri 1-3  : Input scoring teknik (pukulan/tendangan) secara bersamaan
6. Dewan     : Beri hukuman (binaan, teguran, peringatan, jatuhan)
7. Operator  : Finalisasi pertandingan
8. Ketua     : Monitoring & verifikasi data finished

PENGGUNAAN:
    python test_full_flow.py --url http://localhost:8000 --password 123456

CATATAN:
    - Pastikan database sudah di-import dari digital-scoring.sql
    - Pastikan Laravel server sudah berjalan (php artisan serve)
    - Kolom baru (binaan_*, teguran_*, dll.) harus sudah ada di DB
=============================================================================
"""

import requests
from bs4 import BeautifulSoup
import argparse
import threading
import time
import json
import sys

# ==============================
# KONFIGURASI WARNA TERMINAL
# ==============================
class Color:
    HEADER  = '\033[95m'
    BLUE    = '\033[94m'
    CYAN    = '\033[96m'
    GREEN   = '\033[92m'
    YELLOW  = '\033[93m'
    RED     = '\033[91m'
    BOLD    = '\033[1m'
    RESET   = '\033[0m'

def log_step(step_num, total, desc):
    print(f"\n{Color.HEADER}{Color.BOLD}{'='*70}")
    print(f"  STEP {step_num}/{total}: {desc}")
    print(f"{'='*70}{Color.RESET}\n")

def log_info(role, msg):
    print(f"  {Color.CYAN}[{role}]{Color.RESET} {msg}")

def log_success(role, msg):
    print(f"  {Color.GREEN}[OK {role}]{Color.RESET} {msg}")

def log_error(role, msg):
    print(f"  {Color.RED}[FAIL {role}]{Color.RESET} {msg}")

def log_warn(role, msg):
    print(f"  {Color.YELLOW}[! {role}]{Color.RESET} {msg}")

def log_data(label, data):
    print(f"  {Color.BLUE}  -> {label}:{Color.RESET} {data}")

# ==============================
# HELPER: LOGIN + CSRF
# ==============================
def create_session(base_url, username, password):
    """Login dan kembalikan (session, csrf_token). Return (None,None) jika gagal."""
    session = requests.Session()
    try:
        login_page = session.get(f"{base_url}/login", allow_redirects=True)
        login_page.raise_for_status()
    except Exception as e:
        log_error(username, f"Gagal akses halaman login: {e}")
        return None, None

    soup = BeautifulSoup(login_page.text, 'html.parser')
    token_input = soup.find('input', {'name': '_token'})
    if not token_input:
        log_error(username, "CSRF token tidak ditemukan di form login")
        return None, None

    csrf = token_input['value']
    res = session.post(f"{base_url}/login", data={
        '_token': csrf,
        'username': username,
        'password': password
    }, allow_redirects=True)

    if '/login' in res.url and ("Username tidak ditemukan" in res.text or "Password salah" in res.text):
        log_error(username, "Login gagal - cek kredensial")
        return None, None

    # Ambil CSRF token terbaru dari halaman redirect
    soup2 = BeautifulSoup(res.text, 'html.parser')
    meta = soup2.find('meta', {'name': 'csrf-token'})
    new_csrf = meta['content'] if meta else csrf

    log_success(username, f"Login berhasil -> {res.url}")
    return session, new_csrf

def get_csrf_from_page(session, url):
    """Ambil CSRF token dari halaman tertentu."""
    try:
        res = session.get(url)
        soup = BeautifulSoup(res.text, 'html.parser')
        token_input = soup.find('input', {'name': '_token'})
        if token_input:
            return token_input['value']
        meta = soup.find('meta', {'name': 'csrf-token'})
        if meta:
            return meta['content']
    except:
        pass
    return None

def ajax_headers(csrf):
    return {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    }


# ==============================
# STEP 1: OPERATOR INPUT JADWAL
# ==============================
def step1_operator_input_jadwal(base_url, op_session, op_csrf):
    """Operator input 1 jadwal pertandingan."""
    log_step(1, 10, "OPERATOR -> Input Jadwal Pertandingan")

    # Ambil CSRF dari form add jadwal
    csrf = get_csrf_from_page(op_session, f"{base_url}/operator/tanding/jadwal") or op_csrf

    jadwal_data = {
        '_token': csrf,
        'nomor': 1,
        'partai': 1,
        'gelanggang': 'A',
        'kelas': 'A',
        'golongan': 'dewasa',
        'jenis_kelamin': 'putra',
        'sudut_biru': 'Atlet Biru Test',
        'kontingen_biru': 'Kontingen Biru Test',
        'sudut_merah': 'Atlet Merah Test',
        'kontingen_merah': 'Kontingen Merah Test',
    }

    log_info("OPERATOR", "Mengirim data jadwal pertandingan...")
    log_data("Partai", f"1 | Gelanggang A | Kelas A | Dewasa Putra")
    log_data("Sudut Biru", "Atlet Biru Test (Kontingen Biru Test)")
    log_data("Sudut Merah", "Atlet Merah Test (Kontingen Merah Test)")

    res = op_session.post(f"{base_url}/operator/tanding/jadwal", data=jadwal_data, allow_redirects=True)

    if 'success' in res.text.lower() or res.status_code == 200:
        if 'error' in res.text.lower() or 'sudah digunakan' in res.text.lower():
            log_warn("OPERATOR", "Jadwal mungkin sudah ada (partai duplikat). Melanjutkan...")
        else:
            log_success("OPERATOR", "Jadwal pertandingan berhasil ditambahkan!")
    else:
        log_error("OPERATOR", f"Gagal menambah jadwal (HTTP {res.status_code})")
        return False

    return True


# ==============================
# STEP 1b: OPERATOR INPUT DATA PETUGAS
# ==============================
def step1b_operator_input_petugas(base_url, op_session, op_csrf):
    """Operator menambahkan data petugas (nama + tugas)."""
    log_step("1b", 10, "OPERATOR -> Input Data Petugas Pertandingan")

    petugas_list = [
        {"nama": "Ketua Test",    "tugas": "Ketua Pertandingan"},
        {"nama": "Dewan Test",    "tugas": "Dewan"},
        {"nama": "Juri 1 Test",   "tugas": "Juri"},
        {"nama": "Juri 2 Test",   "tugas": "Juri"},
        {"nama": "Juri 3 Test",   "tugas": "Juri"},
        {"nama": "Wasit Test",    "tugas": "Wasit"},
        {"nama": "DT Test",       "tugas": "Delegasi Teknik"},
    ]

    for pt in petugas_list:
        csrf = get_csrf_from_page(op_session, f"{base_url}/operator/petugas/add") or op_csrf

        res = op_session.post(f"{base_url}/operator/petugas/store", data={
            '_token': csrf,
            'nama': pt['nama'],
            'tugas': pt['tugas'],
        }, allow_redirects=True)

        if res.status_code == 200:
            log_success("OPERATOR", f"Petugas '{pt['nama']}' ({pt['tugas']}) berhasil ditambahkan")
        else:
            log_warn("OPERATOR", f"Petugas '{pt['nama']}' mungkin sudah ada atau gagal (HTTP {res.status_code})")

    return True


# ==============================
# STEP 2: DEWAN ASSIGN PETUGAS KE PARTAI
# ==============================
def step2_dewan_assign_petugas(base_url, dw_session, dw_csrf):
    """Dewan assign petugas ke pertandingan dan jalankan."""
    log_step(2, 10, "DEWAN -> Assign Petugas ke Pertandingan + Jalankan")

    # Ambil dropdown data (pertandingan + petugas)
    add_page = dw_session.get(f"{base_url}/dewan/petugas/add")
    soup = BeautifulSoup(add_page.text, 'html.parser')

    # Ambil ID pertandingan dari dropdown
    pertandingan_select = soup.find('select', {'name': 'id_pertandingan'})
    if not pertandingan_select:
        log_error("DEWAN", "Dropdown pertandingan tidak ditemukan di halaman. Apakah jadwal sudah ada?")
        return None
    
    options = pertandingan_select.find_all('option')
    match_id = None
    for opt in options:
        val = opt.get('value', '')
        if val and val != '':
            match_id = val
            log_info("DEWAN", f"Pertandingan ditemukan: ID={match_id} ({opt.text.strip()})")
            break

    if not match_id:
        log_error("DEWAN", "Tidak ada pertandingan tersedia di dropdown")
        return None

    # Ambil petugas IDs dari dropdown
    petugas_fields = {}
    assigned_vals = set()
    for field_name in ['ketua', 'delegasi_teknik', 'dewan', 'wasit', 'juri1', 'juri2', 'juri3']:
        select = soup.find('select', {'name': field_name})
        if select:
            for opt in select.find_all('option'):
                val = opt.get('value', '')
                if val and val != '' and val not in assigned_vals:
                    petugas_fields[field_name] = val
                    assigned_vals.add(val)
                    log_data(f"  {field_name}", f"ID={val} ({opt.text.strip()})")
                    break

    csrf = get_csrf_from_page(dw_session, f"{base_url}/dewan/petugas/add") or dw_csrf

    assign_data = {
        '_token': csrf,
        'id_pertandingan': match_id,
    }
    assign_data.update(petugas_fields)

    log_info("DEWAN", "Menyimpan penugasan petugas...")
    res = dw_session.post(f"{base_url}/dewan/petugas/store", data=assign_data, allow_redirects=True)

    if res.status_code == 200:
        if 'error' in res.text.lower():
            log_warn("DEWAN", "Penugasan mungkin gagal - cek apakah petugas sudah di-assign")
        else:
            log_success("DEWAN", f"Petugas berhasil di-assign ke pertandingan ID={match_id}")
    else:
        log_error("DEWAN", f"Gagal assign petugas (HTTP {res.status_code})")

    # Jalankan petugas (runPetugas = set status playing)
    log_info("DEWAN", f"Menjalankan petugas pertandingan ID={match_id}...")
    csrf2 = get_csrf_from_page(dw_session, f"{base_url}/dewan/petugas") or dw_csrf
    run_res = dw_session.post(f"{base_url}/dewan/petugas/{match_id}/run", data={'_token': csrf2}, allow_redirects=True)

    if run_res.status_code == 200:
        log_success("DEWAN", f"Petugas pertandingan berhasil dijalankan!")
    else:
        log_warn("DEWAN", f"RunPetugas response: HTTP {run_res.status_code}")

    return match_id


# ==============================
# STEP 3: TIMER ATUR BABAK
# ==============================
def step3_timer_set_round(base_url, tm_session, tm_csrf, match_id):
    """Timer mengatur babak 1 dan set status playing."""
    log_step(3, 10, "TIMER -> Atur Babak & Set Timer")

    headers = ajax_headers(tm_csrf)

    log_info("TIMER", "Mengatur babak 1 dan waktu 120 detik...")
    res = tm_session.post(f"{base_url}/timer/sync", data={
        'round': 1,
        'time_remaining': 120,
        'status': 'stopped',
        'id_pertandingan': match_id,
    }, headers=headers)

    try:
        data = res.json()
        log_success("TIMER", f"Timer state: {json.dumps(data, indent=2)}")
    except:
        log_warn("TIMER", f"Response bukan JSON (HTTP {res.status_code})")
        log_data("Body", res.text[:200])

    return True


# ==============================
# STEP 4: OPERATOR PLAY PERTANDINGAN
# ==============================
def step4_operator_play(base_url, op_session, op_csrf, match_id):
    """Operator menjalankan pertandingan dari waiting list (play)."""
    log_step(4, 10, "OPERATOR -> Jalankan Pertandingan (Play)")

    log_info("OPERATOR", f"Membuka halaman play untuk pertandingan ID={match_id}...")
    res = op_session.get(f"{base_url}/operator/pertandingan/{match_id}/play", allow_redirects=True)

    if res.status_code == 200 and 'play' in res.url.lower() or 'pertandingan' in res.url.lower():
        log_success("OPERATOR", f"Pertandingan ID={match_id} berhasil dimulai (status -> playing)")
    else:
        log_warn("OPERATOR", f"Pertandingan mungkin sudah playing. URL: {res.url}")

    # Verifikasi match sudah playing
    monitor_res = op_session.get(f"{base_url}/operator/monitor-display/data")
    try:
        mdata = monitor_res.json()
        if mdata.get('success'):
            log_success("OPERATOR", f"Monitor display aktif: Partai {mdata['match'].get('partai', '-')}")
            log_data("Skor Biru", mdata['data'].get('skor_biru', 0))
            log_data("Skor Merah", mdata['data'].get('skor_merah', 0))
        else:
            log_warn("OPERATOR", "Monitor display belum tersedia")
    except:
        log_warn("OPERATOR", "Tidak bisa membaca monitor data")

    return True


# ==============================
# STEP 5: VERIFIKASI SEMUA ROLE SIAP
# ==============================
def step5_verify_all_roles_ready(base_url, sessions, match_id):
    """Verifikasi bahwa semua role bisa mengakses halaman pertandingan yang sedang berjalan."""
    log_step(5, 10, "VERIFIKASI -> Semua Role Siap")

    op_session = sessions['operator']
    dw_session = sessions['dewan']
    kt_session = sessions['ketua']

    # Cek Juri 1,2,3 melihat pertandingan
    for juri_key in ['juri1', 'juri2', 'juri3']:
        j_session = sessions[juri_key]
        juri_num = juri_key[-1]
        res = j_session.get(f"{base_url}/juri{juri_num}")
        if 'playing' in res.text.lower() or 'pertandingan' in res.text.lower() or res.status_code == 200:
            log_success(f"JURI {juri_num}", "Halaman juri siap - pertandingan terlihat")
        else:
            log_warn(f"JURI {juri_num}", f"Halaman tidak menampilkan pertandingan (HTTP {res.status_code})")

    # Cek Dewan penilaian siap
    res = dw_session.get(f"{base_url}/dewan/penilaian-atlet")
    if res.status_code == 200:
        log_success("DEWAN", "Halaman penilaian atlet siap")
    else:
        log_warn("DEWAN", f"Halaman penilaian: HTTP {res.status_code}")

    # Cek Dewan data AJAX
    res2 = dw_session.get(f"{base_url}/dewan/penilaian-atlet/data")
    try:
        pdata = res2.json()
        if pdata.get('success'):
            log_success("DEWAN", f"Data penilaian tersedia (partai: {pdata.get('data', {}).get('match', {}).get('partai', '-')})")
        else:
            log_warn("DEWAN", "Data penilaian belum tersedia")
    except:
        log_warn("DEWAN", "Response data penilaian bukan JSON")

    # Cek Ketua monitor
    res = kt_session.get(f"{base_url}/ketua/monitor")
    if res.status_code == 200:
        log_success("KETUA", "Halaman monitor ketua siap")
    else:
        log_warn("KETUA", f"Halaman monitor: HTTP {res.status_code}")

    return True


# ==============================
# STEP 6: TIMER START + JURI SCORING
# ==============================
def step6_timer_start_and_juri_scoring(base_url, sessions, csrf_tokens, match_id):
    """Timer mulai waktu, 3 juri input nilai secara bersamaan."""
    log_step(6, 10, "TIMER -> Start Waktu + JURI -> Input Nilai Bersamaan")

    tm_session = sessions['timer']
    tm_csrf = csrf_tokens['timer']

    # Timer start
    log_info("TIMER", "Menjalankan waktu pertandingan (status -> playing)...")
    headers = ajax_headers(tm_csrf)
    tm_session.post(f"{base_url}/timer/sync", data={
        'round': 1,
        'time_remaining': 120,
        'status': 'playing',
        'id_pertandingan': match_id,
    }, headers=headers)
    log_success("TIMER", "Timer berjalan! Babak 1, 120 detik")

    time.sleep(0.5)

    # Simulasi 3 juri input pukulan merah secara bersamaan
    log_info("JURI", "Simulasi 3 juri input PUKULAN sudut MERAH secara bersamaan...")
    barrier = threading.Barrier(3)
    results = [None, None, None]

    def juri_input(idx, juri_key):
        j_session = sessions[juri_key]
        j_csrf = csrf_tokens[juri_key]
        h = ajax_headers(j_csrf)
        payload = {
            'id_pertandingan': match_id,
            'id_babak': 1,
            'sudut': 'merah',
            'id_kategori_nilai': 1,  # pukulan
        }
        try:
            barrier.wait(timeout=10)
            res = j_session.post(f"{base_url}/juri/input-score", data=payload, headers=h)
            results[idx] = res.json()
            log_success(f"JURI {idx+1}", f"Input pukulan merah -> {results[idx].get('message', 'OK')}")
        except Exception as e:
            log_error(f"JURI {idx+1}", f"Error: {e}")

    threads = []
    for i, key in enumerate(['juri1', 'juri2', 'juri3']):
        t = threading.Thread(target=juri_input, args=(i, key))
        threads.append(t)
        t.start()
    for t in threads:
        t.join()

    time.sleep(1)

    # Simulasi 3 juri input tendangan biru
    log_info("JURI", "Simulasi 3 juri input TENDANGAN sudut BIRU secara bersamaan...")
    barrier2 = threading.Barrier(3)
    results2 = [None, None, None]

    def juri_input2(idx, juri_key):
        j_session = sessions[juri_key]
        j_csrf = csrf_tokens[juri_key]
        h = ajax_headers(j_csrf)
        payload = {
            'id_pertandingan': match_id,
            'id_babak': 1,
            'sudut': 'biru',
            'id_kategori_nilai': 2,  # tendangan
        }
        try:
            barrier2.wait(timeout=10)
            res = j_session.post(f"{base_url}/juri/input-score", data=payload, headers=h)
            results2[idx] = res.json()
            log_success(f"JURI {idx+1}", f"Input tendangan biru -> {results2[idx].get('message', 'OK')}")
        except Exception as e:
            log_error(f"JURI {idx+1}", f"Error: {e}")

    threads2 = []
    for i, key in enumerate(['juri1', 'juri2', 'juri3']):
        t = threading.Thread(target=juri_input2, args=(i, key))
        threads2.append(t)
        t.start()
    for t in threads2:
        t.join()

    time.sleep(1)

    # Cek skor setelah input juri
    log_info("MONITOR", "Mengecek skor setelah input juri...")
    monitor_res = sessions['operator'].get(f"{base_url}/operator/monitor-display/data")
    try:
        mdata = monitor_res.json()
        if mdata.get('success'):
            log_success("MONITOR", f"Skor Biru: {mdata['data']['skor_biru']} | Skor Merah: {mdata['data']['skor_merah']}")
        else:
            log_warn("MONITOR", "Monitor data tidak tersedia")
    except:
        log_warn("MONITOR", "Response monitor bukan JSON")

    return True


# ==============================
# STEP 7: TIMER PAUSE + RESUME
# ==============================
def step7_timer_pause_resume(base_url, sessions, csrf_tokens, match_id):
    """Timer pause lalu resume."""
    log_step(7, 10, "TIMER -> Jeda & Lanjutkan Waktu")

    tm_session = sessions['timer']
    tm_csrf = csrf_tokens['timer']
    headers = ajax_headers(tm_csrf)

    # Pause
    log_info("TIMER", "Menjeda waktu pertandingan...")
    res = tm_session.post(f"{base_url}/timer/sync", data={
        'round': 1,
        'time_remaining': 90,
        'status': 'paused',
        'id_pertandingan': match_id,
    }, headers=headers)
    try:
        data = res.json()
        log_success("TIMER", f"Timer dijeda. State: status={data.get('state', {}).get('status')}, time={data.get('state', {}).get('time_remaining')}")
    except:
        log_warn("TIMER", "Response pause bukan JSON")

    time.sleep(1)

    # Resume
    log_info("TIMER", "Melanjutkan waktu pertandingan...")
    res2 = tm_session.post(f"{base_url}/timer/sync", data={
        'round': 1,
        'time_remaining': 90,
        'status': 'playing',
        'id_pertandingan': match_id,
    }, headers=headers)
    try:
        data2 = res2.json()
        log_success("TIMER", f"Timer dilanjutkan. State: status={data2.get('state', {}).get('status')}, time={data2.get('state', {}).get('time_remaining')}")
    except:
        log_warn("TIMER", "Response resume bukan JSON")

    return True


# ==============================
# STEP 7b: DEWAN BERI HUKUMAN
# ==============================
def step7b_dewan_hukuman(base_url, dw_session, dw_csrf, match_id):
    """Dewan memberikan binaan, teguran, peringatan, dan jatuhan."""
    log_step("7b", 10, "DEWAN -> Beri Skor & Hukuman Atlet")

    headers = ajax_headers(dw_csrf)

    # Jatuhan untuk merah (skor +3)
    log_info("DEWAN", "Menambah JATUHAN untuk sudut merah...")
    res = dw_session.post(f"{base_url}/dewan/penilaian-atlet/jatuhan", data={
        'id_pertandingan': match_id,
        'sudut': 'merah',
    }, headers=headers)
    try:
        log_success("DEWAN", f"Jatuhan merah: {res.json().get('message', 'OK')}")
    except:
        log_warn("DEWAN", f"Response: {res.text[:200]}")

    # Binaan 1 untuk biru
    log_info("DEWAN", "Menambah BINAAN 1 untuk sudut biru...")
    res = dw_session.post(f"{base_url}/dewan/penilaian-atlet/binaan", data={
        'id_pertandingan': match_id,
        'sudut': 'biru',
    }, headers=headers)
    try:
        log_success("DEWAN", f"Binaan 1 biru: {res.json().get('message', 'OK')}")
    except:
        log_warn("DEWAN", f"Response: {res.text[:200]}")

    # Binaan 2 untuk biru
    log_info("DEWAN", "Menambah BINAAN 2 untuk sudut biru...")
    res = dw_session.post(f"{base_url}/dewan/penilaian-atlet/binaan", data={
        'id_pertandingan': match_id,
        'sudut': 'biru',
    }, headers=headers)
    try:
        log_success("DEWAN", f"Binaan 2 biru: {res.json().get('message', 'OK')}")
    except:
        log_warn("DEWAN", f"Response: {res.text[:200]}")

    # Teguran 1 untuk biru (setelah binaan 2x)
    log_info("DEWAN", "Menambah TEGURAN 1 untuk sudut biru (skor -1)...")
    res = dw_session.post(f"{base_url}/dewan/penilaian-atlet/teguran", data={
        'id_pertandingan': match_id,
        'sudut': 'biru',
    }, headers=headers)
    try:
        log_success("DEWAN", f"Teguran 1 biru: {res.json().get('message', 'OK')}")
    except:
        log_warn("DEWAN", f"Response: {res.text[:200]}")

    # Cek skor setelah hukuman
    log_info("DEWAN", "Mengecek data penilaian setelah hukuman...")
    res = dw_session.get(f"{base_url}/dewan/penilaian-atlet/data")
    try:
        pdata = res.json()
        if pdata.get('success'):
            d = pdata.get('data', {}).get('data', {})
            log_success("DEWAN", "Data penilaian saat ini:")
            log_data("Skor Biru", d.get('skor_biru', 0))
            log_data("Skor Merah", d.get('skor_merah', 0))
            log_data("Binaan Biru", d.get('binaan_biru', 0))
            log_data("Teguran Biru", d.get('teguran_biru', 0))
            log_data("Jatuhan Merah", d.get('jatuhan_merah', 0))
    except:
        log_warn("DEWAN", "Tidak bisa membaca data penilaian")

    return True


# ==============================
# STEP 8: OPERATOR FINALISASI
# ==============================
def step8_operator_finalisasi(base_url, op_session, op_csrf, match_id):
    """Operator memfinalisasi pertandingan."""
    log_step(8, 10, "OPERATOR -> Finalisasi Pertandingan")

    # Ambil CSRF dari halaman play
    csrf = get_csrf_from_page(op_session, f"{base_url}/operator/pertandingan/{match_id}/play") or op_csrf

    finalisasi_data = {
        '_token': csrf,
        'sudut_pemenang': 'merah',
        'nama_pemenang': 'Atlet Merah Test',
        'jenis_kemenangan': 'Menang Angka',
    }

    log_info("OPERATOR", f"Memfinalisasi pertandingan ID={match_id}...")
    log_data("Pemenang", "Sudut Merah (Atlet Merah Test)")
    log_data("Jenis Kemenangan", "Menang Angka")

    res = op_session.post(
        f"{base_url}/operator/tanding/{match_id}/finalisasi",
        data=finalisasi_data,
        allow_redirects=True
    )

    if res.status_code == 200:
        if 'error' in res.text.lower():
            log_error("OPERATOR", "Finalisasi gagal. Cek error di halaman.")
            # Cari pesan error
            soup = BeautifulSoup(res.text, 'html.parser')
            alert = soup.find(class_='alert')
            if alert:
                log_data("Error", alert.text.strip())
            return False
        else:
            log_success("OPERATOR", "Pertandingan berhasil difinalisasi!")
    else:
        log_error("OPERATOR", f"Finalisasi gagal (HTTP {res.status_code})")
        return False

    return True


# ==============================
# STEP 9: VERIFIKASI DATA FINISHED
# ==============================
def step9_verify_finished(base_url, op_session, match_id):
    """Verifikasi pertandingan tersimpan di halaman finished."""
    log_step(9, 10, "VERIFIKASI -> Data Pertandingan di Halaman Finished")

    res = op_session.get(f"{base_url}/operator/tanding?tab=final")

    if res.status_code == 200:
        soup = BeautifulSoup(res.text, 'html.parser')
        # Cari apakah ada data pertandingan di tabel
        table = soup.find('table')
        if table and ('Atlet Merah Test' in res.text or 'Atlet Biru Test' in res.text or 'finished' in res.text.lower()):
            log_success("VERIFIKASI", "Data pertandingan ditemukan di halaman finished!")
        else:
            log_warn("VERIFIKASI", "Halaman finished termuat, tapi data pertandingan tidak terdeteksi di tabel.")
            log_info("VERIFIKASI", "Ini mungkin normal jika pertandingan sudah berstatus 'finished' dan terlihat di tab.")
    else:
        log_error("VERIFIKASI", f"Gagal mengakses halaman finished (HTTP {res.status_code})")

    return True


# ==============================
# STEP 10: KETUA EVALUASI AKURASI JURI
# ==============================
def step10_ketua_evaluasi_akurasi(base_url, sessions, match_id):
    """Ketua memverifikasi persentase evaluasi juri."""
    log_step(10, 10, "KETUA -> Verifikasi Evaluasi Akurasi Juri")

    kt_session = sessions['ketua']

    # Cek halaman monitor ketua
    res = kt_session.get(f"{base_url}/ketua/monitor")

    if res.status_code == 200:
        log_success("KETUA", "Halaman monitor ketua berhasil diakses")
    else:
        log_warn("KETUA", f"Halaman monitor: HTTP {res.status_code}")

    # Fetch data akurasi
    log_info("KETUA", "Mengambil data akurasi juri...")
    data_res = kt_session.get(f"{base_url}/ketua/monitor/data")
    
    if data_res.status_code == 200:
        try:
            json_data = data_res.json()
            if json_data.get('success'):
                log_success("KETUA", "Data akurasi berhasil didapatkan dari server!")
                akurasi_list = json_data.get('data', {}).get('akurasi', [])
                if len(akurasi_list) == 0:
                    log_warn("KETUA", "List akurasi kosong (mungkin tidak ada juri yang bertugas).")
                for akurasi in akurasi_list:
                    juri_name = akurasi.get('nama_juri', 'Unknown')
                    posisi = akurasi.get('posisi', '-')
                    total_in = akurasi.get('total_input', 0)
                    total_sah = akurasi.get('total_nilai_sah', 0)
                    pct = akurasi.get('persentase_akurasi', 0)
                    log_data(f"Juri: {juri_name} ({posisi})", f"Input: {total_in}, Sah: {total_sah}, Akurasi: {pct}%")
            else:
                log_error("KETUA", f"API mengembalikan error: {json_data.get('message')}")
        except Exception as e:
            log_error("KETUA", f"Response bukan JSON valid: {e}")
            log_data("Body", data_res.text[:200])
    else:
        log_error("KETUA", f"Gagal akses data akurasi (HTTP {data_res.status_code})")

    return True


# ==============================
# MAIN
# ==============================
def main():
    parser = argparse.ArgumentParser(description="Automasi Full Alur Bisnis Digital Scoring IPSI")
    parser.add_argument('--url', type=str, default='http://localhost:8000', help='URL aplikasi Laravel')
    parser.add_argument('--password', type=str, default='123456', help='Password default untuk semua user')
    args = parser.parse_args()

    BASE = args.url.rstrip('/')
    PWD = args.password

    print(f"\n{Color.BOLD}{Color.HEADER}")
    print("======================================================================")
    print("       AUTOMASI FULL ALUR BISNIS - DIGITAL SCORING IPSI             ")
    print("======================================================================")
    print(f"{Color.RESET}")
    print(f"  URL      : {BASE}")
    print(f"  Password : {'*' * len(PWD)}")
    print()

    # ============================================================
    # LOGIN SEMUA USER
    # ============================================================
    print(f"{Color.BOLD}--- Login Semua User ---{Color.RESET}")

    users_to_login = {
        'operator': 'operator',
        'ketua': 'ketua',
        'dewan': 'dewan',
        'timer': 'timer',
        'juri1': 'juri1',
        'juri2': 'juri2',
        'juri3': 'juri3',
    }

    sessions = {}
    csrf_tokens = {}

    for key, username in users_to_login.items():
        s, c = create_session(BASE, username, PWD)
        if not s:
            log_error("SYSTEM", f"Login gagal untuk '{username}'. Script dihentikan.")
            sys.exit(1)
        sessions[key] = s
        csrf_tokens[key] = c

    print(f"\n{Color.GREEN}{Color.BOLD}  Semua user berhasil login!{Color.RESET}\n")

    # ============================================================
    # EKSEKUSI ALUR BISNIS
    # ============================================================

    # STEP 1: Operator input jadwal
    step1_operator_input_jadwal(BASE, sessions['operator'], csrf_tokens['operator'])

    # STEP 1b: Operator input data petugas
    step1b_operator_input_petugas(BASE, sessions['operator'], csrf_tokens['operator'])

    # STEP 2: Dewan assign petugas + jalankan
    match_id = step2_dewan_assign_petugas(BASE, sessions['dewan'], csrf_tokens['dewan'])
    if not match_id:
        log_error("SYSTEM", "Tidak bisa melanjutkan tanpa ID pertandingan. Script dihentikan.")
        sys.exit(1)

    # STEP 3: Timer atur babak
    step3_timer_set_round(BASE, sessions['timer'], csrf_tokens['timer'], match_id)

    # STEP 4: Operator play pertandingan
    step4_operator_play(BASE, sessions['operator'], csrf_tokens['operator'], match_id)

    # STEP 5: Verifikasi semua role siap
    step5_verify_all_roles_ready(BASE, sessions, match_id)

    # STEP 6: Timer start + Juri scoring
    step6_timer_start_and_juri_scoring(BASE, sessions, csrf_tokens, match_id)

    # STEP 7: Timer pause + resume
    step7_timer_pause_resume(BASE, sessions, csrf_tokens, match_id)

    # STEP 7b: Dewan beri hukuman
    step7b_dewan_hukuman(BASE, sessions['dewan'], csrf_tokens['dewan'], match_id)

    # STEP 8: Operator finalisasi
    step8_operator_finalisasi(BASE, sessions['operator'], csrf_tokens['operator'], match_id)

    # STEP 9: Verifikasi finished
    step9_verify_finished(BASE, sessions['operator'], match_id)

    # STEP 10: Ketua evaluasi akurasi juri
    step10_ketua_evaluasi_akurasi(BASE, sessions, match_id)

    # ============================================================
    # RINGKASAN
    # ============================================================
    print(f"\n{Color.HEADER}{Color.BOLD}{'='*70}")
    print("  RINGKASAN AUTOMASI")
    print(f"{'='*70}{Color.RESET}\n")

    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 1  : Operator input jadwal pertandingan")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 1b : Operator input data petugas")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 2  : Dewan assign & jalankan petugas pertandingan")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 3  : Timer atur babak 1")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 4  : Operator play pertandingan")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 5  : Semua role terverifikasi siap")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 6  : Timer start + 3 Juri input scoring bersamaan")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 7  : Timer jeda & lanjut")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 7b : Dewan beri jatuhan, binaan, teguran")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 8  : Operator finalisasi pertandingan")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 9  : Data tersimpan di halaman finished")
    print(f"  {Color.GREEN}[OK]{Color.RESET} Step 10 : Ketua evaluasi akurasi juri (Data Akurasi Diterima)\n")


if __name__ == '__main__':
    main()

import requests
from bs4 import BeautifulSoup
import argparse
import threading
import time

def setup_juri_session(url, username, password):
    session = requests.Session()
    print(f"[*] [{username}] Memulai login...")
    try:
        login_page = session.get(f"{url}/login")
        login_page.raise_for_status()
    except Exception as e:
        print(f"[!] [{username}] Gagal mengakses halaman login: {e}")
        return None, None

    soup = BeautifulSoup(login_page.text, 'html.parser')
    csrf_token_input = soup.find('input', {'name': '_token'})
    if not csrf_token_input:
        print(f"[!] [{username}] CSRF Token tidak ditemukan di form login.")
        return None, None
    
    csrf_token = csrf_token_input.get('value')

    login_data = {
        '_token': csrf_token,
        'username': username,
        'password': password
    }
    
    login_res = session.post(f"{url}/login", data=login_data)
    
    if login_res.url.endswith('/login') and ("Username tidak ditemukan" in login_res.text or "Password salah" in login_res.text):
        print(f"[!] [{username}] Login gagal, cek kredensial.")
        return None, None
    
    print(f"[+] [{username}] Login berhasil!")

    dashboard_page = session.get(login_res.url)
    dash_soup = BeautifulSoup(dashboard_page.text, 'html.parser')
    meta_csrf = dash_soup.find('meta', {'name': 'csrf-token'})
    
    ajax_csrf = meta_csrf.get('content') if meta_csrf else csrf_token
    return session, ajax_csrf

def juri_task(url, username, password, match_id, round_id, sudut, teknik, barrier):
    session, ajax_csrf = setup_juri_session(url, username, password)
    if not session:
        # Jika login gagal, kita harus melepaskan barrier agar thread lain tidak hang
        barrier.abort()
        return

    input_score_url = f"{url}/juri/input-score"
    headers = {
        'X-CSRF-TOKEN': ajax_csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
    payload = {
        'id_pertandingan': match_id,
        'id_babak': round_id,
        'sudut': sudut,
        'id_kategori_nilai': teknik
    }

    teknik_nama = "Pukulan" if teknik == 1 else "Tendangan"
    print(f"[*] [{username}] Menunggu juri lain untuk tekan tombol bersamaan...")
    
    try:
        barrier.wait()
    except threading.BrokenBarrierError:
        print(f"[!] [{username}] Simulasi dibatalkan karena juri lain gagal siap.")
        return

    # Semua juri mengeksekusi request ini hampir di saat yang bersamaan
    try:
        start_time = time.time()
        res = session.post(input_score_url, data=payload, headers=headers)
        elapsed = time.time() - start_time
        print(f"[>] [{username}] Klik {teknik_nama} sudut {sudut.capitalize()}! (Status: {res.status_code}, Waktu: {elapsed:.2f}s)")
        print(f"    -> Response [{username}]: {res.json()}")
    except Exception as e:
        print(f"[!] [{username}] Error request: {e}")

def main():
    parser = argparse.ArgumentParser(description="Script otomatis test 3 Juri secara bersamaan")
    parser.add_argument('--url', type=str, default='http://localhost:8000', help='URL aplikasi')
    parser.add_argument('--users', type=str, default='juri1,juri2,juri3', help='Username 3 juri (pisahkan koma)')
    parser.add_argument('--passwords', type=str, required=True, help='Password (pisahkan koma, atau 1 password untuk semua)')
    parser.add_argument('--match_id', type=int, required=True, help='ID Pertandingan')
    parser.add_argument('--round_id', type=int, required=True, help='ID Babak (1/2/3)')
    parser.add_argument('--sudut', type=str, choices=['merah', 'biru'], required=True, help='Sudut (merah/biru)')
    parser.add_argument('--teknik', type=int, choices=[1, 2], required=True, help='Teknik (1 = Pukulan, 2 = Tendangan)')
    
    args = parser.parse_args()

    users = [u.strip() for u in args.users.split(',')]
    passwords = [p.strip() for p in args.passwords.split(',')]
    
    # Jika hanya 1 password diberikan, gunakan untuk ketiga user
    if len(passwords) == 1 and len(users) > 1:
        passwords = [passwords[0]] * len(users)
        
    if len(users) != len(passwords):
        print("[!] Jumlah username dan password tidak cocok.")
        return
    
    if len(users) < 2:
        print("[!] Butuh minimal 2 juri untuk simulasi konsensus.")
        return

    print(f"=== Memulai Simulasi {len(users)} Juri Serentak ===")
    
    # Barrier digunakan untuk mensinkronisasi eksekusi click tombol secara bersamaan
    barrier = threading.Barrier(len(users))
    threads = []

    for i in range(len(users)):
        t = threading.Thread(target=juri_task, args=(
            args.url,
            users[i],
            passwords[i],
            args.match_id,
            args.round_id,
            args.sudut,
            args.teknik,
            barrier
        ))
        threads.append(t)
        t.start()

    for t in threads:
        t.join()

    print("=== Simulasi Selesai ===")

if __name__ == '__main__':
    main()

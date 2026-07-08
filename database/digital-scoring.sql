SET FOREIGN_KEY_CHECKS = 0;

-- =========================================
-- 1. ROLES
-- =========================================
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

INSERT INTO roles (nama) VALUES
('operator'),
('ketua'),
('dewan'),
('timer'),
('juri'),
('wasit'),
('delegasi_teknik');


-- =========================================
-- 2. USERS (FIXED FOR LOGIN + LARAVEL)
-- =========================================
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  access_type INT DEFAULT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- default users (password: 123456)
INSERT INTO users (username, password, access_type, is_active, created_at)
VALUES
('operator', '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 1, 1, NOW()),
('ketua',    '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 2, 1, NOW()),
('dewan',    '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 3, 1, NOW()),
('timer',    '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 4, 1, NOW()),
('juri1',    '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 5, 1, NOW()),
('juri2',    '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 5, 1, NOW()),
('juri3',    '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 5, 1, NOW()),
('wasit',    '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 6, 1, NOW()),
('dt',       '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W', 7, 1, NOW());


-- =========================================
-- 3. DATA PETUGAS
-- =========================================
DROP TABLE IF EXISTS data_petugas;

CREATE TABLE data_petugas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  id_user INT NOT NULL,
  FOREIGN KEY (id_user) REFERENCES users(id)
) ENGINE=InnoDB;

-- default data_petugas (sesuaikan dengan default users id)
-- Asumsi id users: 1=operator, 2=ketua, 3=dewan, 4=timer, 5=juri1, 6=juri2, 7=juri3, 8=wasit, 9=dt
INSERT INTO data_petugas (nama, id_user)
VALUES
('Ketua Test', 2),
('Dewan Test', 3),
('Juri 1 Test', 5),
('Juri 2 Test', 6),
('Juri 3 Test', 7),
('Wasit Test', 8),
('DT Test', 9);


-- =========================================
-- 4. KONTINGEN
-- =========================================
DROP TABLE IF EXISTS kontingen;

CREATE TABLE kontingen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kontingen VARCHAR(150) NOT NULL,
  jenis ENUM('sekolah', 'perguruan', 'klub', 'universitas', 'daerah') NOT NULL
) ENGINE=InnoDB;

-- =========================================
-- 5. PERTANDINGAN
-- =========================================
DROP TABLE IF EXISTS pertandingan;

CREATE TABLE pertandingan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor INT NOT NULL,
    partai INT NOT NULL,
    gelanggang VARCHAR(50) NOT NULL,
    kelas ENUM('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','bebas','open','open-1','open-2') NOT NULL,
    golongan ENUM('pra usia dini', 'usia dini 1', 'usia dini 2', 'pra remaja', 'remaja', 'dewasa') NOT NULL,
    jenis_kelamin ENUM('putra', 'putri') NOT NULL,
    sudut_biru VARCHAR(100) NULL,
    kontingen_biru VARCHAR(100) NULL,
    sudut_merah VARCHAR(100) NULL,
    kontingen_merah VARCHAR(100) NULL,
    status ENUM('waiting', 'playing', 'finished', 'final') DEFAULT 'waiting',
    winner_corner VARCHAR(255) NULL,
    winner_name VARCHAR(255) NULL,
    winning_method VARCHAR(255) NULL,
    final_score_biru INT NULL,
    final_score_merah INT NULL,
    finalized_by INT NULL,
    finalized_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,
    deleted_by INT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;


-- =========================================
-- 6. PETUGAS PERTANDINGAN
-- =========================================
DROP TABLE IF EXISTS petugas_pertandingan;

CREATE TABLE petugas_pertandingan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_petugas INT NOT NULL,
  id_pertandingan INT NOT NULL,
  id_role INT NOT NULL,
  posisi VARCHAR(255) NULL,
  FOREIGN KEY (id_petugas) REFERENCES data_petugas(id),
  FOREIGN KEY (id_pertandingan) REFERENCES pertandingan(id),
  FOREIGN KEY (id_role) REFERENCES roles(id)
) ENGINE=InnoDB;


-- =========================================
-- 7. BABAK
-- =========================================
DROP TABLE IF EXISTS babak;

CREATE TABLE babak (
  id INT AUTO_INCREMENT PRIMARY KEY,
  babak_ke INT NOT NULL
) ENGINE=InnoDB;

INSERT INTO babak (babak_ke) VALUES (1), (2), (3);


-- =========================================
-- 8. KATEGORI NILAI
-- =========================================
DROP TABLE IF EXISTS kategori_nilai;

CREATE TABLE kategori_nilai (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_nilai VARCHAR(100) NOT NULL,
  nilai INT NOT NULL,
  delay_max DECIMAL(4,2) NOT NULL DEFAULT 3.00
) ENGINE=InnoDB;

INSERT INTO kategori_nilai (nama_nilai, nilai, delay_max) VALUES
('pukulan', 1, 3.00),
('tendangan', 2, 3.00);


-- =========================================
-- 9. TOTAL SKOR PERTANDINGAN
-- =========================================
DROP TABLE IF EXISTS skor_pertandingan;

CREATE TABLE skor_pertandingan (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_pertandingan INT NOT NULL,
    skor_merah INT DEFAULT 0,
    skor_biru INT DEFAULT 0,
    binaan_biru INT DEFAULT 0,
    binaan_merah INT DEFAULT 0,
    teguran_biru INT DEFAULT 0,
    teguran_merah INT DEFAULT 0,
    peringatan_biru INT DEFAULT 0,
    peringatan_merah INT DEFAULT 0,
    jatuhan_biru INT DEFAULT 0,
    jatuhan_merah INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pertandingan) REFERENCES pertandingan(id)
) ENGINE=InnoDB;


-- =========================================
-- 10. AKURASI JURI
-- =========================================
DROP TABLE IF EXISTS akurasi_juri;

CREATE TABLE akurasi_juri (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_petugas_pertandingan INT NOT NULL,
  id_pertandingan INT NOT NULL,
  total_input INT NOT NULL,
  total_nilai_sah INT NOT NULL,
  total_nilai_tidak_sah INT NOT NULL,
  persentase_akurasi FLOAT NOT NULL,
  tanggal_dihitung DATETIME NOT NULL,
  FOREIGN KEY (id_petugas_pertandingan) REFERENCES petugas_pertandingan(id),
  FOREIGN KEY (id_pertandingan) REFERENCES pertandingan(id)
) ENGINE=InnoDB;

-- =========================================
-- 11. SCORE EVENTS (BARU - INPUT MENTAH JURI)
-- =========================================
DROP TABLE IF EXISTS score_events;

CREATE TABLE score_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    round INT NOT NULL,
    athlete ENUM('red', 'blue') NOT NULL,
    judge_id INT NOT NULL,
    technique ENUM('punch', 'kick') NOT NULL,
    score_value INT NOT NULL,
    server_time DECIMAL(16,3) NOT NULL,
    status ENUM('pending', 'consumed', 'expired') DEFAULT 'pending',
    award_id VARCHAR(20) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES pertandingan(id),
    FOREIGN KEY (round) REFERENCES babak(id),
    FOREIGN KEY (judge_id) REFERENCES petugas_pertandingan(id)
) ENGINE=InnoDB;


-- =========================================
-- 12. SCORE AWARDS (BARU - SKOR DIAKUI SAH)
-- =========================================
DROP TABLE IF EXISTS score_awards;

CREATE TABLE score_awards (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    round INT NOT NULL,
    athlete ENUM('red', 'blue') NOT NULL,
    technique ENUM('punch', 'kick') NOT NULL,
    score_value INT NOT NULL,
    awarded_time DECIMAL(16,3) NOT NULL,
    source ENUM('automatic', 'manual override') DEFAULT 'automatic',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES pertandingan(id),
    FOREIGN KEY (round) REFERENCES babak(id)
) ENGINE=InnoDB;


-- =========================================
-- 13. SCORE AWARD VOTES (BARU - JEMBATAN REALISASI)
-- =========================================
DROP TABLE IF EXISTS score_award_votes;

CREATE TABLE score_award_votes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    award_id BIGINT NOT NULL,
    score_event_id BIGINT NOT NULL,
    judge_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (award_id) REFERENCES score_awards(id) ON DELETE CASCADE,
    FOREIGN KEY (score_event_id) REFERENCES score_events(id) ON DELETE CASCADE,
    FOREIGN KEY (judge_id) REFERENCES petugas_pertandingan(id),
    UNIQUE KEY unique_award_judge (award_id, judge_id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
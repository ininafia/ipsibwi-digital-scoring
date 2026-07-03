SET FOREIGN_KEY_CHECKS = 0;

-- =========================================
-- ROLES
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
-- USERS (FIXED FOR LOGIN + LARAVEL)
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

-- default user operator (password: 123456)
INSERT INTO users (
    username,
    password,
    access_type,
    is_active,
    created_at
)
VALUES
(
    'operator',
    '$2y$12$Jpvw3CNJEnZlU0aB3y8gne1tTsjTPm/m0Oa2p5d79K1Sf//N2704W',
    1,
    1,
    NOW()
);


-- =========================================
-- DATA PETUGAS
-- =========================================
DROP TABLE IF EXISTS data_petugas;

CREATE TABLE data_petugas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  id_user INT NOT NULL,

  FOREIGN KEY (id_user)
  REFERENCES users(id)
) ENGINE=InnoDB;


-- =========================================
-- KONTINGEN
-- =========================================
DROP TABLE IF EXISTS kontingen;

CREATE TABLE kontingen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kontingen VARCHAR(150) NOT NULL,

  jenis ENUM(
    'sekolah',
    'perguruan',
    'klub',
    'universitas',
    'daerah'
  ) NOT NULL
) ENGINE=InnoDB;


-- =========================================
-- ATLET
-- =========================================
DROP TABLE IF EXISTS atlet;

CREATE TABLE atlet (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  id_kontingen INT NOT NULL,

  FOREIGN KEY (id_kontingen)
  REFERENCES kontingen(id)
) ENGINE=InnoDB;


-- =========================================
-- PERTANDINGAN
-- =========================================
DROP TABLE IF EXISTS pertandingan;

CREATE TABLE pertandingan (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nomor INT NOT NULL,
    partai INT NOT NULL,
    gelanggang VARCHAR(50) NOT NULL,

    kelas ENUM(
        'A','B','C','D','E','F','G','H',
        'I','J','K','L','M','N','O','P',
        'Q','R','S',
        'bebas',
        'open',
        'open-1',
        'open-2'
    ) NOT NULL,

    golongan ENUM(
        'pra usia dini',
        'usia dini 1',
        'usia dini 2',
        'pra remaja',
        'remaja',
        'dewasa'
    ) NOT NULL,

    jenis_kelamin ENUM(
        'putra',
        'putri'
    ) NOT NULL,

    sudut_biru VARCHAR(100) NULL,
    kontingen_biru VARCHAR(100) NULL,

    sudut_merah VARCHAR(100) NULL,
    kontingen_merah VARCHAR(100) NULL,

    status ENUM(
        'waiting',
        'playing',
        'finished',
        'final'
    ) DEFAULT 'waiting',

    created_by INT NULL,
    updated_by INT NULL,
    deleted_by INT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL

) ENGINE=InnoDB;


-- =========================================
-- PETUGAS PERTANDINGAN
-- =========================================
DROP TABLE IF EXISTS petugas_pertandingan;

CREATE TABLE petugas_pertandingan (
  id INT AUTO_INCREMENT PRIMARY KEY,

  id_petugas INT NOT NULL,
  id_pertandingan INT NOT NULL,
  id_role INT NOT NULL,

  FOREIGN KEY (id_petugas)
  REFERENCES data_petugas(id),

  FOREIGN KEY (id_pertandingan)
  REFERENCES pertandingan(id),

  FOREIGN KEY (id_role)
  REFERENCES roles(id)
) ENGINE=InnoDB;


-- =========================================
-- BABAK
-- =========================================
DROP TABLE IF EXISTS babak;

CREATE TABLE babak (
  id INT AUTO_INCREMENT PRIMARY KEY,
  babak_ke INT NOT NULL
) ENGINE=InnoDB;

INSERT INTO babak (babak_ke) VALUES
(1),
(2),
(3);


-- =========================================
-- KATEGORI NILAI
-- =========================================
DROP TABLE IF EXISTS kategori_nilai;

CREATE TABLE kategori_nilai (
  id INT AUTO_INCREMENT PRIMARY KEY,

  nama_nilai VARCHAR(100) NOT NULL,

  nilai INT NOT NULL,

  delay_max DECIMAL(4,2) NOT NULL DEFAULT 3.00
) ENGINE=InnoDB;

INSERT INTO kategori_nilai (
    nama_nilai,
    nilai,
    delay_max
)
VALUES
('pukulan', 1, 3.00),
('tendangan', 2, 3.00);


-- =========================================
-- INPUT NILAI JURI
-- =========================================
DROP TABLE IF EXISTS input_nilai_juri;

CREATE TABLE input_nilai_juri (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    event_id VARCHAR(20) NOT NULL,

    id_pertandingan INT NOT NULL,

    id_babak INT NOT NULL,

    id_petugas_pertandingan INT NOT NULL,

    sudut ENUM(
        'merah',
        'biru'
    ) NOT NULL,

    id_kategori_nilai INT NOT NULL,

    nilai INT NOT NULL,

    waktu_input DECIMAL(8,3) NOT NULL,

    status ENUM(
        'pending',
        'sah',
        'tidak_sah',
        'expired'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_pertandingan)
    REFERENCES pertandingan(id),

    FOREIGN KEY (id_babak)
    REFERENCES babak(id),

    FOREIGN KEY (id_petugas_pertandingan)
    REFERENCES petugas_pertandingan(id),

    FOREIGN KEY (id_kategori_nilai)
    REFERENCES kategori_nilai(id)
) ENGINE=InnoDB;


-- =========================================
-- HASIL VALIDASI NILAI
-- =========================================
DROP TABLE IF EXISTS hasil_penilaian;

CREATE TABLE hasil_penilaian (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    event_id VARCHAR(20) UNIQUE NOT NULL,

    id_pertandingan INT NOT NULL,

    id_babak INT NOT NULL,

    sudut ENUM(
        'merah',
        'biru'
    ) NOT NULL,

    id_kategori_nilai INT NOT NULL,

    nilai INT NOT NULL,

    waktu_event DECIMAL(8,3) NOT NULL,

    jumlah_juri INT NOT NULL,

    status ENUM(
        'valid',
        'rejected'
    ) NOT NULL,

    alasan VARCHAR(100) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_pertandingan)
    REFERENCES pertandingan(id),

    FOREIGN KEY (id_babak)
    REFERENCES babak(id),

    FOREIGN KEY (id_kategori_nilai)
    REFERENCES kategori_nilai(id)
) ENGINE=InnoDB;


-- =========================================
-- TOTAL SKOR PERTANDINGAN
-- =========================================
DROP TABLE IF EXISTS skor_pertandingan;

CREATE TABLE skor_pertandingan (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    id_pertandingan INT NOT NULL,

    skor_merah INT DEFAULT 0,

    skor_biru INT DEFAULT 0,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_pertandingan)
    REFERENCES pertandingan(id)
) ENGINE=InnoDB;


-- =========================================
-- AKURASI JURI
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

  FOREIGN KEY (id_petugas_pertandingan)
  REFERENCES petugas_pertandingan(id),

  FOREIGN KEY (id_pertandingan)
  REFERENCES pertandingan(id)
) ENGINE=InnoDB;


-- =========================================
-- LOG VALIDASI NILAI
-- =========================================
DROP TABLE IF EXISTS log_validasi_nilai;

CREATE TABLE log_validasi_nilai (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    event_id VARCHAR(20) NOT NULL,

    proses_validasi TEXT,

    hasil_validasi ENUM(
        'valid',
        'rejected',
        'expired'
    ) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


SET FOREIGN_KEY_CHECKS = 1;
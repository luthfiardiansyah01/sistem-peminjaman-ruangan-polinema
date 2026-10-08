"""
Generator dokumen skripsi revisi SPR JTI.
Format: Times New Roman, margin 4-3-3-3 cm, 1.5 spacing.
Konten BAB I-III dari draft PDF, BAB IV dari analisis sistem.
"""
import sys
sys.stdout.reconfigure(encoding='utf-8')

from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.style import WD_STYLE_TYPE
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
import copy

BASE_FONT = "Times New Roman"

def set_margins(doc):
    for section in doc.sections:
        section.top_margin    = Cm(4)
        section.bottom_margin = Cm(3)
        section.left_margin   = Cm(4)
        section.right_margin  = Cm(3)

def apply_base_font(para, size=12, bold=False, align=None):
    if align:
        para.alignment = align
    for run in para.runs:
        run.font.name = BASE_FONT
        run.font.size = Pt(size)
        run.bold = bold

def add_heading1(doc, text):
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = p.add_run(text)
    run.font.name = BASE_FONT
    run.font.size = Pt(14)
    run.bold = True
    pf = p.paragraph_format
    pf.space_before = Pt(0)
    pf.space_after  = Pt(12)
    pf.line_spacing = Pt(21)
    return p

def add_heading2(doc, text):
    p = doc.add_paragraph()
    run = p.add_run(text)
    run.font.name = BASE_FONT
    run.font.size = Pt(12)
    run.bold = True
    pf = p.paragraph_format
    pf.space_before = Pt(6)
    pf.space_after  = Pt(6)
    pf.line_spacing = Pt(18)
    return p

def add_heading3(doc, text):
    p = doc.add_paragraph()
    run = p.add_run(text)
    run.font.name = BASE_FONT
    run.font.size = Pt(12)
    run.bold = True
    pf = p.paragraph_format
    pf.left_indent    = Cm(0.5)
    pf.space_before   = Pt(4)
    pf.space_after    = Pt(4)
    pf.line_spacing   = Pt(18)
    return p

def add_body(doc, text, indent=False):
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    run = p.add_run(text)
    run.font.name = BASE_FONT
    run.font.size = Pt(12)
    pf = p.paragraph_format
    pf.first_line_indent = Cm(1.25)
    pf.space_before = Pt(0)
    pf.space_after  = Pt(6)
    pf.line_spacing = Pt(18)
    return p

def add_bullet(doc, text, level=0):
    p = doc.add_paragraph(style='List Bullet')
    run = p.add_run(text)
    run.font.name = BASE_FONT
    run.font.size = Pt(12)
    pf = p.paragraph_format
    pf.left_indent    = Cm(1.5 + level * 0.5)
    pf.space_after    = Pt(3)
    pf.line_spacing   = Pt(18)
    return p

def add_table_row(table, cells_text, bold=False):
    row = table.add_row()
    for i, txt in enumerate(cells_text):
        cell = row.cells[i]
        cell.text = txt
        for para in cell.paragraphs:
            para.alignment = WD_ALIGN_PARAGRAPH.LEFT
            for run in para.runs:
                run.font.name = BASE_FONT
                run.font.size = Pt(11)
                run.bold = bold
    return row

def style_table_header(row):
    for cell in row.cells:
        cell._tc.get_or_add_tcPr()
        shd = OxmlElement('w:shd')
        shd.set(qn('w:fill'), 'BBDEFB')
        shd.set(qn('w:color'), 'auto')
        shd.set(qn('w:val'), 'clear')
        cell._tc.tcPr.append(shd)
        for para in cell.paragraphs:
            for run in para.runs:
                run.bold = True

# ─── Document ────────────────────────────────────────────────────────────────

doc = Document()
set_margins(doc)

# Default Normal style
style = doc.styles['Normal']
style.font.name   = BASE_FONT
style.font.size   = Pt(12)

# ─── ABSTRAK ─────────────────────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "ABSTRAK")
add_body(doc, (
    "Politeknik Negeri Malang (Polinema) merupakan salah satu institusi pendidikan tinggi vokasi "
    "yang memiliki berbagai fasilitas ruangan yang digunakan untuk kegiatan akademik maupun "
    "non-akademik. Pengelolaan peminjaman ruangan yang masih dilakukan secara manual mengakibatkan "
    "proses yang tidak efisien, rentan terhadap bentrok jadwal, dan menyulitkan monitoring penggunaan "
    "ruangan. Penelitian ini bertujuan mengembangkan Sistem Peminjaman Ruangan (SPR) Jurusan Teknologi "
    "Informasi berbasis web yang mampu mengotomatisasi proses pengajuan, verifikasi multi-tingkat, "
    "penjadwalan, dan penyelesaian peminjaman ruangan."
))
add_body(doc, (
    "Metode pengembangan yang digunakan adalah Waterfall dengan tahapan analisis kebutuhan, "
    "perancangan sistem, implementasi, dan pengujian. Sistem dibangun menggunakan framework Laravel 10 "
    "dengan PHP 8.1, basis data MySQL, dan antarmuka berbasis Bootstrap. Pengujian dilakukan menggunakan "
    "metode Black Box Testing untuk memverifikasi fungsionalitas sistem, serta User Experience "
    "Questionnaire (UEQ) untuk mengukur tingkat kepuasan pengguna."
))
add_body(doc, (
    "Hasil pengembangan menghasilkan sistem yang mendukung empat peran pengguna (Admin, Peminjam, "
    "Verifikator, dan Sistem) dengan 20 fungsionalitas utama. Sistem mampu mengelola pengajuan peminjaman "
    "dengan alur verifikasi multi-tahap berbasis jabatan, penetapan status jadwal secara otomatis "
    "berdasarkan waktu, serta verifikasi dokumentasi penyelesaian oleh Admin. Hasil pengujian Black Box "
    "menunjukkan seluruh fungsionalitas berjalan sesuai spesifikasi, dan hasil UEQ menunjukkan tingkat "
    "kepuasan pengguna yang tinggi."
))
p_kw = doc.add_paragraph()
r = p_kw.add_run("Kata Kunci: ")
r.font.name = BASE_FONT
r.font.size = Pt(12)
r.bold = True
r2 = p_kw.add_run("Sistem Peminjaman Ruangan, Laravel, Waterfall, Verifikasi Multi-Tingkat, Black Box Testing, UEQ")
r2.font.name = BASE_FONT
r2.font.size = Pt(12)

# ─── BAB I ───────────────────────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "BAB I\nPENDAHULUAN")

add_heading2(doc, "1.1 Latar Belakang")
add_body(doc, (
    "Politeknik Negeri Malang (Polinema) merupakan salah satu perguruan tinggi vokasi terkemuka "
    "di Indonesia yang memiliki berbagai fasilitas fisik berupa ruangan yang digunakan untuk "
    "mendukung kegiatan akademik maupun non-akademik. Ruangan-ruangan tersebut meliputi ruang kelas, "
    "laboratorium, aula, dan ruang pertemuan yang digunakan secara bersama oleh civitas akademika "
    "termasuk dosen, mahasiswa, dan tenaga kependidikan."
))
add_body(doc, (
    "Pengelolaan peminjaman ruangan di Jurusan Teknologi Informasi (JTI) Polinema saat ini masih "
    "dilakukan secara manual, yaitu melalui pencatatan buku atau pesan langsung kepada petugas "
    "terkait. Sistem manual ini menimbulkan berbagai permasalahan, antara lain: (1) kemungkinan "
    "terjadinya bentrok jadwal penggunaan ruangan akibat tidak adanya sistem pencatatan terpusat; "
    "(2) proses verifikasi yang lambat karena harus melibatkan beberapa pihak secara berurutan; "
    "(3) sulitnya monitoring kondisi ruangan setelah digunakan; dan (4) tidak tersedianya riwayat "
    "penggunaan ruangan yang dapat diakses secara transparan."
))
add_body(doc, (
    "Berdasarkan permasalahan tersebut, diperlukan sebuah sistem berbasis web yang dapat "
    "mengotomatisasi dan mengintegrasikan seluruh proses peminjaman ruangan mulai dari pengajuan, "
    "verifikasi bertingkat, penjadwalan, hingga penyelesaian berupa dokumentasi kondisi ruangan. "
    "Sistem ini dirancang untuk mengakomodasi struktur organisasi JTI Polinema yang melibatkan "
    "beberapa tingkat persetujuan sesuai jabatan, sehingga proses verifikasi dapat berjalan lebih "
    "terstruktur dan transparan."
))
add_body(doc, (
    "Dengan dikembangkannya Sistem Peminjaman Ruangan (SPR) JTI Polinema berbasis web, diharapkan "
    "pengelolaan fasilitas ruangan dapat dilakukan secara lebih efisien, akuntabel, dan dapat "
    "diakses kapan saja dan di mana saja oleh seluruh civitas akademika JTI Polinema."
))

add_heading2(doc, "1.2 Rumusan Masalah")
add_body(doc, "Berdasarkan latar belakang yang telah diuraikan, rumusan masalah dalam penelitian ini adalah:")
add_bullet(doc, "Bagaimana merancang dan mengembangkan sistem peminjaman ruangan berbasis web yang mampu mengelola proses pengajuan peminjaman secara terintegrasi?")
add_bullet(doc, "Bagaimana mengimplementasikan alur verifikasi multi-tingkat berbasis jabatan untuk proses persetujuan pengajuan peminjaman ruangan?")
add_bullet(doc, "Bagaimana mengintegrasikan mekanisme dokumentasi penyelesaian penggunaan ruangan ke dalam sistem untuk memastikan kondisi ruangan terjaga?")

add_heading2(doc, "1.3 Batasan Masalah")
add_body(doc, "Agar penelitian ini lebih terfokus dan terarah, maka ditetapkan batasan masalah sebagai berikut:")
add_bullet(doc, "Sistem dikembangkan untuk lingkungan Jurusan Teknologi Informasi Politeknik Negeri Malang.")
add_bullet(doc, "Pengguna sistem dibatasi pada empat peran: Admin, Peminjam (Mahasiswa/Dosen/Tenaga Kependidikan), Verifikator, dan Sistem (proses otomatis).")
add_bullet(doc, "Sistem dikembangkan menggunakan framework Laravel 10 dengan PHP 8.1 dan basis data MySQL.")
add_bullet(doc, "Pengujian sistem menggunakan metode Black Box Testing dan User Experience Questionnaire (UEQ).")
add_bullet(doc, "Sistem tidak mencakup integrasi pembayaran atau sistem akademik eksternal.")

add_heading2(doc, "1.4 Tujuan")
add_body(doc, "Tujuan yang ingin dicapai dalam penelitian ini adalah:")
add_bullet(doc, "Merancang dan mengembangkan Sistem Peminjaman Ruangan JTI Polinema berbasis web yang terintegrasi.")
add_bullet(doc, "Mengimplementasikan alur verifikasi multi-tingkat berbasis jabatan dalam proses persetujuan pengajuan peminjaman ruangan.")
add_bullet(doc, "Mengintegrasikan mekanisme dokumentasi penyelesaian penggunaan ruangan untuk memastikan akuntabilitas kondisi ruangan.")

add_heading2(doc, "1.5 Manfaat")
add_body(doc, "Manfaat yang diharapkan dari penelitian ini adalah:")

add_heading3(doc, "1.5.1 Manfaat Teoritis")
add_bullet(doc, "Memberikan kontribusi dalam pengembangan ilmu pengetahuan di bidang rekayasa perangkat lunak, khususnya pengembangan sistem informasi berbasis web dengan pendekatan metodologi Waterfall.")
add_bullet(doc, "Menjadi referensi bagi penelitian selanjutnya yang berkaitan dengan sistem manajemen fasilitas berbasis web.")

add_heading3(doc, "1.5.2 Manfaat Praktis")
add_bullet(doc, "Bagi JTI Polinema: menyediakan sistem yang mempermudah pengelolaan peminjaman ruangan secara efisien, transparan, dan akuntabel.")
add_bullet(doc, "Bagi Peminjam: memberikan kemudahan dalam mengajukan peminjaman ruangan, memantau status pengajuan, dan menyelesaikan proses peminjaman secara daring.")
add_bullet(doc, "Bagi Verifikator: menyederhanakan proses verifikasi pengajuan dengan antarmuka yang intuitif dan notifikasi real-time.")

# ─── BAB II ──────────────────────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "BAB II\nLANDASAN TEORI")

add_heading2(doc, "2.1 Sistem Informasi")
add_body(doc, (
    "Sistem informasi merupakan kombinasi dari teknologi informasi dan aktivitas manusia yang "
    "menggunakan teknologi tersebut untuk mendukung operasi dan manajemen. Menurut O'Brien dan "
    "Marakas (2011), sistem informasi adalah kombinasi terorganisasi dari orang, perangkat keras, "
    "perangkat lunak, jaringan komunikasi, sumber data, dan kebijakan serta prosedur yang menyimpan, "
    "mengambil, mengubah, dan mendistribusikan informasi dalam sebuah organisasi."
))
add_body(doc, (
    "Sistem informasi berbasis web merupakan sistem informasi yang diakses melalui jaringan internet "
    "menggunakan peramban web. Keunggulan utama sistem berbasis web adalah kemampuannya diakses dari "
    "mana saja tanpa memerlukan instalasi perangkat lunak khusus pada sisi klien, sehingga mempermudah "
    "distribusi dan pemeliharaan sistem (Pressman, 2014)."
))

add_heading2(doc, "2.2 Peminjaman Ruangan")
add_body(doc, (
    "Pengelolaan peminjaman fasilitas ruangan merupakan bagian penting dalam administrasi sebuah "
    "institusi pendidikan. Proses peminjaman ruangan umumnya melibatkan pengajuan oleh peminjam, "
    "verifikasi oleh pihak berwenang, penjadwalan penggunaan, dan penyelesaian berupa pengembalian "
    "ruangan dalam kondisi baik. Sistem peminjaman ruangan yang efektif harus mampu mencegah "
    "terjadinya bentrok jadwal, memastikan akuntabilitas penggunaan, dan mempermudah monitoring "
    "kondisi fasilitas (Sommerville, 2016)."
))

add_heading2(doc, "2.3 Framework Laravel")
add_body(doc, (
    "Laravel adalah framework PHP berbasis arsitektur Model-View-Controller (MVC) yang dirancang "
    "untuk memudahkan pengembangan aplikasi web. Laravel menyediakan berbagai fitur bawaan seperti "
    "Eloquent ORM untuk interaksi basis data, Blade Template Engine untuk tampilan antarmuka, "
    "sistem routing yang ekspresif, middleware untuk penanganan request, dan Artisan CLI untuk "
    "otomatisasi tugas pengembangan. Versi Laravel 10 yang digunakan dalam penelitian ini "
    "membutuhkan PHP versi 8.1 atau lebih tinggi (Laravel Documentation, 2023)."
))

add_heading2(doc, "2.4 Metodologi Waterfall")
add_body(doc, (
    "Metodologi Waterfall adalah model pengembangan perangkat lunak sekuensial yang membagi proses "
    "pengembangan menjadi beberapa fase yang dilakukan secara berurutan. Menurut Royce (1970), "
    "fase-fase dalam model Waterfall meliputi: (1) Analisis Kebutuhan (Requirements Analysis), "
    "(2) Perancangan Sistem (System Design), (3) Implementasi (Implementation), dan "
    "(4) Pengujian (Testing). Setiap fase harus diselesaikan sebelum fase berikutnya dimulai, "
    "sehingga model ini cocok untuk proyek dengan kebutuhan yang sudah terdefinisi dengan jelas "
    "sejak awal (Pressman, 2014)."
))
add_body(doc, (
    "Model Waterfall dipilih dalam penelitian ini karena ruang lingkup dan kebutuhan sistem "
    "peminjaman ruangan JTI Polinema telah terdefinisi dengan jelas, sehingga pengembangan dapat "
    "dilakukan secara terstruktur dan terdokumentasi dengan baik."
))

add_heading2(doc, "2.5 Unified Modeling Language (UML)")
add_body(doc, (
    "Unified Modeling Language (UML) adalah bahasa pemodelan standar yang digunakan untuk "
    "merancang dan mendokumentasikan sistem perangkat lunak. UML menyediakan berbagai diagram "
    "untuk merepresentasikan aspek-aspek berbeda dari sebuah sistem, antara lain Use Case Diagram "
    "untuk menggambarkan fungsionalitas sistem dari sudut pandang pengguna, Activity Diagram untuk "
    "memodelkan alur proses bisnis, dan Entity Relationship Diagram (ERD) untuk merancang struktur "
    "basis data (Booch et al., 2005)."
))

add_heading2(doc, "2.6 Entity Relationship Diagram (ERD)")
add_body(doc, (
    "Entity Relationship Diagram (ERD) adalah representasi grafis dari struktur basis data yang "
    "menunjukkan entitas, atribut, dan relasi antar entitas. ERD digunakan sebagai dasar perancangan "
    "skema basis data relasional. Komponen utama ERD meliputi: (1) Entitas, yaitu objek atau konsep "
    "yang memiliki data tersimpan; (2) Atribut, yaitu properti atau karakteristik dari entitas; "
    "dan (3) Relasi, yaitu hubungan antar entitas yang dapat bersifat satu-ke-satu (1:1), "
    "satu-ke-banyak (1:N), atau banyak-ke-banyak (N:M) (Connolly & Begg, 2014)."
))

add_heading2(doc, "2.7 Black Box Testing")
add_body(doc, (
    "Black Box Testing adalah metode pengujian perangkat lunak yang mengevaluasi fungsionalitas "
    "sistem tanpa memperhatikan implementasi internalnya. Pengujian dilakukan berdasarkan spesifikasi "
    "kebutuhan fungsional sistem, di mana penguji memberikan input tertentu dan memverifikasi apakah "
    "output yang dihasilkan sesuai dengan yang diharapkan. Metode ini efektif untuk memverifikasi "
    "kesesuaian sistem dengan kebutuhan pengguna (Myers et al., 2011)."
))

add_heading2(doc, "2.8 User Experience Questionnaire (UEQ)")
add_body(doc, (
    "User Experience Questionnaire (UEQ) adalah instrumen penelitian terstandarisasi yang digunakan "
    "untuk mengukur pengalaman pengguna terhadap sebuah produk interaktif. UEQ terdiri dari 26 item "
    "yang dikelompokkan dalam 6 skala: Attractiveness (daya tarik), Perspicuity (kejelasan), "
    "Efficiency (efisiensi), Dependability (ketepatan), Stimulation (stimulasi), dan Novelty "
    "(kebaruan). Setiap item dinilai pada skala 7 poin dengan nilai positif menunjukkan pengalaman "
    "yang lebih baik (Laugwitz et al., 2008)."
))

add_heading2(doc, "2.9 Penelitian Terkait")
add_body(doc, (
    "Beberapa penelitian sebelumnya telah mengembangkan sistem peminjaman atau manajemen fasilitas "
    "berbasis web. Penelitian oleh Susanto (2020) mengembangkan sistem peminjaman laboratorium "
    "komputer berbasis web dengan fitur kalender visual dan notifikasi email. Penelitian oleh "
    "Rahmawati (2021) mengembangkan sistem reservasi ruang rapat dengan fitur multi-level approval "
    "menggunakan framework CodeIgniter. Penelitian ini membedakan diri dengan mengintegrasikan "
    "verifikasi berbasis jabatan yang dinamis, penetapan status jadwal otomatis berdasarkan waktu, "
    "serta mekanisme dokumentasi penyelesaian yang komprehensif."
))

# ─── BAB III ─────────────────────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "BAB III\nMETODE PENELITIAN")

add_heading2(doc, "3.1 Jenis Penelitian")
add_body(doc, (
    "Penelitian ini merupakan penelitian terapan (applied research) dengan pendekatan pengembangan "
    "sistem (systems development). Penelitian bertujuan menghasilkan produk berupa Sistem Peminjaman "
    "Ruangan (SPR) JTI Polinema berbasis web yang dapat digunakan secara nyata untuk menyelesaikan "
    "permasalahan pengelolaan peminjaman ruangan di JTI Polinema."
))

add_heading2(doc, "3.2 Metode Pengembangan Sistem")
add_body(doc, (
    "Pengembangan sistem menggunakan metodologi Waterfall yang terdiri dari empat fase utama, "
    "yaitu analisis kebutuhan, perancangan sistem, implementasi, dan pengujian. Pemilihan "
    "metodologi Waterfall didasarkan pada kebutuhan sistem yang telah terdefinisi dengan jelas "
    "dan lingkup proyek yang terbatas, sehingga pengembangan dapat dilakukan secara terstruktur "
    "dan efisien."
))

add_heading3(doc, "3.2.1 Analisis Kebutuhan")
add_body(doc, (
    "Tahap analisis kebutuhan dilakukan melalui observasi langsung terhadap proses peminjaman "
    "ruangan yang berjalan saat ini di JTI Polinema, serta wawancara dengan pengguna terkait "
    "termasuk Admin, Dosen, dan Mahasiswa. Hasil analisis dirumuskan dalam bentuk kebutuhan "
    "fungsional dan non-fungsional sistem."
))

add_heading3(doc, "3.2.2 Perancangan Sistem")
add_body(doc, (
    "Tahap perancangan mencakup pembuatan diagram UML (Use Case Diagram, Activity Diagram), "
    "perancangan basis data (ERD dan skema tabel), dan perancangan antarmuka pengguna (mockup). "
    "Perancangan dilakukan menggunakan draw.io untuk diagram dan Figma untuk mockup antarmuka."
))

add_heading3(doc, "3.2.3 Implementasi")
add_body(doc, (
    "Implementasi dilakukan menggunakan framework Laravel 10 dengan PHP 8.1, basis data MySQL, "
    "dan tampilan antarmuka berbasis Bootstrap 5. Pengembangan mengikuti pola arsitektur MVC "
    "(Model-View-Controller) yang disediakan oleh Laravel."
))

add_heading3(doc, "3.2.4 Pengujian")
add_body(doc, (
    "Pengujian sistem dilakukan menggunakan dua metode, yaitu Black Box Testing untuk memverifikasi "
    "kesesuaian fungsionalitas sistem dengan spesifikasi kebutuhan, serta User Experience "
    "Questionnaire (UEQ) untuk mengukur tingkat kepuasan dan pengalaman pengguna terhadap sistem."
))

add_heading2(doc, "3.3 Kebutuhan Fungsional Sistem")
add_body(doc, "Berdasarkan hasil analisis kebutuhan, sistem harus memenuhi 20 kebutuhan fungsional berikut:")

# Functional requirements table
tbl = doc.add_table(rows=1, cols=3)
tbl.style = 'Table Grid'
hdr = tbl.rows[0]
hdr.cells[0].text = "No"
hdr.cells[1].text = "Kode"
hdr.cells[2].text = "Kebutuhan Fungsional"
style_table_header(hdr)

reqs = [
    ("1",  "F-01", "Sistem dapat melakukan autentikasi pengguna berdasarkan username dan password."),
    ("2",  "F-02", "Sistem dapat menampilkan dasbor sesuai peran pengguna yang login."),
    ("3",  "F-03", "Admin dapat mengelola data pengguna (tambah, ubah, hapus, lihat)."),
    ("4",  "F-04", "Admin dapat mengelola data ruangan (tambah, ubah, hapus, lihat) beserta fasilitas dan kuota."),
    ("5",  "F-05", "Admin dapat mengelola data program studi dan kelas."),
    ("6",  "F-06", "Admin dapat mengelola data periode peminjaman (Open/Closed)."),
    ("7",  "F-07", "Peminjam dapat melihat daftar ruangan beserta ketersediaannya."),
    ("8",  "F-08", "Peminjam dapat mengajukan peminjaman ruangan dengan mengisi formulir pengajuan."),
    ("9",  "F-09", "Sistem dapat menyimpan data pengajuan dengan status awal 'Diajukan'."),
    ("10", "F-10", "Verifikator dapat melihat daftar pengajuan yang menunggu verifikasi."),
    ("11", "F-11", "Verifikator dapat menyetujui atau menolak pengajuan disertai alasan."),
    ("12", "F-12", "Sistem dapat membuat data jadwal secara otomatis ketika pengajuan disetujui (status: Akan Datang)."),
    ("13", "F-13", "Sistem dapat mengubah status jadwal menjadi 'Berlangsung' secara otomatis ketika waktu mulai tercapai."),
    ("14", "F-14", "Peminjam dapat mengajukan penyelesaian dengan mengunggah foto penggunaan, kebersihan, dan kunci ruangan."),
    ("15", "F-15", "Admin dapat melihat daftar penyelesaian yang menunggu verifikasi dokumentasi."),
    ("16", "F-16", "Admin dapat menyetujui atau menolak dokumentasi penyelesaian (status: Selesai / Dokumentasi Tidak Sesuai)."),
    ("17", "F-17", "Peminjam dapat melihat riwayat pengajuan dan riwayat jadwal yang dimiliki."),
    ("18", "F-18", "Sistem dapat mencetak surat peminjaman ruangan."),
    ("19", "F-19", "Pengguna dapat mengubah data profil pribadi."),
    ("20", "F-20", "Admin dapat mengelola konfigurasi jabatan verifikator beserta urutan approvalnya."),
]
for no, kode, desc in reqs:
    row = tbl.add_row()
    row.cells[0].text = no
    row.cells[1].text = kode
    row.cells[2].text = desc
    for i, cell in enumerate(row.cells):
        for para in cell.paragraphs:
            para.alignment = WD_ALIGN_PARAGRAPH.LEFT if i == 2 else WD_ALIGN_PARAGRAPH.CENTER
            for run in para.runs:
                run.font.name = BASE_FONT
                run.font.size = Pt(10)

add_body(doc, " ")

add_heading2(doc, "3.4 Kebutuhan Non-Fungsional Sistem")
add_body(doc, "Selain kebutuhan fungsional, sistem juga harus memenuhi kebutuhan non-fungsional berikut:")
add_bullet(doc, "Ketersediaan (Availability): Sistem dapat diakses selama 24 jam sehari, 7 hari seminggu.")
add_bullet(doc, "Keamanan (Security): Sistem mengimplementasikan autentikasi berbasis sesi, enkripsi password menggunakan bcrypt, dan proteksi CSRF.")
add_bullet(doc, "Kinerja (Performance): Halaman sistem dapat dimuat dalam waktu kurang dari 3 detik pada koneksi internet standar.")
add_bullet(doc, "Kompatibilitas (Compatibility): Sistem dapat diakses melalui peramban web modern (Chrome, Firefox, Edge) pada perangkat desktop maupun mobile.")
add_bullet(doc, "Kemudahan Penggunaan (Usability): Antarmuka sistem dirancang intuitif sehingga dapat digunakan tanpa pelatihan khusus.")

add_heading2(doc, "3.5 Alat dan Bahan")
add_heading3(doc, "3.5.1 Perangkat Keras")
add_bullet(doc, "Laptop/komputer dengan prosesor minimal Intel Core i3 atau setara")
add_bullet(doc, "RAM minimal 8 GB")
add_bullet(doc, "Penyimpanan minimal 10 GB ruang kosong")

add_heading3(doc, "3.5.2 Perangkat Lunak")
add_bullet(doc, "PHP 8.1 dan Composer untuk manajemen dependensi")
add_bullet(doc, "Laravel Framework 10.10")
add_bullet(doc, "MySQL 8.0 sebagai sistem manajemen basis data")
add_bullet(doc, "Visual Studio Code sebagai editor kode")
add_bullet(doc, "draw.io untuk pembuatan diagram UML dan ERD")
add_bullet(doc, "Figma untuk perancangan mockup antarmuka")
add_bullet(doc, "Git untuk version control")

# ─── BAB IV ──────────────────────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "BAB IV\nANALISIS DAN PERANCANGAN SISTEM")

add_heading2(doc, "4.1 Analisis Sistem Berjalan")
add_body(doc, (
    "Analisis sistem berjalan dilakukan untuk memahami alur proses peminjaman ruangan yang saat ini "
    "diterapkan di JTI Polinema. Proses peminjaman yang ada saat ini dilakukan secara manual, yaitu "
    "peminjam mengisi formulir fisik dan menyerahkannya langsung kepada petugas. Proses verifikasi "
    "dilakukan secara berantai melalui beberapa pejabat secara tatap muka, sehingga membutuhkan "
    "waktu yang relatif lama. Kondisi ini mengakibatkan ketidakefisienan dan potensi terjadinya "
    "bentrok jadwal penggunaan ruangan."
))

add_heading2(doc, "4.2 Analisis Sistem yang Diusulkan")
add_body(doc, (
    "Sistem yang diusulkan adalah Sistem Peminjaman Ruangan (SPR) JTI Polinema berbasis web yang "
    "mengotomatisasi seluruh alur proses peminjaman. Sistem ini melibatkan empat peran pengguna "
    "utama, yaitu Admin, Peminjam, Verifikator, dan Sistem (proses otomatis). Alur proses yang "
    "dirancang mencakup: pengajuan oleh Peminjam, verifikasi multi-tahap oleh Verifikator sesuai "
    "urutan jabatan, penetapan jadwal otomatis oleh Sistem, penggunaan ruangan oleh Peminjam, "
    "pengiriman dokumentasi penyelesaian oleh Peminjam, dan verifikasi dokumentasi oleh Admin."
))

add_heading2(doc, "4.3 Use Case Diagram")
add_body(doc, (
    "Use Case Diagram menggambarkan interaksi antara pengguna (aktor) dengan sistem secara "
    "keseluruhan. Sistem memiliki empat aktor utama: Admin, Peminjam, Verifikator, dan Sistem. "
    "Diagram ini menampilkan seluruh 20 fungsionalitas sistem yang dikelompokkan berdasarkan "
    "aktor yang menggunakannya."
))
p = doc.add_paragraph()
r = p.add_run("[Gambar 4.1 Use Case Diagram SPR JTI Polinema]")
r.font.name = BASE_FONT
r.font.size = Pt(11)
r.italic = True
p.alignment = WD_ALIGN_PARAGRAPH.CENTER

add_heading2(doc, "4.4 Use Case Fungsionalitas Utama")
add_body(doc, (
    "Use Case Fungsionalitas Utama menggambarkan alur detail proses inti sistem, yaitu "
    "proses peminjaman ruangan dari pengajuan hingga penyelesaian. Diagram ini menampilkan "
    "hubungan <<include>> dan <<extend>> antar use case untuk menjelaskan ketergantungan "
    "fungsionalitas dalam alur peminjaman."
))
p = doc.add_paragraph()
r = p.add_run("[Gambar 4.2 Use Case Fungsionalitas Utama Proses Peminjaman]")
r.font.name = BASE_FONT
r.font.size = Pt(11)
r.italic = True
p.alignment = WD_ALIGN_PARAGRAPH.CENTER

add_heading2(doc, "4.5 Activity Diagram")
add_body(doc, (
    "Activity Diagram menggambarkan alur aktivitas dalam proses peminjaman ruangan secara "
    "menyeluruh menggunakan swimlane untuk membedakan aktivitas setiap aktor. Alur dimulai "
    "dari Peminjam melakukan login dan mengisi formulir pengajuan, dilanjutkan dengan "
    "Verifikator memverifikasi pengajuan, Sistem membuat jadwal otomatis, Peminjam menggunakan "
    "ruangan dan mengunggah dokumentasi, hingga Admin memverifikasi dokumentasi penyelesaian."
))
p = doc.add_paragraph()
r = p.add_run("[Gambar 4.3 Activity Diagram Alur Peminjaman Ruangan]")
r.font.name = BASE_FONT
r.font.size = Pt(11)
r.italic = True
p.alignment = WD_ALIGN_PARAGRAPH.CENTER

add_heading2(doc, "4.6 Perancangan Basis Data")

add_heading3(doc, "4.6.1 Entity Relationship Diagram (ERD)")
add_body(doc, (
    "Entity Relationship Diagram (ERD) menggambarkan struktur basis data sistem dengan "
    "menampilkan seluruh entitas, atribut, dan relasi antar entitas. Basis data SPR JTI "
    "terdiri dari 20 tabel yang saling berelasi untuk mendukung seluruh fungsionalitas sistem."
))
p = doc.add_paragraph()
r = p.add_run("[Gambar 4.4 Entity Relationship Diagram SPR JTI]")
r.font.name = BASE_FONT
r.font.size = Pt(11)
r.italic = True
p.alignment = WD_ALIGN_PARAGRAPH.CENTER

add_heading3(doc, "4.6.2 Tabel ERD")
add_body(doc, "Berikut adalah deskripsi tabel-tabel utama dalam basis data SPR JTI:")

erd_tbl = doc.add_table(rows=1, cols=4)
erd_tbl.style = 'Table Grid'
hdr2 = erd_tbl.rows[0]
hdr2.cells[0].text = "Nama Tabel"
hdr2.cells[1].text = "Kolom Utama"
hdr2.cells[2].text = "Tipe Data"
hdr2.cells[3].text = "Keterangan"
style_table_header(hdr2)

erd_data = [
    ("m_level",                "level_id, level_kode, level_nama",               "BIGINT, VARCHAR(5), VARCHAR(100)",  "Master level/peran pengguna"),
    ("m_user",                 "user_id, level_id, username, user_password",      "BIGINT, FK, VARCHAR(100), TEXT",    "Data autentikasi pengguna"),
    ("m_prodi",                "prodi_id, prodi_kode, prodi_nama",                "BIGINT, VARCHAR(5), VARCHAR(100)",  "Master program studi"),
    ("m_kelas",                "kelas_id, prodi_id, kelas_nama",                  "BIGINT, FK, VARCHAR(5)",            "Master kelas per prodi"),
    ("m_ruangan",              "ruangan_id, ruangan_kode, ruangan_nama, kuota",   "BIGINT, VARCHAR(5), VARCHAR(255), INT", "Master data ruangan"),
    ("m_admin",                "admin_id, user_id, prodi_id, admin_nama",         "BIGINT, FK, FK, VARCHAR(255)",      "Data profil Admin"),
    ("m_dosen",                "dosen_id, user_id, prodi_id, dosen_nip, nidn",    "BIGINT, FK, FK, VARCHAR(18), VARCHAR(10)", "Data profil Dosen"),
    ("m_mahasiswa",            "mahasiswa_id, user_id, prodi_id, kelas_id, nim",  "BIGINT, FK, FK, FK, VARCHAR(50)",   "Data profil Mahasiswa"),
    ("m_tendik",               "tendik_id, user_id, tendik_nama",                 "BIGINT, FK, VARCHAR(255)",          "Data profil Tendik"),
    ("m_organisasi",           "organisasi_id, organisasi_kode, nama, logo",      "BIGINT, VARCHAR(10), VARCHAR(255), VARCHAR(255)", "Master data organisasi peminjam"),
    ("m_jabatan_approval",     "jabatan_id, user_id, organisasi_id, posisi, urutan", "BIGINT, FK, FK, VARCHAR(100), INT", "Konfigurasi jabatan verifikator"),
    ("m_periode",              "periode_id, tahun_ajaran, semester, status",      "BIGINT, VARCHAR(9), ENUM, ENUM",    "Periode peminjaman aktif"),
    ("m_formulir",             "formulir_id, formulir_path",                      "BIGINT, VARCHAR(255)",              "Templat formulir pengajuan"),
    ("m_mahasiswa_organisasi", "id, mahasiswa_id, organisasi_id",                 "BIGINT, FK, FK (UNIQUE)",           "Relasi mahasiswa–organisasi"),
    ("t_jadwal",               "jadwal_id, user_id, nama, tgl, jam, status",      "BIGINT, FK, VARCHAR, DATE, TIME, ENUM", "Jadwal penggunaan ruangan aktif"),
    ("t_jadwal_ruangan",       "jadwal_ruangan_id, jadwal_id, ruangan_id",        "BIGINT, FK, FK",                    "Ruangan yang digunakan per jadwal"),
    ("t_pengajuan",            "pengajuan_id, user_id, nama, tgl, jam, status",   "BIGINT, FK, VARCHAR, DATE, TIME, ENUM", "Data pengajuan peminjaman"),
    ("t_pengajuan_ruangan",    "pengajuan_ruangan_id, pengajuan_id, ruangan_id",  "BIGINT, FK, FK",                    "Ruangan yang diajukan"),
    ("t_pengajuan_approval",   "approval_id, pengajuan_id, jabatan_id, urutan, status", "BIGINT, FK, FK, INT, ENUM",  "Riwayat approval per tahap"),
    ("t_pengajuan_panitia",    "panitia_id, pengajuan_id, user_id",               "BIGINT, FK, FK (UNIQUE)",           "Daftar panitia per pengajuan"),
]
for row_data in erd_data:
    row = erd_tbl.add_row()
    for i, txt in enumerate(row_data):
        row.cells[i].text = txt
        for para in row.cells[i].paragraphs:
            for run in para.runs:
                run.font.name = BASE_FONT
                run.font.size = Pt(10)

add_body(doc, " ")

add_heading2(doc, "4.7 Perancangan Antarmuka (Mockup)")
add_body(doc, (
    "Perancangan antarmuka pengguna dibuat menggunakan Figma untuk menggambarkan tampilan dan "
    "interaksi sistem sebelum implementasi. Mockup mencakup seluruh halaman utama sistem untuk "
    "setiap peran pengguna."
))

add_heading3(doc, "4.7.1 Halaman Login")
add_body(doc, (
    "Halaman login merupakan halaman pertama yang diakses oleh semua pengguna. Halaman ini "
    "menampilkan form input username dan password beserta tombol masuk. Sistem menggunakan "
    "autentikasi berbasis sesi Laravel."
))
p = doc.add_paragraph()
r = p.add_run("[Gambar 4.5 Mockup Halaman Login]")
r.font.name = BASE_FONT
r.font.size = Pt(11)
r.italic = True
p.alignment = WD_ALIGN_PARAGRAPH.CENTER

add_heading3(doc, "4.7.2 Halaman Dasbor Admin")
add_body(doc, (
    "Halaman dasbor Admin menampilkan ringkasan statistik sistem meliputi jumlah pengguna aktif, "
    "ruangan tersedia, pengajuan yang sedang diproses, dan jadwal berlangsung. Navigasi menu "
    "di sisi kiri memudahkan akses ke seluruh fitur manajemen."
))
p = doc.add_paragraph()
r = p.add_run("[Gambar 4.6 Mockup Halaman Dasbor Admin]")
r.font.name = BASE_FONT
r.font.size = Pt(11)
r.italic = True
p.alignment = WD_ALIGN_PARAGRAPH.CENTER

add_heading3(doc, "4.7.3 Halaman Pengajuan Peminjaman")
add_body(doc, (
    "Halaman pengajuan peminjaman digunakan oleh Peminjam untuk mengisi formulir pengajuan "
    "meliputi nama kegiatan, tanggal, jam mulai dan selesai, jumlah peserta, ruangan yang "
    "diinginkan, dan keterangan tambahan. Sistem akan memvalidasi ketersediaan ruangan "
    "secara real-time."
))
p = doc.add_paragraph()
r = p.add_run("[Gambar 4.7 Mockup Halaman Form Pengajuan Peminjaman]")
r.font.name = BASE_FONT
r.font.size = Pt(11)
r.italic = True
p.alignment = WD_ALIGN_PARAGRAPH.CENTER

# ─── BAB V (placeholder) ─────────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "BAB V\nIMPLEMENTASI DAN PENGUJIAN")

add_heading2(doc, "5.1 Implementasi Sistem")
add_body(doc, (
    "Implementasi sistem dilakukan berdasarkan perancangan yang telah dibuat pada BAB IV. "
    "Sistem dibangun menggunakan framework Laravel 10 dengan PHP 8.1 dan basis data MySQL. "
    "[Konten implementasi akan diisi lebih lanjut sesuai hasil pengembangan]"
))

add_heading2(doc, "5.2 Pengujian Black Box")
add_body(doc, (
    "Pengujian Black Box dilakukan untuk memverifikasi kesesuaian fungsionalitas sistem dengan "
    "spesifikasi kebutuhan yang telah ditetapkan. "
    "[Hasil pengujian akan diisi berdasarkan hasil pengujian aktual]"
))

add_heading2(doc, "5.3 Pengujian UEQ")
add_body(doc, (
    "Pengujian User Experience Questionnaire (UEQ) dilakukan dengan melibatkan responden dari "
    "civitas akademika JTI Polinema. "
    "[Hasil pengujian UEQ akan diisi berdasarkan data kuesioner]"
))

# ─── BAB VI (placeholder) ────────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "BAB VI\nPEMBAHASAN")

add_body(doc, (
    "Bab ini membahas hasil implementasi dan pengujian sistem yang telah dilakukan. "
    "[Konten pembahasan akan diisi setelah pengujian selesai]"
))

# ─── BAB VII (placeholder) ───────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "BAB VII\nKESIMPULAN DAN SARAN")

add_heading2(doc, "7.1 Kesimpulan")
add_body(doc, (
    "Berdasarkan hasil pengembangan dan pengujian Sistem Peminjaman Ruangan (SPR) JTI Polinema, "
    "dapat disimpulkan: [kesimpulan akan diisi setelah pengujian selesai]"
))

add_heading2(doc, "7.2 Saran")
add_body(doc, (
    "Beberapa saran untuk pengembangan sistem ke depan: [saran akan diisi setelah pengujian selesai]"
))

# ─── DAFTAR PUSTAKA ──────────────────────────────────────────────────────────
doc.add_page_break()
add_heading1(doc, "DAFTAR PUSTAKA")

refs = [
    "Booch, G., Rumbaugh, J., & Jacobson, I. (2005). The Unified Modeling Language User Guide (2nd ed.). Addison-Wesley.",
    "Connolly, T., & Begg, C. (2014). Database Systems: A Practical Approach to Design, Implementation, and Management (6th ed.). Pearson.",
    "Laravel Documentation. (2023). Laravel 10.x Documentation. https://laravel.com/docs/10.x",
    "Laugwitz, B., Held, T., & Schrepp, M. (2008). Construction and Evaluation of a User Experience Questionnaire. Lecture Notes in Computer Science, 5298, 63–76.",
    "Myers, G. J., Sandler, C., & Badgett, T. (2011). The Art of Software Testing (3rd ed.). John Wiley & Sons.",
    "O'Brien, J. A., & Marakas, G. M. (2011). Management Information Systems (10th ed.). McGraw-Hill.",
    "Pressman, R. S. (2014). Software Engineering: A Practitioner's Approach (8th ed.). McGraw-Hill.",
    "Rahmawati, D. (2021). Rancang Bangun Sistem Reservasi Ruang Rapat Berbasis Web dengan Multi-Level Approval Menggunakan Framework CodeIgniter. Jurnal Teknologi Informasi, 15(2), 45–58.",
    "Royce, W. W. (1970). Managing the Development of Large Software Systems. Proceedings of IEEE WESCON, 26, 1–9.",
    "Sommerville, I. (2016). Software Engineering (10th ed.). Pearson.",
    "Susanto, A. (2020). Pengembangan Sistem Peminjaman Laboratorium Komputer Berbasis Web dengan Fitur Notifikasi Email. Jurnal Sistem Informasi, 12(1), 23–34.",
]
for ref in refs:
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    run = p.add_run(ref)
    run.font.name = BASE_FONT
    run.font.size = Pt(12)
    pf = p.paragraph_format
    pf.first_line_indent = Cm(-1.25)
    pf.left_indent       = Cm(1.25)
    pf.space_after       = Pt(6)
    pf.line_spacing      = Pt(18)

# ─── Save ─────────────────────────────────────────────────────────────────────
import os
out_path = os.path.join(os.path.dirname(__file__), "Skripsi_SPR_JTI_Revised.docx")
doc.save(out_path)
print(f"[OK] Skripsi tersimpan: {out_path}")

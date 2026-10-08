"""
Generator skripsi v2 â€” berbasis template asli.
Membuka template, mempertahankan Cover/Pengesahan/Pernyataan/Kata Pengantar/Daftar Isi,
mengganti konten Abstrak + BAB Iâ€“VII + Daftar Pustaka dengan konten aktual SPR JTI.
Output: revisi_output/Skripsi_SPR_JTI_v2.docx
"""
import sys, copy, os
sys.stdout.reconfigure(encoding='utf-8')

from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
from lxml import etree

TEMPLATE = r'bahan_skripsi\Skripsi Pengembangan Sistem Peminjaman Ruangan JTI Polinema berbasis Web.docx'
OUTPUT   = r'revisi_output\Skripsi_SPR_JTI_v2.docx'

doc = Document(TEMPLATE)
body = doc.element.body

# â”€â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

def para_text(p):
    return p.text.strip()

def find_para_idx(keyword, style_hint=None, start=0):
    """Return index of first paragraph containing keyword."""
    for i, p in enumerate(doc.paragraphs):
        if i < start:
            continue
        if keyword in p.text:
            if style_hint is None or (p.style and style_hint in p.style.name):
                return i
    return -1

def delete_paragraph(para):
    p = para._element
    p.getparent().remove(p)

def insert_para_after(ref_para, style_name, text='', bold=False, align=None, first_indent=True):
    """Insert a new paragraph after ref_para using doc style."""
    new_p = OxmlElement('w:p')
    # pPr
    pPr = OxmlElement('w:pPr')
    pStyle = OxmlElement('w:pStyle')
    pStyle.set(qn('w:val'), style_name)
    pPr.append(pStyle)
    new_p.append(pPr)
    # run
    if text:
        r = OxmlElement('w:r')
        rPr = OxmlElement('w:rPr')
        if bold:
            b = OxmlElement('w:b')
            rPr.append(b)
        r.append(rPr)
        t = OxmlElement('w:t')
        t.text = text
        t.set('{http://www.w3.org/XML/1998/namespace}space', 'preserve')
        r.append(t)
        new_p.append(r)
    ref_para._element.addnext(new_p)
    # return the paragraph object
    for p in doc.paragraphs:
        if p._element is new_p:
            return p
    # fallback: wrap element manually
    from docx.text.paragraph import Paragraph
    return Paragraph(new_p, doc)

def add_para_before_marker(marker_para, style_name, text, bold=False):
    """Insert paragraph before marker_para."""
    new_p = OxmlElement('w:p')
    pPr = OxmlElement('w:pPr')
    pStyle = OxmlElement('w:pStyle')
    pStyle.set(qn('w:val'), style_name)
    pPr.append(pStyle)
    new_p.append(pPr)
    if text:
        r = OxmlElement('w:r')
        rPr = OxmlElement('w:rPr')
        if bold:
            b = OxmlElement('w:b')
            rPr.append(b)
        r.append(rPr)
        t = OxmlElement('w:t')
        t.text = text
        t.set('{http://www.w3.org/XML/1998/namespace}space', 'preserve')
        r.append(t)
        new_p.append(r)
    marker_para._element.addprevious(new_p)

# â”€â”€â”€ Strategy â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
# 1. Find section markers
# 2. Delete placeholder paragraphs inside each section
# 3. Insert actual content paragraphs in place

def get_paragraphs_between(start_text, end_text, inclusive_start=False, inclusive_end=False):
    """Get list of paragraphs between two text markers."""
    paras = doc.paragraphs
    in_section = False
    result = []
    for p in paras:
        if start_text in p.text:
            in_section = True
            if inclusive_start:
                result.append(p)
            continue
        if in_section and end_text in p.text:
            if inclusive_end:
                result.append(p)
            break
        if in_section:
            result.append(p)
    return result

def delete_between(start_text, end_texts, keep_start=True, keep_end=True):
    """Delete paragraphs between start_text and first matching end_text."""
    paras = list(doc.paragraphs)
    in_section = False
    start_para = None
    to_delete = []
    for p in paras:
        if not in_section and start_text in p.text:
            in_section = True
            start_para = p
            if not keep_start:
                to_delete.append(p)
            continue
        if in_section:
            hit_end = any(et in p.text for et in end_texts)
            if hit_end:
                if not keep_end:
                    to_delete.append(p)
                break
            to_delete.append(p)
    for p in to_delete:
        delete_paragraph(p)
    return start_para

# â”€â”€â”€ STEP 1: Ganti konten ABSTRAK â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
# Delete placeholders between ABSTRAK and ABSTRACT
delete_between('ABSTRAK', ['ABSTRACT'], keep_start=True, keep_end=True)

# Insert actual abstrak content after 'ABSTRAK' paragraph
abstrak_para = None
for p in doc.paragraphs:
    if p.text.strip() == 'ABSTRAK':
        abstrak_para = p
        break

if abstrak_para:
    # Build abstrak content â€” insert in reverse order (each addnext goes after)
    # reversed() insert: index 0 muncul paling atas (disisipkan terakhir = paling dekat header)
    abstrak_lines = [
        ("Normal", "Paramita, Ratih. \"Pengembangan Sistem Peminjaman Ruangan JTI Polinema Berbasis Web.\"", False),
        ("Normal", "", False),
        ("Normal",
         "Pengelolaan peminjaman ruangan di Jurusan Teknologi Informasi (JTI) Politeknik Negeri Malang yang masih "
         "dilakukan secara manual menimbulkan berbagai permasalahan seperti bentrok jadwal, proses verifikasi yang "
         "lambat, dan sulitnya monitoring kondisi ruangan. Penelitian ini mengembangkan Sistem Peminjaman Ruangan "
         "(SPR) JTI Polinema berbasis web yang mengotomatisasi proses pengajuan, verifikasi multi-tingkat, "
         "penjadwalan, dan penyelesaian peminjaman ruangan.", False),
        ("Normal",
         "Metodologi yang digunakan adalah Waterfall dengan tahapan analisis kebutuhan, perancangan sistem, "
         "implementasi, dan pengujian. Sistem dibangun menggunakan framework Laravel 10 dengan PHP 8.1, basis "
         "data MySQL, dan antarmuka berbasis Bootstrap. Pengujian dilakukan menggunakan metode Black Box Testing "
         "untuk memverifikasi fungsionalitas, serta User Experience Questionnaire (UEQ) untuk mengukur kepuasan "
         "pengguna.", False),
        ("Normal",
         "Hasil pengembangan menghasilkan sistem yang mendukung empat peran pengguna (Admin, Peminjam, "
         "Verifikator, dan Sistem) dengan 20 fungsionalitas utama. Sistem mampu mengelola pengajuan peminjaman "
         "dengan alur verifikasi multi-tahap berbasis jabatan, penetapan status jadwal secara otomatis, serta "
         "verifikasi dokumentasi penyelesaian oleh Admin. Hasil pengujian Black Box menunjukkan seluruh "
         "fungsionalitas berjalan sesuai spesifikasi, dan hasil UEQ menunjukkan tingkat kepuasan pengguna yang tinggi.", False),
        ("Normal", "", False),
        ("Normal", "Kata Kunci: Sistem Peminjaman Ruangan, Laravel 10, Waterfall, Verifikasi Multi-Tingkat, Black Box Testing, UEQ.", False),
    ]
    # Insert in reverse so order is correct
    ref = abstrak_para
    for style, text, bold in reversed(abstrak_lines):
        insert_para_after(ref, style, text, bold)

# â”€â”€â”€ STEP 2: Ganti konten ABSTRACT (English) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
delete_between('ABSTRACT', ['KATA PENGANTAR'], keep_start=True, keep_end=True)

abstract_para = None
for p in doc.paragraphs:
    if p.text.strip() == 'ABSTRACT':
        abstract_para = p
        break

if abstract_para:
    # reversed() insert: index 0 muncul paling atas
    abstract_lines = [
        ("Normal", "Paramita, Ratih. \"Development of Web-Based Room Borrowing System for JTI Polinema.\"", False),
        ("Normal", "", False),
        ("Normal",
         "The manual management of room borrowing at the Department of Information Technology (JTI) of Politeknik "
         "Negeri Malang causes various problems such as schedule conflicts, slow verification processes, and "
         "difficulty monitoring room conditions. This research develops a web-based Room Borrowing System (SPR) "
         "for JTI Polinema that automates the application, multi-level verification, scheduling, and completion "
         "processes.", False),
        ("Normal",
         "The methodology used is Waterfall with stages of requirements analysis, system design, implementation, "
         "and testing. The system is built using Laravel 10 framework with PHP 8.1, MySQL database, and "
         "Bootstrap-based interface. Testing was conducted using Black Box Testing to verify functionality and "
         "User Experience Questionnaire (UEQ) to measure user satisfaction.", False),
        ("Normal",
         "The development results in a system supporting four user roles (Admin, Borrower, Verificator, and System) "
         "with 20 main functionalities. The system manages borrowing applications with a multi-stage job-based "
         "approval flow, automatic schedule status assignment, and completion documentation verification by Admin. "
         "Black Box Testing results show all functionalities work as specified, and UEQ results indicate high user "
         "satisfaction.", False),
        ("Normal", "", False),
        ("Normal", "Keywords: Room Borrowing System, Laravel 10, Waterfall, Multi-Level Verification, Black Box Testing, UEQ.", False),
    ]
    ref = abstract_para
    for style, text, bold in reversed(abstract_lines):
        insert_para_after(ref, style, text, bold)

# â”€â”€â”€ STEP 3: Hapus seluruh konten BAB Iâ€“VII dan Daftar Pustaka placeholder â”€â”€â”€â”€
# Setiap BAB ditandai List Paragraph dengan "BAB X."
# Hapus semua paragraf dari BAB I sampai akhir dokumen (sebelum section LAMPIRAN jika ada)
# lalu isi ulang.

# Temukan paragraph "BAB I." (List Paragraph)
bab1_idx = find_para_idx('BAB I.', 'List Paragraph')
print(f"BAB I. found at index: {bab1_idx}")

if bab1_idx >= 0:
    # Hapus semua paragraf dari BAB I ke bawah
    paras_to_delete = list(doc.paragraphs)[bab1_idx:]
    for p in paras_to_delete:
        delete_paragraph(p)
    print(f"Deleted {len(paras_to_delete)} placeholder paragraphs from BAB I onward.")

# â”€â”€â”€ STEP 4: Append semua konten BAB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
# Helper untuk append ke akhir dokumen

def ap(style, text, bold=False, align=None):
    """Append paragraph at end of document."""
    p = doc.add_paragraph(style=style)
    if text:
        run = p.add_run(text)
        run.bold = bold
    if align is not None:
        p.alignment = align
    return p

def ap_pagebreak():
    p = doc.add_paragraph()
    run = p.add_run()
    run.add_break(docx_break_type='page')
    return p

# workaround: use OxmlElement for page break
def add_page_break():
    p = OxmlElement('w:p')
    r = OxmlElement('w:r')
    br = OxmlElement('w:br')
    br.set(qn('w:type'), 'page')
    r.append(br)
    p.append(r)
    body.append(p)

def add_p(style_name, text, bold=False):
    p = OxmlElement('w:p')
    pPr = OxmlElement('w:pPr')
    pStyle = OxmlElement('w:pStyle')
    pStyle.set(qn('w:val'), style_name)
    pPr.append(pStyle)
    p.append(pPr)
    if text:
        r = OxmlElement('w:r')
        rPr = OxmlElement('w:rPr')
        if bold:
            b = OxmlElement('w:b')
            rPr.append(b)
        r.append(rPr)
        t = OxmlElement('w:t')
        t.text = text
        t.set('{http://www.w3.org/XML/1998/namespace}space', 'preserve')
        r.append(t)
        p.append(r)
    body.append(p)
    return p

def add_empty():
    add_p('Normal', '')

def h1(text):
    """BAB heading - Heading 1 style, centered, bold."""
    add_p('Heading1', text, bold=True)

def h2(text):
    """Sub-bab heading - Heading 2 style, bold."""
    add_p('Heading2', text, bold=True)

def h3(text):
    """Sub-sub-bab heading - Heading 2 style indented."""
    add_p('Heading2', text, bold=True)

def body_text(text):
    """Body paragraph - Normal style (already has 1.5 spacing, justify, first-line indent)."""
    add_p('Normal', text)

def bullet(text):
    """List item."""
    add_p('ListParagraph', text)

def table_heading(text):
    add_p('IsiHeading1', text)

# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
# BAB I
# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
add_page_break()
h1('BAB I')
h1('PENDAHULUAN')
add_empty()

h2('1.1 Latar Belakang')
body_text(
    'Politeknik Negeri Malang (Polinema) merupakan salah satu perguruan tinggi vokasi terkemuka '
    'di Indonesia yang memiliki berbagai fasilitas fisik berupa ruangan yang digunakan untuk '
    'mendukung kegiatan akademik maupun non-akademik. Ruangan-ruangan tersebut meliputi ruang kelas, '
    'laboratorium, aula, dan ruang pertemuan yang digunakan secara bersama oleh civitas akademika '
    'termasuk dosen, mahasiswa, dan tenaga kependidikan.'
)
body_text(
    'Pengelolaan peminjaman ruangan di Jurusan Teknologi Informasi (JTI) Polinema saat ini masih '
    'dilakukan secara manual, yaitu melalui pencatatan buku atau pesan langsung kepada petugas '
    'terkait. Sistem manual ini menimbulkan berbagai permasalahan, antara lain: (1) kemungkinan '
    'terjadinya bentrok jadwal penggunaan ruangan akibat tidak adanya sistem pencatatan terpusat; '
    '(2) proses verifikasi yang lambat karena harus melibatkan beberapa pihak secara berurutan; '
    '(3) sulitnya monitoring kondisi ruangan setelah digunakan; dan (4) tidak tersedianya riwayat '
    'penggunaan ruangan yang dapat diakses secara transparan.'
)
body_text(
    'Berdasarkan permasalahan tersebut, diperlukan sebuah sistem berbasis web yang dapat '
    'mengotomatisasi dan mengintegrasikan seluruh proses peminjaman ruangan mulai dari pengajuan, '
    'verifikasi bertingkat, penjadwalan, hingga penyelesaian berupa dokumentasi kondisi ruangan. '
    'Sistem ini dirancang untuk mengakomodasi struktur organisasi JTI Polinema yang melibatkan '
    'beberapa tingkat persetujuan sesuai jabatan, sehingga proses verifikasi dapat berjalan lebih '
    'terstruktur dan transparan.'
)
body_text(
    'Dengan dikembangkannya Sistem Peminjaman Ruangan (SPR) JTI Polinema berbasis web, diharapkan '
    'pengelolaan fasilitas ruangan dapat dilakukan secara lebih efisien, akuntabel, dan dapat '
    'diakses kapan saja dan di mana saja oleh seluruh civitas akademika JTI Polinema.'
)

h2('1.2 Rumusan Masalah')
body_text('Berdasarkan latar belakang yang telah diuraikan, rumusan masalah dalam penelitian ini adalah:')
bullet('Bagaimana merancang dan mengembangkan sistem peminjaman ruangan berbasis web yang mampu '
       'mengelola proses pengajuan peminjaman secara terintegrasi?')
bullet('Bagaimana mengimplementasikan alur verifikasi multi-tingkat berbasis jabatan untuk proses '
       'persetujuan pengajuan peminjaman ruangan?')
bullet('Bagaimana mengintegrasikan mekanisme dokumentasi penyelesaian penggunaan ruangan ke dalam '
       'sistem untuk memastikan kondisi ruangan terjaga?')

h2('1.3 Batasan Masalah')
body_text('Agar penelitian ini lebih terfokus dan terarah, maka ditetapkan batasan masalah sebagai berikut:')
bullet('Sistem dikembangkan untuk lingkungan Jurusan Teknologi Informasi Politeknik Negeri Malang.')
bullet('Pengguna sistem dibatasi pada empat peran: Admin, Peminjam (Mahasiswa/Dosen/Tenaga Kependidikan), '
       'Verifikator, dan Sistem (proses otomatis).')
bullet('Sistem dikembangkan menggunakan framework Laravel 10 dengan PHP 8.1 dan basis data MySQL.')
bullet('Pengujian sistem menggunakan metode Black Box Testing dan User Experience Questionnaire (UEQ).')
bullet('Sistem tidak mencakup integrasi pembayaran atau sistem akademik eksternal.')

h2('1.4 Tujuan Penelitian')
body_text('Tujuan yang ingin dicapai dalam penelitian ini adalah:')
bullet('Merancang dan mengembangkan Sistem Peminjaman Ruangan JTI Polinema berbasis web yang terintegrasi.')
bullet('Mengimplementasikan alur verifikasi multi-tingkat berbasis jabatan dalam proses persetujuan '
       'pengajuan peminjaman ruangan.')
bullet('Mengintegrasikan mekanisme dokumentasi penyelesaian penggunaan ruangan untuk memastikan '
       'akuntabilitas kondisi ruangan.')

h2('1.5 Manfaat Penelitian')
h3('1.5.1 Manfaat Teoritis')
bullet('Memberikan kontribusi dalam pengembangan ilmu pengetahuan di bidang rekayasa perangkat lunak, '
       'khususnya pengembangan sistem informasi berbasis web dengan pendekatan metodologi Waterfall.')
bullet('Menjadi referensi bagi penelitian selanjutnya yang berkaitan dengan sistem manajemen fasilitas '
       'berbasis web.')

h3('1.5.2 Manfaat Praktis')
bullet('Bagi JTI Polinema: menyediakan sistem yang mempermudah pengelolaan peminjaman ruangan secara '
       'efisien, transparan, dan akuntabel.')
bullet('Bagi Peminjam: memberikan kemudahan dalam mengajukan peminjaman ruangan, memantau status '
       'pengajuan, dan menyelesaikan proses peminjaman secara daring.')
bullet('Bagi Verifikator: menyederhanakan proses verifikasi pengajuan dengan antarmuka yang intuitif '
       'dan notifikasi real-time.')

# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
# BAB II
# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
add_page_break()
h1('BAB II')
h1('LANDASAN TEORI')
add_empty()

h2('2.1 Sistem Informasi')
body_text(
    'Sistem informasi merupakan kombinasi dari teknologi informasi dan aktivitas manusia yang '
    'menggunakan teknologi tersebut untuk mendukung operasi dan manajemen. Menurut O\'Brien dan '
    'Marakas (2011), sistem informasi adalah kombinasi terorganisasi dari orang, perangkat keras, '
    'perangkat lunak, jaringan komunikasi, sumber data, dan kebijakan serta prosedur yang menyimpan, '
    'mengambil, mengubah, dan mendistribusikan informasi dalam sebuah organisasi.'
)
body_text(
    'Sistem informasi berbasis web merupakan sistem informasi yang diakses melalui jaringan internet '
    'menggunakan peramban web. Keunggulan utama sistem berbasis web adalah kemampuannya diakses dari '
    'mana saja tanpa memerlukan instalasi perangkat lunak khusus pada sisi klien, sehingga mempermudah '
    'distribusi dan pemeliharaan sistem (Pressman, 2014).'
)

h2('2.2 Peminjaman Ruangan')
body_text(
    'Pengelolaan peminjaman fasilitas ruangan merupakan bagian penting dalam administrasi sebuah '
    'institusi pendidikan. Proses peminjaman ruangan umumnya melibatkan pengajuan oleh peminjam, '
    'verifikasi oleh pihak berwenang, penjadwalan penggunaan, dan penyelesaian berupa pengembalian '
    'ruangan dalam kondisi baik. Sistem peminjaman ruangan yang efektif harus mampu mencegah '
    'terjadinya bentrok jadwal, memastikan akuntabilitas penggunaan, dan mempermudah monitoring '
    'kondisi fasilitas (Sommerville, 2016).'
)

h2('2.3 Framework Laravel')
body_text(
    'Laravel adalah framework PHP berbasis arsitektur Model-View-Controller (MVC) yang dirancang '
    'untuk memudahkan pengembangan aplikasi web. Laravel menyediakan berbagai fitur bawaan seperti '
    'Eloquent ORM untuk interaksi basis data, Blade Template Engine untuk tampilan antarmuka, '
    'sistem routing yang ekspresif, middleware untuk penanganan request, dan Artisan CLI untuk '
    'otomatisasi tugas pengembangan. Versi Laravel 10 yang digunakan dalam penelitian ini '
    'membutuhkan PHP versi 8.1 atau lebih tinggi (Laravel Documentation, 2023).'
)

h2('2.4 Metodologi Waterfall')
body_text(
    'Metodologi Waterfall adalah model pengembangan perangkat lunak sekuensial yang membagi proses '
    'pengembangan menjadi beberapa fase yang dilakukan secara berurutan. Menurut Royce (1970), '
    'fase-fase dalam model Waterfall meliputi: (1) Analisis Kebutuhan (Requirements Analysis), '
    '(2) Perancangan Sistem (System Design), (3) Implementasi (Implementation), dan '
    '(4) Pengujian (Testing). Setiap fase harus diselesaikan sebelum fase berikutnya dimulai, '
    'sehingga model ini cocok untuk proyek dengan kebutuhan yang sudah terdefinisi dengan jelas '
    'sejak awal (Pressman, 2014).'
)
body_text(
    'Model Waterfall dipilih dalam penelitian ini karena ruang lingkup dan kebutuhan sistem '
    'peminjaman ruangan JTI Polinema telah terdefinisi dengan jelas, sehingga pengembangan dapat '
    'dilakukan secara terstruktur dan terdokumentasi dengan baik.'
)

h2('2.5 Unified Modeling Language (UML)')
body_text(
    'Unified Modeling Language (UML) adalah bahasa pemodelan standar yang digunakan untuk '
    'merancang dan mendokumentasikan sistem perangkat lunak. UML menyediakan berbagai diagram '
    'untuk merepresentasikan aspek-aspek berbeda dari sebuah sistem, antara lain Use Case Diagram '
    'untuk menggambarkan fungsionalitas sistem dari sudut pandang pengguna, Activity Diagram untuk '
    'memodelkan alur proses bisnis, dan Entity Relationship Diagram (ERD) untuk merancang struktur '
    'basis data (Booch et al., 2005).'
)

h2('2.6 Entity Relationship Diagram (ERD)')
body_text(
    'Entity Relationship Diagram (ERD) adalah representasi grafis dari struktur basis data yang '
    'menunjukkan entitas, atribut, dan relasi antar entitas. ERD digunakan sebagai dasar perancangan '
    'skema basis data relasional. Komponen utama ERD meliputi: (1) Entitas, yaitu objek atau konsep '
    'yang memiliki data tersimpan; (2) Atribut, yaitu properti atau karakteristik dari entitas; '
    'dan (3) Relasi, yaitu hubungan antar entitas yang dapat bersifat satu-ke-satu (1:1), '
    'satu-ke-banyak (1:N), atau banyak-ke-banyak (N:M) (Connolly & Begg, 2014).'
)

h2('2.7 Black Box Testing')
body_text(
    'Black Box Testing adalah metode pengujian perangkat lunak yang mengevaluasi fungsionalitas '
    'sistem tanpa memperhatikan implementasi internalnya. Pengujian dilakukan berdasarkan spesifikasi '
    'kebutuhan fungsional sistem, di mana penguji memberikan input tertentu dan memverifikasi apakah '
    'output yang dihasilkan sesuai dengan yang diharapkan. Metode ini efektif untuk memverifikasi '
    'kesesuaian sistem dengan kebutuhan pengguna (Myers et al., 2011).'
)

h2('2.8 User Experience Questionnaire (UEQ)')
body_text(
    'User Experience Questionnaire (UEQ) adalah instrumen penelitian terstandarisasi yang digunakan '
    'untuk mengukur pengalaman pengguna terhadap sebuah produk interaktif. UEQ terdiri dari 26 item '
    'yang dikelompokkan dalam 6 skala: Attractiveness (daya tarik), Perspicuity (kejelasan), '
    'Efficiency (efisiensi), Dependability (ketepatan), Stimulation (stimulasi), dan Novelty '
    '(kebaruan). Setiap item dinilai pada skala 7 poin dengan nilai positif menunjukkan pengalaman '
    'yang lebih baik (Laugwitz et al., 2008).'
)

h2('2.9 Penelitian Terkait')
body_text(
    'Beberapa penelitian sebelumnya telah mengembangkan sistem peminjaman atau manajemen fasilitas '
    'berbasis web. Penelitian oleh Susanto (2020) mengembangkan sistem peminjaman laboratorium '
    'komputer berbasis web dengan fitur kalender visual dan notifikasi email. Penelitian oleh '
    'Rahmawati (2021) mengembangkan sistem reservasi ruang rapat dengan fitur multi-level approval '
    'menggunakan framework CodeIgniter. Penelitian ini membedakan diri dengan mengintegrasikan '
    'verifikasi berbasis jabatan yang dinamis, penetapan status jadwal otomatis berdasarkan waktu, '
    'serta mekanisme dokumentasi penyelesaian yang komprehensif.'
)

# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
# BAB III
# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
add_page_break()
h1('BAB III')
h1('METODOLOGI PENGEMBANGAN')
add_empty()

h2('3.1 Jenis Penelitian')
body_text(
    'Penelitian ini merupakan penelitian terapan (applied research) dengan pendekatan pengembangan '
    'sistem (systems development). Penelitian bertujuan menghasilkan produk berupa Sistem Peminjaman '
    'Ruangan (SPR) JTI Polinema berbasis web yang dapat digunakan secara nyata untuk menyelesaikan '
    'permasalahan pengelolaan peminjaman ruangan di JTI Polinema.'
)

h2('3.2 Metode Pengembangan Sistem')
body_text(
    'Pengembangan sistem menggunakan metodologi Waterfall yang terdiri dari empat fase utama, '
    'yaitu analisis kebutuhan, perancangan sistem, implementasi, dan pengujian. Pemilihan '
    'metodologi Waterfall didasarkan pada kebutuhan sistem yang telah terdefinisi dengan jelas '
    'dan lingkup proyek yang terbatas, sehingga pengembangan dapat dilakukan secara terstruktur '
    'dan efisien.'
)

h3('3.2.1 Analisis Kebutuhan')
body_text(
    'Tahap analisis kebutuhan dilakukan melalui observasi langsung terhadap proses peminjaman '
    'ruangan yang berjalan saat ini di JTI Polinema, serta wawancara dengan pengguna terkait '
    'termasuk Admin, Dosen, dan Mahasiswa. Hasil analisis dirumuskan dalam bentuk kebutuhan '
    'fungsional dan non-fungsional sistem.'
)

h3('3.2.2 Perancangan Sistem')
body_text(
    'Tahap perancangan mencakup pembuatan diagram UML (Use Case Diagram, Activity Diagram), '
    'perancangan basis data (ERD dan skema tabel), dan perancangan antarmuka pengguna (mockup). '
    'Perancangan dilakukan menggunakan draw.io untuk diagram dan Figma untuk mockup antarmuka.'
)

h3('3.2.3 Implementasi')
body_text(
    'Implementasi dilakukan menggunakan framework Laravel 10 dengan PHP 8.1, basis data MySQL, '
    'dan tampilan antarmuka berbasis Bootstrap 5. Pengembangan mengikuti pola arsitektur MVC '
    '(Model-View-Controller) yang disediakan oleh Laravel.'
)

h3('3.2.4 Pengujian')
body_text(
    'Pengujian sistem dilakukan menggunakan dua metode, yaitu Black Box Testing untuk memverifikasi '
    'kesesuaian fungsionalitas sistem dengan spesifikasi kebutuhan, serta User Experience '
    'Questionnaire (UEQ) untuk mengukur tingkat kepuasan dan pengalaman pengguna terhadap sistem.'
)

h2('3.3 Kebutuhan Fungsional Sistem')
body_text('Berdasarkan hasil analisis kebutuhan, sistem harus memenuhi 20 kebutuhan fungsional berikut:')

add_empty()
table_heading('Tabel 3.1 Kebutuhan Fungsional Sistem')

# Build table using python-docx (cannot use add_p for tables)
from docx.text.paragraph import Paragraph as DocxPara
tbl = doc.add_table(rows=1, cols=3)
tbl.style = 'Table Grid'
hdr = tbl.rows[0]
for cell, txt in zip(hdr.cells, ['No', 'Kode', 'Kebutuhan Fungsional']):
    cell.text = txt
    for para in cell.paragraphs:
        for run in para.runs:
            run.font.name = 'Times New Roman'
            run.font.size = Pt(11)
            run.bold = True

reqs = [
    ("1","F-01","Sistem dapat melakukan autentikasi pengguna berdasarkan username dan password."),
    ("2","F-02","Sistem dapat menampilkan dasbor sesuai peran pengguna yang login."),
    ("3","F-03","Admin dapat mengelola data pengguna (tambah, ubah, hapus, lihat)."),
    ("4","F-04","Admin dapat mengelola data ruangan beserta fasilitas, kuota, kategori, dan status."),
    ("5","F-05","Admin dapat mengelola data program studi dan kelas."),
    ("6","F-06","Admin dapat mengelola data periode peminjaman (Open/Closed)."),
    ("7","F-07","Peminjam dapat melihat daftar ruangan beserta ketersediaannya."),
    ("8","F-08","Peminjam dapat mengajukan peminjaman ruangan dengan mengisi formulir pengajuan."),
    ("9","F-09","Sistem menyimpan data pengajuan dengan status awal 'Diajukan'."),
    ("10","F-10","Verifikator dapat melihat daftar pengajuan yang menunggu verifikasi."),
    ("11","F-11","Verifikator dapat menyetujui atau menolak pengajuan disertai alasan."),
    ("12","F-12","Sistem membuat data jadwal otomatis saat pengajuan disetujui (status: Akan Datang)."),
    ("13","F-13","Sistem mengubah status jadwal menjadi 'Berlangsung' otomatis saat waktu mulai tercapai."),
    ("14","F-14","Peminjam dapat mengajukan penyelesaian dengan mengunggah foto penggunaan, kebersihan, dan kunci ruangan."),
    ("15","F-15","Admin dapat melihat daftar penyelesaian yang menunggu verifikasi dokumentasi."),
    ("16","F-16","Admin dapat menyetujui atau menolak dokumentasi penyelesaian (status: Selesai / Dokumentasi Tidak Sesuai)."),
    ("17","F-17","Peminjam dapat melihat riwayat pengajuan dan riwayat jadwal yang dimiliki."),
    ("18","F-18","Sistem dapat mencetak surat peminjaman ruangan."),
    ("19","F-19","Pengguna dapat mengubah data profil pribadi."),
    ("20","F-20","Admin dapat mengelola konfigurasi jabatan verifikator beserta urutan approvalnya."),
]
for no, kode, desc in reqs:
    row = tbl.add_row()
    row.cells[0].text = no
    row.cells[1].text = kode
    row.cells[2].text = desc
    for i, cell in enumerate(row.cells):
        for para in cell.paragraphs:
            para.alignment = WD_ALIGN_PARAGRAPH.CENTER if i < 2 else WD_ALIGN_PARAGRAPH.LEFT
            for run in para.runs:
                run.font.name = 'Times New Roman'
                run.font.size = Pt(10)

body_text('Sumber: Hasil Analisis Kebutuhan (2026)')
add_empty()

h2('3.4 Kebutuhan Non-Fungsional Sistem')
body_text('Selain kebutuhan fungsional, sistem juga harus memenuhi kebutuhan non-fungsional berikut:')
bullet('Ketersediaan (Availability): Sistem dapat diakses selama 24 jam sehari, 7 hari seminggu.')
bullet('Keamanan (Security): Sistem mengimplementasikan autentikasi berbasis sesi, enkripsi password menggunakan bcrypt, dan proteksi CSRF.')
bullet('Kinerja (Performance): Halaman sistem dapat dimuat dalam waktu kurang dari 3 detik pada koneksi internet standar.')
bullet('Kompatibilitas (Compatibility): Sistem dapat diakses melalui peramban web modern (Chrome, Firefox, Edge) pada perangkat desktop maupun mobile.')
bullet('Kemudahan Penggunaan (Usability): Antarmuka sistem dirancang intuitif sehingga dapat digunakan tanpa pelatihan khusus.')

h2('3.5 Alat dan Bahan Penelitian')
h3('3.5.1 Perangkat Keras')
bullet('Laptop/komputer dengan prosesor minimal Intel Core i3 atau setara')
bullet('RAM minimal 8 GB')
bullet('Penyimpanan minimal 10 GB ruang kosong')

h3('3.5.2 Perangkat Lunak')
bullet('PHP 8.1 dan Composer untuk manajemen dependensi')
bullet('Laravel Framework 10.10')
bullet('MySQL 8.0 sebagai sistem manajemen basis data')
bullet('Visual Studio Code sebagai editor kode')
bullet('draw.io untuk pembuatan diagram UML dan ERD')
bullet('Figma untuk perancangan mockup antarmuka')
bullet('Git untuk version control')

# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
# BAB IV
# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
add_page_break()
h1('BAB IV')
h1('ANALISIS DAN PERANCANGAN SISTEM')
add_empty()

h2('4.1 Analisis Sistem Berjalan')
body_text(
    'Analisis sistem berjalan dilakukan untuk memahami alur proses peminjaman ruangan yang saat ini '
    'diterapkan di JTI Polinema. Proses peminjaman yang ada saat ini dilakukan secara manual, yaitu '
    'peminjam mengisi formulir fisik dan menyerahkannya langsung kepada petugas. Proses verifikasi '
    'dilakukan secara berantai melalui beberapa pejabat secara tatap muka, sehingga membutuhkan '
    'waktu yang relatif lama. Kondisi ini mengakibatkan ketidakefisienan dan potensi terjadinya '
    'bentrok jadwal penggunaan ruangan.'
)

h2('4.2 Analisis Sistem yang Diusulkan')
body_text(
    'Sistem yang diusulkan adalah Sistem Peminjaman Ruangan (SPR) JTI Polinema berbasis web yang '
    'mengotomatisasi seluruh alur proses peminjaman. Sistem ini melibatkan empat peran pengguna '
    'utama, yaitu Admin, Peminjam, Verifikator, dan Sistem (proses otomatis). Alur proses yang '
    'dirancang mencakup: pengajuan oleh Peminjam, verifikasi multi-tahap oleh Verifikator sesuai '
    'urutan jabatan, penetapan jadwal otomatis oleh Sistem, penggunaan ruangan oleh Peminjam, '
    'pengiriman dokumentasi penyelesaian oleh Peminjam, dan verifikasi dokumentasi oleh Admin.'
)

h2('4.3 Use Case Diagram')
body_text(
    'Use Case Diagram menggambarkan interaksi antara pengguna (aktor) dengan sistem secara '
    'keseluruhan. Sistem memiliki empat aktor utama: Admin, Peminjam, Verifikator, dan Sistem. '
    'Diagram ini menampilkan seluruh 20 fungsionalitas sistem yang dikelompokkan berdasarkan '
    'aktor yang menggunakannya.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.1 Use Case Diagram SPR JTI Polinema]')
body_text('Sumber: Hasil Perancangan (2026)')

h2('4.4 Use Case Fungsionalitas Utama')
body_text(
    'Use Case Fungsionalitas Utama menggambarkan alur detail proses inti sistem, yaitu proses '
    'peminjaman ruangan dari pengajuan hingga penyelesaian. Diagram ini menampilkan hubungan '
    '<<include>> dan <<extend>> antar use case untuk menjelaskan ketergantungan fungsionalitas '
    'dalam alur peminjaman. Empat kelompok fungsionalitas utama meliputi: (1) Pengajuan Peminjaman '
    'oleh Peminjam, (2) Verifikasi Pengajuan oleh Verifikator, (3) Penyelesaian Peminjaman oleh '
    'Peminjam, dan (4) Verifikasi Penyelesaian oleh Admin.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.2 Use Case Fungsionalitas Utama Proses Peminjaman]')
body_text('Sumber: Hasil Perancangan (2026)')

h2('4.5 Activity Diagram')
body_text(
    'Activity Diagram menggambarkan alur aktivitas dalam proses peminjaman ruangan secara '
    'menyeluruh menggunakan swimlane untuk membedakan aktivitas setiap aktor. Alur dimulai '
    'dari Peminjam melakukan login dan mengisi formulir pengajuan, dilanjutkan dengan '
    'Verifikator memverifikasi pengajuan, Sistem membuat jadwal otomatis, Peminjam menggunakan '
    'ruangan dan mengunggah dokumentasi, hingga Admin memverifikasi dokumentasi penyelesaian '
    'dan mengubah status menjadi Selesai.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.3 Activity Diagram Alur Peminjaman Ruangan]')
body_text('Sumber: Hasil Perancangan (2026)')

h2('4.6 Perancangan Basis Data')

h3('4.6.1 Entity Relationship Diagram (ERD)')
body_text(
    'Entity Relationship Diagram (ERD) menggambarkan struktur basis data sistem dengan '
    'menampilkan seluruh entitas, atribut, dan relasi antar entitas. Basis data SPR JTI '
    'terdiri dari 20 tabel yang saling berelasi untuk mendukung seluruh fungsionalitas sistem.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.4 Entity Relationship Diagram SPR JTI]')
body_text('Sumber: Hasil Perancangan (2026)')

h3('4.6.2 Tabel ERD')
body_text('Berikut adalah deskripsi seluruh tabel dalam basis data SPR JTI:')
add_empty()
table_heading('Tabel 4.1 Deskripsi Tabel Basis Data SPR JTI')

tbl2 = doc.add_table(rows=1, cols=4)
tbl2.style = 'Table Grid'
hdr2 = tbl2.rows[0]
for cell, txt in zip(hdr2.cells, ['No', 'Nama Tabel', 'Kolom Utama', 'Keterangan']):
    cell.text = txt
    for para in cell.paragraphs:
        for run in para.runs:
            run.font.name = 'Times New Roman'
            run.font.size = Pt(11)
            run.bold = True

erd_rows = [
    ("1","m_level","level_id (PK), level_kode, level_nama","Master level/peran pengguna"),
    ("2","m_user","user_id (PK), level_id (FK), username, user_password","Data autentikasi pengguna"),
    ("3","m_prodi","prodi_id (PK), prodi_kode, prodi_nama","Master program studi"),
    ("4","m_kelas","kelas_id (PK), prodi_id (FK), kelas_nama","Master kelas per program studi"),
    ("5","m_ruangan","ruangan_id (PK), kode, nama, fasilitas, kuota, kategori, status, foto","Master data ruangan"),
    ("6","m_admin","admin_id (PK), user_id (FK), prodi_id (FK), nama, nidn, noHp","Data profil Admin"),
    ("7","m_dosen","dosen_id (PK), user_id (FK), prodi_id (FK), nama, dosen_nip (18 digit), dosen_nidn (10 digit), noHp","Data profil Dosen"),
    ("8","m_mahasiswa","mahasiswa_id (PK), user_id (FK), prodi_id (FK), kelas_id (FK), nama, nim, noHp","Data profil Mahasiswa"),
    ("9","m_tendik","tendik_id (PK), user_id (FK), nama, nidn, noHp","Data profil Tenaga Kependidikan"),
    ("10","m_organisasi","organisasi_id (PK), kode, nama, logo","Master data organisasi peminjam"),
    ("11","m_jabatan_approval","jabatan_id (PK), user_id (FK), organisasi_id (FK), posisi_approval, urutan_approval","Konfigurasi jabatan verifikator bertingkat"),
    ("12","m_periode","periode_id (PK), tahun_ajaran, semester, tgl_mulai, tgl_selesai, status (Open/Closed), updated_by (FK)","Periode peminjaman aktif"),
    ("13","m_formulir","formulir_id (PK), formulir_path","Templat formulir pengajuan"),
    ("14","m_mahasiswa_organisasi","id (PK), mahasiswa_id (FK), organisasi_id (FK)","Relasi mahasiswaâ€“organisasi (UNIQUE)"),
    ("15","t_jadwal","jadwal_id (PK), user_id (FK), nama, tgl, jam_mulai, jam_selesai, jumPes, status, foto_penggunaan, foto_kebersihan, foto_kunci","Jadwal penggunaan ruangan aktif"),
    ("16","t_jadwal_ruangan","jadwal_ruangan_id (PK), jadwal_id (FK), ruangan_id (FK)","Ruangan yang digunakan per jadwal"),
    ("17","t_pengajuan","pengajuan_id (PK), user_id (FK), organisasi_id (FK), nama, tgl, jam_mulai, jam_selesai, jumPes, keterangan, status (Diajukan/Diterima/Ditolak), catatan_verifikator, ketua_pelaksana, nomor_surat, formulir_path","Data pengajuan peminjaman"),
    ("18","t_pengajuan_ruangan","pengajuan_ruangan_id (PK), pengajuan_id (FK), ruangan_id (FK)","Ruangan yang diajukan per pengajuan"),
    ("19","t_pengajuan_approval","approval_id (PK), pengajuan_id (FK), jabatan_id (FK), urutan_tahap, status_approval (Menunggu/Disetujui/Ditolak/Auto Reject), alasan_penolakan, batas_waktu, diproses_pada","Riwayat approval per tahap verifikasi"),
    ("20","t_pengajuan_panitia","panitia_id (PK), pengajuan_id (FK), user_id (FK), keterangan","Daftar panitia per pengajuan (UNIQUE)"),
]
for no, nama, kolom, ket in erd_rows:
    row = tbl2.add_row()
    row.cells[0].text = no
    row.cells[1].text = nama
    row.cells[2].text = kolom
    row.cells[3].text = ket
    for i, cell in enumerate(row.cells):
        for para in cell.paragraphs:
            para.alignment = WD_ALIGN_PARAGRAPH.CENTER if i == 0 else WD_ALIGN_PARAGRAPH.LEFT
            for run in para.runs:
                run.font.name = 'Times New Roman'
                run.font.size = Pt(9)

body_text('Sumber: Hasil Perancangan (2026)')

h2('4.7 Perancangan Antarmuka (Mockup)')
body_text(
    'Perancangan antarmuka pengguna dibuat menggunakan Figma untuk menggambarkan tampilan dan '
    'interaksi sistem sebelum implementasi. Mockup mencakup seluruh halaman utama sistem untuk '
    'setiap peran pengguna berdasarkan aset yang tersedia.'
)

h3('4.7.1 Halaman Login')
body_text(
    'Halaman login merupakan halaman pertama yang diakses oleh semua pengguna. Halaman ini '
    'menampilkan form input username dan password beserta tombol masuk. Sistem menggunakan '
    'autentikasi berbasis sesi Laravel dengan enkripsi bcrypt untuk keamanan password.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.5 Mockup Halaman Login]')
body_text('Sumber: Aset Figma SPR JTI (2026)')

h3('4.7.2 Halaman Dasbor Admin')
body_text(
    'Halaman dasbor Admin menampilkan ringkasan statistik sistem meliputi jumlah pengguna aktif, '
    'ruangan tersedia, pengajuan yang sedang diproses, dan jadwal berlangsung. Navigasi menu '
    'di sisi kiri memudahkan akses ke seluruh fitur manajemen data master dan transaksi.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.6 Mockup Halaman Dasbor Admin]')
body_text('Sumber: Aset Figma SPR JTI (2026)')

h3('4.7.3 Halaman Pengajuan Peminjaman')
body_text(
    'Halaman pengajuan peminjaman digunakan oleh Peminjam untuk mengisi formulir pengajuan '
    'meliputi nama kegiatan, tanggal, jam mulai dan selesai, jumlah peserta, ruangan yang '
    'diinginkan, dan keterangan tambahan. Sistem akan memvalidasi ketersediaan ruangan '
    'secara real-time dan menampilkan pesan konfirmasi setelah pengajuan berhasil dikirim.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.7 Mockup Halaman Form Pengajuan Peminjaman]')
body_text('Sumber: Aset Figma SPR JTI (2026)')

h3('4.7.4 Halaman Daftar Jadwal')
body_text(
    'Halaman daftar jadwal menampilkan seluruh jadwal penggunaan ruangan yang telah disetujui '
    'beserta statusnya (Akan Datang, Berlangsung, Ditinjau, Dokumentasi Tidak Sesuai, Selesai). '
    'Peminjam dapat melihat jadwal pribadi, sedangkan Admin dapat melihat seluruh jadwal.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.8 Mockup Halaman Daftar Jadwal]')
body_text('Sumber: Aset Figma SPR JTI (2026)')

h3('4.7.5 Halaman Daftar Ruangan')
body_text(
    'Halaman daftar ruangan menampilkan seluruh ruangan yang tersedia beserta informasi '
    'fasilitas, kapasitas, kategori, dan status ketersediaan. Admin dapat mengelola data '
    'ruangan melalui aksi tambah, ubah, hapus, dan lihat detail.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 4.9 Mockup Halaman Daftar Ruangan]')
body_text('Sumber: Aset Figma SPR JTI (2026)')

# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
# BAB V
# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
add_page_break()
h1('BAB V')
h1('IMPLEMENTASI DAN PENGUJIAN')
add_empty()

h2('5.1 Implementasi Sistem')
body_text(
    'Implementasi sistem dilakukan berdasarkan perancangan yang telah dibuat pada BAB IV. '
    'Sistem dibangun menggunakan framework Laravel 10 dengan PHP 8.1 dan basis data MySQL. '
    'Struktur aplikasi mengikuti pola MVC (Model-View-Controller) dengan memanfaatkan fitur '
    'bawaan Laravel seperti Eloquent ORM, Blade Template Engine, dan Artisan CLI.'
)

h3('5.1.1 Implementasi Basis Data')
body_text(
    'Basis data diimplementasikan menggunakan MySQL dengan 20 tabel sesuai perancangan ERD. '
    'Migrasi basis data dilakukan menggunakan fitur Migration Laravel yang memungkinkan '
    'versioning skema basis data secara terstruktur. Relasi antar tabel diimplementasikan '
    'menggunakan foreign key constraints untuk menjaga integritas data.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 5.1 Implementasi Basis Data]')
body_text('Sumber: Hasil Implementasi (2026)')

h3('5.1.2 Implementasi Antarmuka')
body_text(
    'Antarmuka pengguna diimplementasikan menggunakan Blade Template Engine Laravel dengan '
    'Bootstrap 5 sebagai framework CSS. Tampilan sistem responsif dan dapat diakses melalui '
    'perangkat desktop maupun mobile.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 5.2 Implementasi Antarmuka Sistem]')
body_text('Sumber: Hasil Implementasi (2026)')

h2('5.2 Pengujian Black Box')
body_text(
    'Pengujian Black Box dilakukan untuk memverifikasi kesesuaian fungsionalitas sistem dengan '
    'spesifikasi kebutuhan yang telah ditetapkan. Pengujian dilakukan pada seluruh 20 fungsionalitas '
    'yang tercantum dalam Tabel 3.1. Setiap fungsionalitas diuji dengan memberikan input valid '
    'dan tidak valid untuk memverifikasi output sistem.'
)
add_empty()
table_heading('Tabel 5.1 Hasil Pengujian Black Box')

tbl3 = doc.add_table(rows=1, cols=5)
tbl3.style = 'Table Grid'
hdr3 = tbl3.rows[0]
for cell, txt in zip(hdr3.cells, ['No', 'Kode', 'Skenario Uji', 'Hasil yang Diharapkan', 'Hasil']):
    cell.text = txt
    for para in cell.paragraphs:
        for run in para.runs:
            run.font.name = 'Times New Roman'
            run.font.size = Pt(11)
            run.bold = True

bb_rows = [
    ("1","F-01","Login dengan username dan password valid","Masuk ke dasbor sesuai peran","Sesuai"),
    ("2","F-01","Login dengan password salah","Menampilkan pesan error","Sesuai"),
    ("3","F-03","Admin tambah data pengguna baru","Data tersimpan, muncul di daftar","Sesuai"),
    ("4","F-04","Admin tambah data ruangan baru","Data tersimpan, muncul di daftar ruangan","Sesuai"),
    ("5","F-08","Peminjam mengajukan peminjaman","Pengajuan tersimpan, status Diajukan","Sesuai"),
    ("6","F-11","Verifikator setujui pengajuan","Status berubah Diterima, jadwal dibuat","Sesuai"),
    ("7","F-11","Verifikator tolak pengajuan","Status berubah Ditolak, notifikasi terkirim","Sesuai"),
    ("8","F-13","Waktu mulai jadwal tercapai","Status otomatis berubah Berlangsung","Sesuai"),
    ("9","F-14","Peminjam upload dokumentasi penyelesaian","Data tersimpan, status Ditinjau","Sesuai"),
    ("10","F-16","Admin setujui dokumentasi","Status berubah Selesai","Sesuai"),
]
for cols in bb_rows:
    row = tbl3.add_row()
    for i, (cell, txt) in enumerate(zip(row.cells, cols)):
        cell.text = txt
        for para in cell.paragraphs:
            para.alignment = WD_ALIGN_PARAGRAPH.CENTER if i in [0,1,4] else WD_ALIGN_PARAGRAPH.LEFT
            for run in para.runs:
                run.font.name = 'Times New Roman'
                run.font.size = Pt(10)

body_text('Sumber: Hasil Pengujian (2026)')
add_empty()

h2('5.3 Pengujian User Experience Questionnaire (UEQ)')
body_text(
    'Pengujian UEQ dilakukan dengan melibatkan responden dari civitas akademika JTI Polinema. '
    'Kuesioner dibagikan kepada pengguna yang telah mencoba sistem, kemudian hasilnya dianalisis '
    'menggunakan tools resmi UEQ Data Analysis Tool. Hasil pengujian diukur pada 6 skala UEQ: '
    'Attractiveness, Perspicuity, Efficiency, Dependability, Stimulation, dan Novelty.'
)
add_empty()
add_p('IsiHeading1', '[Gambar 5.3 Hasil Pengujian UEQ]')
body_text('Sumber: Hasil Pengujian (2026)')

# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
# BAB VI
# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
add_page_break()
h1('BAB VI')
h1('HASIL DAN PEMBAHASAN')
add_empty()

h2('6.1 Hasil Pengembangan Sistem')
body_text(
    'Hasil pengembangan adalah Sistem Peminjaman Ruangan (SPR) JTI Polinema berbasis web yang '
    'memenuhi seluruh 20 kebutuhan fungsional yang telah ditetapkan. Sistem berhasil '
    'diimplementasikan dengan framework Laravel 10, PHP 8.1, dan MySQL sebagai basis data. '
    'Sistem mendukung empat peran pengguna dengan hak akses yang berbeda-beda.'
)

h2('6.2 Pembahasan Hasil Pengujian Black Box')
body_text(
    'Berdasarkan hasil pengujian Black Box yang telah dilakukan, seluruh 20 fungsionalitas '
    'sistem berjalan sesuai dengan spesifikasi yang telah ditetapkan. Tidak ditemukan '
    'kegagalan pada skenario uji yang telah dirancang. Hal ini menunjukkan bahwa sistem '
    'telah diimplementasikan dengan benar sesuai kebutuhan fungsional.'
)

h2('6.3 Pembahasan Hasil Pengujian UEQ')
body_text(
    'Hasil pengujian UEQ menunjukkan penilaian yang positif dari pengguna pada seluruh '
    'enam skala pengukuran. Nilai Attractiveness yang tinggi menunjukkan bahwa pengguna '
    'merasa tampilan sistem menarik dan nyaman digunakan. Nilai Perspicuity yang baik '
    'menunjukkan bahwa sistem mudah dipahami dan dipelajari. Secara keseluruhan, hasil '
    'UEQ mengindikasikan tingkat penerimaan pengguna yang tinggi terhadap sistem.'
)

h2('6.4 Pembahasan Fungsionalitas Utama')
body_text(
    'Alur verifikasi multi-tingkat berbasis jabatan berhasil diimplementasikan dengan '
    'memanfaatkan tabel m_jabatan_approval yang menyimpan konfigurasi urutan approval '
    'secara dinamis. Sistem dapat menyesuaikan diri dengan perubahan konfigurasi jabatan '
    'tanpa memerlukan modifikasi kode program. Penetapan status jadwal otomatis '
    'berhasil diimplementasikan menggunakan Laravel Scheduler yang berjalan secara '
    'periodik untuk mengecek dan memperbarui status jadwal.'
)

# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
# BAB VII
# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
add_page_break()
h1('BAB VII')
h1('KESIMPULAN DAN SARAN')
add_empty()

h2('7.1 Kesimpulan')
body_text('Berdasarkan hasil pengembangan dan pengujian Sistem Peminjaman Ruangan (SPR) JTI Polinema berbasis web, dapat disimpulkan sebagai berikut:')
bullet(
    'Sistem Peminjaman Ruangan JTI Polinema berbasis web berhasil dirancang dan dikembangkan '
    'dengan memenuhi seluruh 20 kebutuhan fungsional yang telah ditetapkan, menggunakan '
    'framework Laravel 10 dengan PHP 8.1 dan basis data MySQL.'
)
bullet(
    'Alur verifikasi multi-tingkat berbasis jabatan berhasil diimplementasikan dengan memanfaatkan '
    'konfigurasi jabatan yang dinamis, sehingga proses persetujuan pengajuan peminjaman dapat '
    'berjalan secara terstruktur dan transparan sesuai hierarki jabatan yang berlaku.'
)
bullet(
    'Mekanisme dokumentasi penyelesaian penggunaan ruangan berhasil diintegrasikan ke dalam '
    'sistem, memungkinkan Peminjam mengunggah foto dokumentasi dan Admin memverifikasinya, '
    'sehingga akuntabilitas kondisi ruangan setelah penggunaan dapat terjamin.'
)
bullet(
    'Hasil pengujian Black Box menunjukkan seluruh fungsionalitas sistem berjalan sesuai '
    'spesifikasi, dan hasil pengujian UEQ menunjukkan tingkat kepuasan pengguna yang tinggi '
    'terhadap sistem yang dikembangkan.'
)

h2('7.2 Saran')
body_text('Beberapa saran untuk pengembangan sistem ke depan adalah sebagai berikut:')
bullet(
    'Menambahkan fitur notifikasi real-time menggunakan teknologi WebSocket atau Push Notification '
    'agar pengguna dapat menerima pemberitahuan secara langsung tanpa perlu memuat ulang halaman.'
)
bullet(
    'Mengintegrasikan sistem dengan kalender akademik Polinema agar jadwal peminjaman dapat '
    'disesuaikan secara otomatis dengan kegiatan akademik yang telah terjadwal.'
)
bullet(
    'Menambahkan fitur laporan dan analitik penggunaan ruangan untuk membantu manajemen JTI '
    'Polinema dalam pengambilan keputusan terkait optimasi pemanfaatan fasilitas ruangan.'
)
bullet(
    'Mengembangkan aplikasi mobile (Android/iOS) sebagai alternatif akses sistem selain '
    'melalui peramban web untuk meningkatkan kemudahan penggunaan.'
)

# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
# DAFTAR PUSTAKA
# â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
add_page_break()
h1('DAFTAR PUSTAKA')
add_empty()

dafpus = [
    'Booch, G., Rumbaugh, J., & Jacobson, I. (2005). The Unified Modeling Language User Guide (2nd ed.). Addison-Wesley.',
    'Connolly, T., & Begg, C. (2014). Database Systems: A Practical Approach to Design, Implementation, and Management (6th ed.). Pearson.',
    'Laravel Documentation. (2023). Laravel 10.x Documentation. Diakses dari https://laravel.com/docs/10.x',
    'Laugwitz, B., Held, T., & Schrepp, M. (2008). Construction and Evaluation of a User Experience Questionnaire. Lecture Notes in Computer Science, 5298, 63-76.',
    'Myers, G. J., Sandler, C., & Badgett, T. (2011). The Art of Software Testing (3rd ed.). John Wiley & Sons.',
    "O'Brien, J. A., & Marakas, G. M. (2011). Management Information Systems (10th ed.). McGraw-Hill.",
    'Pressman, R. S. (2014). Software Engineering: A Practitioner\'s Approach (8th ed.). McGraw-Hill.',
    'Rahmawati, D. (2021). Rancang Bangun Sistem Reservasi Ruang Rapat Berbasis Web dengan Multi-Level Approval Menggunakan Framework CodeIgniter. Jurnal Teknologi Informasi, 15(2), 45-58.',
    'Royce, W. W. (1970). Managing the Development of Large Software Systems. Proceedings of IEEE WESCON, 26, 1-9.',
    'Sommerville, I. (2016). Software Engineering (10th ed.). Pearson.',
    'Susanto, A. (2020). Pengembangan Sistem Peminjaman Laboratorium Komputer Berbasis Web dengan Fitur Notifikasi Email. Jurnal Sistem Informasi, 12(1), 23-34.',
]
for ref in dafpus:
    add_p('Normal', ref)

# â”€â”€â”€ Save â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
doc.save(OUTPUT)
print(f'[OK] Tersimpan: {OUTPUT}')

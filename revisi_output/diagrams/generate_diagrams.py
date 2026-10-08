"""
Generator untuk semua diagram .drawio SPR JTI.
Mencocokkan gaya diagram klien: strokeWidth=2, orthogonalEdge, swimlane, flowchart shapes.
"""

# ─── Use Case Diagram ────────────────────────────────────────────────────────

USE_CASE_XML = '''<mxfile host="app.diagrams.net" scale="1" border="0">
  <diagram id="uc1" name="Use Case Diagram">
    <mxGraphModel grid="1" page="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" pageScale="1" pageWidth="1169" pageHeight="827" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />

        <!-- System Boundary -->
        <mxCell id="sys" value="Sistem Peminjaman Ruangan JTI Polinema" vertex="1" parent="1"
          style="swimlane;startSize=30;html=1;fontSize=13;fontStyle=1;strokeWidth=2;align=center;whiteSpace=wrap;fillColor=#f5f5f5;strokeColor=#666666;fontColor=#333333;">
          <mxGeometry x="170" y="40" width="830" height="730" as="geometry" />
        </mxCell>

        <!-- ══ ACTORS ══ -->
        <!-- Admin -->
        <mxCell id="act_admin" value="Admin" vertex="1" parent="1"
          style="shape=mxgraph.uml2.actor;html=1;whiteSpace=wrap;strokeWidth=2;fontSize=12;verticalLabelPosition=bottom;verticalAlign=top;">
          <mxGeometry x="50" y="200" width="60" height="80" as="geometry" />
        </mxCell>
        <!-- Peminjam -->
        <mxCell id="act_peminjam" value="Peminjam&#xa;(Mhs/Dosen/Tendik)" vertex="1" parent="1"
          style="shape=mxgraph.uml2.actor;html=1;whiteSpace=wrap;strokeWidth=2;fontSize=12;verticalLabelPosition=bottom;verticalAlign=top;">
          <mxGeometry x="50" y="500" width="60" height="80" as="geometry" />
        </mxCell>
        <!-- Verifikator -->
        <mxCell id="act_verf" value="Verifikator" vertex="1" parent="1"
          style="shape=mxgraph.uml2.actor;html=1;whiteSpace=wrap;strokeWidth=2;fontSize=12;verticalLabelPosition=bottom;verticalAlign=top;">
          <mxGeometry x="1080" y="350" width="60" height="80" as="geometry" />
        </mxCell>
        <!-- Sistem -->
        <mxCell id="act_sistem" value="Sistem" vertex="1" parent="1"
          style="shape=mxgraph.uml2.actor;html=1;whiteSpace=wrap;strokeWidth=2;fontSize=12;verticalLabelPosition=bottom;verticalAlign=top;">
          <mxGeometry x="1080" y="600" width="60" height="80" as="geometry" />
        </mxCell>

        <!-- ══ USE CASES – Shared ══ -->
        <mxCell id="uc_login" value="Login" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="350" y="60" width="150" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_ruangan" value="Melihat Daftar Ruangan" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="340" y="140" width="170" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_jadwal" value="Melihat Daftar Jadwal" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="340" y="210" width="170" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_profil" value="Mengelola Profil" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="340" y="280" width="170" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_dasbor" value="Melihat Dasbor" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="340" y="350" width="170" height="50" as="geometry" />
        </mxCell>

        <!-- ══ USE CASES – Admin ══ -->
        <mxCell id="uc_kd_user" value="Mengelola Data Pengguna" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="50" y="60" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_kd_ruangan" value="Mengelola Data Ruangan" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="50" y="140" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_kd_prodi" value="Mengelola Data Program Studi" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="50" y="220" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_kd_kelas" value="Mengelola Data Kelas" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="50" y="300" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_kd_periode" value="Mengelola Data Periode" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="50" y="380" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_penyelesaian" value="Melihat Daftar Penyelesaian" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="50" y="460" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_verf_selesai" value="Verifikasi Penyelesaian" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="50" y="540" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_cetak" value="Mencetak Surat Peminjaman" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="50" y="620" width="200" height="50" as="geometry" />
        </mxCell>

        <!-- ══ USE CASES – Verifikator ══ -->
        <mxCell id="uc_list_pengajuan" value="Melihat Daftar Pengajuan" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="580" y="140" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_verf_pengajuan" value="Verifikasi Pengajuan" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="580" y="210" width="200" height="50" as="geometry" />
        </mxCell>

        <!-- ══ USE CASES – Peminjam ══ -->
        <mxCell id="uc_jadwal_pribadi" value="Melihat Jadwal Pribadi" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="580" y="350" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_ajukan" value="Mengajukan Peminjaman" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="580" y="430" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_selesaikan" value="Mengajukan Penyelesaian" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="580" y="510" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_riwayat_pengajuan" value="Melihat Riwayat Pengajuan" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="580" y="590" width="200" height="50" as="geometry" />
        </mxCell>
        <mxCell id="uc_riwayat_jadwal" value="Melihat Riwayat Jadwal" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="580" y="660" width="200" height="50" as="geometry" />
        </mxCell>

        <!-- ══ USE CASES – Sistem ══ -->
        <mxCell id="uc_auto_status" value="Penetapan Status&#xa;Otomatis" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="580" y="60" width="200" height="50" as="geometry" />
        </mxCell>

        <!-- ══ ACTOR CONNECTIONS – Admin ══ -->
        <mxCell id="e1" edge="1" source="act_admin" target="uc_login" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=1.5;exitX=1;exitY=0.5;exitDx=0;exitDy=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e2" edge="1" source="act_admin" target="uc_kd_user" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e3" edge="1" source="act_admin" target="uc_kd_ruangan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e4" edge="1" source="act_admin" target="uc_kd_prodi" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e5" edge="1" source="act_admin" target="uc_kd_kelas" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e6" edge="1" source="act_admin" target="uc_kd_periode" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e7" edge="1" source="act_admin" target="uc_penyelesaian" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e8" edge="1" source="act_admin" target="uc_verf_selesai" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e9" edge="1" source="act_admin" target="uc_cetak" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e10" edge="1" source="act_admin" target="uc_ruangan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e11" edge="1" source="act_admin" target="uc_jadwal" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e12" edge="1" source="act_admin" target="uc_dasbor" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e13" edge="1" source="act_admin" target="uc_profil" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- ══ ACTOR CONNECTIONS – Peminjam ══ -->
        <mxCell id="e20" edge="1" source="act_peminjam" target="uc_login" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e21" edge="1" source="act_peminjam" target="uc_ruangan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e22" edge="1" source="act_peminjam" target="uc_jadwal" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e23" edge="1" source="act_peminjam" target="uc_jadwal_pribadi" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e24" edge="1" source="act_peminjam" target="uc_ajukan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e25" edge="1" source="act_peminjam" target="uc_selesaikan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e26" edge="1" source="act_peminjam" target="uc_riwayat_pengajuan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e27" edge="1" source="act_peminjam" target="uc_riwayat_jadwal" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e28" edge="1" source="act_peminjam" target="uc_cetak" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e29" edge="1" source="act_peminjam" target="uc_dasbor" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e30" edge="1" source="act_peminjam" target="uc_profil" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- ══ ACTOR CONNECTIONS – Verifikator ══ -->
        <mxCell id="e40" edge="1" source="act_verf" target="uc_login" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e41" edge="1" source="act_verf" target="uc_ruangan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e42" edge="1" source="act_verf" target="uc_jadwal" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e43" edge="1" source="act_verf" target="uc_list_pengajuan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e44" edge="1" source="act_verf" target="uc_verf_pengajuan" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e45" edge="1" source="act_verf" target="uc_dasbor" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="e46" edge="1" source="act_verf" target="uc_profil" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- ══ ACTOR CONNECTIONS – Sistem ══ -->
        <mxCell id="e50" edge="1" source="act_sistem" target="uc_auto_status" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

      </root>
    </mxGraphModel>
  </diagram>
</mxfile>'''

# ─── Use Case Fungsionalitas Utama ──────────────────────────────────────────

USE_CASE_UTAMA_XML = '''<mxfile host="app.diagrams.net" scale="1" border="0">
  <diagram id="uc2" name="Use Case Fungsionalitas Utama">
    <mxGraphModel grid="1" page="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" pageScale="1" pageWidth="1169" pageHeight="827" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />

        <!-- System Boundary -->
        <mxCell id="sys" value="Fungsionalitas Utama: Proses Peminjaman Ruangan" vertex="1" parent="1"
          style="swimlane;startSize=30;html=1;fontSize=13;fontStyle=1;strokeWidth=2;fillColor=#f5f5f5;strokeColor=#666666;fontColor=#333333;">
          <mxGeometry x="160" y="40" width="830" height="730" as="geometry" />
        </mxCell>

        <!-- Actors -->
        <mxCell id="act_p" value="Peminjam" vertex="1" parent="1"
          style="shape=mxgraph.uml2.actor;html=1;strokeWidth=2;fontSize=12;verticalLabelPosition=bottom;verticalAlign=top;">
          <mxGeometry x="50" y="340" width="60" height="80" as="geometry" />
        </mxCell>
        <mxCell id="act_v" value="Verifikator" vertex="1" parent="1"
          style="shape=mxgraph.uml2.actor;html=1;strokeWidth=2;fontSize=12;verticalLabelPosition=bottom;verticalAlign=top;">
          <mxGeometry x="1060" y="200" width="60" height="80" as="geometry" />
        </mxCell>
        <mxCell id="act_a" value="Admin" vertex="1" parent="1"
          style="shape=mxgraph.uml2.actor;html=1;strokeWidth=2;fontSize=12;verticalLabelPosition=bottom;verticalAlign=top;">
          <mxGeometry x="1060" y="500" width="60" height="80" as="geometry" />
        </mxCell>
        <mxCell id="act_s" value="Sistem" vertex="1" parent="1"
          style="shape=mxgraph.uml2.actor;html=1;strokeWidth=2;fontSize=12;verticalLabelPosition=bottom;verticalAlign=top;">
          <mxGeometry x="1060" y="650" width="60" height="80" as="geometry" />
        </mxCell>

        <!-- ── Sub-use case group: Pengajuan Peminjaman ── -->
        <mxCell id="grp1" value="Pengajuan Peminjaman" vertex="1" parent="sys"
          style="swimlane;startSize=25;html=1;fontSize=11;fontStyle=1;strokeWidth=1.5;strokeColor=#23547B;fillColor=#dae8fc;">
          <mxGeometry x="30" y="60" width="350" height="280" as="geometry" />
        </mxCell>
        <mxCell id="uc_login2" value="Login" vertex="1" parent="grp1"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="90" y="40" width="160" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_lihat_ruangan" value="Melihat Daftar Ruangan" vertex="1" parent="grp1"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="90" y="105" width="160" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_isi_form" value="Mengisi Formulir Pengajuan" vertex="1" parent="grp1"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="90" y="170" width="160" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_kirim" value="Mengirim Pengajuan" vertex="1" parent="grp1"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="90" y="215" width="160" height="45" as="geometry" />
        </mxCell>

        <!-- ── Sub-use case group: Verifikasi Pengajuan ── -->
        <mxCell id="grp2" value="Verifikasi Pengajuan" vertex="1" parent="sys"
          style="swimlane;startSize=25;html=1;fontSize=11;fontStyle=1;strokeWidth=1.5;strokeColor=#006EAF;fillColor=#dae8fc;">
          <mxGeometry x="440" y="60" width="340" height="230" as="geometry" />
        </mxCell>
        <mxCell id="uc_list_peng" value="Melihat Daftar Pengajuan" vertex="1" parent="grp2"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="85" y="40" width="160" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_terima" value="Menerima Pengajuan" vertex="1" parent="grp2"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="85" y="105" width="160" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_tolak" value="Menolak Pengajuan" vertex="1" parent="grp2"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="85" y="165" width="160" height="45" as="geometry" />
        </mxCell>

        <!-- ── Sub-use case group: Penyelesaian Peminjaman ── -->
        <mxCell id="grp3" value="Penyelesaian Peminjaman" vertex="1" parent="sys"
          style="swimlane;startSize=25;html=1;fontSize=11;fontStyle=1;strokeWidth=1.5;strokeColor=#3D7317;fillColor=#d5e8d4;">
          <mxGeometry x="30" y="390" width="350" height="290" as="geometry" />
        </mxCell>
        <mxCell id="uc_dok_penggunaan" value="Upload Foto Penggunaan" vertex="1" parent="grp3"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="80" y="40" width="180" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_dok_kebersihan" value="Upload Foto Kebersihan" vertex="1" parent="grp3"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="80" y="105" width="180" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_dok_kunci" value="Upload Foto Kunci" vertex="1" parent="grp3"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="80" y="170" width="180" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_kirim_dok" value="Mengirim Penyelesaian" vertex="1" parent="grp3"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="80" y="235" width="180" height="45" as="geometry" />
        </mxCell>

        <!-- ── Sub-use case group: Verifikasi Penyelesaian ── -->
        <mxCell id="grp4" value="Verifikasi Penyelesaian (Admin)" vertex="1" parent="sys"
          style="swimlane;startSize=25;html=1;fontSize=11;fontStyle=1;strokeWidth=1.5;strokeColor=#AE4132;fillColor=#ffe6cc;">
          <mxGeometry x="440" y="340" width="340" height="230" as="geometry" />
        </mxCell>
        <mxCell id="uc_list_selesai" value="Melihat Daftar Penyelesaian" vertex="1" parent="grp4"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="80" y="40" width="175" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_setujui_dok" value="Menyetujui Dokumentasi" vertex="1" parent="grp4"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="80" y="105" width="175" height="45" as="geometry" />
        </mxCell>
        <mxCell id="uc_tolak_dok" value="Menolak Dokumentasi" vertex="1" parent="grp4"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="80" y="165" width="175" height="45" as="geometry" />
        </mxCell>

        <!-- ── Auto status – Sistem ── -->
        <mxCell id="uc_auto2" value="Penetapan Status&#xa;Berlangsung (Otomatis)" vertex="1" parent="sys"
          style="ellipse;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=11;">
          <mxGeometry x="480" y="610" width="250" height="60" as="geometry" />
        </mxCell>

        <!-- ── include arrows ── -->
        <mxCell id="inc1" value="&lt;&lt;include&gt;&gt;" edge="1" source="uc_isi_form" target="uc_login2" parent="grp1"
          style="edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;html=1;strokeWidth=1.5;fontSize=10;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="inc2" value="&lt;&lt;include&gt;&gt;" edge="1" source="uc_isi_form" target="uc_lihat_ruangan" parent="grp1"
          style="edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;html=1;strokeWidth=1.5;fontSize=10;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="inc3" value="&lt;&lt;include&gt;&gt;" edge="1" source="uc_kirim" target="uc_isi_form" parent="grp1"
          style="edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;html=1;strokeWidth=1.5;fontSize=10;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="inc4" value="&lt;&lt;extend&gt;&gt;" edge="1" source="uc_terima" target="uc_list_peng" parent="grp2"
          style="edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;html=1;strokeWidth=1.5;fontSize=10;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="inc5" value="&lt;&lt;extend&gt;&gt;" edge="1" source="uc_tolak" target="uc_list_peng" parent="grp2"
          style="edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;html=1;strokeWidth=1.5;fontSize=10;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="inc6" value="&lt;&lt;include&gt;&gt;" edge="1" source="uc_kirim_dok" target="uc_dok_penggunaan" parent="grp3"
          style="edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;html=1;strokeWidth=1.5;fontSize=10;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="inc7" value="&lt;&lt;include&gt;&gt;" edge="1" source="uc_kirim_dok" target="uc_dok_kebersihan" parent="grp3"
          style="edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;html=1;strokeWidth=1.5;fontSize=10;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="inc8" value="&lt;&lt;include&gt;&gt;" edge="1" source="uc_kirim_dok" target="uc_dok_kunci" parent="grp3"
          style="edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;html=1;strokeWidth=1.5;fontSize=10;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- ── Actor connections ── -->
        <mxCell id="ea1" edge="1" source="act_p" target="grp1" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ea2" edge="1" source="act_p" target="grp3" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ea3" edge="1" source="act_v" target="grp2" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ea4" edge="1" source="act_a" target="grp4" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ea5" edge="1" source="act_s" target="uc_auto2" parent="1"
          style="html=1;strokeWidth=1.5;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

      </root>
    </mxGraphModel>
  </diagram>
</mxfile>'''

# ─── Activity Diagram ────────────────────────────────────────────────────────

ACTIVITY_XML = '''<mxfile host="app.diagrams.net" scale="1" border="0">
  <diagram id="act1" name="Activity Diagram Alur Baru">
    <mxGraphModel grid="1" page="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" pageScale="1" pageWidth="1169" pageHeight="1654" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />

        <!-- Swimlane: Peminjam -->
        <mxCell id="sw_p" value="Peminjam" vertex="1" parent="1"
          style="swimlane;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=13;fontStyle=1;fillColor=#dae8fc;strokeColor=#6c8ebf;startSize=40;">
          <mxGeometry x="20" y="20" width="260" height="1580" as="geometry" />
        </mxCell>

        <!-- Swimlane: Verifikator -->
        <mxCell id="sw_v" value="Verifikator" vertex="1" parent="1"
          style="swimlane;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=13;fontStyle=1;fillColor=#d5e8d4;strokeColor=#82b366;startSize=40;">
          <mxGeometry x="290" y="20" width="260" height="1580" as="geometry" />
        </mxCell>

        <!-- Swimlane: Admin -->
        <mxCell id="sw_a" value="Admin" vertex="1" parent="1"
          style="swimlane;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=13;fontStyle=1;fillColor=#ffe6cc;strokeColor=#d6b656;startSize=40;">
          <mxGeometry x="560" y="20" width="260" height="1580" as="geometry" />
        </mxCell>

        <!-- Swimlane: Sistem -->
        <mxCell id="sw_s" value="Sistem" vertex="1" parent="1"
          style="swimlane;whiteSpace=wrap;html=1;strokeWidth=2;fontSize=13;fontStyle=1;fillColor=#f8cecc;strokeColor=#b85450;startSize=40;">
          <mxGeometry x="830" y="20" width="260" height="1580" as="geometry" />
        </mxCell>

        <!-- ════ Peminjam steps ════ -->
        <mxCell id="p_start" value="START" vertex="1" parent="sw_p"
          style="strokeWidth=2;html=1;shape=mxgraph.flowchart.start_1;whiteSpace=wrap;">
          <mxGeometry x="80" y="60" width="80" height="60" as="geometry" />
        </mxCell>
        <mxCell id="p_login" value="Login" vertex="1" parent="sw_p"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="80" y="160" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="p_lihat_ruangan" value="Melihat Daftar Ruangan" vertex="1" parent="sw_p"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="80" y="260" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="p_lihat_jadwal" value="Melihat Daftar Jadwal" vertex="1" parent="sw_p"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="80" y="350" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="p_isi_form" value="Mengisi Formulir Pengajuan" vertex="1" parent="sw_p"
          style="shape=parallelogram;html=1;strokeWidth=2;perimeter=parallelogramPerimeter;whiteSpace=wrap;rounded=1;arcSize=12;size=0.23;">
          <mxGeometry x="70" y="450" width="140" height="50" as="geometry" />
        </mxCell>
        <mxCell id="p_kirim_peng" value="Mengirim Pengajuan&#xa;(Status: Diajukan)" vertex="1" parent="sw_p"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="80" y="550" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="p_end1" value="END" vertex="1" parent="sw_p"
          style="strokeWidth=2;html=1;shape=mxgraph.flowchart.start_1;whiteSpace=wrap;">
          <mxGeometry x="80" y="780" width="80" height="60" as="geometry" />
        </mxCell>
        <mxCell id="p_notif_tolak" value="Terima Notifikasi&#xa;Pengajuan Ditolak" vertex="1" parent="sw_p"
          style="shape=parallelogram;html=1;strokeWidth=2;perimeter=parallelogramPerimeter;whiteSpace=wrap;rounded=1;arcSize=12;size=0.23;">
          <mxGeometry x="70" y="700" width="140" height="50" as="geometry" />
        </mxCell>
        <mxCell id="p_gunakan" value="Menggunakan Ruangan" vertex="1" parent="sw_p"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="80" y="1000" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="p_upload_dok" value="Upload Dokumentasi&#xa;(Penggunaan, Kebersihan,&#xa;Kunci)" vertex="1" parent="sw_p"
          style="shape=parallelogram;html=1;strokeWidth=2;perimeter=parallelogramPerimeter;whiteSpace=wrap;rounded=1;arcSize=12;size=0.23;">
          <mxGeometry x="70" y="1100" width="140" height="60" as="geometry" />
        </mxCell>
        <mxCell id="p_kirim_dok" value="Mengirim Penyelesaian&#xa;(Status: Ditinjau)" vertex="1" parent="sw_p"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="80" y="1210" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="p_dok_not_ok" value="Upload Ulang&#xa;Dokumentasi" vertex="1" parent="sw_p"
          style="shape=parallelogram;html=1;strokeWidth=2;perimeter=parallelogramPerimeter;whiteSpace=wrap;rounded=1;arcSize=12;size=0.23;">
          <mxGeometry x="70" y="1380" width="140" height="50" as="geometry" />
        </mxCell>

        <!-- ════ Verifikator steps ════ -->
        <mxCell id="v_terima_notif" value="Terima Notifikasi&#xa;Pengajuan" vertex="1" parent="sw_v"
          style="shape=parallelogram;html=1;strokeWidth=2;perimeter=parallelogramPerimeter;whiteSpace=wrap;rounded=1;arcSize=12;size=0.23;">
          <mxGeometry x="60" y="620" width="140" height="50" as="geometry" />
        </mxCell>
        <mxCell id="v_tinjau" value="Meninjau Pengajuan" vertex="1" parent="sw_v"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="70" y="690" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="v_keputusan" value="Keputusan?" vertex="1" parent="sw_v"
          style="strokeWidth=2;html=1;shape=mxgraph.flowchart.decision;whiteSpace=wrap;">
          <mxGeometry x="70" y="780" width="120" height="80" as="geometry" />
        </mxCell>

        <!-- ════ Admin steps ════ -->
        <mxCell id="a_terima_notif" value="Terima Notifikasi&#xa;Penyelesaian" vertex="1" parent="sw_a"
          style="shape=parallelogram;html=1;strokeWidth=2;perimeter=parallelogramPerimeter;whiteSpace=wrap;rounded=1;arcSize=12;size=0.23;">
          <mxGeometry x="60" y="1250" width="140" height="50" as="geometry" />
        </mxCell>
        <mxCell id="a_periksa" value="Memeriksa Dokumentasi" vertex="1" parent="sw_a"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="70" y="1330" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="a_keputusan" value="Sesuai?" vertex="1" parent="sw_a"
          style="strokeWidth=2;html=1;shape=mxgraph.flowchart.decision;whiteSpace=wrap;">
          <mxGeometry x="70" y="1420" width="120" height="80" as="geometry" />
        </mxCell>
        <mxCell id="a_setujui" value="Setujui&#xa;(Status: Selesai)" vertex="1" parent="sw_a"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="70" y="1540" width="120" height="50" as="geometry" />
        </mxCell>
        <mxCell id="a_end" value="END" vertex="1" parent="sw_a"
          style="strokeWidth=2;html=1;shape=mxgraph.flowchart.start_1;whiteSpace=wrap;">
          <mxGeometry x="90" y="1480" width="80" height="60" as="geometry" />
        </mxCell>

        <!-- ════ Sistem steps ════ -->
        <mxCell id="s_buat_jadwal" value="Membuat Data Jadwal&#xa;(Status: Akan Datang)" vertex="1" parent="sw_s"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="65" y="850" width="130" height="60" as="geometry" />
        </mxCell>
        <mxCell id="s_trigger" value="Waktu Mulai&#xa;Tercapai?" vertex="1" parent="sw_s"
          style="strokeWidth=2;html=1;shape=mxgraph.flowchart.decision;whiteSpace=wrap;">
          <mxGeometry x="65" y="950" width="130" height="80" as="geometry" />
        </mxCell>
        <mxCell id="s_berlangsung" value="Ubah Status&#xa;→ Berlangsung" vertex="1" parent="sw_s"
          style="rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;">
          <mxGeometry x="65" y="1070" width="130" height="50" as="geometry" />
        </mxCell>

        <!-- ════ EDGES ════ -->
        <!-- Peminjam flow -->
        <mxCell id="ep1" edge="1" source="p_start" target="p_login" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ep2" edge="1" source="p_login" target="p_lihat_ruangan" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ep3" edge="1" source="p_lihat_ruangan" target="p_lihat_jadwal" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ep4" edge="1" source="p_lihat_jadwal" target="p_isi_form" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ep5" edge="1" source="p_isi_form" target="p_kirim_peng" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ep6" edge="1" source="p_notif_tolak" target="p_end1" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ep7" edge="1" source="p_gunakan" target="p_upload_dok" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ep8" edge="1" source="p_upload_dok" target="p_kirim_dok" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- Cross-swimlane: Peminjam → Verifikator -->
        <mxCell id="ex1" edge="1" source="p_kirim_peng" target="v_terima_notif" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;dashed=1;exitX=1;exitY=0.5;exitDx=0;exitDy=0;entryX=0;entryY=0.5;entryDx=0;entryDy=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- Verifikator flow -->
        <mxCell id="ev1" edge="1" source="v_terima_notif" target="v_tinjau" parent="sw_v"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ev2" edge="1" source="v_tinjau" target="v_keputusan" parent="sw_v"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- Verifikator → Tolak → Peminjam -->
        <mxCell id="ex2" edge="1" source="v_keputusan" target="p_notif_tolak" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;dashed=1;exitX=0;exitY=0.5;exitDx=0;exitDy=0;entryX=1;entryY=0.5;entryDx=0;entryDy=0;">
          <mxGeometry relative="1" as="geometry">
            <mxPoint as="sourcePoint" />
          </mxGeometry>
        </mxCell>
        <mxCell id="ex2l" connectable="0" parent="ex2" style="edgeLabel;html=1;align=center;" value="Tolak" vertex="1">
          <mxGeometry relative="0.3" as="geometry" />
        </mxCell>

        <!-- Verifikator → Terima → Sistem -->
        <mxCell id="ex3" edge="1" source="v_keputusan" target="s_buat_jadwal" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;dashed=1;exitX=1;exitY=0.5;exitDx=0;exitDy=0;entryX=0;entryY=0.5;entryDx=0;entryDy=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ex3l" connectable="0" parent="ex3" style="edgeLabel;html=1;align=center;" value="Terima" vertex="1">
          <mxGeometry relative="0.3" as="geometry" />
        </mxCell>

        <!-- Sistem flow -->
        <mxCell id="es1" edge="1" source="s_buat_jadwal" target="s_trigger" parent="sw_s"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="es2" edge="1" source="s_trigger" target="s_berlangsung" parent="sw_s"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="es2l" connectable="0" parent="es2" style="edgeLabel;html=1;align=center;" value="Ya" vertex="1">
          <mxGeometry relative="0.5" as="geometry" />
        </mxCell>

        <!-- Sistem berlangsung → Peminjam gunakan -->
        <mxCell id="ex4" edge="1" source="s_berlangsung" target="p_gunakan" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;dashed=1;exitX=0;exitY=0.5;exitDx=0;exitDy=0;entryX=1;entryY=0.5;entryDx=0;entryDy=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- Peminjam kirim dok → Admin -->
        <mxCell id="ex5" edge="1" source="p_kirim_dok" target="a_terima_notif" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;dashed=1;exitX=1;exitY=0.5;exitDx=0;exitDy=0;entryX=0;entryY=0.5;entryDx=0;entryDy=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- Admin flow -->
        <mxCell id="ea1" edge="1" source="a_terima_notif" target="a_periksa" parent="sw_a"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ea2" edge="1" source="a_periksa" target="a_keputusan" parent="sw_a"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ea3" edge="1" source="a_keputusan" target="a_setujui" parent="sw_a"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ea3l" connectable="0" parent="ea3" style="edgeLabel;html=1;align=center;" value="Sesuai" vertex="1">
          <mxGeometry relative="0.5" as="geometry" />
        </mxCell>
        <mxCell id="ea4" edge="1" source="a_setujui" target="a_end" parent="sw_a"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- Admin tidak sesuai → Peminjam upload ulang -->
        <mxCell id="ex6" edge="1" source="a_keputusan" target="p_dok_not_ok" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;dashed=1;exitX=0;exitY=0.5;exitDx=0;exitDy=0;entryX=1;entryY=0.5;entryDx=0;entryDy=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="ex6l" connectable="0" parent="ex6" style="edgeLabel;html=1;align=center;" value="Tidak Sesuai" vertex="1">
          <mxGeometry relative="0.3" as="geometry" />
        </mxCell>

        <!-- Upload ulang → upload dok -->
        <mxCell id="ep9" edge="1" source="p_dok_not_ok" target="p_upload_dok" parent="sw_p"
          style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;">
          <mxGeometry relative="1" as="geometry">
            <Array as="points">
              <mxPoint x="30" y="1405" />
              <mxPoint x="30" y="1130" />
            </Array>
          </mxGeometry>
        </mxCell>

      </root>
    </mxGraphModel>
  </diagram>
</mxfile>'''

# ─── ERD ─────────────────────────────────────────────────────────────────────

ERD_XML = '''<mxfile host="app.diagrams.net" scale="1" border="0">
  <diagram id="erd1" name="ERD">
    <mxGraphModel grid="1" page="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" pageScale="1" pageWidth="1654" pageHeight="1169" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />

        <!-- ── m_level ── -->
        <mxCell id="tbl_level" value="m_level" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#dae8fc;strokeColor=#6c8ebf;">
          <mxGeometry x="40" y="100" width="200" height="90" as="geometry" />
        </mxCell>
        <mxCell id="lv1" value="PK  level_id (BIGINT)" vertex="1" parent="tbl_level"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="200" height="30" as="geometry" />
        </mxCell>
        <mxCell id="lv2" value="level_kode (VARCHAR 5)" vertex="1" parent="tbl_level"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="200" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_user ── -->
        <mxCell id="tbl_user" value="m_user" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#dae8fc;strokeColor=#6c8ebf;">
          <mxGeometry x="340" y="60" width="220" height="120" as="geometry" />
        </mxCell>
        <mxCell id="us1" value="PK  user_id (BIGINT)" vertex="1" parent="tbl_user"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="220" height="30" as="geometry" />
        </mxCell>
        <mxCell id="us2" value="FK  level_id (BIGINT)" vertex="1" parent="tbl_user"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="220" height="30" as="geometry" />
        </mxCell>
        <mxCell id="us3" value="username, user_password" vertex="1" parent="tbl_user"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="220" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_prodi ── -->
        <mxCell id="tbl_prodi" value="m_prodi" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#d5e8d4;strokeColor=#82b366;">
          <mxGeometry x="700" y="60" width="220" height="90" as="geometry" />
        </mxCell>
        <mxCell id="pr1" value="PK  prodi_id (BIGINT)" vertex="1" parent="tbl_prodi"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="220" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pr2" value="prodi_kode, prodi_nama" vertex="1" parent="tbl_prodi"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="220" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_kelas ── -->
        <mxCell id="tbl_kelas" value="m_kelas" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#d5e8d4;strokeColor=#82b366;">
          <mxGeometry x="700" y="220" width="220" height="90" as="geometry" />
        </mxCell>
        <mxCell id="kl1" value="PK  kelas_id (BIGINT)" vertex="1" parent="tbl_kelas"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="220" height="30" as="geometry" />
        </mxCell>
        <mxCell id="kl2" value="FK  prodi_id | kelas_nama" vertex="1" parent="tbl_kelas"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="220" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_ruangan ── -->
        <mxCell id="tbl_ruangan" value="m_ruangan" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#ffe6cc;strokeColor=#d6b656;">
          <mxGeometry x="700" y="380" width="240" height="150" as="geometry" />
        </mxCell>
        <mxCell id="ru1" value="PK  ruangan_id (BIGINT)" vertex="1" parent="tbl_ruangan"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="ru2" value="ruangan_kode, ruangan_nama" vertex="1" parent="tbl_ruangan"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="ru3" value="ruangan_fasilitas, kuota" vertex="1" parent="tbl_ruangan"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="ru4" value="kategori, status, foto" vertex="1" parent="tbl_ruangan"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="120" width="240" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_admin ── -->
        <mxCell id="tbl_admin" value="m_admin" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#f8cecc;strokeColor=#b85450;">
          <mxGeometry x="340" y="260" width="240" height="120" as="geometry" />
        </mxCell>
        <mxCell id="ad1" value="PK  admin_id (BIGINT)" vertex="1" parent="tbl_admin"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="ad2" value="FK  user_id | FK prodi_id" vertex="1" parent="tbl_admin"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="ad3" value="admin_nama, nidn, noHp" vertex="1" parent="tbl_admin"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="240" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_dosen ── -->
        <mxCell id="tbl_dosen" value="m_dosen" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#f8cecc;strokeColor=#b85450;">
          <mxGeometry x="340" y="430" width="240" height="150" as="geometry" />
        </mxCell>
        <mxCell id="ds1" value="PK  dosen_id (BIGINT)" vertex="1" parent="tbl_dosen"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="ds2" value="FK  user_id | FK prodi_id" vertex="1" parent="tbl_dosen"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="ds3" value="dosen_nama, dosen_nip" vertex="1" parent="tbl_dosen"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="ds4" value="dosen_nidn, noHp" vertex="1" parent="tbl_dosen"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="120" width="240" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_mahasiswa ── -->
        <mxCell id="tbl_mhs" value="m_mahasiswa" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#f8cecc;strokeColor=#b85450;">
          <mxGeometry x="340" y="640" width="240" height="150" as="geometry" />
        </mxCell>
        <mxCell id="mh1" value="PK  mahasiswa_id (BIGINT)" vertex="1" parent="tbl_mhs"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="mh2" value="FK  user_id | FK prodi_id | FK kelas_id" vertex="1" parent="tbl_mhs"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="mh3" value="mahasiswa_nama, nim" vertex="1" parent="tbl_mhs"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="mh4" value="noHp" vertex="1" parent="tbl_mhs"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="120" width="240" height="30" as="geometry" />
        </mxCell>

        <!-- ── t_jadwal ── -->
        <mxCell id="tbl_jadwal" value="t_jadwal" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#fff2cc;strokeColor=#d6b656;">
          <mxGeometry x="1000" y="280" width="280" height="210" as="geometry" />
        </mxCell>
        <mxCell id="jd1" value="PK  jadwal_id (BIGINT)" vertex="1" parent="tbl_jadwal"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jd2" value="FK  user_id (BIGINT)" vertex="1" parent="tbl_jadwal"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jd3" value="jadwal_nama, tgl, jam_mulai" vertex="1" parent="tbl_jadwal"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jd4" value="jam_selesai, jumPes, status" vertex="1" parent="tbl_jadwal"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="120" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jd5" value="foto_penggunaan, foto_kebersihan" vertex="1" parent="tbl_jadwal"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="150" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jd6" value="foto_kunci" vertex="1" parent="tbl_jadwal"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="180" width="280" height="30" as="geometry" />
        </mxCell>

        <!-- ── t_jadwal_ruangan ── -->
        <mxCell id="tbl_jr" value="t_jadwal_ruangan" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#fff2cc;strokeColor=#d6b656;">
          <mxGeometry x="1000" y="540" width="250" height="90" as="geometry" />
        </mxCell>
        <mxCell id="jr1" value="PK  jadwal_ruangan_id" vertex="1" parent="tbl_jr"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="250" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jr2" value="FK  jadwal_id | FK ruangan_id" vertex="1" parent="tbl_jr"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="250" height="30" as="geometry" />
        </mxCell>

        <!-- ── t_pengajuan ── -->
        <mxCell id="tbl_peng" value="t_pengajuan" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#e1d5e7;strokeColor=#9673a6;">
          <mxGeometry x="1000" y="680" width="280" height="210" as="geometry" />
        </mxCell>
        <mxCell id="pe1" value="PK  pengajuan_id (BIGINT)" vertex="1" parent="tbl_peng"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pe2" value="FK  user_id | FK organisasi_id" vertex="1" parent="tbl_peng"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pe3" value="pengajuan_nama, tgl, jam_mulai" vertex="1" parent="tbl_peng"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pe4" value="jam_selesai, jumPes, keterangan" vertex="1" parent="tbl_peng"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="120" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pe5" value="status (Diajukan/Diterima/Ditolak)" vertex="1" parent="tbl_peng"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="150" width="280" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pe6" value="catatan_verifikator, ketua_pelaksana" vertex="1" parent="tbl_peng"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="180" width="280" height="30" as="geometry" />
        </mxCell>

        <!-- ── t_pengajuan_ruangan ── -->
        <mxCell id="tbl_pengr" value="t_pengajuan_ruangan" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#e1d5e7;strokeColor=#9673a6;">
          <mxGeometry x="1000" y="940" width="260" height="90" as="geometry" />
        </mxCell>
        <mxCell id="pgr1" value="PK  pengajuan_ruangan_id" vertex="1" parent="tbl_pengr"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="260" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pgr2" value="FK  pengajuan_id | FK ruangan_id" vertex="1" parent="tbl_pengr"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="260" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_periode ── -->
        <mxCell id="tbl_periode" value="m_periode" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#d5e8d4;strokeColor=#82b366;">
          <mxGeometry x="40" y="500" width="240" height="180" as="geometry" />
        </mxCell>
        <mxCell id="pd1" value="PK  periode_id (BIGINT)" vertex="1" parent="tbl_periode"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pd2" value="tahun_ajaran, semester" vertex="1" parent="tbl_periode"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pd3" value="tanggal_mulai, tanggal_selesai" vertex="1" parent="tbl_periode"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pd4" value="periode_status (Open/Closed)" vertex="1" parent="tbl_periode"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="120" width="240" height="30" as="geometry" />
        </mxCell>
        <mxCell id="pd5" value="FK  updated_by (user_id)" vertex="1" parent="tbl_periode"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="150" width="240" height="30" as="geometry" />
        </mxCell>

        <!-- ── m_jabatan_approval ── -->
        <mxCell id="tbl_jabatan" value="m_jabatan_approval" vertex="1" parent="1"
          style="shape=table;startSize=30;container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;fontSize=12;strokeWidth=2;fillColor=#e1d5e7;strokeColor=#9673a6;">
          <mxGeometry x="40" y="740" width="260" height="150" as="geometry" />
        </mxCell>
        <mxCell id="jb1" value="PK  jabatan_id (BIGINT)" vertex="1" parent="tbl_jabatan"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="30" width="260" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jb2" value="FK  user_id | FK organisasi_id" vertex="1" parent="tbl_jabatan"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="60" width="260" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jb3" value="posisi_approval, urutan_approval" vertex="1" parent="tbl_jabatan"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="90" width="260" height="30" as="geometry" />
        </mxCell>
        <mxCell id="jb4" value="(Verifikator pengajuan)" vertex="1" parent="tbl_jabatan"
          style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;fontStyle=2;top=0;left=0;right=0;bottom=0;strokeWidth=1;">
          <mxGeometry y="120" width="260" height="30" as="geometry" />
        </mxCell>

        <!-- ══ RELATIONSHIPS (arrows) ══ -->
        <!-- m_level → m_user (1:N) -->
        <mxCell id="rel1" edge="1" source="lv1" target="us2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_user → m_admin (1:1) -->
        <mxCell id="rel2" edge="1" source="us1" target="ad2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERone;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_user → m_dosen (1:1) -->
        <mxCell id="rel3" edge="1" source="us1" target="ds2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERone;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_user → m_mahasiswa (1:1) -->
        <mxCell id="rel4" edge="1" source="us1" target="mh2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERone;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_prodi → m_kelas (1:N) -->
        <mxCell id="rel5" edge="1" source="pr1" target="kl2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_prodi → m_mahasiswa (1:N) -->
        <mxCell id="rel6" edge="1" source="pr1" target="mh2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_kelas → m_mahasiswa (1:N) -->
        <mxCell id="rel7" edge="1" source="kl1" target="mh2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_user → t_jadwal (1:N) -->
        <mxCell id="rel8" edge="1" source="us1" target="jd2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_user → t_pengajuan (1:N) -->
        <mxCell id="rel9" edge="1" source="us1" target="pe2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- t_jadwal → t_jadwal_ruangan (1:N) -->
        <mxCell id="rel10" edge="1" source="jd1" target="jr2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_ruangan → t_jadwal_ruangan (1:N) -->
        <mxCell id="rel11" edge="1" source="ru1" target="jr2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- t_pengajuan → t_pengajuan_ruangan (1:N) -->
        <mxCell id="rel12" edge="1" source="pe1" target="pgr2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_ruangan → t_pengajuan_ruangan (1:N) -->
        <mxCell id="rel13" edge="1" source="ru1" target="pgr2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

        <!-- m_user → m_jabatan_approval (1:N) -->
        <mxCell id="rel14" edge="1" source="us1" target="jb2" parent="1"
          style="edgeStyle=orthogonalEdgeStyle;html=1;strokeWidth=2;endArrow=ERmany;startArrow=ERone;endFill=0;startFill=0;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>

      </root>
    </mxGraphModel>
  </diagram>
</mxfile>'''

# ─── Write all files ─────────────────────────────────────────────────────────

import os

BASE = os.path.dirname(__file__)

files = {
    "Use_Case_Diagram.drawio": USE_CASE_XML,
    "Use_Case_Fungsionalitas_Utama.drawio": USE_CASE_UTAMA_XML,
    "Activity_Diagram.drawio": ACTIVITY_XML,
    "ERD.drawio": ERD_XML,
}

for fname, content in files.items():
    path = os.path.join(BASE, fname)
    with open(path, "w", encoding="utf-8") as f:
        f.write(content.strip())
    print(f"[OK] {fname}")

print("All diagram files generated.")

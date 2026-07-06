<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Hasil Studi (KHS) - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 10px; }
        .mt-4 { margin-top: 20px; }
        
        table.header-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        table.header-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
        }
        
        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .kop-surat {
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background-color: #ddd;
            display: inline-block;
            line-height: 80px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="kop-surat">
        <table width="100%">
            <tr>
                <td width="15%" class="text-center">
                    <!-- Placeholder logo, in real app you can use <img src="{{ public_path('logo.png') }}"> -->
                    <div class="logo-placeholder">LOGO</div>
                </td>
                <td width="85%" class="text-center">
                    <div style="font-size: 20px; font-weight: bold;">UNIVERSITAS TEKNOLOGI ABSENSI</div>
                    <div style="font-size: 14px;">Fakultas Ilmu Komputer & Teknologi Informasi</div>
                    <div style="font-size: 12px; margin-top: 5px;">Jl. Pendidikan No. 123, Kota Pelajar, Indonesia 12345</div>
                    <div style="font-size: 12px;">Telp: (021) 1234567 | Email: info@univabsensi.ac.id | Web: www.univabsensi.ac.id</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="text-center mb-2">
        <div class="header-title">Kartu Hasil Studi (KHS)</div>
    </div>

    <table class="header-table">
        <tr>
            <td width="15%" class="font-bold">Nama Lengkap</td>
            <td width="2%">:</td>
            <td width="40%">{{ $user->name }}</td>
            <td width="15%" class="font-bold">Tahun Ajaran</td>
            <td width="2%">:</td>
            <td width="26%">2024/2025</td>
        </tr>
        <tr>
            <td class="font-bold">NIM</td>
            <td>:</td>
            <td>{{ $user->nim ?? '-' }}</td>
            <td class="font-bold">Program Studi</td>
            <td>:</td>
            <td>{{ $user->kelas->department->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">Kelas</td>
            <td>:</td>
            <td>{{ $user->kelas->nomor_kelas ?? '-' }} (Semester {{ $user->kelas->semester ?? '-' }})</td>
            <td class="font-bold">Tanggal Cetak</td>
            <td>:</td>
            <td>{{ now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Matkul</th>
                <th width="40%">Mata Kuliah</th>
                <th width="10%">SKS</th>
                <th width="10%">Semester</th>
                <th width="10%">Nilai Huruf</th>
                <th width="10%">Bobot</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalSks = 0; 
                $totalBobotSks = 0;
            @endphp
            
            @forelse($grades as $i => $g)
                @php 
                    $sks = $g->course->sks ?? 0;
                    $totalSks += $sks;
                    
                    // Simple logic for Bobot, adjust if logic is different
                    $bobot = 0;
                    switch($g->nilai_huruf) {
                        case 'A': $bobot = 4.0; break;
                        case 'B': $bobot = 3.0; break;
                        case 'C': $bobot = 2.0; break;
                        case 'D': $bobot = 1.0; break;
                        case 'E': $bobot = 0.0; break;
                    }
                    $totalBobotSks += ($bobot * $sks);
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $g->course->kode_matkul ?? '-' }}</td>
                    <td>{{ $g->course->nama_matkul ?? '-' }}</td>
                    <td class="text-center">{{ $sks }}</td>
                    <td class="text-center">{{ $g->course->semester ?? '-' }}</td>
                    <td class="text-center font-bold">{{ $g->nilai_huruf ?? '-' }}</td>
                    <td class="text-center">{{ number_format($bobot, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data nilai.</td>
                </tr>
            @endforelse
        </tbody>
        @if($totalSks > 0)
        <tfoot>
            <tr>
                <td colspan="3" class="text-right font-bold" style="padding-right: 15px;">Total SKS yang Diambil:</td>
                <td class="text-center font-bold">{{ $totalSks }}</td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="3" class="text-right font-bold" style="padding-right: 15px;">Indeks Prestasi Semester (IPS):</td>
                <td colspan="4" class="text-center font-bold" style="font-size: 14px;">
                    {{ number_format($totalBobotSks / $totalSks, 2) }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>

    <table width="100%" class="mt-4" style="margin-top: 40px;">
        <tr>
            <td width="60%"></td>
            <td width="40%" class="text-center">
                <p>Mengetahui,</p>
                <p style="margin-bottom: 70px;">Ketua Program Studi</p>
                <p class="font-bold" style="text-decoration: underline;">Dr. Fulan bin Fulan, M.Kom.</p>
                <p>NIDN. 123456789</p>
            </td>
        </tr>
    </table>

</body>
</html>

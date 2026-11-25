<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Kehadiran Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; }
        h2 { text-align: center; margin-bottom: 4px; }
        p.sub { text-align: center; color: #555; margin-top: 0; margin-bottom: 16px; }
        .course-header { background: #1e3c72; color: #fff; padding: 6px 10px; font-weight: bold; margin-top: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #333; padding: 5px 6px; text-align: left; }
        th { background-color: #f0f2f5; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Rekap Kehadiran Mahasiswa</h2>
    <p class="sub">Universitas Tangsel Raya | Dicetak pada {{ now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</p>

    @forelse($rekap as $item)
        <div class="course-header">
            {{ $item['schedule']->course->nama_matkul ?? '-' }}
            &mdash; {{ $item['schedule']->kelas->department->name ?? '-' }}
            &mdash; Kelas {{ $item['schedule']->kelas->nomor_kelas ?? '-' }} (Sem {{ $item['schedule']->kelas->semester ?? '-' }})
            &mdash; Dosen: {{ $item['schedule']->lecturer->name ?? '-' }}
            ({{ $item['pertemuan'] }} Pertemuan)
        </div>
        <table>
            <thead>
                <tr>
                    <th width="30" class="text-center">#</th>
                    <th width="90">NIM</th>
                    <th>Nama</th>
                    <th width="45" class="text-center">Hadir</th>
                    <th width="45" class="text-center">Izin</th>
                    <th width="45" class="text-center">Sakit</th>
                    <th width="65" class="text-center">Tidak Hadir</th>
                    <th width="45" class="text-center">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($item['rows'] as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row['mahasiswa']->nim ?? '-' }}</td>
                    <td>{{ $row['mahasiswa']->name }}</td>
                    <td class="text-center">{{ $row['hadir'] }}</td>
                    <td class="text-center">{{ $row['izin'] }}</td>
                    <td class="text-center">{{ $row['sakit'] }}</td>
                    <td class="text-center">{{ $row['tidak_hadir'] }}</td>
                    <td class="text-center">{{ $row['persen'] }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada mahasiswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    @empty
        <p class="text-center">Belum ada data rekap kehadiran.</p>
    @endforelse
</body>
</html>

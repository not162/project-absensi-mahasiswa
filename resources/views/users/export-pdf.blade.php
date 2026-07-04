<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 30px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.sub { text-align: center; color: #555; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background-color: #667eea; color: #fff; }
        tr:nth-child(even) { background-color: #f5f5f5; }
        .print-btn { text-align: center; margin-bottom: 20px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()" style="padding:8px 16px;">🖨️ Cetak / Simpan sebagai PDF</button>
    </div>

    <h2>Data Mahasiswa</h2>
    <p class="sub">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. HP</th>
                <th>Program Studi</th>
                <th>Kelas</th>
                <th>Semester</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $i => $mhs)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $mhs->nim ?? '-' }}</td>
                <td>{{ $mhs->name }}</td>
                <td>{{ $mhs->email }}</td>
                <td>{{ $mhs->phone ?? '-' }}</td>
                <td>{{ $mhs->department->name ?? '-' }}</td>
                <td>{{ $mhs->kelas->nomor_kelas ?? '-' }}</td>
                <td>{{ $mhs->kelas->semester ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

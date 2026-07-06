<!DOCTYPE html>
<html>
<head>
    <title>Hasil Ujian STEM</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; color: #2c3e50; margin: 0; }
        .subtitle { font-size: 14px; color: #7f8c8d; margin-top: 5px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; }
        .score-box { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; text-align: center; width: 30%; display: inline-block; margin-right: 2%; box-sizing: border-box; }
        .score-box h3 { margin: 0; font-size: 14px; color: #6c757d; }
        .score-box h1 { margin: 5px 0 0 0; font-size: 28px; color: #2c3e50; }
        .section-title { font-size: 18px; font-weight: bold; color: #2c3e50; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; }
        .path-box { background-color: #e9ecef; padding: 15px; border-radius: 5px; }
        .path-item { display: inline-block; background: #3498db; color: #fff; padding: 5px 10px; border-radius: 3px; font-weight: bold; font-size: 12px; margin-right: 5px; margin-bottom: 5px;}
        .footer { position: absolute; bottom: 30px; width: 100%; text-align: center; font-size: 12px; color: #95a5a6; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">LAPORAN HASIL UJIAN STEM</h1>
        <p class="subtitle">Evaluasi Berbasis Artificial Intelligence (Fuzzy Logic & A* Pathfinding)</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>Nama Mahasiswa</strong></td>
            <td width="30%">: {{ $attempt->user->name }}</td>
            <td width="20%"><strong>Program Studi</strong></td>
            <td width="30%">: {{ $attempt->user->department->name ?? 'Umum' }}</td>
        </tr>
        <tr>
            <td><strong>NIM</strong></td>
            <td>: {{ $attempt->user->nim ?? '-' }}</td>
            <td><strong>Tanggal Ujian</strong></td>
            <td>: {{ $attempt->finished_at ? $attempt->finished_at->format('d F Y H:i') : '-' }}</td>
        </tr>
    </table>

    <div style="text-align: center; margin-bottom: 20px;">
        <div class="score-box">
            <h3>SKOR MENTAH</h3>
            <h1>{{ number_format($attempt->raw_score, 1) }}</h1>
        </div>
        <div class="score-box">
            <h3>SKOR FUZZY</h3>
            <h1>{{ number_format($attempt->fuzzy_score, 1) }}</h1>
        </div>
        <div class="score-box" style="margin-right: 0;">
            <h3>KEPUTUSAN</h3>
            <h1 style="color: {{ str_contains($attempt->decision, 'Lulus') ? '#27ae60' : '#e74c3c' }}; font-size: 22px; padding-top: 5px;">
                {{ strtoupper($attempt->decision) }}
            </h1>
        </div>
    </div>

    <div class="section-title">Rekomendasi Jalur Belajar Remedial (Dijkstra)</div>
    <div class="path-box">
        <p style="margin-top:0;">Berdasarkan analisis algoritma pencarian rute terpendek dari kategori terlemah Anda, berikut adalah urutan materi yang harus dipelajari:</p>
        
        @if(isset($attempt->remedial_path['path']) && count($attempt->remedial_path['path']) > 0)
            <div style="margin-bottom: 15px;">
                @foreach($attempt->remedial_path['path'] as $idx => $node)
                    <span class="path-item">{{ $node }}</span>
                    @if($idx < count($attempt->remedial_path['path']) - 1)
                        <span style="color:#7f8c8d; font-size: 16px;">&rarr;</span>
                    @endif
                @endforeach
            </div>
            <p style="margin-bottom:0;"><strong>Estimasi Waktu Belajar:</strong> {{ $attempt->remedial_path['total_effort_hours'] ?? 0 }} Jam</p>
        @else
            <p><strong>Bagus Sekali!</strong> Anda tidak memerlukan rute remedial khusus.</p>
        @endif
    </div>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem SPK Evaluasi STEM &copy; {{ date('Y') }}
    </div>

</body>
</html>

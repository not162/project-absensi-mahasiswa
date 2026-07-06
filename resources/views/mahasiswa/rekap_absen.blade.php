@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-primary me-2"></i>Rekap Absensi Anda</h2>
        <p class="text-muted">Universitas Tangsel Raya | Riwayat kehadiran Anda pada setiap pertemuan perkuliahan.</p>
    </div>

    <div class="row g-4">
        {{-- Table Rekap Absen --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">Daftar Kehadiran</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rekapTable" class="table table-striped table-hover align-middle mb-0" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>STATUS ABSEN</th>
                                    <th>TANGGAL</th>
                                    <th>MATAKULIAH</th>
                                    <th>PERTEMUAN</th>
                                    <th>RANGKUMAN</th>
                                    <th>BERITA ACARA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $index => $att)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($att->status === 'hadir')
                                            <span class="badge bg-success py-2 px-3 text-uppercase"><i class="fas fa-check-circle me-1"></i> Hadir</span>
                                        @elseif($att->status === 'izin')
                                            <span class="badge bg-warning text-dark py-2 px-3 text-uppercase"><i class="fas fa-info-circle me-1"></i> Izin</span>
                                        @elseif($att->status === 'sakit')
                                            <span class="badge bg-info py-2 px-3 text-uppercase"><i class="fas fa-procedures me-1"></i> Sakit</span>
                                        @else
                                            <span class="badge bg-danger py-2 px-3 text-uppercase"><i class="fas fa-times-circle me-1"></i> Tidak Hadir</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($att->meeting->tanggal)->translatedFormat('d F Y') }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $att->meeting->schedule->course->nama_matkul ?? '-' }}</strong>
                                        <span class="text-muted d-block small">Dosen: {{ $att->meeting->schedule->lecturer->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">Pertemuan {{ $att->meeting->pertemuan_ke }}</span>
                                    </td>
                                    <td>
                                        <span class="text-wrap d-block" style="max-width: 250px;">{{ $att->meeting->materi ?? 'Tidak ada rangkuman materi.' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary py-2 px-3"><i class="fas fa-stamp me-1"></i> Sukses</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- STARS & Decision Side Widget --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-microchip text-primary me-2"></i>STARS Decision Engine</h5>
                </div>
                <div class="card-body">
                    @php
                        $stars = $analysis['stars_analysis']['stars_count'];
                        $inputs = $analysis['stars_analysis']['decision_input'];
                        $output = $analysis['stars_analysis']['decision_output'];
                    @endphp
                    <div class="text-center py-3 border-bottom mb-4">
                        <div class="text-warning mb-2" style="font-size: 2.2rem; letter-spacing: 2px;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $stars ? 'fas' : 'far' }} fa-star"></i>
                            @endfor
                        </div>
                        <h4 class="fw-bold text-dark">{{ $stars }} / 5 Bintang</h4>
                        <span class="badge bg-primary rounded-pill px-3 py-1">Rating Kinerja STARS</span>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2 small"><i class="fas fa-brain text-primary me-1"></i> Keputusan Rekomendasi:</h6>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                            {{ $output }}
                        </p>
                    </div>

                    <div>
                        <h6 class="fw-bold text-dark mb-2 small"><i class="fas fa-tasks text-muted me-1"></i> Logika Penilaian STARS:</h6>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            <li class="small d-flex justify-content-between border-bottom pb-1">
                                <span class="text-muted">Rasio Absensi:</span>
                                <strong class="text-dark">{{ $inputs['attendance'] ?? '-' }}</strong>
                            </li>
                            <li class="small d-flex justify-content-between border-bottom pb-1">
                                <span class="text-muted">Rasio Tugas:</span>
                                <strong class="text-dark">{{ $inputs['submission'] ?? '-' }}</strong>
                            </li>
                            <li class="small d-flex justify-content-between pb-1">
                                <span class="text-muted">Kualitas Nilai:</span>
                                <strong class="text-dark">{{ $inputs['grades'] ?? '-' }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- DataTables CSS CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" />
<style>
    .dt-buttons .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<!-- jQuery & DataTables JS CDN -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        $('#rekapTable').DataTable({
            dom: "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-3'<'col-md-5'i><'col-md-7'p>>",
            buttons: [
                { extend: 'copy', className: 'btn btn-outline-secondary' },
                { extend: 'csv', className: 'btn btn-outline-primary' },
                { extend: 'excel', className: 'btn btn-outline-success' },
                { extend: 'pdf', className: 'btn btn-outline-danger' },
                { extend: 'print', className: 'btn btn-outline-dark' }
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                zeroRecords: "Tidak ada data yang cocok ditemukan",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });
    });
</script>
@endpush

@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-primary me-2"></i>Rekap Absensi Anda</h2>
        <p class="text-muted">Universitas Tangsel Raya &mdash; Riwayat kehadiran Anda pada setiap pertemuan perkuliahan.</p>
    </div>

    <div class="card border-0 shadow-sm">
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

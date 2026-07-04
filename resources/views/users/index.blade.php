@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Data Mahasiswa</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('users.exportPdf') }}" target="_blank" class="btn btn-outline-danger">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-excel me-1"></i> Import Excel
            </button>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Mahasiswa
            </a>
        </div>
    </div>

    {{-- Modal Import Excel --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Data Mahasiswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            File CSV/Excel dengan kolom: <code>nim, name, email, phone, department_id, class_id</code>.
                            Baris pertama harus header.
                        </p>
                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DataTables Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="users-table" style="width: 100%">
                    <thead class="table-light">
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Program Studi</th>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true, // SSR enabled
            ajax: '{{ route('users.index') }}',
            columns: [
                { data: 'nim', name: 'nim', render: function(data) {
                    return data ? '<span class="badge bg-secondary">'+data+'</span>' : '-';
                }},
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone', render: function(data) {
                    return data ? data : '-';
                }},
                { data: 'department', name: 'department.name', orderable: false, searchable: false },
                { data: 'kelas', name: 'kelas.nomor_kelas', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' // Bahasa Indonesia
            }
        });
    });
</script>
@endpush
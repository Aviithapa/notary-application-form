@extends('admin.layout.app')

@section('content')

    <!-- start page title -->
    @include('admin.component.breadcrumb', ['title' => 'Legal Documents'])
    <!-- end page title -->

    <!-- Body -->
    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="header-title mb-3">Upload Legal Documents</h5>
                    <form method="POST" action="{{ route('legal-documents.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="title">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                                placeholder="Optional" name="title" value="{{ old('title') }}">
                            <small class="text-muted">Used only when uploading a single file. With multiple files, each
                                file name becomes its title.</small>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="title_np">Title (नेपाली)</label>
                            <input type="text" class="form-control @error('title_np') is-invalid @enderror" id="title_np"
                                placeholder="ऐच्छिक" name="title_np" value="{{ old('title_np') }}">
                            @error('title_np')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="inputFiles">PDF Files</label>
                            <input type="file" name="files[]" id="inputFiles" accept="application/pdf" multiple required
                                class="form-control @if ($errors->has('files') || $errors->has('files.*')) is-invalid @endif" />
                            <small class="text-muted">You can select multiple PDFs at once (max 20 MB each).</small>
                            @if ($errors->has('files') || $errors->has('files.*'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('files') ?: collect($errors->get('files.*'))->flatten()->first() }}
                                </div>
                            @endif
                        </div>

                        <button class="btn btn-primary" type="submit">
                            <i class="bi-upload"></i> Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <h5 class="header-title mb-0">All Legal Documents</h5>
                        <span class="badge bg-primary">{{ $documents->total() }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Title</th>
                                    <th>Uploaded</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($documents as $doc)
                                    <tr>
                                        <td>{{ $documents->firstItem() + $loop->index }}</td>
                                        <td>
                                            <i class="bi-file-earmark-pdf text-danger"></i>
                                            {{ $doc->title }}
                                        </td>
                                        <td>{{ $doc->created_at?->format('M d, Y') }}</td>
                                        <td class="text-end">
                                            @if ($doc->getImageUrlAttribute())
                                                <a href="{{ $doc->getImageUrlAttribute() }}" target="_blank"
                                                    class="btn btn-sm btn-light" title="View">
                                                    <i class="bi-eye"></i>
                                                </a>
                                            @endif
                                            <a data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                data-attr="{{ route('legal-documents.destroy', ['id' => $doc->id]) }}"
                                                class="btn btn-sm btn-light text-danger" title="Delete">
                                                <i class="bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No legal documents uploaded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="padding: 10px; float:right;">
                        {{ $documents->appends(request()->query())->links('admin.layout.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="mySmallModalLabel">Delete Modal</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure want to delete?
                </div>
                <div class="modal-footer">
                    <form action="#" method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Body -->

@endsection

@push('scripts')
    <script>
        const exampleModal = document.getElementById('exampleModal')
        exampleModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget
            document.getElementById('deleteForm').action = button.getAttribute('data-attr');
        })
    </script>
@endpush

@extends('admin.layout.app')

@section('content')
    <!-- start page title -->
    @include('admin.component.breadcrumb', ['title' => 'Board Members'])
    <!-- end page title -->


    <!-- Body -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="p-3">
                        <h5 class="header-title mb-0" style="text-transform: capitalize;">Board Members List</h5>
                    </div>
                    <div id="yearly-sales-collapse" class="collapse show row">
                        <!-- List -->

                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                            <div class="card">
                                <div class="card-body p-3">
                                    <form method="POST" action="{{ route('bod.store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div>
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="validationCustom01">BOD Name</label>
                                                    <input type="text" class="form-control" placeholder="BOD Name"
                                                        name="title" required
                                                        value="{{ isset($model) ? $model->title : old('title') }}">
                                                    @if ($errors->any())
                                                        {{ $errors->first('title') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="title_np">BOD Name (नेपाली)</label>
                                                    <input type="text" class="form-control" placeholder="नाम थर"
                                                        name="title_np" id="title_np" value="{{ old('title_np') }}">
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="validationCustom01">Position</label>
                                                    <select class="form-select mb-3" name="excerpt" required>
                                                        <option value="chairman">Chairman</option>
                                                        <option value="registrar">Registrar</option>
                                                        <option value="member">Member</option>
                                                        <option value="information_officer">Information Officer</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="content">Full Position (optional)</label>
                                                    <input type="text" class="form-control" placeholder="e.g. Chairman, Notary Public Council"
                                                        name="content" id="content" value="{{ old('content') }}">
                                                    <small class="text-muted">Shown on the website instead of the short position above.</small>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="content_np">Full Position (नेपाली)</label>
                                                    <input type="text" class="form-control" placeholder="जस्तै: अध्यक्ष, नोटरी पब्लिक परिषद्"
                                                        name="content_np" id="content_np" value="{{ old('content_np') }}">
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="bodPhone">Phone (optional)</label>
                                                    <input type="text" class="form-control" placeholder="Phone"
                                                        name="phone" id="bodPhone" value="{{ old('phone') }}">
                                                    @if ($errors->any())
                                                        {{ $errors->first('phone') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="phone_np">Phone (नेपाली, optional)</label>
                                                    <input type="text" class="form-control" placeholder="९८XXXXXXXX"
                                                        name="phone_np" id="phone_np" value="{{ old('phone_np') }}">
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="bodEmail">Email (optional)</label>
                                                    <input type="email" class="form-control" placeholder="Email"
                                                        name="email" id="bodEmail" value="{{ old('email') }}">
                                                    @if ($errors->any())
                                                        {{ $errors->first('email') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="inputFile">Select Image:</label>
                                                <input type="file" name="file" accept="image/*" id="inputFile"
                                                    class="form-control @error('file') is-invalid @enderror" />
                                            </div>
                                        </div>
                                </div>
                                <button class="btn btn-primary" type="submit">Create BOD</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-xl-7 col-lg-7 col-md-7 col-sm-12">
                            <div class="row">
                                @php
                                    $sortedBanner = $banner->sort(function ($a, $b) {
                                        if ($a->excerpt === 'registrar') {
                                            return -1;
                                        }
                                        if ($a->excerpt === 'chairman') {
                                            return -1;
                                        }
                                        if ($b->excerpt === 'registrar' || $b->excerpt === 'chairman') {
                                            return 1;
                                        }
                                        return 0;
                                    });
                                @endphp

                                @foreach ($sortedBanner as $ba)
                                    <div class="col-xl-4" style="position: relative; margin-top: 2px; text-align:center;">
                                        <a data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@fat"
                                            data-attr="{{ route('bod.destroy', ['bod' => $ba->id]) }}"
                                            style="cursor: pointer;"> <i class="bi-trash"
                                                style="color: red;
                                                                        border-radius: 50%;
                                                                        padding: 3px;
                                                                        background: white;
                                                                        cursor: pointer;
                                                                        position: absolute;
                                                                        top: 5px;
                                                                        right: 16px;"></i>
                                        </a>
                                        <img src="{{ $ba->getFirstMediaUrl('bod') }}" alt="{{ $ba->title }}"
                                            style="object-fit: cover; width:100%; height: 200px;" /> <br />
                                        <h5>{{ $ba->title }}</h5>
                                        <h6>{{ $ba->content ?: ucwords(str_replace('_', ' ', $ba->excerpt)) }}</h6>
                                        <h6>{{ $ba->email }}</h6>
                                        <h6>{{ $ba->phone }}</h6>


                                    </div>
                                @endforeach

                            </div>
                            <div style="padding: 10px; float:right;">
                                {{ $banner->appends(request()->query())->links('admin.layout.pagination') }}
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                        aria-labelledby="mySmallModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="mySmallModalLabel">Delete Modal</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure want to delete?
                                </div>
                                <div class="modal-footer">
                                    <form action="#" method="POST" id="deleteForm">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </form>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


                    <!-- List -->
                </div>
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
            const recipient = button.getAttribute('data-attr')
            document.getElementById('deleteForm').action = recipient;
        })
    </script>
@endpush

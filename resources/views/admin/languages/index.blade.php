@extends('layouts.admin')

@section('title', 'Languages Management')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white p-3">
                <h5 class="mb-0">Add New Language</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.languages.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Language</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Slug</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($languages as $language)
                        <tr>
                            <td class="ps-4">{{ $language->name }}</td>
                            <td><code>{{ $language->slug }}</code></td>
                            <td class="text-end pe-4">
                                <form action="{{ route('admin.languages.destroy', $language) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">Delete</button>
                                </form>
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

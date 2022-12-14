@extends('master')

@section('content')
<h2>Sửa học sinh</h2>
<div class="row">
    <form method="POST" action="{{ route('user.update', ['user' => $user->id]) }}" class="col-8">
        @csrf
        <div class="form-group">
            <label for="exampleInputEmail1">User name</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="username" value="{{ $user->username }}">
            @error('username')
                <span style="color: red;">{{ $errors->first('username') }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">Email</label>
            <input type="text" class="form-control" id="exampleInputEmail2" aria-describedby="emailHelp" name="email" value="{{ $user->email }}">
            @error('email')
                <span style="color: red;">{{ $errors->first('email') }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail3">Full name</label>
            <input type="text" class="form-control" id="exampleInputEmail3" aria-describedby="emailHelp" name="name" value="{{ $user->name }}">
            @error('name')
                <span style="color: red;">{{ $errors->first('name') }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="exampleInputEmail4">SĐT</label>
            <input type="text" class="form-control" id="exampleInputEmail4" aria-describedby="emailHelp" name="sdt" value="{{ $user->sdt }}">
            @error('sdt')
                <span style="color: red;">{{ $errors->first('sdt') }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="gender">Giới tính</label>
            <div class="col-3 row">
                <select name="gender" id="gender">
                    <option value="" disabled selected>Chọn</option>
                    <option value="1" {{ $user->gender == 1 ? 'selected' : '' }}>Nam</option>
                    <option value="0" {{ $user->gender == 0 ? 'selected' : '' }}>Nữ</option>
                </select>
                @error('gender')
                    <span style="color: red;">{{ $errors->first('gender') }}</span>
                @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="roles">Vai trò</label>
            <select name="roles[]" multiple class="col-12" id="roles">
                @foreach ($roles as $role)
                    @if (auth()->user()->isRole('teacher') && $role->name === 'admin')
                        @continue
                    @endif
                    <option value="{{ $role->id }}" {{ $listRoles->contains($role->id) ? 'selected' : '' }}>{{ $role->display_name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection
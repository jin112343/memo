@extends('layout')

@section('content')

  <div class="text-right">
    <a href="{{ route('create') }}" class="btn btn-outline-secondary btn-sm mt-4">メモを作成</a>
  </div>

  <div class="container">
    <div class="row">

        @foreach ($memos as $memo)
        <div class="col-md-4 text-left mt-4">

          <div class="card">

            <div class="card-body">
              <p class="text-black-50"> {{ $memo->content }} </p>
            </div>

            <div class="card-footer text-right">
              <a href="{{ route('edit', ['id'=>$memo->id])}}" class="text-info">編集</a>
              <form method="POST" action="{{ route('delete', ['id'=>$memo->id]) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-info p-0">削除</button>
              </form>
            </div>

          </div>

        </div>
        @endforeach

      </div>
  </div>

@endsection

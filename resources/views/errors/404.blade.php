@extends('layouts.layout', ['title'=>"404 ошибка. Выпопали не туда"])

@section('content')
    <div class="card">
        <img src="{{asset('img/404.jpg')}}" alt="space">
    </div>

    <a href="/" class="btn btn-outline-primary">Срочно вернуться на главную</a>
@endsection

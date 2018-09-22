
@extends('layouts.app')

@section('content')
    
    <table border="0" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>NAME</th>
                <th>TEAM LINK</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($teams as $team)
            <tr>
                <td>{{ $team->id }}</td>
                <td>{{ $team->name }}</td>
                <td>{{ $team->team_link }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

@endsection

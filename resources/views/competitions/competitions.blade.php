
@extends('layouts.app')

@section('navbar')
    @parent
    
    
@endsection

@section('content')
    
    <table border="0" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>NAME</th>
                <th>SERVICE</th>
                <th>COMPETITION LINK</th>
                <th>TABLE LINK</th>
                <th>SCORES LINK</th>
                <th>FIXTURES LINK</th>
                <th>RESULTS LINK</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($competitions as $competition)
            <tr>
                <td>{{ $competition->id }}</td>
                <td>{{ $competition->name }}</td>
                <td>{{ $competition->service }}</td>
                <td>{{ $competition->competition_link }}</td>
                <td>{{ $competition->table_link }}</td>
                <td>{{ $competition->scores_link }}</td>
                <td>{{ $competition->fixtures_link }}</td>
                <td>{{ $competition->results_link }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

@endsection

@extends('layouts.app')

@section('title', 'Laporan Masuk')
@section('page-title', 'Laporan Masuk')
@section('content')

@include('layouts.reports.incoming-reports', [
    'reports' => $reports,
    'counts' => $counts,
    'tab' => $tab,
    'tabRouteName' => 'supervisor.laporan-masuk',
    'reviewRouteName' => 'supervisor.laporan.show',
    'accent' => 'emerald',
    'mode' => 'supervisor',
    'description' => 'Laporan dari analis yang dikirimkan kepada Anda untuk ditinjau.',
])

@endsection

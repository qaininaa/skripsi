@extends('layouts.app')

@section('title', 'Laporan Masuk')
@section('page-title', 'Laporan Masuk')
@section('content')

@include('layouts.reports.incoming-reports', [
    'reports' => $reports,
    'counts' => $counts,
    'tab' => $tab,
    'tabRouteName' => 'manajer.laporan-masuk',
    'reviewRouteName' => 'manajer.laporan.show',
    'accent' => 'blue',
    'mode' => 'manager',
    'description' => 'Laporan dari Supervisor yang dikirimkan kepada Anda untuk ditinjau.',
])

@endsection

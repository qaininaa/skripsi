@extends('layouts.app')

@section('title', 'Laporan Sedang Dikerjakan')
@section('page-title', 'Laporan Sedang Dikerjakan')
@section('content')

@include('layouts.reports.ongoing-reports', [
    'reports' => $reports,
    'counts' => $counts,
    'status' => $status,
    'tabRouteName' => 'manajer.laporan-sedang-dikerjakan',
    'previewRouteName' => 'manajer.laporan.preview',
    'accent' => 'blue',
    'description' => 'Pantau laporan yang sedang dikerjakan analis pada tahap monitoring maupun pembacaan.',
])

@endsection

@extends('layouts.app')

@section('title', 'Laporan Sedang Dikerjakan')
@section('page-title', 'Laporan Sedang Dikerjakan')
@section('content')

@include('layouts.reports.ongoing-reports', [
    'reports' => $reports,
    'counts' => $counts,
    'status' => $status,
    'tabRouteName' => 'supervisor.laporan-sedang-dikerjakan',
    'previewRouteName' => 'supervisor.laporan.preview',
    'accent' => 'emerald',
    'description' => 'Pantau laporan yang sedang dikerjakan analis pada tahap monitoring maupun pembacaan.',
])

@endsection

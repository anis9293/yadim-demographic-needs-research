<?php

use Illuminate\Support\Facades\Route;

$demographicFeed = static fn () => [
    'map' => 'Malaysia',
    'data' => [
        ['state' => 'Johor', 'subject-1' => 60, 'subject-2' => 20, 'subject-3' => 10],
        ['state' => 'Kedah', 'subject-1' => 28, 'subject-2' => 34, 'subject-3' => 16],
        ['state' => 'Kelantan', 'subject-1' => 22, 'subject-2' => 18, 'subject-3' => 48],
        ['state' => 'Melaka', 'subject-1' => 38, 'subject-2' => 26, 'subject-3' => 18],
        ['state' => 'Negeri Sembilan', 'subject-1' => 31, 'subject-2' => 42, 'subject-3' => 14],
        ['state' => 'Pahang', 'subject-1' => 46, 'subject-2' => 18, 'subject-3' => 24],
        ['state' => 'Perak', 'subject-1' => 25, 'subject-2' => 44, 'subject-3' => 12],
        ['state' => 'Perlis', 'subject-1' => 19, 'subject-2' => 22, 'subject-3' => 41],
        ['state' => 'Pulau Pinang', 'subject-1' => 15, 'subject-2' => 45, 'subject-3' => 5],
        ['state' => 'Sabah', 'subject-1' => 41, 'subject-2' => 17, 'subject-3' => 33],
        ['state' => 'Sarawak', 'subject-1' => 34, 'subject-2' => 21, 'subject-3' => 39],
        ['state' => 'Selangor', 'subject-1' => 30, 'subject-2' => 10, 'subject-3' => 1],
        ['state' => 'Terengganu', 'subject-1' => 24, 'subject-2' => 29, 'subject-3' => 36],
        ['state' => 'W.P. Kuala Lumpur', 'subject-1' => 55, 'subject-2' => 32, 'subject-3' => 8],
        ['state' => 'W.P. Labuan', 'subject-1' => 18, 'subject-2' => 47, 'subject-3' => 20],
        ['state' => 'W.P. Putrajaya', 'subject-1' => 36, 'subject-2' => 24, 'subject-3' => 29],
    ],
];

Route::get('/', fn () => view('map', ['feed' => $demographicFeed()]));
Route::get('/map', fn () => view('map', ['feed' => $demographicFeed()]));

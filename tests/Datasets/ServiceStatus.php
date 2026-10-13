<?php

use App\Enums\ServiceStatus;

dataset('statusAktif', [
    'Pending' => ServiceStatus::Pending,
    'Confirmed' => ServiceStatus::Confirmed,
    'InProgress' => ServiceStatus::InProgress,
]);

dataset('statusFinal', [
    'Completed' => ServiceStatus::Completed,
    'Cancelled' => ServiceStatus::Cancelled,
    'Rejected' => ServiceStatus::Rejected,
]);

dataset('statusNonSelesai', [
    'Pending' => ServiceStatus::Pending,
    'Confirmed' => ServiceStatus::Confirmed,
    'InProgress' => ServiceStatus::InProgress,
    'Cancelled' => ServiceStatus::Cancelled,
    'Rejected' => ServiceStatus::Rejected,
]);
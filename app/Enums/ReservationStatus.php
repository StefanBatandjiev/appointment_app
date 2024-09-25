<?php

namespace App\Enums;

enum ReservationStatus : string
{
    case SCHEDULED = 'Scheduled';
    case ONGOING = 'Ongoing';
    case PENDING_FINISH = 'Pending Finish';
    case FINISHED = 'Finished';
    case CANCELED = 'Canceled';
}

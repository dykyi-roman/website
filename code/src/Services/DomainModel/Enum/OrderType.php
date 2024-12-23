<?php

declare(strict_types=1);

namespace Services\DomainModel\Enum;

enum OrderType: string
{
    case DATE_ASC = 'date_asc';
    case DATE_DESC = 'date_desc';

    case PRICE_ASC = 'price_asc';
    case PRICE_DESC = 'price_desc';

    case RATING_ASC = 'rating_asc';
    case RATING_DESC = 'rating_desc';
}
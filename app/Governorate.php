<?php
namespace App\Enums;


enum Governorate: string 

{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    case DAMASCUS = 'دمشق';       
    case RIF_DIMASHQ = 'ريف دمشق';
    case ALEPPO = 'حلب';

}
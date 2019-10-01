<?php

declare(strict_types=1);

namespace App\Model\Shop\Entity\Product\Type;

use App\Model\User\Entity\User\ValueObject\Id;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class IdType extends StringType
{
    public const NAME = 'shop_product_products_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof Id ? $value->getValue() : $value;
    }
    
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return !empty($value) ? new Id($value) : null;
    }
    
    public function getName(): string
    {
        return self::NAME;
    }
}

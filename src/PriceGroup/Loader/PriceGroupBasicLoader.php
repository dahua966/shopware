<?php

namespace Shopware\PriceGroup\Loader;

use Doctrine\DBAL\Connection;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Struct\SortArrayByKeysTrait;
use Shopware\PriceGroup\Factory\PriceGroupBasicFactory;
use Shopware\PriceGroup\Struct\PriceGroupBasicCollection;
use Shopware\PriceGroup\Struct\PriceGroupBasicStruct;

class PriceGroupBasicLoader
{
    use SortArrayByKeysTrait;

    /**
     * @var PriceGroupBasicFactory
     */
    private $factory;

    public function __construct(
        PriceGroupBasicFactory $factory
    ) {
        $this->factory = $factory;
    }

    public function load(array $uuids, TranslationContext $context): PriceGroupBasicCollection
    {
        if (empty($uuids)) {
            return new PriceGroupBasicCollection();
        }

        $priceGroups = $this->read($uuids, $context);

        return $priceGroups;
    }

    private function read(array $uuids, TranslationContext $context): PriceGroupBasicCollection
    {
        $query = $this->factory->createQuery($context);

        $query->andWhere('price_group.uuid IN (:ids)');
        $query->setParameter(':ids', $uuids, Connection::PARAM_STR_ARRAY);

        $rows = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $structs = [];
        foreach ($rows as $row) {
            $struct = $this->factory->hydrate($row, new PriceGroupBasicStruct(), $query->getSelection(), $context);
            $structs[$struct->getUuid()] = $struct;
        }

        return new PriceGroupBasicCollection(
            $this->sortIndexedArrayByKeys($uuids, $structs)
        );
    }
}

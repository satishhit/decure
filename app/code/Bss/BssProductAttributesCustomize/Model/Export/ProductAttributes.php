<?php
namespace Bss\BssProductAttributesCustomize\Model\Export;

use Magento\Store\Model\Store;
class ProductAttributes extends \Bss\ProductAttributesImportExport\Model\Export\ProductAttributes
{
    /**
     * @return mixed
     */
    public function export()
    {
        set_time_limit(0);

        $writer = $this->getWriter();
        $entityCollection = $this->_getEntityCollection(true);
        $entityCollection->setOrder('has_options', 'asc');
        $entityCollection->setStoreId(Store::DEFAULT_STORE_ID);
        $this->_prepareEntityCollection($entityCollection);
        $this->paginateCollection(1, $this->getItemsPerPage());
        $exportData = $this->getExportData();
        $writer->setHeaderCols($this->_getHeaderColumns());
        foreach ($exportData as $dataRow) {
            $writer->writeRow($dataRow);
        }
        return $writer->getContents();
    }
}
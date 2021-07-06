<?php
namespace Random\CmsImport\Model\Import;

use Random\CmsImport\Model\Import\CmsImport\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\ResourceConnection;

class CmsBlock extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{

    const ID = 'block_id';
    const TITLE = 'title';
    const INDENTIFIER = 'identifier';
    const DESC = 'content';
    const CREATE = 'creation_time';
    const UPDATE = 'update_time';
    const ACTIVE = 'is_active';
    const STORE = 'store_id';

    const TABLE_Entity = 'cms_block';
    const TABLE_Entity2 = 'cms_block_store';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_TITLE_IS_EMPTY => 'TITLE is empty',
    ];

     protected $_permanentAttributes = [self::ID];
    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;
    protected $groupFactory;
    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::ID,
        self::TITLE,
        self::INDENTIFIER,
        self::DESC,
        self::CREATE,
        self::UPDATE,
        self::ACTIVE,
        self::STORE,
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    protected $_validators = [];


    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_connection;
    protected $_resource;

    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Customer\Model\GroupFactory $groupFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->groupFactory = $groupFactory;
    }
    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'cms_block';
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {

        $title = false;

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;
        // BEHAVIOR_DELETE use specific validation logic
       // if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::TITLE]) || empty($rowData[self::TITLE])) {
                $this->addRowError(ValidatorInterface::ERROR_TITLE_IS_EMPTY, $rowNum);
                return false;
            }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }


    /**
     * Create Advanced price data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteEntity();
            $this->deleteStore();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceEntity();
            $this->replaceStore();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveEntity();
            $this->saveStore();
        }

        return true;
    }
    /**
     * Save Block Data
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    /**
     * Save Block Storeid
     *
     * @return $this
     */
    public function saveStore()
    {
        $this->saveAndReplaceStore();
        return $this;
    }
    /**
     * Replace block data
     *
     * @return $this
     */
    public function replaceEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    /**
     * Replace block storeid
     *
     * @return $this
     */
    public function replaceStore()
    {
        $this->saveAndReplaceStore();
        return $this;
    }
    /**
     * Deletes block data data from raw data.
     *
     * @return $this
     */
    public function deleteEntity()
    {
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowTtile = $rowData[self::ID];
                    $listTitle[] = $rowTtile;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listTitle) {
            $this->deleteEntityFinish(array_unique($listTitle),self::TABLE_Entity);
        }
        return $this;
    }
     /**
     * Deletes block store data from raw data.
     *
     * @return $this
     */
    public function deleteStore()
    {
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowTtile = $rowData[self::ID];
                    $listTitle[] = $rowTtile;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listTitle) {
            $this->deleteStoreFinish(array_unique($listTitle),self::TABLE_Entity2);
        }
        return $this;
    }
     /**
     * Save and replace block data
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_TITLE_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowTtile= $rowData[self::ID];
                $listTitle[] = $rowTtile;
                $entityList[$rowTtile][] = [
                  self::ID => $rowData[self::ID],  
                  self::TITLE => $rowData[self::TITLE],
                  self::INDENTIFIER => $rowData[self::INDENTIFIER],
                  self::DESC => $rowData[self::DESC],
                  self::CREATE => $rowData[self::CREATE],
                  self::UPDATE => $rowData[self::UPDATE],
                  self::ACTIVE => $rowData[self::ACTIVE],
                ];
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                if ($listTitle) {
                    if ($this->deleteEntityFinish(array_unique(  $listTitle), self::TABLE_Entity)) {
                        $this->saveEntityFinish($entityList, self::TABLE_Entity);
                    }
                }
            } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->saveEntityFinish($entityList, self::TABLE_Entity);
            }
        }
        return $this;
    }
    /**
     * Save and replace block store
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceStore()
    {
        $behavior = $this->getBehavior();
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_TITLE_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowTtile= $rowData[self::ID];
                $listTitle[] = $rowTtile;
                $entityList[$rowTtile][] = [
                  self::ID => $rowData[self::ID],  
                  self::STORE => $rowData[self::STORE],
                ];
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                if ($listTitle) {
                    if ($this->deleteStoreFinish(array_unique(  $listTitle), self::TABLE_Entity2)) {
                        $this->saveStoreFinish($entityList, self::TABLE_Entity2);
                    }
                }
            } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->saveStoreFinish($entityList, self::TABLE_Entity2);
            }
        }
        return $this;
    }
    /**
     * Save block data.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    protected function saveEntityFinish(array $entityData, $table)
    {
        if ($entityData) {
            $tableName = $this->_connection->getTableName($table);
            $entityIn = [];
            foreach ($entityData as $id => $entityRows) {
                    foreach ($entityRows as $row) {
                        $entityIn[] = $row;
                    }
            }
            if ($entityIn) {
                $this->_connection->insertOnDuplicate($tableName, $entityIn,[
                self::ID,
                self::TITLE,
                self::INDENTIFIER,
                self::DESC,
                self::CREATE,
                self::UPDATE,
                self::ACTIVE,
            ]);
            }
        }
        return $this;
    }
    /**
     * Save block store.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    protected function saveStoreFinish(array $entityData, $table)
    {
        if ($entityData) {
            $tableName = $this->_connection->getTableName($table);
            $entityIn = [];
            foreach ($entityData as $id => $entityRows) {
                    foreach ($entityRows as $row) {
                        $entityIn[] = $row;
                    }
            }
            if ($entityIn) {
                $this->_connection->insertOnDuplicate($tableName, $entityIn,[
                self::ID,
                self::STORE,
            ]);
            }
        }
        return $this;
    }
    protected function deleteEntityFinish(array $listTitle, $table)
    {
        if ($table && $listTitle) {
                try {
                    $this->countItemsDeleted += $this->_connection->delete(
                        $this->_connection->getTableName($table),
                        $this->_connection->quoteInto('block_id IN (?)', $listTitle)
                    );
                    return true;
                } catch (\Exception $e) {
                    return false;
                }

        } else {
            return false;
        }
    }
    protected function deleteStoreFinish(array $listTitle, $table)
    {
        if ($table && $listTitle) {
                try {
                    $this->countItemsDeleted += $this->_connection->delete(
                        $this->_connection->getTableName($table),
                        $this->_connection->quoteInto('block_id IN (?)', $listTitle)
                    );
                    return true;
                } catch (\Exception $e) {
                    return false;
                }

        } else {
            return false;
        }
    }
}

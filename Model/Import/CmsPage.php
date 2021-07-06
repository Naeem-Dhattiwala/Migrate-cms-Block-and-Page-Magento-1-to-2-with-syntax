<?php
namespace Random\CmsImport\Model\Import;

use Random\CmsImport\Model\Import\CmsImport\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\ResourceConnection;

class CmsPage extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{

    const ID = 'page_id';
    const TITLE = 'title';
    const LAYOUT = 'page_layout';
    const METAKEY = 'meta_keywords';
    const METADESC = 'meta_description';
    const INDENTIFIER = 'identifier';
    const HEADING = 'content_heading';
    const DESC = 'content';
    const CREATE = 'creation_time';
    const UPDATE = 'update_time';
    const ACTIVE = 'is_active';
    const SORT = 'sort_order';
    const lAYOUTUPDATEXML = 'layout_update_xml';
    const THEME = 'custom_theme';
    const CREATETEMPLATE = 'custom_root_template';
    const lAYOUTUPDATE = 'custom_layout_update_xml'; 
    const lAYOUTUPDATESELECTED = 'layout_update_selected';
    const THEMEFROM = 'custom_theme_from';
    const THEMETO = 'custom_theme_to';
    const METATITLE = 'meta_title';
    const STORE = 'store_id';


    const TABLE_Entity = 'cms_page';
    const TABLE_Entity2 = 'cms_page_store';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_TITLE_IS_EMPTY => 'TITLE is empty',
    ];

     protected $_permanentAttributes = [self::TITLE];
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
        self::LAYOUT,
        self::METAKEY,
        self::METADESC,
        self::INDENTIFIER,
        self::HEADING,
        self::DESC,
        self::CREATE,
        self::UPDATE,
        self::ACTIVE,
        self::SORT,
        self::lAYOUTUPDATEXML,
        self::THEME,
        self::CREATETEMPLATE,
        self::lAYOUTUPDATE,
        self::lAYOUTUPDATESELECTED,
        self::THEMEFROM,
        self::THEMETO,
        self::METATITLE,
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
        return 'cms_page';
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
            if (!isset($rowData[self::ID]) || empty($rowData[self::ID])) {
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
     * Save Cms Page
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Save Cms Page Storeid
     *
     * @return $this
     */
    public function saveStore()
    {
        $this->saveAndReplaceStore();
        return $this;
    }
    /**
     * Replace Cms Page
     *
     * @return $this
     */
    public function replaceEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    /**
     * Replace Cms Store
     *
     * @return $this
     */
    public function replaceStore()
    {
        $this->saveAndReplaceStore();
        return $this;
    }
    /**
     * Deletes Cms data data from raw data.
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
     * Deletes Cms Storeid data from raw data.
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
     * Save and replace Cms subscriber
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
                  self::LAYOUT => $rowData[self::LAYOUT],
                  self::METAKEY => $rowData[self::METAKEY],
                  self::METADESC => $rowData[self::METADESC],
                  self::INDENTIFIER => $rowData[self::INDENTIFIER],
                  self::HEADING => $rowData[self::HEADING],
                  self::DESC => $rowData[self::DESC],
                  self::CREATE => $rowData[self::CREATE],
                  self::UPDATE => $rowData[self::UPDATE],
                  self::ACTIVE => $rowData[self::ACTIVE],
                  self::SORT => $rowData[self::SORT],
                  self::lAYOUTUPDATEXML => $rowData[self::lAYOUTUPDATEXML],
                  self::THEME => $rowData[self::THEME],
                  self::CREATETEMPLATE => $rowData[self::CREATETEMPLATE],
                  self::lAYOUTUPDATE => $rowData[self::lAYOUTUPDATE],
                  self::lAYOUTUPDATESELECTED => $rowData[self::lAYOUTUPDATESELECTED],
                  self::THEMEFROM => $rowData[self::THEMEFROM],
                  self::THEMETO => $rowData[self::THEMETO],
                  self::METATITLE => $rowData[self::METATITLE],
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
                  self::STORE => $rowData[self::STORE]
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
     * Save Cms Data.
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
                self::LAYOUT,
                self::METAKEY,
                self::METADESC,
                self::INDENTIFIER,
                self::HEADING,
                self::DESC,
                self::CREATE,
                self::UPDATE,
                self::ACTIVE,
                self::SORT,
                self::lAYOUTUPDATEXML,
                self::THEME,
                self::CREATETEMPLATE,
                self::lAYOUTUPDATE,
                self::lAYOUTUPDATESELECTED,
                self::THEMEFROM,
                self::THEMETO,
                self::METATITLE
            ]);
            }
        }
        return $this;
    }
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
                self::STORE
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
                        $this->_connection->quoteInto('page_id IN (?)', $listTitle)
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
                        $this->_connection->quoteInto('page_id IN (?)', $listTitle)
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

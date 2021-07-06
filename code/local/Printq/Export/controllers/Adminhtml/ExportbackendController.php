<?php
class Printq_Export_Adminhtml_ExportbackendController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('export/exportbackend');
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Export Content"));
	   $this->renderLayout();
    }
    public function exportAction()
	{
	 	if ($_GET) {
		    if ($_GET['cms-entity'] == 'page') {
		    	$f = fopen("export_cms_page.csv", "w");
		    	$header = array('page_id','title','page_layout','meta_keywords','meta_description','identifier','content_heading','content','creation_time','update_time','is_active','sort_order','layout_update_xml','custom_theme','custom_root_template','custom_layout_update_xml','layout_update_selected','custom_theme_from','custom_theme_to','meta_title','store_id');
		    	fputcsv($f, $header);
		    	$find = array('cms/widget_page_link',
		    				  'cms/widget',
		    				  'widget_block',
		    				  'cms/widget/static_block',
		    				  'catalog/category_widget_link',
		    				  'catalog/category',
		    				  'catalog/product_widget_new', 
		    				  'catalog/product',
		    				  'product_widget_link',
		    				  'sales/widget_guest_form',
		    				  'sales/widget',
		    				  'reports/product_widget_compared',
		    				  'reports/widget',
		    				  'reports/product_widget_viewed'
		    				);

		    	$replace = array('Magento\Cms\Block\Widget\Page\Link', 
		    					 'widget',
		    					 'Magento\Cms\Block\Widget\Block',
		    					 'widget/static_block',
		    					 'Magento\Catalog\Block\Category\Widget\Link',
		    					 'category',
		    					 'Magento\Catalog\Block\Product\Widget\NewWidget', 
		    					 'product',
		    					 'Magento\Catalog\Block\Product\Widget\Link',
		    					 'Magento\Sales\Block\Widget\Guest\Form',
		    					 'sales',
		    					 'Magento\Catalog\Block\Widget\RecentlyCompared',
		    					 'widget',
		    					 'Magento\Catalog\Block\Widget\RecentlyViewed'
		    					);
		    	$find2 = array('empty',
		    				  'one_column',
		    				  'two_columns_left',
		    				  'two_columns_right',
		    				  'three_columns'
		    				);

		    	$replace2 = array('empty', 
		    					 '1column',
		    					 '2columns-left',
		    					 '2columns-right',
		    					 '3columns'
		    					);
		    	$collection = Mage::getModel('cms/page')->getCollection();
			    foreach ($collection as $page) {
			    	  $content = str_replace($find, $replace, $page->getContent());
			    	  $layout = str_replace($find2, $replace2, $page->getRoot_template());
			    	  $data = array($page->getPage_id(),$page->getTitle(),$layout,$page->getMeta_keywords(),$page->getMeta_description(),$page->getIdentifier(),$page->getContent_heading(),$content,$page->getCreation_time(),$page->getUpdate_time(),$page->getIs_active(),$page->getSort_order(),$page->getLayout_update_xml(),$page->getCustom_theme(),$page->getCustom_root_template(),$page->getCustom_layout_update_xml(),'',$page->getCustom_theme_from(),$page->getCustom_theme_to(),$page->getTitle(),$page->getWebsite_root());
			    	$csv = fputcsv($f, $data);
			    }
			    if ($csv) {
			    	$this->_redirect('admin_export/adminhtml_exportbackend/');
			    	Mage::getSingleton('core/session')->addSuccess('File Downloaded Sucessfully in Root Path');
			    	fclose($f);
			    }
			    else{
			    	$this->_redirect('admin_export/adminhtml_exportbackend/');
			    	Mage::getSingleton('adminhtml/session')->addWarning('File not Downloaded');
			    }
		    }
		    else {
		    	$f2 = fopen("export_cms_block.csv", "w");
		    	$header = array('block_id','title','identifier','content','creation_time','update_time','is_active','store_id');
		    	fputcsv($f2, $header);
		    	$find = array('cms/widget_page_link',
		    				  'cms/widget',
		    				  'widget_block',
		    				  'cms/widget/static_block',
		    				  'catalog/category_widget_link',
		    				  'catalog/category',
		    				  'catalog/product_widget_new', 
		    				  'catalog/product',
		    				  'product_widget_link',
		    				  'sales/widget_guest_form',
		    				  'sales/widget',
		    				  'reports/product_widget_compared',
		    				  'reports/widget',
		    				  'reports/product_widget_viewed'
		    				);

		    	$replace = array('Magento\Cms\Block\Widget\Page\Link', 
		    					 'widget',
		    					 'Magento\Cms\Block\Widget\Block',
		    					 'widget/static_block',
		    					 'Magento\Catalog\Block\Category\Widget\Link',
		    					 'category',
		    					 'Magento\Catalog\Block\Product\Widget\NewWidget', 
		    					 'product',
		    					 'Magento\Catalog\Block\Product\Widget\Link',
		    					 'Magento\Sales\Block\Widget\Guest\Form',
		    					 'sales',
		    					 'Magento\Catalog\Block\Widget\RecentlyCompared',
		    					 'widget',
		    					 'Magento\Catalog\Block\Widget\RecentlyViewed'
		    					);

		    	$collection = Mage::getModel('cms/block')->getCollection();
			    foreach ($collection as $block) {
			    	$storeIds = implode('', $block->getResource()->lookupStoreIds($block->getBlockId()));
			    	$content = str_replace($find, $replace, $block->getContent());
			    	$data = array($block->getBlock_id(),$block->getTitle(),$block->getIdentifier(),$content,$block->getCreation_time(),$block->getUpdate_time(),$block->getIs_active(),$storeIds);
			    	$csv2 = fputcsv($f2, $data);
			    }

			    if ($csv2) {
			    	$this->_redirect('admin_export/adminhtml_exportbackend/');
			    	Mage::getSingleton('core/session')->addSuccess('File Downloaded Sucessfully in Root Path');
			    	fclose($f2);
			    }
			    else{
			    	$this->_redirect('admin_export/adminhtml_exportbackend/');
			    	Mage::getSingleton('adminhtml/session')->addWarning('File not Downloaded');
			    }
		    }
	 	}
	}
}
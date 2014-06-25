<?php

/**
 * Copyright (C) 2014 HB Agency
 * 
 * @author		Blair Winans <bwinans@hbagency.com>
 * @author		Adam Fisher <afisher@hbagency.com>
 * @link		http://www.hbagency.com
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */
 
namespace IsotopeDirect\Module;

use IsotopeDirect\Filter\Filter;

use Haste\Haste;
use Haste\Generator\RowClass;
use Haste\Http\Response\HtmlResponse;

use Isotope\Isotope;
use Isotope\Frontend as Isotope_Frontend;
use Isotope\Model\Product as Product_Model;
use Isotope\Model\ProductType as ProductType_Model;
use Isotope\Model\Product\Standard as Standard_Product;
use Isotope\Module\Module as Isotope_Module;
use Isotope\Module\ProductList as Isotope_ProductList;
use Isotope\RequestCache\Sort as RequestCache_Sort;



/**
 * Class ProductList
 * A more direct version of the product list
 */
class ProductList extends Isotope_ProductList
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_productlist_direct';

    /**
     * Cache items. Can be disable in a child class, e.g. a "random items list"
     * @var boolean
     */
    protected $blnCacheProperties = false;


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT LIST - DIRECT ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }
        
        // Hide item list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && \Input::get('product') != '')
        {
            return '';
        }

        /*$this->iso_filterModules = deserialize($this->iso_filterModules, true);

        // Apply limit from filter module
        if (is_array($this->iso_filterModules))
        {
            // We only do this once. getFiltersAndSorting() then automatically has the correct sorting
            $this->iso_filterModules = array_reverse($this->iso_filterModules);

            foreach ($this->iso_filterModules as $module)
            {
                if ($GLOBALS['ISO_LIMIT'][$module] > 0)
                {
                    $this->perPage = $GLOBALS['ISO_LIMIT'][$module];
                    break;
                }
            }
        }*/

        return parent::generate();
    }


    /**
     * Generate a single item and return it's HTML string
     * TEMPORARY UNTIL WE UPDATE THE AJAX STUFF
     * @return string
     */
    public function generateAjax()
    {
        $objItem = Isotope_Frontend::getProduct(\Input::get('item'));

        if ($objItem !== null)
        {
            return $objItem->generateAjax($this);
        }

        return '';
    }


    /**
     * Compile item list.
     *
     * This function is specially designed so you can keep it in your child classes and only override findProperties().
     * You will automatically gain item caching (see class property), grid classes, pagination and more.
     *
     * @return void
     */
    protected function compile()
    {
        global $objPage;
        
        //Get products
        $arrProducts = $this->findProducts();
        
        // No items found
        if (!is_array($arrProducts) || empty($arrProducts))
        {
            // Do not index or cache the page
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $this->Template->empty = true;
            $this->Template->type = 'empty';
            $this->Template->message = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noProducts'];
            $this->Template->items = array();

            return;
        }
		
		//Handle jump to first
        if ($this->iso_jump_first && \Input::get('product') == '')
        {
            $objProduct = $objProducts->first();
            $this->redirect($objProduct->href_reader);
        }
        $arrBuffer = array();

        $arrDefaultOptions = $this->getDefaultProductOptions();

        foreach ($arrProducts as $objProduct) {
            $arrConfig = array(
                'module'        => $this,
                'template'      => ($this->iso_list_layout ?: $objProduct->getRelated('type')->list_template),
                'gallery'       => ($this->iso_gallery ?: $objProduct->getRelated('type')->list_gallery),
                'buttons'       => deserialize($this->iso_buttons, true),
                'useQuantity'   => $this->iso_use_quantity,
                'jumpTo'        => $this->findJumpToPage($objProduct),
            );

            if (\Environment::get('isAjaxRequest') && \Input::post('AJAX_MODULE') == $this->id && \Input::post('AJAX_PRODUCT') == $objProduct->getProductId()) {
                $objResponse = new HtmlResponse($objProduct->generate($arrConfig));
                $objResponse->send();
            }

            $objProduct->mergeRow($arrDefaultOptions);

            // Must be done after setting options to generate the variant config into the URL
            if ($this->iso_jump_first && \Haste\Input\Input::getAutoItem('product') == '') {
                \Controller::redirect($objProduct->generateUrl($arrConfig['jumpTo']));
            }

            $arrCSS = deserialize($objProduct->cssID, true);

            $arrBuffer[] = array(
                'cssID'     => ($arrCSS[0] != '') ? ' id="' . $arrCSS[0] . '"' : '',
                'class'     => trim('product ' . ($objProduct->isNew() ? 'new ' : '') . $arrCSS[1]),
                'html'      => $objProduct->generate($arrConfig),
                'product'   => $objProduct,
            );
        }

        // HOOK: to add any product field or attribute to mod_iso_productlist template
        if (isset($GLOBALS['ISO_HOOKS']['generateProductList']) && is_array($GLOBALS['ISO_HOOKS']['generateProductList'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateProductList'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrBuffer   = $objCallback->$callback[1]($arrBuffer, $arrProducts, $this->Template, $this);
            }
        }

        RowClass::withKey('class')->addCount('product_')->addEvenOdd('product_')->addFirstLast('product_')->addGridRows($this->iso_cols)->addGridCols($this->iso_cols)->applyTo($arrBuffer);

        $this->Template->products = $arrBuffer;
                
    }


    /**
     * Find all properties we need to list.
     * @return array
     */
    protected function findProducts($arrCacheIds=null)
    {
        $arrColumns    = array();
        $arrCategories = $this->findCategories();
		
		//Get filters and sorting values
        list($arrValues, $strWhere, $strSorting) = $this->getFiltersAndSorting();
		
		//Handle no values
        if (!is_array($arrValues)) {
            $arrValues = array();
        }
        
        //Add categories to query
        //$arrColumns[] = "c.page_id IN (" . implode(',', $arrCategories) . ")";
	    $arrColumns[] = Product_Model::getTable() . ".id IN( SELECT pid FROM tl_iso_product_category WHERE page_id IN (" . implode(',', $arrCategories) . "))"; 
		
		//Get only cache IDs
        if (!empty($arrCacheIds) && is_array($arrCacheIds)) {
            $arrColumns[] = Product_Model::getTable() . ".id IN (" . implode(',', $arrCacheIds) . ")";
        }

        // Apply new/old product filter
        if ($this->iso_newFilter == 'show_new') {
            $arrColumns[] = Product_Model::getTable() . ".dateAdded>=" . Isotope::getConfig()->getNewProductLimit();
        } elseif ($this->iso_newFilter == 'show_old') {
            $arrColumns[] = Product_Model::getTable() . ".dateAdded<" . Isotope::getConfig()->getNewProductLimit();
        }

        if ($this->iso_list_where != '') {
            $arrColumns[] = Haste::getInstance()->call('replaceInsertTags', $this->iso_list_where);
        }
        
        //Add where query from filters/sorting
        if ($strWhere != '') {
            $arrColumns[] = $strWhere;
        }
		
		//Calculate the total on the query
        $intTotal = static::countPublishedBy($arrColumns, $arrValues, $arrOptions);
	
		//Generate pagination and get offset
		$offset = $this->generatePagination($intTotal);
		
		//Build options
		$arrOptions = array
		(
			'offset'	=> $offset,
			'limit'		=> $this->perPage,
			'order'		=> $strSorting,
		);
		
		//Run query
        $objProducts = Product_Model::findPublishedBy(
            $arrColumns,
            $arrValues,
            $arrOptions
            /*array(
                 'order'   => $strSorting ?: 'c.sorting',
                 'filters' => array(),
                 'sorting' => array(),
            )*/
        );

        return (null === $objProducts) ? array() : $objProducts->getModels();
    }


    /**
     * Generate the pagination
     * @param integer
     * @return integer
     */
    protected function generatePagination($total)
    {
        // Add pagination
        if ($this->perPage > 0 && $total > 0)
        {
            $page = \Input::get('page') ? \Input::get('page') : 1;

            // Check the maximum page number
            if ($page > ($total/$this->perPage))
            {
                $page = ceil($total/$this->perPage);
            }

            $offset = ($page - 1) * $this->perPage;

            $objPagination = new \Pagination($total, $this->perPage);
            $this->Template->pagination = $objPagination->generate("\n  ");

            return $offset;
        }

        return 0;
    }
    
    
    
    /**
     * Get filter & sorting configuration
     * @return array
     */
    protected function getFiltersAndSorting($blnNativeSQL = true)
    {
    	$strWhere 	= '';
    	$strSorting = '';
    	$arrFilters	= array();
    	$arrWhere	= array();
    	$arrValues 	= array();
    	$arrSorting	= array();
    	
    	
    	// Sorting
    	if (\Input::get('sorting'))
    	{
    		$arrSortField = explode('-', \Input::get('sorting'));
    		
	    	// Needs to be a field value in tl_iso_product and either be asc or desc
	    	if (\Database::getInstance()->fieldExists($arrSortField[0], Product_Model::getTable()) && (strtolower($arrSortField[1])=='asc' || strtolower($arrSortField[1])=='desc'))
	    	{
		    	$strSorting = $arrSortField[0] . ' ' . strtoupper($arrSortField[1]);
	            //$arrSorting[$arrSortField[0]] = (strtoupper($arrSortField[1]) == 'DESC' ? RequestCache_Sort::descending() : RequestCache_Sort::ascending());
	    	}
    	}
    	
    	// Default sorting
    	if (!($strSorting) && $this->iso_listingSortField && $this->iso_listingSortDirection)
    	{
	    	$strSorting = $this->iso_listingSortField . ' ' . $this->iso_listingSortDirection;
            //$arrSorting[$this->iso_listingSortField] = ($this->iso_listingSortDirection == 'DESC' ? RequestCache_Sort::descending() : RequestCache_Sort::ascending());
	    	
    	}
    	
    	
    	// Price range
    	if (\Input::get('pricerange'))
    	{
    		$arrTempWhere = array();
    		$arrGet = array_map(array('IsotopeDirect\Filter\Filter', 'uncleanChars'), explode(',', \Input::get('pricerange')));
    		
    		foreach ($arrGet as $get)
    		{
	    		$arrRange = trimsplit('to', $get);
	    		
	    		if (empty($arrRange))
	    		{
		    		continue;
	    		}
	    		
	    		// todo: add config_id, member_group, start, stop
	    		$strRangeWhere = "SELECT p.pid AS `product_id` FROM tl_iso_product_price p
									INNER JOIN tl_iso_product_pricetier pt
										ON p.id = pt.pid
									WHERE pt.min = 1";
									
		    	$strRangeWhere .= " AND pt.price >= " . intval($arrRange[0]);
	    		
	    		if (count($arrRange) > 1 && !empty($arrRange[1]))
	    		{
			    	$strRangeWhere .= " AND pt.price < " . intval($arrRange[1]);
	    		}
	    		
	    		$objResult = \Database::getInstance()->executeUncached($strRangeWhere);
	    		
	    		if ($objResult->numRows)
	    		{
	    			$arrTempWhere[] = Product_Model::getTable() . ".id IN (".implode(',', $objResult->fetchEach('product_id')).")";
	    		}
    		}
    		
    		if (count($arrTempWhere) > 0)
    		{
    			//Note that this filter is an OR
			    $arrWhere['pricerange'] = '(' . implode(" OR ", $arrTempWhere) . ')';
    		}
    	}
    	
    	
    	// Keywords
    	if (\Input::get('keywords'))
    	{
    		$arrFields = deserialize($this->iso_searchFields, true);
    		
    		if (count($arrFields))
    		{
    			$where = array();
    			include_once(TL_ROOT . '/system/modules/isotope_direct/config/stopwords.php');
    			
	    		$arrKeywords = array_map(array('IsotopeDirect\Filter\Filter', 'uncleanChars'), explode(',', \Input::get('keywords')));
    			
    			foreach ($arrKeywords as $keyword)
    			{
    				// Look for all words in the fields
	    			$arrFinalKeywords = explode(' ', $keyword);
	    			
	    			foreach ($arrFinalKeywords as $finalKeyword)
	    			{
	    				$strTerm = trim($finalKeyword);
	    				
	    				if (empty($strTerm) || in_array(strtolower($strTerm), array_map('strtolower', $GLOBALS['KEYWORD_STOP_WORDS'])) || in_array(strtolower($strTerm), array_map('strtolower', $GLOBALS['KEYWORD_STOP_WORDS'])))
	    				{
		    				continue;
	    				}
	    				
			    		foreach ($arrFields as $field)
			    		{
				    		$where[] = Product_Model::getTable() . ".$field REGEXP ?";
					    	$arrValues[] = $strTerm;
			    		}
	    			}
    				
    			}
	    		
	    		if (count($where))
	    		{
			    	$arrWhere['keywords'] = '('.implode(' OR ', $where).')';
			    }
    		}
    	}

        // !HOOK: custom filter/sorting types
        if (isset($GLOBALS['ISO_HOOKS']['processFiltersAndSorting']) && is_array($GLOBALS['ISO_HOOKS']['processFiltersAndSorting']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['processFiltersAndSorting'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                list($arrValues, $arrWhere, $strSorting) = $objCallback->$callback[1]($arrValues, $arrWhere, $strSorting, $this->findCategories(), $this);
            }
        }
    	    	
    	// Now put together the entire WHERE
    	if(count($arrWhere) > 0)
    	{
	    	$strWhere = implode(' AND ', $arrWhere);
    	}
    	
    	return array($arrValues, $strWhere, $strSorting);
    }
    
    
    /**
     * Find published products by condition
     * @param   mixed
     * @param   mixed
     * @param   array
     * @return  \Collection
     */
    public static function countPublishedBy($arrColumns, $arrValues, $arrOptions=array())
    {
        $p = Product_Model::getTable();
        $t = ProductType_Model::getTable();

        $arrValues = (array) $arrValues;

        if (!is_array($arrColumns)) {
            $arrColumns = array();
        }
        
        if (BE_USER_LOGGED_IN !== true) {
            $time = time();
            $arrColumns[] = "$p.published='1' AND ($p.start='' OR $p.start<$time) AND ($p.stop='' OR $p.stop>$time)";
        }
        
        //Running a straight up SQL query here to optimize
        $strQuery = "SELECT COUNT($p.id) AS count FROM $p";
        $strQuery .= " INNER JOIN $t t ON $p.type=t.id";
        $strQuery .= " WHERE " . implode(" AND ", $arrColumns);
        
        //Group
        if($arrOptions['group'] !== null)
        {
	        $strQuery .= " GROUP BY " . $arrOptions['group'];
        }
        
        return (int) \Database::getInstance()->prepare($strQuery)->execute($arrValues)->count;
    }

}

<?php

/**
 * Copyright (C) 2015 Rhyme Digital, LLC
 * 
 * @author		Blair Winans <blair@rhyme.digital>
 * @author		Adam Fisher <adam@rhyme.digital>
 * @link		http://rhyme.digital
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


namespace IsotopeDirect\Module;

use IsotopeDirect\Filter\Filter;

use Isotope\Isotope;
use Isotope\Model\Product as Product_Model;
use Isotope\Module\Module as Isotope_Module;


/**
 * Class ProductFilter
 * A more direct version of the product filter
 */
class ProductFilter extends Isotope_Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_filter_direct_default';

    /**
     * Form ID
     * @var string
     */
    protected $strFormIdPrefix = 'iso_filter_';
    
    /**
     * Global categories for filters
     * @var array
     */
    protected $arrCategories;

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT FILTERS - DIRECT ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Hide filters in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && \Input::get('product') != '')
        {
            return '';
        }
        
        // Override the template
        if ($this->iso_filterTpl)
        {
	        $this->strTemplate = $this->iso_filterTpl;
        }

        $strBuffer = parent::generate();

        return $strBuffer;
    }


    /**
     * Generate the module
     * @return void
     */
    protected function compile()
    {
    	global $objPage;
    	$this->loadDataContainer('tl_iso_product');
    	
    	//Get initial categories
    	$this->arrCategories = $this->findCategories($this->iso_category_scope);
    
    	//Handle requests before generating as we will likely redirect
    	if (\Input::post('FORM_SUBMIT') == $this->strFormIdPrefix . $this->id)
    	{
    		$this->handleRequests();
		}
		
		//Generate the filters if there are no request redirects
    	$objTemplate = $this->Template;
    	
    	$arrFilterTypes = deserialize($this->iso_filterTypes, true);
    	
    	foreach ($arrFilterTypes as $filterType)
    	{
	    	if (!isset($GLOBALS['PRODUCT_FILTERS'][$filterType]))
	    	{
		    	continue;
	    	}
	    	
			$strClass = $GLOBALS['PRODUCT_FILTERS'][$filterType]['class'];
			
			if (!class_exists($strClass))
			{
				continue;
			}
	    	
	    	$strClass::generateFilter($this->arrCategories, $objTemplate, $this);
    	}
    	
    	$this->Template = $objTemplate;
        $this->Template->id = $this->id;
        $this->Template->formId = $this->strFormIdPrefix . $this->id;
        $this->Template->actionClear = \Controller::generateFrontendUrl($objPage->row());
        $this->Template->clearLabel = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
        $this->Template->slabel = $GLOBALS['TL_LANG']['MSC']['submitLabel'];
    }
    
    
    /**
     * Handle incoming filter requests and generate appropriate URL
     * @return void
     */
    protected function handleRequests()
    {
    	if (!$this->jumpTo)
    	{
	    	global $objPage;
    	}
    	else
    	{
	    	$objPage = \PageModel::findWithDetails($this->jumpTo);
    	}
    	
    	$arrRedirectParams = array();
    	$strRedirectParams = '';
    	
    	$arrFilterTypes = deserialize($this->iso_filterTypes, true);
    	
    	// Loop through each filter type to get the URL params
    	foreach ($arrFilterTypes as $filterType)
    	{
	    	if (!isset($GLOBALS['PRODUCT_FILTERS'][$filterType]))
	    	{
		    	continue;
	    	}
	    	
			$strClass = $GLOBALS['PRODUCT_FILTERS'][$filterType]['class'];
			
			if (!class_exists($strClass))
			{
				continue;
			}
	    	
	    	$varReturn = $strClass::generateFilter($this->arrCategories, $objTemplate, $this, true);
	    	
	    	if(!empty($varReturn))
	    	{
	    		$arrRedirectParams[] = $varReturn;
	    	}
    	}
    	
    	// Get any previously submitted URL params that aren't updated by this module
    	$arrKeys = array_diff(array_keys($GLOBALS['PRODUCT_FILTERS']), $arrFilterTypes);
    	
    	foreach ($arrKeys as $key)
    	{
	    	if (\Input::get($key))
	    	{
	    		$arrRedirectParams[] = $key . '/' . Filter::uncleanChars(\Input::get($key));
	    	}
    	}
    	
    	$strRedirectParams = implode('/', $arrRedirectParams);
  	
    	$strRedirect = \Controller::generateFrontendUrl($objPage->row(), (strlen($strRedirectParams) ? '/' . $strRedirectParams : null) );
    	
    	\Controller::redirect($strRedirect);
    }


    /**
     * Generate a limit form
     */
    protected function generateLimit()
    {
    }


    /**
     * Generate ajax
     * @return mixed
     */
    public function generateAjax()
    {
        /*$strKeywords = \Input::get('keywords');
        
        if ( (($this->property_searchAutocomplete && \Input::get('autocomplete')) || ($this->property_locationsAutocomplete && \Input::get('locationsautocomplete'))) && (!empty($strKeywords) || !empty($strLocations)) )
        {
	    	\System::loadLanguageFile('tl_property');
    		
            $time = time();
			$arrRegexs = array();
			$arrValues = array();
            $t = Product_Model::getTable();
	    	$arrFields = (array)deserialize(\Input::get('autocomplete') ? $this->property_searchAutocomplete : $this->property_locationsAutocomplete);
            $arrCategories = $this->findCategories($this->property_category_scope);
            
			$arrColumns = array("$t.id IN( SELECT pid FROM tl_property_categories WHERE page_id IN (" . implode(',', $this->arrCategories) . "))");
			
			foreach ($arrFields as $field)
			{
				if ($GLOBALS['TL_DCA']['tl_property']['fields'][$field]['sql'] != 'text NULL' && $GLOBALS['TL_DCA']['tl_property']['fields'][$field]['sql'] != 'blob NULL')
				{
					$arrRegexs[] = "$t." . $field . " REGEXP ?";
					$arrValues[] = \Input::get('autocomplete') ? $strKeywords : $strLocations;
				}
			}
			
			$arrColumns[] = '('. implode(" OR ", $arrRegexs) . ')';
        
	        //Add where statement from module config
	        if ($this->property_list_where != '') {
	            $arrColumns[] = $this->property_list_where;
	        }
			
            $objProperties = Product_Model::findPublishedBy($arrColumns, $arrValues, array('limit'=>300, 'order'=>'tstamp DESC'));
            
            if ($objProperties !== null && $objProperties->count())
            {
            	$arrReturn = array();
            	
            	while ($objProperties->next())
            	{
					foreach ($arrFields as $field)
					{
						if ($GLOBALS['TL_DCA']['tl_property']['fields'][$field]['sql'] != 'text NULL' && $GLOBALS['TL_DCA']['tl_property']['fields'][$field]['sql'] != 'blob NULL')
						{
							$arrReturn[] = $objProperties->current()->{$field};
						}
					}
            	}
				
	            return array_values(array_unique($arrReturn));
            }
        }*/

        return '';
    }
}

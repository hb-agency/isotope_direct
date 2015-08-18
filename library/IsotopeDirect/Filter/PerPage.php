<?php

/**
 * Copyright (C) 2015 Rhyme Digital, LLC
 *
 * @author		Blair Winans <blair@rhyme.digital>
 * @author		Adam Fisher <adam@rhyme.digital>
 * @link		http://rhyme.digital
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace IsotopeDirect\Filter;


/**
 * Class PerPage
 * Per Page filter
 */
class PerPage extends Filter
{
	
	/**
	 * Filter key
	 * @var string
	 */
	protected static $strKey = 'perpage';
	

	/**
     * Add this filter to the module's template or get the URL params
     * @param   array
     * @param   object
     * @param   array
     * @param   object
     * @param   boolean
     * @return  mixed (redirect params or false)
     */
	public static function generateFilter(&$arrCategories, &$objTemplate, $objModule, $blnGenURL=false)
	{        
    	if ($blnGenURL)
    	{
	        $arrLimit   = array_map('intval', trimsplit(',', $objModule->iso_perPage));
	        
	        if (\Input::post(static::$strKey) && in_array(\Input::post(static::$strKey), $arrLimit))
	        {
		    	return static::$strKey . '/' . \Input::post(static::$strKey);
	        }
    		
	    	return false;
    	}

        $objTemplate->hasPerPage = false;
        $arrOptions = array();
        $arrLimit   = array_map('intval', trimsplit(',', $objModule->iso_perPage));
        
        if (!empty($arrLimit))
        {
	        $arrLimit   = array_unique($arrLimit);
	        sort($arrLimit);
	
	        foreach ($arrLimit as $limit) {
	            $arrOptions[] = array
	            (
	                'label'   => $limit,
	                'value'   => $limit,
	                'default' => (\Input::get('perpage') == $limit ? '1' : ''),
	            );
	        }

	        $objTemplate->hasPerPage     = true;
	        $objTemplate->perpageLabel   = $GLOBALS['TL_LANG']['MSC']['perpageFilterLabel'];
	        $objTemplate->perpageOptions = $arrOptions;
        }
	}

}
<?php

/**
 * Copyright (C) 2014 HB Agency
 * 
 * @author		Blair Winans <bwinans@hbagency.com>
 * @author		Adam Fisher <afisher@hbagency.com>
 * @link		http://www.hbagency.com
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace IsotopeDirect\Filter;


/**
 * Class Sorting
 * Sorting filter
 */
class Sorting extends Filter
{
	
	/**
	 * Filter key
	 * @var string
	 */
	protected static $strKey = 'sorting';
	

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
        \System::loadLanguageFile(static::$strProductTable);
        $objModule->loadDataContainer(static::$strProductTable);
        
		$arrFields = deserialize($objModule->iso_sortingFields, true);

    	if($blnGenURL)
    	{
	    	//return the URL fragment needed for this filter to pass to the lister
	    	if(\Input::post(static::$strKey) && in_array(str_replace(array('-asc', '-desc'), '', \Input::post(static::$strKey)), $arrFields))
	    	{
		    	return static::$strKey . '/' . urlencode(\Input::post(static::$strKey));
	    	}
	    	
	    	return false;
    	}
		
    	$arrAvailable = array(''=>$GLOBALS['TL_LANG']['MSC'][static::$strKey.'FilterLabel'] . ' default');
		
		foreach ($arrFields as $field)
		{
			list($asc, $desc) = static::getSortingLabels($field);
			$strLabel = is_array($GLOBALS['TL_DCA'][static::$strProductTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][static::$strProductTable]['fields'][$field]['label'][0] : $field;
			$arrAvailable[$field.'-asc'] = $strLabel . ' ' . $asc;
			$arrAvailable[$field.'-desc'] = $strLabel . ' ' . $desc;
		}
    	
    	if(count($arrAvailable) > 0)
    	{
			$objTemplate->hasSorting = true;
			$objTemplate->sort = $arrAvailable;
			$objTemplate->sortselected = \Input::get(static::$strKey);
			$objTemplate->psortLabel = $GLOBALS['TL_LANG']['MSC'][static::$strKey.'FilterLabel'];
    	}
	}


    /**
     * Get the sorting labels (asc/desc) for an attribute
     * @param string
     * @return array
     */
    protected static function getSortingLabels($field)
    {
        $arrData = $GLOBALS['TL_DCA'][static::$strProductTable]['fields'][$field];

        switch ($arrData['eval']['rgxp'])
        {
            case 'price':
            case 'digit':
                return array($GLOBALS['TL_LANG']['MSC']['low_to_high'], $GLOBALS['TL_LANG']['MSC']['high_to_low']);

            case 'date':
            case 'time':
            case 'datim':
                return array($GLOBALS['TL_LANG']['MSC']['old_to_new'], $GLOBALS['TL_LANG']['MSC']['new_to_old']);
        }

        // !HOOK: custom sorting labels
        if (isset($GLOBALS['ISO_HOOKS']['sortingLabels']) && is_array($GLOBALS['ISO_HOOKS']['sortingLabels']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['sortingLabels'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $varReturn = $objCallback->$callback[1]($field, $arrData, null);
                
                if ($varReturn !== false)
                {
	                return $varReturn;
                }
            }
        }

        return array($GLOBALS['TL_LANG']['MSC']['a_to_z'], $GLOBALS['TL_LANG']['MSC']['z_to_a']);
    }

}